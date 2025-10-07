<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Upload multiple documents/images to a folder
     */
    public function store(Request $request)
    {
        // Validate request
        $request->validate([
            'folder_id' => 'required|exists:folders,id',
            'title' => 'required|string|max:255',
            'files' => 'required',
            'files.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx',
        ]);

        $files = $request->file('files');

        if (!$files || count($files) === 0) {
            return response()->json([
                'message' => 'No files uploaded'
            ], 422);
        }

        $uploadedDocs = [];

        foreach ($files as $file) {
            if (!$file) continue; // Skip null files

            $path = $file->store('documents', 'public');

            $doc = Document::create([
                'folder_id' => $request->folder_id,
                'title' => $request->title,
                'file_path' => $path,
                'type' => in_array(strtolower($file->extension()), ['jpg','jpeg','png']) ? 'image' : 'document',
            ]);

            $uploadedDocs[] = $doc;
        }

        return response()->json([
            'message' => 'Documents uploaded successfully',
            'documents' => $uploadedDocs
        ]);
    }

    /**
     * List all documents in a folder
     */
    public function index($folderId)
    {
        $documents = Document::where('folder_id', $folderId)->get();

        return response()->json([
            'message' => 'Documents fetched successfully',
            'documents' => $documents
        ]);
    }
}
