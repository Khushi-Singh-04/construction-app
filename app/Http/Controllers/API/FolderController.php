<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Folder;
use Illuminate\Support\Facades\Auth;

class FolderController extends Controller
{

    /**
     * Create a folder (or sub-folder)
     * Logic:
     * - If parent_id exists → create sub-folder
     * - If parent_id does NOT exist → create as top-level folder
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|integer',
        ]);

        $parentId = $request->parent_id;

        // Check if parent exists
        if ($parentId && Folder::where('id', $parentId)->exists()) {
            // Parent exists → create as sub-folder
            $folder = Folder::create([
                'user_id' => Auth::id(),
                'parent_id' => $parentId,
                'name' => $request->name,
                'description' => $request->description,
            ]);

            return response()->json([
                'message' => 'Sub-folder created successfully',
                'folder' => $folder
            ]);
        }

        // Parent does not exist or parent_id not provided → create top-level folder
        $folder = Folder::create([
            'user_id' => Auth::id(),
            'parent_id' => null,
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'Top-level folder created successfully',
            'folder' => $folder
        ]);
    }

    /**
     * Optional: Show a single folder with its children and documents
     */
    public function index()
    {
        $folders = Folder::with(['children', 'documents'])
            ->where('user_id', Auth::id())
            ->whereNull('parent_id')
            ->get();

        return response()->json([
            'message' => 'Folder fetched successfully',
            'folders' => $folders
        ]);
    }

    /**
     * Optional: Delete a folder (and optionally its sub-folders)
     */
    public function destroy($id)
    {
        $folder = Folder::where('user_id', Auth::id())->findOrFail($id);
        $folder->delete();

        return response()->json([
            'message' => 'Folder deleted successfully'
        ]);
    }
}
