<?php

namespace App\Http\Controllers;

use App\Models\Comics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ComicsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $comics = Comics::select('id', 'title', 'date', 'images')->get();
        return response()->json([
            'success' => true,
            'comics' => $comics,
        ], 200);
    }

    public function getComic(Request $request, Comics $comic)
    {
        return response()->json([
            'success' => true,
            'comic' => $comic,
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
            'images.*' => 'nullable|file|mimes:jpg,jpeg,png,webp,svg',
            'date' => 'required|date|before_or_equal:today',
        ]);

        $images = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('public/comics', $filename);
                $images[] = asset(Storage::url($path)); // convert to public URL
            }
        }


        $comic = Comics::create([
            'title' => $validated['title'],
            'date' => $validated['date'],
            'images' => json_encode($images),
        ]);

        return response()->json([
            'success' => true,
            'comic' => $comic,
        ], 200);
    }

    public function update(Request $request, Comics $comic)
    {
        //
        $validated = $request->validate([
            'title' => 'required|string',
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
                $path = $file->storeAs('public/comics', $filename);
                $images[] = asset(Storage::url($path));
            }
        }

        // Step 3: Delete images user removed
        $oldImages = json_decode($comic->images) ?? [];
        $removed = array_diff($oldImages, $images);

        foreach ($removed as $oldUrl) {
            // Convert full URL (e.g., https://app.test/storage/images/abc.jpg)
            // back to storage path (e.g., public/images/abc.jpg)
            $relativePath = str_replace(asset('storage/'), 'public/', $oldUrl);
            Storage::delete($relativePath);
        }


        $comic->update([
            'title' => $validated['title'],
            'date' => $validated['date'],
            'images' => $images,
        ]);

        return response()->json([
            'success' => true,
            'comic' => $comic->fresh(),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comics $comic)
    {
        // Delete cover image if exists
        // Step 1: Delete each stored image
        if (is_array(json_decode($comic->images))) {
            foreach (json_decode($comic->images) as $url) {
                // Convert full URL like "https://example.com/storage/images/xyz.jpg"
                // back to storage path like "public/images/xyz.jpg"
                $relativePath = str_replace(asset('storage/'), 'public/', $url);
                Storage::delete($relativePath);
            }
        }

        // Step 2: Delete the record itself
        $comic->delete();

        // Step 3: Respond success
        return response()->json([
            'success' => true,
            'message' => 'Comic and its images have been deleted.',
        ]);
    }
}
