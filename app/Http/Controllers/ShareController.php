<?php

namespace App\Http\Controllers;

use App\Helpers\Crawler;
use App\Models\Article;
use App\Models\Course;
use App\Models\Interview;
use App\Models\Music;
use App\Models\Policy;
use App\Models\Poster;
use App\Models\Statement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShareController extends Controller
{
    //
    public function article(Request $request, Article $article)
    {

        $userAgent = $request->header('User-Agent');

        // If NOT crawler → redirect user to the frontend SPA
        if (!Crawler::isCrawler($userAgent)) {
            return redirect()->to(config('services.frontend.url') . "viewArticle/" . $article->id);
        }

        // If crawler → return OG meta blade view
        return response()->view('share.article', [
            'article' => $article
        ]);
    }

    public function statement(Request $request, Statement $statement)
    {

        $userAgent = $request->header('User-Agent');

        // If NOT crawler → redirect user to the frontend SPA
        if (!Crawler::isCrawler($userAgent)) {
            return redirect()->to(config('services.frontend.url') . "statements/");
        }

        // If crawler → return OG meta blade view
        return response()->view('share.statement', [
            'statement' => $statement
        ]);
    }

    public function poster(Request $request, Poster $poster)
    {

        $userAgent = $request->header('User-Agent');

        // If NOT crawler → redirect user to the frontend SPA
        if (!Crawler::isCrawler($userAgent)) {
            return redirect()->to(config('services.frontend.url') . "Posters/");
        }

        // If crawler → return OG meta blade view
        return response()->view('share.poster', [
            'poster' => $poster
        ]);
    }

    public function media(Request $request, Interview $interview)
    {

        $userAgent = $request->header('User-Agent');

        // If NOT crawler → redirect user to the frontend SPA
        if (!Crawler::isCrawler($userAgent)) {
            return redirect()->to(config('services.frontend.url') . "Media/");
        }
        Log::info('Interview : ', [$interview]);

        // If crawler → return OG meta blade view
        return response()->view('share.media', [
            'interview' => $interview
        ]);
    }

    public function music(Request $request, Music $music)
    {

        $userAgent = $request->header('User-Agent');

        // If NOT crawler → redirect user to the frontend SPA
        if (!Crawler::isCrawler($userAgent)) {
            return redirect()->to(config('services.frontend.url') . "ArtMovements/Musics");
        }
        Log::info("Music : ", [$music]);
        // If crawler → return OG meta blade view
        return response()->view('share.music', [
            'music' => $music
        ]);
    }

    public function policies(Request $request, Policy $policy)
    {

        $userAgent = $request->header('User-Agent');

        // If NOT crawler → redirect user to the frontend SPA
        if (!Crawler::isCrawler($userAgent)) {
            return redirect()->to(config('services.frontend.url') . "policies/$policy->id");
        }
        Log::info("policy : ", [$policy]);
        // If crawler → return OG meta blade view
        return response()->view('share.policy', [
            'policy' => $policy
        ]);
    }

    public function course(Request $request, Course $course)
    {

        $userAgent = $request->header('User-Agent');

        // If NOT crawler → redirect user to the frontend SPA
        if (!Crawler::isCrawler($userAgent)) {
            return redirect()->to(config('services.frontend.url') . "course/$course->id");
        }
        Log::info("course : ", [$course]);
        // If crawler → return OG meta blade view
        return response()->view('share.course', [
            'course' => $course
        ]);
    }
}
