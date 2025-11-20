<?php

namespace App\Http\Controllers;

use App\Models\Music;
use App\Services\MetaVideoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        $musics = Music::select('id', 'title', 'links', 'tags', 'file_url', 'date', 'file_url', 'updated_at')->get();

        // Add individual record etags
        $musics->transform(function ($s) {
            $s->etag = md5($s->updated_at);
            return $s;
        });

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


    public function deltaSync(Request $request)
    {
        $clientRecords = $request->json()->all(); // [{id, etag}]
        $clientMap = collect($clientRecords)->pluck('etag', 'id');

        // Get all existing statements
        $allStatements = Music::get();

        // Determine new and updated separately
        $added = $allStatements->filter(function ($stmt) use ($clientMap) {
            return !isset($clientMap[$stmt->id]); // new to client
        })->values();

        $updated = $allStatements->filter(function ($stmt) use ($clientMap) {
            $currentEtag = md5($stmt->updated_at);
            return isset($clientMap[$stmt->id]) && $clientMap[$stmt->id] !== $currentEtag;
        })->values();

        $added->transform(function ($stmt) {
            $stmt->etag = md5($stmt->updated_at);
            return $stmt;
        });

        $updated->transform(function ($stmt) {
            $stmt->etag = md5($stmt->updated_at);
            return $stmt;
        });

        $deletedIds = $clientMap->keys()->diff($allStatements->pluck('id'));

        return response()->json([
            'added' => $added->values(),
            'updated' => $updated->values(),
            'deleted' => $deletedIds->values(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, MetaVideoService $meta)
    {
        //
        $validated = $request->validate([
            'title' => 'required|string',
            'tags' => 'required|string',
            'file_url' => 'required|file|mimetypes:video/mp4,video/avi,video/mpeg,video/quicktime,video/x-ms-wmv',
            'date' => 'required|date|before_or_equal:today',
            'thumbnail' => 'required|file|mimes:jpg,jpeg,png',
            'FbEnabled' => 'required|boolean',
            'FbMessage' => 'nullable|string'
        ]);

        $coverUrl = null;

        // Handle file upload
        if ($request->hasFile('file_url')) {
            $file = $request->file('file_url');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/music', $filename); // stored in storage/app/public/covers

            // Force permission for web access
            chmod(storage_path('app/public/music/' . $filename), 0644);


            $coverUrl = Storage::url($path); // generates /storage/covers/xxxx.jpg
            // Optional: full URL if needed
            $coverUrl = asset($coverUrl);


            $thumbFile = $request->file('thumbnail');
            $thumbName = Str::uuid() . '.' . $thumbFile->getClientOriginalExtension();
            $thumbPath = $thumbFile->storeAs('public/thumbnails', $thumbName);
            $thumbnailUrl = Storage::url($thumbPath);
            $thumbnailUrl = asset($thumbnailUrl);
        }


        $music = Music::create([
            'title' => $validated['title'],
            'links' => $validated['links'],
            'tags' => $validated['tags'],
            'date' => $validated['date'],
            'file_url' => $coverUrl,
            'thumbnail' => $thumbnailUrl
        ]);


        if ($validated['FbEnabled']) {
            // POST video with message
            $videoId = $meta->createVideoPost($coverUrl, $validated["FbMessage"]);
        }

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
            'tags' => 'required|string',
            'file_url' => 'nullable|file|mimetypes:video/mp4,video/avi,video/mpeg,video/quicktime,video/x-ms-wmv',
            'date' => 'required|date|before_or_equal:today',
            'thumbnail' => 'nullable|file|mimes:jpg,jpeg,png',
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

            // Force permission for web access
            chmod(storage_path('app/public/music/' . $filename), 0644);


            $coverUrl = Storage::url($path); // generates /storage/covers/xxxx.jpg
            // Optional: full URL if needed
            $coverUrl = asset($coverUrl);

            $thumbPublicPath = $music->thumbnail;
            $oldPath = str_replace(asset('/storage/'), '', $thumbPublicPath);

            if (Storage::disk('public')->exists($oldPath)) {

                Storage::disk('public')->delete($oldPath);
            }

            $thumbFile = $request->file('thumbnail');
            $thumbName = Str::uuid() . '.' . $thumbFile->getClientOriginalExtension();
            $thumbPath = $thumbFile->storeAs('public/thumbnails', $thumbName);
            $thumbnailUrl = Storage::url($thumbPath);
            $thumbnailUrl = asset($thumbnailUrl);
        }


        $music->update([
            'title' => $validated['title'],
            'links' => $validated['links'],
            'tags' => $validated['tags'],
            'file_url' => $coverUrl ?? $music->file_url,
            'thumbnail' => $thumbnailUrl ?? $music->thumbnail
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
