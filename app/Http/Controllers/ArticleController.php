<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $article = Article::select('id', 'title_eng', 'title_bur', 'cover_url', 'author', 'date', 'description')->get();
        return response()->json([
            'success' => true,
            'articles' => $article,
        ], 200);
    }

    public function getArticle(Request $request, Article $article)
    {
        return response()->json([
            'success' => true,
            'article' => $article,
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        //
        $validated = $request->validate([
            'title_eng' => 'required|string',
            'title_bur' => 'required|string',
            'cover_url' => 'required|file|mimes:jpg,jpeg,png,webp,svg',
            'author' => 'required|string',
            'description' => 'required|string',
            'date' => 'required|date|before_or_equal:today',
            'content_eng' => 'nullable|string',
            'content_bur' => 'nullable|string',
        ]);

        $coverUrl = null;

        // Handle file upload
        if ($request->hasFile('cover_url')) {
            $file = $request->file('cover_url');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/article', $filename); // stored in storage/app/public/covers

            // Force permission for web access
            chmod(storage_path('app/public/article/' . $filename), 0644);


            $coverUrl = Storage::url($path); // generates /storage/covers/xxxx.jpg
            // Optional: full URL if needed
            $coverUrl = asset($coverUrl);
        }


        $article = Article::create([
            'title_eng' => $validated['title_eng'],
            'title_bur' => $validated['title_bur'],
            'author' => $validated['author'],
            'description' => $validated['description'],
            'date' => $validated['date'],
            'content_eng' => $validated['content_eng'] ?? '',
            'content_bur' => $validated['content_bur'] ?? '',
            'cover_url' => $coverUrl,
        ]);

        return response()->json([
            'success' => true,
            'article' => $article,
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Article $article)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Article $article)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Article $article)
    {
        //
        $validated = $request->validate([
            'title_eng' => 'required|string',
            'title_bur' => 'required|string',
            'author' => 'required|string',
            'description' => 'required|string',
            'date' => 'required|date|before_or_equal:today',
            'cover_url' => 'nullable|file|mimes:jpg,jpeg,png,webp,svg',
            'content_eng' => 'nullable|string',
            'content_bur' => 'nullable|string',
        ]);

        $coverUrl = null;

        // Handle file upload
        if ($request->hasFile('cover_url')) {

            if ($article->cover_url) {
                $oldPath = str_replace('/storage/', 'article/', $article->cover_url);
                if (Storage::exists($oldPath)) {
                    Storage::delete($oldPath);
                }
            }

            $coverUrl = null;
            // Store new image


            $file = $request->file('cover_url');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/article', $filename); // stored in storage/app/public/covers

            // Force permission for web access
            chmod(storage_path('app/public/article/' . $filename), 0644);

            $coverUrl = Storage::url($path); // generates /storage/covers/xxxx.jpg
            // Optional: full URL if needed
            $coverUrl = asset($coverUrl);
        }


        $article->update([
            'title_eng' => $validated['title_eng'],
            'title_bur' => $validated['title_bur'],
            'author' => $validated['author'],
            'date' => $validated['date'],
            'description' => $validated['description'],
            'content_eng' => $validated['content_eng'] ?? $article->content_eng,
            'content_bur' => $validated['content_bur'] ?? $article->content_bur,
            'cover_url' => $coverUrl ?? $article->cover_url,
        ]);

        return response()->json([
            'success' => true,
            'article' => $article,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Article $article)
    {
        // Delete cover image if exists
        if ($article->cover_url) {
            $coverPath = str_replace(asset('/storage/'), '', $article->cover_url);
            Storage::disk('public')->delete($coverPath);
        }

        // Delete course
        $article->delete();

        return response()->json([
            'success' => true,
            'message' => 'Article have been deleted.',
        ], 200);
    }
}
