<?php

namespace App\Http\Controllers;

use App\Models\Interview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InterviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $interview = Interview::select('id', 'quote', 'date', 'tags', 'type', 'persons', 'videoFile')->get();
        return response()->json([
            'success' => true,
            'interviews' => $interview,
        ], 200);
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
    public function create(Request $request)
    {
        //
        $validated = $request->validate([
            'tags' => 'required|string', // will be JSON string, not array
            'persons' => 'required|string', // JSON string (you can decode it later)
            'type' => 'required|in:INTERVIEW,DISCUSSION', // must match enum values
            'date' => 'required|date|before_or_equal:today',
            'videoFile' => 'required|file|mimes:mp4,mov,avi,webm', // or 'file|mimetypes:video/*' if uploading file directly
            'quote' => 'nullable|string',
        ]);

        $coverUrl = null;

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
        }


        $interview = Interview::create([
            'tags' => $validated['tags'],
            'persons' => $validated['persons'],
            'type' => $validated['type'],
            'date' => $validated['date'],
            'quote' => $validated['quote'],
            'videoFile' => $coverUrl
        ]);

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
        ]);

        $coverUrl = null;

        // Handle file upload
        if ($request->hasFile('videoFile')) {

            if ($interview->cover_url) {
                $oldPath = str_replace('/storage/', 'interviews/', $interview->videoFile);
                if (Storage::exists($oldPath)) {
                    Storage::delete($oldPath);
                }
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
        }


        $interview->update([
            'tags' => $validated['tags'],
            'persons' => $validated['persons'],
            'type' => $validated['type'],
            'date' => $validated['date'],
            'quote' => $validated['quote'] ?? null,
            'videoFile' => $coverUrl ?? $interview->videoFile,
        ]);

        return response()->json([
            'success' => true,
            'interview' => $interview,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Interview $poem)
    {
        // Delete cover image if exists
        if ($poem->cover_url) {
            $coverPath = str_replace(asset('/storage/'), '', $poem->cover_url);
            Storage::disk('public')->delete($coverPath);
        }

        // Delete course
        $poem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Interview have been deleted.',
        ], 200);
    }
}
