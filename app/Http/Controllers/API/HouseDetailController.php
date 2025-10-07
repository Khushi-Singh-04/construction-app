<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\House;
use App\Models\HouseDetail;
use Illuminate\Support\Facades\Auth;
use App\Models\HouseDetailCategory;
use App\Models\HouseDetailSubCategory;


class HouseDetailController extends Controller
{
    // Store answers for a house
    public function store(Request $request, $houseId)
{
    $user = Auth::user();

    // Ensure house belongs to user
    $house = House::where('firebase_uid', $user->firebase_uid)->findOrFail($houseId);

    // Basic validation
    $request->validate([
        'details' => 'required|array',
        'details.*.question' => 'required|string',
        'details.*.answer' => 'nullable',
        'details.*.options' => 'nullable|array',
    ]);

    $savedDetails = [];

    foreach ($request->details as $detail) {
        $answerValue = null;
        $type = 'normal';

        // If it's an option-based question
        if (!empty($detail['options'])) {
            // Normalize selected options
            $normalizeOptions = function ($options) use (&$normalizeOptions) {
                $result = [];
                foreach ($options as $opt) {
                    $sub = [];
                    if (!empty($opt['sub_options'])) {
                        $sub = $normalizeOptions($opt['sub_options']);
                    }
                    $result[] = [
                        'option' => $opt['option'] ?? null,
                        'selected' => (bool)($opt['selected'] ?? true),
                        'sub_options' => $sub
                    ];
                }
                return $result;
            };

            $normalized = $normalizeOptions($detail['options']);

            // 🔹 If only one option is allowed (single-select)
            if (isset($detail['single_select']) && $detail['single_select'] === true) {
                // keep only the first selected option
                $selected = collect($normalized)->firstWhere('selected', true);
                $answerValue = $selected ? [$selected] : [];
            } else {
                // Multi-select allowed
                $answerValue = array_values(array_filter($normalized, fn($o) => $o['selected'] ?? false));
            }

            $type = 'option';
        }
        // If it's a normal Q&A
        elseif (array_key_exists('answer', $detail)) {
            $answerValue = $detail['answer'];
            $type = 'normal';
        }

        // Store or update in DB
        $existing = HouseDetail::firstOrNew([
            'house_id' => $house->id,
            'question' => $detail['question']
        ]);

        $existing->answer = json_encode([
            'type' => $type,
            'value' => $answerValue
        ]);
        $existing->save();

        $savedDetails[] = $existing;
    }

    return response()->json([
        'message' => 'Details saved successfully',
        'details' => $savedDetails,
    ], 201);
}



    // Get details for a specific house
    public function index($houseId)
{
    $user = Auth::user();
    $house = House::where('firebase_uid', $user->firebase_uid)->findOrFail($houseId);

    $details = $house->details->map(function ($detail) {
        $decoded = json_decode($detail->answer, true);
        $type = $decoded['type'] ?? 'normal';
        $value = $decoded['value'] ?? null;

        // If option type → ensure proper recursion
        if ($type == 'option' && is_array($value)) {
            $mapOptions = function($options) use (&$mapOptions) {
                $out = [];
                foreach ($options as $opt) {
                    $sub = [];
                    if (!empty($opt['sub_options'])) {
                        $sub = $mapOptions($opt['sub_options']);
                    }
                    $out[] = [
                        'option' => $opt['option'],
                        'selected' => (bool)($opt['selected'] ?? false),
                        'sub_options' => $sub
                    ];
                }
                return $out;
            };
            $value = $mapOptions($value);
        }

        return [
            'id' => $detail->id,
            'question' => $detail->question,
            'type' => $type,
            'answer' => $value,
        ];
    });

    return response()->json($details);
}


    // storeCategoryImages
    public function storeCategoriesAndSubCategories(Request $request, $houseId)
   {
    $user = Auth::user();
    $house = House::where('firebase_uid', $user->firebase_uid)->findOrFail($houseId);

    //  Validate request
    $request->validate([
        'question' => 'required|string', // e.g., "q4: create category & upload reference images"
        'categories' => 'required|array',
        'categories.*.category_name' => 'required|string',
        'categories.*.sub_categories' => 'required|array',
        'categories.*.sub_categories.*.sub_category_name' => 'required|string',
        'categories.*.sub_categories.*.description' => 'nullable|string',
        'categories.*.sub_categories.*.images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
    ]);

    //  Create/find HouseDetail row for this question
    $houseDetail = HouseDetail::firstOrCreate(
        [
            'house_id' => $house->id,
            'question' => $request->question,
        ],
        [
            'answer' => null
        ]
    );

    $savedCategories = [];

    foreach ($request->categories as $categoryData) {
        // Save category
        $category = HouseDetailCategory::create([
            'house_detail_id' => $houseDetail->id,
            'category_name' => $categoryData['category_name']
        ]);

        $savedSubCategories = [];

        foreach ($categoryData['sub_categories'] as $subCategoryData) {
            $imagePaths = [];

            // ✅ Handle image uploads
            if (isset($subCategoryData['images'])) {
                foreach ($subCategoryData['images'] as $image) {
                    $path = $image->store('house_images', 'public');
                    $imagePaths[] = $path;
                }
            }

            // Save sub-category
            $subCategory = $category->subCategories()->create([
                'sub_category_name' => $subCategoryData['sub_category_name'],
                'description' => $subCategoryData['description'] ?? null,
                'images' => $imagePaths,
            ]);

            $savedSubCategories[] = $subCategory;
        }

        $category->setRelation('subCategories', collect($savedSubCategories));
        $savedCategories[] = $category;
    }

    return response()->json([
        'message' => 'Categories & sub-categories saved successfully',
        'question' => $request->question,
        'data' => $savedCategories,
    ], 201);
   }

   //get category - images
   public function getCategoriesAndSubCategories($houseId)
{
    $user = Auth::user();

    $house = House::where('firebase_uid', $user->firebase_uid)
        ->findOrFail($houseId);

    // Eager load categories and sub-categories
    $details = $house->details()->with('categories.subCategories')->get();

    return response()->json([
        'message' => 'Data fetched successfully',
        'house_id' => $houseId,
        'details' => $details
    ], 200);
}

}
