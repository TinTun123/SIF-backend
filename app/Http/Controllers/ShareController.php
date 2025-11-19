<?php

namespace App\Http\Controllers;

use App\Helpers\Crawler;
use App\Models\Article;
use App\Models\Interview;
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
}
