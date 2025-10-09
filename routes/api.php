<?php

use App\Http\Controllers\API\PolicyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\VideoController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\SubsessionController;

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

Route::get('courses/sessions/{id}', [CourseController::class, 'sessionsData']);
Route::get('/sessions/{id}', [CourseController::class, 'getSession']);

Route::prefix('subsessions')->group(function () {

    Route::post('/', [SubsessionController::class, 'store']);
    Route::put('/{id}', [SubsessionController::class, 'update']);
});

Route::get('subsessions/{subSessionId}', [SubsessionController::class, 'getSubsession']);
Route::get('sessions/subSession/{sessionId}', [SubsessionController::class, 'subsessionData']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
