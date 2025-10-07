<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // Get Profile
    public function getProfile()
    {
        return response()->json([
            'message' => 'Profile fetched successfully',
            'data' => Auth::user()
        ]);
    }

    // Complete Profile (for new users)
    public function completeProfile(Request $request)
    {
        $user = Auth::user();

        if (!empty($user->name) && !empty($user->profession) && !empty($user->image)) {
            return response()->json(['message' => 'Profile already completed'], 400);
        }

        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'profession' => 'required|string|max:255',
            'image'      => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $path = $request->file('image')->store('profile_images', 'public');

        $user->update([
            'name'       => $validated['name'],
            'profession' => $validated['profession'],
            'image'      => '/storage/' . $path,
        ]);

        return response()->json([
            'message' => 'Profile completed successfully',
            'data'    => $user
        ]);
    }

    // Edit Profile (optional fields)
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'full_name'   => 'nullable|string|max:255',
            'phone_no'    => 'nullable|string|max:20',
            'dob'         => 'nullable|date',
            'city'        => 'nullable|string|max:255',
            'state'       => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'profession'  => 'nullable|string|max:255',
            'name'        => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = '/storage/' . $request->file('image')->store('profile_images', 'public');
        }

        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully',
            'data' => $user
        ]);
    }
}
