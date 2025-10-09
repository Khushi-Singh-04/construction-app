<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DailyProgressCategory;
use App\Models\DailyProgressWorkerImage;
use App\Models\DailyProgressWorker;
use App\Models\House;

class DailyProgressController extends Controller
{
    // Fetch all daily progress categories for a house (client overview)
    public function index($houseId)
    {
        $house = House::find($houseId);

        if (!$house) {
            return response()->json(['message' => 'House not found'], 404);
        }

        // Fetch categories with only cover image
        $categories = DailyProgressCategory::with(['works'])
            ->where('house_id', $houseId)
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'created_at' => $category->created_at,
                    'cover_image' => $category->coverImage,
                ];
            });

        // Prepare user info
        $user = auth()->user();

        return response()->json([
            'message' => 'Daily progress fetched successfully',
            'data' => [
                'user' => [
                    'name' => $user->name,
                    'image' => $user->image,
                    'first_upload_date' => DailyProgressCategory::where('house_id', $houseId)
                        ->orderBy('created_at', 'asc')
                        ->value('created_at'),
                ],
                'categories' => $categories
            ]
        ]);
    }
    // Fetch all works for a specific category (only work names and ids)
    public function show($houseId, $categoryId)
    {
        $category = DailyProgressCategory::find($categoryId);

        if (!$category || $category->house_id != $houseId) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        // Return only the list of works: id and name
        $works = $category->works()->get(['id', 'name']);

        return response()->json([
            'message' => 'Works fetched successfully',
            'data' => $works
        ]);
    }
}
