<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Firebase Credentials
    |--------------------------------------------------------------------------
    |
    | Absolute path to the Firebase service account JSON file.
    | You can generate this from your Firebase Console:
    | Settings (⚙️) → Project Settings → Service Accounts → Generate new private key.
    |
    */

    'credentials' => [
        'file' => env('FIREBASE_CREDENTIALS_PATH'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Firebase Project Information
    |--------------------------------------------------------------------------
    |
    | This helps in validating tokens properly.
    | The project_id must match the one in your Firebase config (frontend)
    | and also in the service account JSON.
    |
    */

    'project_id' => env('FIREBASE_PROJECT_ID'),

    /*
    |--------------------------------------------------------------------------
    | Database URL (optional)
    |--------------------------------------------------------------------------
    |
    | Only required if you use Firebase Realtime Database.
    |
    */

    'database' => [
        'url' => env('FIREBASE_DATABASE_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Bucket (optional)
    |--------------------------------------------------------------------------
    |
    | Only required if you use Firebase Storage.
    |
    */

    'storage' => [
        'default_bucket' => env('FIREBASE_STORAGE_BUCKET'),
    ],
];
