<?php

namespace App\Http\Controllers;

use App\Models\Interview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\MetaVideoService;

class InterviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $interview = Interview::select('id', 'quote', 'date', 'tags', 'type', 'persons', 'videoFile', 'updated_at')->get();

        // Add individual record etags
        $interview->transform(function ($s) {
            $s->etag = md5($s->updated_at);
            return $s;
        });

        return response()->json([
            'success' => true,
            'interviews' => $interview,
        ], 200);
    }


    public function deltaSync(Request $request)
    {
        $clientRecords = $request->json()->all(); // [{id, etag}]
        $clientMap = collect($clientRecords)->pluck('etag', 'id');

        // Get all existing statements
        $allStatements = Interview::select('id', 'quote', 'date', 'tags', 'type', 'persons', 'videoFile', 'updated_at')->get();

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


    public function getInterview(Request $request, Interview $interview)
    {
        return response()->json([
            'success' => true,
            'interview' => $interview,
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request,  MetaVideoService $meta)
    {
        //
        $validated = $request->validate([
            'tags' => 'required|string', // will be JSON string, not array
            'persons' => 'required|string', // JSON string (you can decode it later)
            'type' => 'required|in:INTERVIEW,DISCUSSION', // must match enum values
            'date' => 'required|date|before_or_equal:today',
            'videoFile' => 'required|file|mimes:mp4,mov,avi,webm', // or 'file|mimetypes:video/*' if uploading file directly
            'quote' => 'nullable|string',
            'thumbnail' => 'required|file|mimes:jpg,jpeg,png',
            'FbEnabled' => 'required|boolean',
            'FbMessage' => 'nullable|string'
        ]);



        $coverUrl = null;
        $filename = null;

        // Handle file upload
        if ($request->hasFile('videoFile')) {
            $file = $request->file('videoFile');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/interviews', $filename); // stored in storage/app/public/covers

            // Force permission for web access
            chmod(storage_path('app/public/interviews/' . $filename), 0644);


            $coverUrl = Storage::url($path); // generates /storage/covers/xxxx.jpg
            // Optional: full URL if needed
            $coverUrl = asset($coverUrl);

            $thumbFile = $request->file('thumbnail');
            $thumbName = Str::uuid() . '.' . $thumbFile->getClientOriginalExtension();
            $thumbPath = $thumbFile->storeAs('public/thumbnails', $thumbName);
            $thumbnailUrl = Storage::url($thumbPath);
            $thumbnailUrl = asset($thumbnailUrl);
        }


        $interview = Interview::create([
            'tags' => $validated['tags'],
            'persons' => $validated['persons'],
            'type' => $validated['type'],
            'date' => $validated['date'],
            'quote' => $validated['quote'],
            'videoFile' => $coverUrl,
            'thumbnail' => $thumbnailUrl
        ]);


        // Facebook posting/upload
        $fbAccessToken = config('services.facebook.page_access_token'); // or wherever you store it
        $fbAppId = config('services.facebook.app_id');

        // $fileHandle = $meta->uploadVideoToMeta(storage_path('app/public/interviews/' . $filename), $filename);


        if ($validated['FbEnabled']) {
            // POST video with message
            $videoId = $meta->createVideoPost($coverUrl, $validated["FbMessage"]);
        }

        return response()->json([
            'success' => true,
            'interview' => $interview,
        ], 200);
    }

    public function update(Request $request, Interview $interview)
    {
        //
        $validated = $request->validate([
            'tags' => 'required|string', // will be JSON string, not array
            'persons' => 'required|string', // JSON string (you can decode it later)
            'type' => 'required|in:INTERVIEW,DISCUSSION', // must match enum values
            'date' => 'required|date|before_or_equal:today',
            'videoFile' => 'nullable|file|mimes:mp4,mov,avi,webm', // or 'file|mimetypes:video/*' if uploading file directly
            'quote' => 'nullable|string',
            'thumbnail' => 'nullable|file|mimes:jpg,jpeg,png',
        ]);

        $coverUrl = null;

        // Handle file upload
        if ($request->hasFile('videoFile')) {

            $publicPath = $interview->videoFile;
            $oldPath = str_replace(asset('/storage/'), '', $publicPath);


            if (Storage::disk('public')->exists($oldPath)) {
                Log::info("Oldpath to delete : ", [$oldPath]);
                Storage::disk('public')->delete($oldPath);
            }


            $coverUrl = null;
            // Store new image


            $file = $request->file('videoFile');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/interviews', $filename); // stored in storage/app/public/covers

            // Force permission for web access
            chmod(storage_path('app/public/interviews/' . $filename), 0644);


            $coverUrl = Storage::url($path); // generates /storage/covers/xxxx.jpg
            // Optional: full URL if needed
            $coverUrl = asset($coverUrl);

            $thumbPublicPath = $interview->thumbnail;
            $oldPath = str_replace(asset('/storage/'), '', $thumbPublicPath);

            if (Storage::disk('public')->exists($oldPath)) {
                Log::info("Oldpath to delete : ", [$oldPath]);
                Storage::disk('public')->delete($oldPath);
            }

            $thumbFile = $request->file('thumbnail');
            $thumbName = Str::uuid() . '.' . $thumbFile->getClientOriginalExtension();
            $thumbPath = $thumbFile->storeAs('public/thumbnails', $thumbName);
            $thumbnailUrl = Storage::url($thumbPath);
            $thumbnailUrl = asset($thumbnailUrl);
        }


        $interview->update([
            'tags' => $validated['tags'],
            'persons' => $validated['persons'],
            'type' => $validated['type'],
            'date' => $validated['date'],
            'quote' => $validated['quote'] ?? null,
            'videoFile' => $coverUrl ?? $interview->videoFile,
            'thumbnail' => $thumbnailUrl  ?? $interview->thumbnail
        ]);

        return response()->json([
            'success' => true,
            'interview' => $interview,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Interview $interview)
    {
        // Delete cover image if exists
        if ($interview->videoFile) {
            $coverPath = str_replace(asset('/storage/'), '', $interview->videoFile);
            Storage::disk('public')->delete($coverPath);
        }

        // Delete course
        $interview->delete();

        return response()->json([
            'success' => true,
            'message' => 'Interview have been deleted.',
        ], 200);
    }
}
