<?php

namespace App\Http\Controllers;

use App\Helpers\Crawler;
use App\Models\Article;
use App\Models\Statement;
use Illuminate\Http\Request;

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
}
