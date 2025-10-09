<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\VideoResource;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class VideoController extends Controller
{
    //

    public function index(Request $request)
    {
        $playlist = $request->query('playlist');

        $query = \App\Models\Video::query();

        if ($playlist) {
            $query->where('playlist', $playlist);
        }

        $videos = $query->orderByDesc('created_at')->get();

        return VideoResource::collection($videos);
    }


    public function upload(Request $request)
    {
        $request->validate([
            'video' => 'required|file|mimes:mp4,mov,avi,webm',
            'playlist' => 'nullable|string|max:255',
        ]);

        $file = $request->file('video');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

        // Save video to storage
        $path = $file->storeAs('public/videos', $filename);
        $videoUrl = Storage::url($path);

        // Generate thumbnail
        $thumbnailName = Str::uuid() . '.jpg';
        $thumbnailPath = "public/thumbnails/{$thumbnailName}";

        // Extract frame at 5 seconds
        FFMpeg::fromDisk('public')
            ->open("videos/{$filename}")
            ->getFrameFromSeconds(5)
            ->export()
            ->toDisk('public')
            ->save("thumbnails/{$thumbnailName}");

        $thumbnailUrl = Storage::url($thumbnailPath);

        // Save to DB
        $video = \App\Models\Video::create([
            'video_url' => $videoUrl,
            'playlist' => $request->playlist ?? null,
            'thumbnail' => $thumbnailUrl,
        ]);

        return response()->json([
            'success' => true,
            'video' => new VideoResource($video),
        ]);
    }

    public function destroy($id)
    {
        $video = Video::find($id);

        if (!$video) {
            return response()->json([
                'success' => false,
                'message' => 'Video not found.'
            ], 404);
        }

        try {
            // Delete video file if it exists
            if ($video->video_url) {
                $videoPath = str_replace('/storage/', 'public/', $video->video_url);
                if (Storage::exists($videoPath)) {
                    Storage::delete($videoPath);
                }
            }

            // Delete thumbnail file if it exists
            if ($video->thumbnail) {
                $thumbPath = str_replace('/storage/', 'public/', $video->thumbnail);
                if (Storage::exists($thumbPath)) {
                    Storage::delete($thumbPath);
                }
            }

            // Delete DB record
            $video->delete();

            return response()->json([
                'success' => true,
                'message' => 'Video deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting video: ' . $e->getMessage()
            ], 500);
        }
    }
}
