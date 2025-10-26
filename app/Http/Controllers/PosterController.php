<?php

namespace App\Http\Controllers;

use App\Models\Poster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PosterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $posters = Poster::select('id', 'date', 'images')->get();
        return response()->json([
            'success' => true,
            'posters' => $posters,
        ], 200);
    }

    public function getPoster(Request $request, Poster $poster)
    {
        return response()->json([
            'success' => true,
            'poster' => $poster,
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        //
        $validated = $request->validate([
            'images.*' => 'nullable|file|mimes:jpg,jpeg,png,webp,svg',
            'date' => 'required|date|before_or_equal:today',
        ]);

        $images = [];

        if ($request->hasFile('images')) {

            foreach ($request->file('images') as $file) {
                $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('public/poster', $filename);

                // Force permission for web access
                chmod(storage_path('app/public/poster/' . $filename), 0644);

                $images[] = asset(Storage::url($path)); // convert to public URL
            }
        }

        $poster = Poster::create([
            'date' => $validated['date'],
            'images' => json_encode($images),
        ]);

        return response()->json([
            'success' => true,
            'poster' => $poster,
        ], 200);
    }

    public function update(Request $request, Poster $poster)
    {
        //
        $validated = $request->validate([
            'date' => 'required|date|before_or_equal:today',
            'existing_images' => 'nullable|array',
            'existing_images.*' => 'nullable|string',
            'images.*' => 'nullable|image|max:2048',
        ]);

        // Step 1: Start from logos user kept
        $images = $validated['existing_images'] ?? [];

        // Step 2: Handle newly uploaded files
        if ($request->hasFile('images')) {

            foreach ($request->file('images') as $file) {
                $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('public/poster', $filename);

                // Force permission for web access
                chmod(storage_path('app/public/poster/' . $filename), 0644);

                $images[] = asset(Storage::url($path));
            }
        }

        // Step 3: Delete images user removed
        $oldImages = json_decode($poster->images) ?? [];
        $removed = array_diff($oldImages, $images);

        foreach ($removed as $oldUrl) {
            // Convert full URL (e.g., https://app.test/storage/images/abc.jpg)
            // back to storage path (e.g., public/images/abc.jpg)
            $relativePath = str_replace(asset('storage/'), 'public/', $oldUrl);
            Storage::delete($relativePath);
        }


        $poster->update([
            'date' => $validated['date'],
            'images' => $images,
        ]);

        return response()->json([
            'success' => true,
            'poster' => $poster->fresh(),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Poster $poster)
    {
        // Step 1: Delete each stored image
        if (is_array(json_decode($poster->images))) {
            foreach (json_decode($poster->images) as $url) {
                // Convert full URL like "https://example.com/storage/images/xyz.jpg"
                // back to storage path like "public/images/xyz.jpg"
                $relativePath = str_replace(asset('storage/'), 'public/', $url);
                Storage::delete($relativePath);
            }
        }

        // Step 2: Delete the record itself
        $poster->delete();

        // Step 3: Respond success
        return response()->json([
            'success' => true,
            'message' => 'Statement and its images have been deleted.',
        ]);
    }
}
