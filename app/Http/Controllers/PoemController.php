<?php

namespace App\Http\Controllers;

use App\Models\Poem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PoemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $poem = Poem::select('id', 'title', 'author', 'cover_url', 'passage')->get();
        return response()->json([
            'success' => true,
            'poems' => $poem,
        ], 200);
    }

    public function getPoem(Request $request, Poem $poem)
    {
        return response()->json([
            'success' => true,
            'poem' => $poem,
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
            'author' => 'required|string',
            'cover_url' => 'required|file|mimes:jpg,jpeg,png,webp,svg',
            'passage' => 'required|string',
        ]);

        $coverUrl = null;

        // Handle file upload
        if ($request->hasFile('cover_url')) {
            $file = $request->file('cover_url');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/poem', $filename); // stored in storage/app/public/covers

            // Force permission for web access
            chmod(storage_path('app/public/poem/' . $filename), 0644);

            $coverUrl = Storage::url($path); // generates /storage/covers/xxxx.jpg
            // Optional: full URL if needed
            $coverUrl = asset($coverUrl);
        }


        $poem = Poem::create([
            'title' => $validated['title'],
            'passage' => $validated['passage'],
            'author' => $validated['author'],
            'cover_url' => $coverUrl,
        ]);
        Log::info("Poem : ", [$poem]);

        return response()->json([
            'success' => true,
            'poem' => $poem,
        ], 200);
    }

    public function update(Request $request, Poem $poem)
    {
        //
        $validated = $request->validate([
            'title' => 'required|string',
            'author' => 'required|string',
            'passage' => 'required|string',
            'cover_url' => 'nullable|file|mimes:jpg,jpeg,png,webp,svg',
        ]);

        $coverUrl = null;

        // Handle file upload
        if ($request->hasFile('cover_url')) {

            if ($poem->cover_url) {
                $oldPath = str_replace('/storage/', 'poem/', $poem->cover_url);
                if (Storage::exists($oldPath)) {
                    Storage::delete($oldPath);
                }
            }

            $coverUrl = null;
            // Store new image


            $file = $request->file('cover_url');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/poem', $filename); // stored in storage/app/public/covers

            // Force permission for web access
            chmod(storage_path('app/public/poem/' . $filename), 0644);

            $coverUrl = Storage::url($path); // generates /storage/covers/xxxx.jpg
            // Optional: full URL if needed
            $coverUrl = asset($coverUrl);
        }


        $poem->update([
            'title' => $validated['title'],
            'author' => $validated['author'],
            'passage' => $validated['passage'],
            'cover_url' => $coverUrl ?? $poem->cover_url,
        ]);

        return response()->json([
            'success' => true,
            'poem' => $poem,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Poem $poem)
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
            'message' => 'Poem have been deleted.',
        ], 200);
    }
}
