<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\House;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class HouseController extends Controller
{
    // Use Passport auth middleware for all routes
    public function __construct() {
        $this->middleware('auth:api');
    }

    // 1 Add a new house
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'type' => 'required|string',
            'stage' => 'required|string',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'area' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Store image in storage/app/public/houses
        $imagePath = $request->file('image')->store('houses', 'public');

        // Create house entry linked to authenticated user
        $house = House::create([
            'firebase_uid' => Auth::user()->firebase_uid, // save Firebase UID from user model
            'title' => $request->title,
            'image' => $imagePath,
            'type' => $request->type,
            'stage' => $request->stage,
            'length' => $request->length,
            'width' => $request->width,
            'area' => $request->area,
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'House added successfully',
            'house' => $house
        ], 201);
    }

    // 2 List all houses for this user
    public function index() {
        $user = Auth::user();
        $houses = House::where('firebase_uid', $user->firebase_uid)->get();
        return response()->json($houses);
    }

    // 3 Show a single house
    public function show($id) {
        $user = Auth::user();
        $house = House::where('firebase_uid', $user->firebase_uid)->findOrFail($id);
        return response()->json($house);
    }

    // 4️ Update a house
    public function update(Request $request, $id) {
        $user = Auth::user();
        $house = House::where('firebase_uid', $user->firebase_uid)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'type' => 'sometimes|string',
            'stage' => 'sometimes|string',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'area' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->hasFile('image')) {
            // Delete old image
            if ($house->image) {
                Storage::disk('public')->delete($house->image);
            }
            $house->image = $request->file('image')->store('houses', 'public');
        }

        $house->fill($request->only(['title','type','stage','length','width','area','description']));
        $house->save();

        return response()->json([
            'message' => 'House updated successfully',
            'house' => $house
        ]);
    }

    // 5️ Delete a house
    public function destroy($id) {
        $user = Auth::user();
        $house = House::where('firebase_uid', $user->firebase_uid)->findOrFail($id);

        if ($house->image) {
            Storage::disk('public')->delete($house->image);
        }

        $house->delete();

        return response()->json(['message' => 'House deleted successfully']);
    }
}
