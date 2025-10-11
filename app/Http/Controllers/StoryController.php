<?php

namespace App\Http\Controllers;

use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $stories = Story::select('id', 'title_eng', 'title_bur', 'cover_url')->get();
        return response()->json([
            'success' => true,
            'stories' => $stories,
        ], 200);
    }

    public function getStory(Request $request, Story $story)
    {
        return response()->json([
            'success' => true,
            'story' => $story,
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
            'content_eng' => 'nullable|string',
            'content_bur' => 'nullable|string',
        ]);

        $coverUrl = null;

        // Handle file upload
        if ($request->hasFile('cover_url')) {
            $file = $request->file('cover_url');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/story', $filename); // stored in storage/app/public/covers
            $coverUrl = Storage::url($path); // generates /storage/covers/xxxx.jpg
            // Optional: full URL if needed
            $coverUrl = asset($coverUrl);
        }


        $story = Story::create([
            'title_eng' => $validated['title_eng'],
            'title_bur' => $validated['title_bur'],
            'content_eng' => $validated['content_eng'] ?? '',
            'content_bur' => $validated['content_bur'] ?? '',
            'cover_url' => $coverUrl,
        ]);

        return response()->json([
            'success' => true,
            'story' => $story,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Story $story)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Story $story)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Story $story)
    {
        //
        $validated = $request->validate([
            'title_eng' => 'required|string',
            'title_bur' => 'required|string',
            'cover_url' => 'nullable|file|mimes:jpg,jpeg,png,webp,svg',
            'content_eng' => 'nullable|string',
            'content_bur' => 'nullable|string',
        ]);

        $coverUrl = null;

        // Handle file upload
        if ($request->hasFile('cover_url')) {

            if ($story->cover_url) {
                $oldPath = str_replace('/storage/', 'story/', $story->cover_url);
                if (Storage::exists($oldPath)) {
                    Storage::delete($oldPath);
                }
            }

            $coverUrl = null;
            // Store new image


            $file = $request->file('cover_url');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/story', $filename); // stored in storage/app/public/covers
            $coverUrl = Storage::url($path); // generates /storage/covers/xxxx.jpg
            // Optional: full URL if needed
            $coverUrl = asset($coverUrl);
        }


        $story->update([
            'title_eng' => $validated['title_eng'],
            'title_bur' => $validated['title_bur'],
            'content_eng' => $validated['content_eng'] ?? $story->content_eng,
            'content_bur' => $validated['content_bur'] ?? $story->content_bur,
            'cover_url' => $coverUrl ?? $story->cover_url,
        ]);

        return response()->json([
            'success' => true,
            'story' => $story,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Story $story)
    {
        // Delete cover image if exists
        if ($story->cover_url) {
            $coverPath = str_replace(asset('/storage/'), '', $story->cover_url);
            Storage::disk('public')->delete($coverPath);
        }

        // Delete course
        $story->delete();

        return response()->json([
            'success' => true,
            'message' => 'story have been deleted.',
        ], 200);
    }
}
