<?php

use App\Http\Controllers\API\PolicyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\VideoController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\MovementController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\SubsessionController;
use App\Models\Subsession;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/videos/upload', [VideoController::class, 'upload']);
Route::get('/videos', [VideoController::class, 'index']);
Route::get('/policies', [PolicyController::class, 'index']);
Route::delete('/policies/{id}', [PolicyController::class, 'destroy']);
Route::get('/policies/{id}', [PolicyController::class, 'show']);
Route::delete('/videos/{id}', [VideoController::class, 'destroy']);
Route::post('/policies', [PolicyController::class, 'store']);
Route::put('/policies/{policy}', [PolicyController::class, 'update']);

Route::prefix('courses')->group(function () {
    Route::get('/', [CourseController::class, 'index']);
    Route::get('/{id}', [CourseController::class, 'get']);
    Route::post('/', [CourseController::class, 'store']);
    Route::put('/{id}', [CourseController::class, 'update']);
    Route::delete('/{id}', [CourseController::class, 'destroy']);
});

// Fetch every session meta data base on courseID without content
Route::get('courses/sessions/{id}', [CourseController::class, 'sessionsData']);

// Fetch one session base on id with content
Route::get('/sessions/{id}', [CourseController::class, 'getSession']);


// Fetch every subsession of particular session including content
Route::get('sessions/all/{id}', [CourseController::class, 'sessionAll']);

Route::prefix('subsessions')->group(function () {
    // Fetch every subsession of particular session including content
    Route::get('all/{id}', [SubsessionController::class, 'subSessionAll']);

    Route::post('/', [SubsessionController::class, 'store']);
    Route::put('/{id}', [SubsessionController::class, 'update']);
});

Route::get('stories/', [StoryController::class, 'index']);
Route::post('stories/', [StoryController::class, 'create']);
Route::put('stories/{story}', [StoryController::class, 'update']);
Route::get('stories/{story}', [StoryController::class, 'getStory']);
Route::delete('stories/{story}', [StoryController::class, 'destroy']);

Route::get('movements/', [MovementController::class, 'index']);
Route::post('movements/', [MovementController::class, 'create']);
Route::put('movements/{movement}', [MovementController::class, 'update']);
Route::get('movements/{movement}', [MovementController::class, 'getStory']);
Route::delete('movements/{movement}', [MovementController::class, 'destroy']);


// Fetch record of single subsession
Route::get('subsessions/{subSessionId}', [SubsessionController::class, 'getSubsession']);

// Fetch a every subsession of certain sessionID without content
Route::get('sessions/subSession/{sessionId}', [SubsessionController::class, 'subsessionData']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
