<?php

use App\Http\Controllers\ShareController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// web.php
Route::get('/share/article/{article}', [ShareController::class, 'article']);
Route::get('/share/statement/{statement}', [ShareController::class, 'statement']);
Route::get('/share/poster/{poster}', [ShareController::class, 'poster']);
Route::get('/share/media/{interview}', [ShareController::class, 'media']);

Route::get('/share/music/{music}', [ShareController::class, 'music']);
Route::get('/share/policies/{policy}', [ShareController::class, 'policies']);
Route::get('/share/courses/{course}', [ShareController::class, 'courses']);

Route::get('/share/movement/{movement}', [ShareController::class, 'movement']);
