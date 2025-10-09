<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\API\HouseController;
use App\Http\Controllers\API\HouseDetailController;
use App\Http\Controllers\API\FolderController;
use App\Http\Controllers\API\DocumentController;
use App\Http\Controllers\API\IdeaBookController;
use App\Http\Controllers\API\DailyProgressController;
use App\Http\Controllers\API\DailyProgressWorkController;


Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:api')->group(function () {
    Route::post('/user/complete-profile', [UserController::class, 'completeProfile']);
    Route::get('/user/profile', [UserController::class, 'getProfile']);
    Route::put('/user/profile', [UserController::class, 'updateProfile']);
    Route::post('/user/profile/update', [UserController::class, 'updateProfile']);
});

Route::middleware('auth:api')->group(function () {
    Route::post('/houses', [HouseController::class, 'store']);       // Add house
    Route::get('/houses', [HouseController::class, 'index']);        // List houses
    Route::get('/houses/{id}', [HouseController::class, 'show']);    // show Single house
    Route::put('/houses/{id}', [HouseController::class, 'update']);  // Update house
    Route::delete('/houses/{id}', [HouseController::class, 'destroy']); // Delete house
});

Route::middleware('auth:api')->group(function () {
    Route::post('/houses/{houseId}/details', [HouseDetailController::class, 'store']); //add details (Q/A)
    Route::get('/houses/{houseId}/details', [HouseDetailController::class, 'index']);  //get details (Q/A)
});


Route::middleware('auth:api')->group(function () {
// POST: Save categories & subcategories
Route::post('/houses/{houseId}/categories-subcategories', [HouseDetailController::class, 'storeCategoriesAndSubCategories']);

// GET: Retrieve categories & subcategories for a specific question
Route::get('/houses/{houseId}/categories-subcategories', [HouseDetailController::class, 'getCategoriesAndSubCategories']);
});

Route::middleware('auth:api')->group(function () {
    // Folder routes
    Route::post('/folders', [FolderController::class, 'store']);
    Route::get('/folders', [FolderController::class, 'index']);

    // Document routes
    Route::post('/documents', [DocumentController::class, 'store']);
    Route::get('/folders/{id}/documents', [DocumentController::class, 'index']);
});

Route::middleware('auth:api')->group(function () {
    Route::post('/idea-books', [IdeaBookController::class, 'store']); //create ideabook
    Route::get('/idea-books', [IdeaBookController::class, 'index']);  //fetch idea book 

    Route::post('/idea-books/{id}/upload-my-ideas', [IdeaBookController::class, 'uploadMyIdeas']); //upload ideas from gallery
    Route::get('/suggestions/{ideaBookTitle?}', [IdeaBookController::class, 'getSuggestions']);// get suggestions from backend
    Route::get('suggestion-details/{id}', [IdeaBookController::class, 'suggestionDetails']); // get user details whose img you are viewing
    Route::post('/idea-books/{id}/save-suggestions', [IdeaBookController::class, 'saveSuggestions']); // save idea from suggestions
});

Route::middleware('auth:api')->group(function () {
    // Daily Progress routes
    Route::get('/houses/{houseId}/daily-progress', [DailyProgressController::class, 'index']); // user view category list + cover img
    Route::get('/houses/{houseId}/daily-progress/categories/{categoryId}', [DailyProgressController::class, 'show']); // list works of a category
    Route::get('/daily-progress/works/{workId}/workers', [DailyProgressWorkController::class, 'listWorkers']); // list workers of a work
    Route::get('/daily-progress/works/{workId}/workers/{workerId}', [DailyProgressWorkController::class, 'workerWorkDetails']); // details of work from a worker
});