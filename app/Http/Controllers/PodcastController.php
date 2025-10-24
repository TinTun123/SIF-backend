<?php

namespace App\Http\Controllers;

use App\Http\Resources\PodcastResource;
use App\Models\Podcast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PodcastController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $podcasts = Podcast::with('episodes')->get();

        return response()->json([
            'podcasts' => PodcastResource::collection($podcasts),
        ]);
    }

    public function show($id)
    {
        $podcast = Podcast::with('episodes')->findOrFail($id);

        return new PodcastResource($podcast);
    }

    public function getPodcast(Request $request, Podcast $podcast)
    {
        return response()->json([
            'success' => true,
            'podcast' => $podcast,
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        //
        $validated = $request->validate([
            'title' => 'required|string',
            'cover_url' => 'required|file|mimes:jpg,jpeg,png,webp,svg',
            'podcaster' => 'required|string',
            'description' => 'required|string',
        ]);

        $coverUrl = null;

        // Handle file upload
        if ($request->hasFile('cover_url')) {
            $file = $request->file('cover_url');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/podcasts', $filename); // stored in storage/app/public/covers
            $coverUrl = Storage::url($path); // generates /storage/covers/xxxx.jpg
            // Optional: full URL if needed
            $coverUrl = asset($coverUrl);
        }


        $podcast = Podcast::create([
            'title' => $validated['title'],
            'podcaster' => $validated['podcaster'],
            'description' => $validated['description'],
            'cover_url' => $coverUrl,
        ]);

        $podcast->load('episodes');

        return response()->json([
            'success' => true,
            'podcast' => $podcast,
        ], 200);
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Podcast $podcast)
    {
        //
        $validated = $request->validate([
            'title' => 'required|string',
            'podcaster' => 'required|string',
            'description' => 'required|string',
            'cover_url' => 'nullable|file|mimes:jpg,jpeg,png,webp,svg',
        ]);

        $coverUrl = null;

        // Handle file upload
        if ($request->hasFile('cover_url')) {

            if ($podcast->cover_url) {
                $oldPath = str_replace('/storage/', 'podcasts/', $podcast->cover_url);
                if (Storage::exists($oldPath)) {
                    Storage::delete($oldPath);
                }
            }

            $coverUrl = null;
            // Store new image


            $file = $request->file('cover_url');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/podcasts', $filename); // stored in storage/app/public/covers
            $coverUrl = Storage::url($path); // generates /storage/covers/xxxx.jpg
            // Optional: full URL if needed
            $coverUrl = asset($coverUrl);
        }


        $podcast->update([
            'title' => $validated['title'],
            'podcaster' => $validated['podcaster'],
            'description' => $validated['description'],
            'cover_url' => $coverUrl ?? $podcast->cover_url,
        ]);

        $podcast->load('episodes');

        return response()->json([
            'success' => true,
            'podcast' => $podcast,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Podcast $podcast)
    {
        // Delete cover image if exists
        if ($podcast->cover_url) {
            $coverPath = str_replace(asset('/storage/'), '', $podcast->cover_url);
            Storage::disk('public')->delete($coverPath);
        }

        // Delete course
        $podcast->delete();

        return response()->json([
            'success' => true,
            'message' => 'Podcast have been deleted.',
        ], 200);
    }
}
