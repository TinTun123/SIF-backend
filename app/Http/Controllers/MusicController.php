<?php

namespace App\Http\Controllers;

use App\Models\Music;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MusicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $musics = Music::select('id', 'title', 'links', 'tags', 'file_url', 'date', 'file_url')->get();
        return response()->json([
            'success' => true,
            'musics' => $musics,
        ], 200);
    }

    public function getMusic(Request $request, Music $music)
    {
        return response()->json([
            'success' => true,
            'music' => $music,
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
            'links' => 'required|string',
            'tags' => 'required|string',
            'file_url' => 'required|file|mimetypes:video/mp4,video/avi,video/mpeg,video/quicktime,video/x-ms-wmv',
            'date' => 'required|date|before_or_equal:today',
        ]);

        $coverUrl = null;

        // Handle file upload
        if ($request->hasFile('file_url')) {
            $file = $request->file('file_url');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/music', $filename); // stored in storage/app/public/covers
            $coverUrl = Storage::url($path); // generates /storage/covers/xxxx.jpg
            // Optional: full URL if needed
            $coverUrl = asset($coverUrl);
        }


        $music = Music::create([
            'title' => $validated['title'],
            'links' => $validated['links'],
            'tags' => $validated['tags'],
            'date' => $validated['date'],
            'file_url' => $coverUrl,
        ]);

        return response()->json([
            'success' => true,
            'music' => $music,
        ], 200);
    }

    public function update(Request $request, Music $music)
    {
        //
        $validated = $request->validate([
            'title' => 'required|string',
            'links' => 'required|string',
            'tags' => 'required|string',
            'file_url' => 'nullable|file|mimetypes:video/mp4,video/avi,video/mpeg,video/quicktime,video/x-ms-wmv',
            'date' => 'required|date|before_or_equal:today',
        ]);

        $coverUrl = null;

        // Handle file upload
        if ($request->hasFile('file_url')) {

            if ($music->file_url) {
                $oldPath = str_replace('/storage/', 'music/', $music->file_url);
                if (Storage::exists($oldPath)) {
                    Storage::delete($oldPath);
                }
            }

            $coverUrl = null;
            // Store new image


            $file = $request->file('file_url');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/music', $filename); // stored in storage/app/public/covers
            $coverUrl = Storage::url($path); // generates /storage/covers/xxxx.jpg
            // Optional: full URL if needed
            $coverUrl = asset($coverUrl);
        }


        $music->update([
            'title' => $validated['title'],
            'links' => $validated['links'],
            'tags' => $validated['tags'],
            'file_url' => $coverUrl ?? $music->file_url,
        ]);

        return response()->json([
            'success' => true,
            'music' => $music,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Music $music)
    {
        // Delete cover image if exists
        if ($music->cover_url) {
            $coverPath = str_replace(asset('/storage/'), '', $music->cover_url);
            Storage::disk('public')->delete($coverPath);
        }

        // Delete course
        $music->delete();

        return response()->json([
            'success' => true,
            'message' => 'Music have been deleted.',
        ], 200);
    }
}
