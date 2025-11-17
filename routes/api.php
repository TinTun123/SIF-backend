<?php

use App\Http\Controllers\API\PolicyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\VideoController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ComicsController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EpisodeController;
use App\Http\Controllers\InterviewController;
use App\Http\Controllers\MovementController;
use App\Http\Controllers\MusicController;
use App\Http\Controllers\PodcastController;
use App\Http\Controllers\PoemController;
use App\Http\Controllers\PosterController;
use App\Http\Controllers\StatementController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\SubsessionController;
use App\Models\Subsession;
use App\Models\User;

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


Route::get('/videos', [VideoController::class, 'index']);
Route::get('/policies', [PolicyController::class, 'index']);
Route::get('/policies/{id}', [PolicyController::class, 'show']);
Route::post('policies/delta-sync', [PolicyController::class, 'deltaSync']);

Route::prefix('courses')->group(function () {
    Route::get('/', [CourseController::class, 'index']);
    Route::post('/delta-sync', [CourseController::class, 'deltaSync']);
    Route::get('/{id}', [CourseController::class, 'get']);
});

// Fetch every session meta data base on courseID without content
Route::get('courses/sessions/{id}', [CourseController::class, 'sessionsData']);

// Fetch one session base on id with content
Route::get('/sessions/{id}', [CourseController::class, 'getSession']);

// Data sync sessions
Route::post('sessions/delta-sync', [CourseController::class, 'deltaSyncSession']);

// Fetch every session of particular course including content
Route::get('sessions/all/{id}', [CourseController::class, 'sessionAll']);

// Fetch every subsession of particular session including content
Route::get('subsessions/all/{id}', [SubsessionController::class, 'subSessionAll']);

// Data sync Subsessions
Route::post('subsessions/delta-sync', [SubsessionController::class, 'deltaSync']);

// Fetch record of single subsession
Route::get('subsessions/{subSessionId}', [SubsessionController::class, 'getSubsession']);

// Fetch a every subsession of certain sessionID without content
Route::get('sessions/subSession/{sessionId}', [SubsessionController::class, 'subsessionData']);

Route::get('stories/', [StoryController::class, 'index']);
Route::get('stories/{story}', [StoryController::class, 'getStory']);

Route::get('movements/', [MovementController::class, 'index']);
Route::get('movements/{movement}', [MovementController::class, 'getStory']);

Route::get('articles/', [ArticleController::class, 'index']);
Route::get('articles/{article}', [ArticleController::class, 'getArticle']);

Route::get('poems/', [PoemController::class, 'index']);
Route::get('poems/{poem}', [PoemController::class, 'getPoem']);


Route::get('musics/', [MusicController::class, 'index']);
Route::get('musics/{music}', [MusicController::class, 'getMusic']);
Route::post('musics/delta-sync', [MusicController::class, 'deltaSync']);

Route::get('comics/', [ComicsController::class, 'index']);
Route::get('comics/{comic}', [ComicsController::class, 'getComics']);


Route::get('statements/', [StatementController::class, 'index']);
Route::get('statements/{statement}', [StatementController::class, 'getStatement']);
Route::post('statements/delta-sync', [StatementController::class, 'deltaSync']);

Route::get('posters/', [PosterController::class, 'index']);
Route::get('posters/{poster}', [PosterController::class, 'getPoster']);

Route::get('interviews/', [InterviewController::class, 'index']);
Route::get('interviews/{interview}', [InterviewController::class, 'getInterview']);
Route::post('interviews/delta-sync', [InterviewController::class, 'deltaSync']);

Route::get('podcasts/', [PodcastController::class, 'index']);
Route::get('podcasts/{podcast}', [PodcastController::class, 'getPodcast']);

Route::get('episodes/', [EpisodeController::class, 'index']);
Route::get('episodes/{podcast}', [EpisodeController::class, 'getEpisodes']);


Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // Video
    Route::delete('/videos/{id}', [VideoController::class, 'destroy']);
    Route::post('/videos/upload', [VideoController::class, 'upload']);

    // Policy
    Route::delete('/policies/{id}', [PolicyController::class, 'destroy']);
    Route::post('/policies', [PolicyController::class, 'store']);
    Route::put('/policies/{policy}', [PolicyController::class, 'update']);

    // Course
    Route::post('courses/', [CourseController::class, 'store']);
    Route::put('courses/{id}', [CourseController::class, 'update']);
    Route::delete('courses/{id}', [CourseController::class, 'destroy']);

    Route::delete('sessions/{session}', [CourseController::class, 'deleteSession']);

    // Sub session
    Route::post('subsessions/', [SubsessionController::class, 'store']);
    Route::put('subsessions/{id}', [SubsessionController::class, 'update']);
    Route::delete('subsessions/{subsession}', [SubsessionController::class, 'destroy']);

    // Stories
    Route::post('stories/', [StoryController::class, 'create']);
    Route::put('stories/{story}', [StoryController::class, 'update']);
    Route::delete('stories/{story}', [StoryController::class, 'destroy']);

    // Movement
    Route::delete('movements/{movement}', [MovementController::class, 'destroy']);
    Route::post('movements/', [MovementController::class, 'create']);
    Route::put('movements/{movement}', [MovementController::class, 'update']);

    // Article
    Route::post('articles/', [ArticleController::class, 'create']);
    Route::put('articles/{article}', [ArticleController::class, 'update']);
    Route::delete('articles/{article}', [ArticleController::class, 'destroy']);

    // Poem
    Route::post('poems/', [PoemController::class, 'create']);
    Route::put('poems/{poem}', [PoemController::class, 'update']);
    Route::delete('poems/{poem}', [PoemController::class, 'destroy']);

    // Music
    Route::post('musics/', [MusicController::class, 'create']);
    Route::put('musics/{music}', [MusicController::class, 'update']);
    Route::delete('musics/{music}', [MusicController::class, 'destroy']);

    // Comic
    Route::post('comics/', [ComicsController::class, 'create']);
    Route::put('comics/{comic}', [ComicsController::class, 'update']);
    Route::delete('comics/{comic}', [ComicsController::class, 'destroy']);

    // Statement
    Route::post('statements/', [StatementController::class, 'create']);
    Route::put('statements/{statement}', [StatementController::class, 'update']);
    Route::delete('statements/{statement}', [StatementController::class, 'destroy']);

    // Poster
    Route::post('posters/', [PosterController::class, 'create']);
    Route::put('posters/{poster}', [PosterController::class, 'update']);
    Route::delete('posters/{poster}', [PosterController::class, 'destroy']);

    // Interview route
    Route::post('interviews/', [InterviewController::class, 'create']);
    Route::put('interviews/{interview}', [InterviewController::class, 'update']);
    Route::delete('interviews/{interview}', [InterviewController::class, 'destroy']);

    // Podcast
    Route::post('podcasts/', [PodcastController::class, 'create']);
    Route::put('podcasts/{podcast}', [PodcastController::class, 'update']);
    Route::delete('podcasts/{podcast}', [PodcastController::class, 'destroy']);

    // Episode routes
    Route::post('podcast/{podcast}/episodes', [EpisodeController::class, 'create']);
    Route::put('podcast/episodes/{episode}', [EpisodeController::class, 'update']);
    Route::delete('episodes/{episode}', [EpisodeController::class, 'destroy']);


    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/user/{user}', [AuthController::class, 'update']);
    Route::delete('/user/{user}', [AuthController::class, 'destroy']);

    Route::post('/register', [AuthController::class, 'register']);
});



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->get('/users', function (Request $request) {
    return User::all();
});
