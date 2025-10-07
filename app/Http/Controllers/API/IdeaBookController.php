<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IdeaBook;
use App\Models\Idea;
use App\Models\Suggestion;
use Illuminate\Support\Facades\Storage;

class IdeaBookController extends Controller
{
    // Create Idea Book
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'house_id' => 'required|exists:houses,id',
        ]);

        $ideaBook = IdeaBook::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'description' => $request->description,
            'house_id' => (int)$request->house_id,
        ]);

        return response()->json([
            'message' => 'Idea book created successfully',
            'data' => $ideaBook
        ]);
    }

    // Get all idea books of logged-in user
    public function index()
    {
    // Fetch all idea books of the user, along with house and ideas
    $ideaBooks = IdeaBook::where('user_id', auth()->id())
        ->with(['house', 'ideas'])
        ->get();

    // Normalize image paths for each idea using your helper function
    $ideaBooks->each(function($book) {
        $book->ideas->each(function($idea) {
            $idea->image_url = $this->normalizeImagePath($idea->image_path);
            unset($idea->image_path);
        });
    });

    // Group by house title (or Default House if missing)
    $grouped = $ideaBooks->groupBy(function($book) {
        return $book->house->title ?? 'Default House';
    })->map(function($books) {
        // Keep all ideas under their book, even if empty
        return $books->map(function($book) {
            return [
                'idea_book_id' => $book->id,
                'name' => $book->name,
                'description' => $book->description,
                'ideas' => $book->ideas->values() // array of ideas
            ];
        })->values();
    });

    return response()->json([
        'message' => 'Idea books fetched successfully',
        'data' => $grouped
    ]);
    }


    // Upload "My Idea" image
    public function uploadMyIdeas(Request $request, $ideaBookId)
{
    $request->validate([
        'images' => 'required|array',
        'images.*' => 'image|mimes:jpg,jpeg,png|max:2048',
        'notes' => 'nullable|array',
        'notes.*' => 'nullable|string|max:1000',
    ]);

    $ideaBook = IdeaBook::where('id', $ideaBookId)
                        ->where('user_id', auth()->id())
                        ->firstOrFail();

    $ideas = [];
    $notes = $request->input('notes', []);

    foreach ($request->file('images') as $index => $image) {
        // Generate safe filename without spaces
        $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $image->getClientOriginalName());
        $filename = time().'_'.uniqid().'_'.$safeName;
        
        // DEBUG: Check where we're storing
        \Log::info('Storing file:', [
            'original_name' => $image->getClientOriginalName(),
            'safe_name' => $safeName,
            'filename' => $filename
        ]);

        // Store using the public disk (this should create in storage/app/public/ideas/)
        $path = $image->storeAs('ideas', $filename, 'public');

        // DEBUG: Verify file was stored
        $fullPath = storage_path('app/public/' . $path);
        \Log::info('File storage verification:', [
            'path' => $path,
            'full_path' => $fullPath,
            'file_exists' => file_exists($fullPath),
            'file_size' => file_exists($fullPath) ? filesize($fullPath) : 0
        ]);

        $note = $notes[$index] ?? null;

        $idea = Idea::create([
            'idea_book_id' => $ideaBook->id,
            'user_id' => auth()->id(),
            'type' => 'my_idea',
            'image_path' => $path, 
            'is_approved' => false,
            'notes' => $note,
        ]);

        $idea->load('ideaBook');
        $mapped = $this->mapIdea($idea);
        $ideas[] = $mapped;
    }

    return response()->json([
        'message' => 'Image uploaded successfully (pending admin approval)',
        'data' => $ideas
    ]);
}

    //  Unified Suggestions (all OR by category)
    public function getSuggestions($ideaBookTitle = null)
    {
        if ($ideaBookTitle) {
            $category = strtolower($ideaBookTitle);

            // 1. Admin-provided global suggestions
            $adminSuggestions = Suggestion::where('category', $category)->get()
                ->map(fn($s) => $this->mapSuggestion($s));

            // 2. Approved user uploads in this category
            $approvedUserIdeas = Idea::where('type', 'my_idea')
                ->where('is_approved', true)
                ->whereHas('ideaBook', fn($q) => $q->where('name', $category))
                ->get()
                ->map(fn($i) => $this->mapIdea($i));

            // 3. Current user’s own unapproved uploads
            $userOwnUnapproved = Idea::where('type', 'my_idea')
                ->where('is_approved', false)
                ->where('user_id', auth()->id())
                ->whereHas('ideaBook', fn($q) => $q->where('name', $category))
                ->get()
                ->map(fn($i) => $this->mapIdea($i));

            return response()->json([
                'message' => "Suggestions for category [$category] fetched successfully",
                'data' => [
                    'admin_suggestions' => $adminSuggestions,
                    'approved_user_ideas' => $approvedUserIdeas,
                    'my_pending_ideas' => $userOwnUnapproved
                ]
            ]);
        }

        //  No category → return all suggestions (global feed)
        $allSuggestions = Suggestion::all()->map(fn($s) => $this->mapSuggestion($s));

        return response()->json([
            'message' => 'All suggestions fetched successfully',
            'data' => $allSuggestions
        ]);
    }

    // Fetch suggestion details
    public function suggestionDetails($ideaId)
    {
        $idea = Idea::where('id', $ideaId)
        ->where('type', 'suggestion')
        ->with(['ideaBook.house', 'user'])
        ->firstOrFail();

        $user = $idea->user;
        $selectedImage = $this->normalizeImagePath($idea->image_url);

        $houseId = $idea->ideaBook->house_id;

        $projectIdeas = Idea::where('user_id', $user->id)
        ->whereHas('ideaBook', function($q) use ($houseId) {
            $q->where('house_id', $houseId);
        })
        ->with(['ideaBook.house'])
        ->get();

        $projects = $projectIdeas->groupBy(function ($i) {
        return $i->ideaBook->house->title ?? 'Default Project';
        })->map(function ($ideasInHouse) {
        return $ideasInHouse->map(fn($i) => [
            'id' => $i->id,
            'idea_book_id' => $i->idea_book_id,
            'idea_book_name' => $i->ideaBook->name ?? null,
            'type' => $i->type,
            'image_url' => $this->normalizeImagePath($i->image_url),
            'is_approved' => $i->is_approved,
            'uploaded_at' => $i->created_at,
        ])->values();
        });

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'joined_at' => $user->created_at,
            ],
            'selected_image' => $selectedImage,
            'uploaded_at' => $idea->created_at,
            'projects' => $projects,
        ]);
    }

    // Save "Idea Suggestion" image
    public function saveSuggestions(Request $request, $ideaBookId)
    {
        $request->validate([
            'image_ids' => 'required|array',
            'image_ids.*' => 'integer|exists:suggestions,id',
            'notes' => 'nullable|array',
            'notes.*' => 'nullable|string|max:1000'
        ]);

        $ideaBook = IdeaBook::where('id', $ideaBookId)
                            ->where('user_id', auth()->id())
                            ->firstOrFail();

        $ideas = [];
        $notes = $request->input('notes', []); 

        foreach ($request->image_ids as $imageId) {
            $suggestion = Suggestion::findOrFail($imageId);

            $note = $notes[$imageId] ?? $notes[(string)$imageId] ?? null;

            $idea = Idea::create([
                'idea_book_id' => $ideaBook->id,
                'user_id' => auth()->id(),
                'type' => 'suggestion',
                'image_path' => $suggestion->image_url,
                'is_approved' => true,
                'notes' => $note ,
            ]);

            // Map idea for response
            $mapped = $this->mapIdea($idea);
            $ideas[] = $mapped;
        }

        return response()->json([
            'message' => 'Suggestion(s) added successfully',
            'data' => $ideas
        ]);
    }

    //Helpers
    private function normalizeImagePath($path)
{
    if (!$path) return null;

    // Already a full URL (external suggestions)
    if (filter_var($path, FILTER_VALIDATE_URL)) return $path;

    // For local storage - ensure we're using the correct path
    // The path should be like 'ideas/filename.png' (no 'public/' prefix)
    $path = str_replace('public/', '', $path);
    
    return asset('storage/' . $path);
}

    private function mapSuggestion($suggestion)
    {
        $suggestion->image_url = $this->normalizeImagePath($suggestion->image_url ?? $suggestion->image_path);
        unset($suggestion->image_path);
        return $suggestion;
    }

    private function mapIdea($idea)
    {
        $idea->image_url = $this->normalizeImagePath($idea->image_path);
        unset($idea->image_path);
        $idea->idea_book_name = $idea->ideaBook->name ?? null;
        $idea->notes = $idea->notes ?? null;
        return $idea;
    }
}
