<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;

class FirebaseService
{
    protected $auth;

    public function __construct()
    {
        // Get the service account path from config
        $serviceAccountPath = config('firebase.credentials.file');

        if (!$serviceAccountPath || !file_exists($serviceAccountPath)) {
            throw new \Exception("Firebase Service Account JSON not found at: $serviceAccountPath");
        }

        $factory = (new Factory)->withServiceAccount($serviceAccountPath);

        $this->auth = $factory->createAuth();
    }

    public function getAuth(): Auth
    {
        return $this->auth;
    }

    /**
     * Verify Firebase ID Token
     */
    public function verifyIdToken(string $idToken)
    {
        try {
            return $this->auth->verifyIdToken($idToken);
        } catch (FailedToVerifyToken $e) {
            throw new \Exception('Invalid or expired Firebase token: ' . $e->getMessage());
        }
    }
}
