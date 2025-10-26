<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\Models\Podcast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EpisodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, Podcast $podcast)
    {
        //
        $validated = $request->validate([
            'title' => 'required|string',
            'number' => 'required|integer',
            'description' => 'required|string',
            'duration' => 'required|String',
            'fileURL' => 'required|file|mimetypes:video/mp4,video/avi,video/mpeg,video/quicktime,video/x-ms-wmv',
        ]);

        // Handle upload
        if ($request->hasFile('fileURL')) {
            $file = $request->file('fileURL');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/episodes', $filename);

            // Force permission for web access
            chmod(storage_path('app/public/episodes/' . $filename), 0644);

            $fileUrl = asset(Storage::url($path));
        }

        // Use relationship to create episode
        $episode = $podcast->episodes()->create([
            'title' => $validated['title'],
            'number' => $validated['number'],
            'description' => $validated['description'],
            'duration' => $validated['duration'],
            'fileURL' => $fileUrl,
        ]);

        return response()->json([
            'success' => true,
            'episode' => $episode,
        ]);
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
    public function show(Episode $episode)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Episode $episode)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Episode $episode)
    {

        $validated = $request->validate([
            'title' => 'required|string',
            'number' => 'required|integer',
            'duration' => 'required|string',
            'description' => 'required|string',
            'fileURL' => 'nullable|file|mimetypes:video/mp4,video/avi,video/mpeg,video/quicktime,video/x-ms-wmv',
        ]);

        $data = [];

        // Copy only fields that are provided
        foreach (['title', 'number', 'description', 'duration'] as $field) {
            if ($request->filled($field)) {
                $data[$field] = $validated[$field];
            }
        }

        // Handle new file upload
        if ($request->hasFile('fileURL')) {

            // Delete the old file (if exists)
            if ($episode->fileURL) {
                // Convert URL like '/storage/episodes/abc.mp4' → 'public/episodes/abc.mp4'
                $oldPath = str_replace('/storage/', 'public/', parse_url($episode->fileURL, PHP_URL_PATH));
                Storage::delete($oldPath);
            }

            // Store new file
            $file = $request->file('fileURL');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/episodes', $filename);

            // Force permission for web access
            chmod(storage_path('app/public/episodes/' . $filename), 0644);

            // Generate public URL
            $data['fileURL'] = asset(Storage::url($path));
        }

        // Update episode
        $episode->update($data);

        return response()->json([
            'success' => true,
            'episode' => $episode->fresh(), // Return updated data
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Episode $episode)
    {
        // Delete cover image if exists
        if ($episode->fileURL) {
            $coverPath = str_replace(asset('/storage/'), '', $episode->fileURL);
            Storage::disk('public')->delete($coverPath);
        }

        // Delete course
        $episode->delete();

        return response()->json([
            'success' => true,
            'message' => 'Episode have been deleted.',
        ], 200);
    }
}
