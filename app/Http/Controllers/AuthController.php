<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Services\FirebaseService;

class AuthController extends Controller
{
    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    /**
     * Login with Firebase ID token
     */
    public function login(Request $request)
    {
        $idToken = $request->bearerToken();

        if (!$idToken) {
            return response()->json(['error' => 'No ID token provided'], 401);
        }

        try {
            $verifiedIdToken = $this->firebase->verifyIdToken($idToken);
            $uid = $verifiedIdToken->claims()->get('sub');
            $email = $verifiedIdToken->claims()->get('email');

            // Find or create user with firebase_uid + email
            $user = User::firstOrCreate(
                ['firebase_uid' => $uid],
                ['email' => $email]
            );

            // Create Passport Token
            $token = $user->createToken('firebase-login')->accessToken;

            return response()->json([
                'access_token' => $token,
                'token_type'   => 'Bearer',
                'user' => [
                    'id'           => $user->id,
                    'firebase_uid' => $user->firebase_uid,
                    'email'        => $user->email,
                    'name'         => $user->name,
                ],
                'is_profile_complete' => $this->isProfileComplete($user),
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid token', 'message' => $e->getMessage()], 401);
        }
    }

    private function isProfileComplete(User $user)
    {
        return !empty($user->name) && !empty($user->profession) && !empty($user->image);
    }
}
