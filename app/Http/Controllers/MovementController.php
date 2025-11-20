<?php

namespace App\Http\Controllers;

use App\Models\Movement;
use App\Services\MetaVideoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class MovementController extends Controller
{
    public function index()
    {
        //
        $movements = Movement::select('id', 'title_eng', 'title_bur', 'cover_url', 'story_date')->get();
        return response()->json([
            'success' => true,
            'movements' => $movements,
        ], 200);
    }

    public function getStory(Request $request, Movement $movement)
    {
        return response()->json([
            'success' => true,
            'movement' => $movement,
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, MetaVideoService $meta)
    {
        //
        $validated = $request->validate([
            'title_eng' => 'required|string',
            'title_bur' => 'required|string',
            'cover_url' => 'required|file|mimes:jpg,jpeg,png,webp,svg',
            'story_date' => 'required|date|before_or_equal:today',
            'content_eng' => 'nullable|string',
            'content_bur' => 'nullable|string',
            'FbEnabled' => 'required|boolean',
            'FbMessage' => 'nullable|string'
        ]);

        $coverUrl = null;

        // Handle file upload
        if ($request->hasFile('cover_url')) {
            $file = $request->file('cover_url');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/movement', $filename); // stored in storage/app/public/covers

            // Force permission for web access
            chmod(storage_path('app/public/movement/' . $filename), 0644);

            $coverUrl = Storage::url($path); // generates /storage/covers/xxxx.jpg
            // Optional: full URL if needed
            $coverUrl = asset($coverUrl);
        }


        $movement = Movement::create([
            'title_eng' => $validated['title_eng'],
            'title_bur' => $validated['title_bur'],
            'story_date' => $validated['story_date'],
            'content_eng' => $validated['content_eng'] ?? '',
            'content_bur' => $validated['content_bur'] ?? '',
            'cover_url' => $coverUrl,
        ]);

        if ($validated['FbEnabled']) {
            // POST video with message
            $respond = $meta->createLinkPost($validated["FbMessage"], url("/share/movement/$movement->id"));
        }

        return response()->json([
            'success' => true,
            'movement' => $movement,
        ], 200);
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
    public function show(Movement $movement)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Movement $movement)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Movement $movement)
    {
        //
        $validated = $request->validate([
            'title_eng' => 'required|string',
            'title_bur' => 'required|string',
            'story_date' => 'required|date|before_or_equal:today',
            'cover_url' => 'nullable|file|mimes:jpg,jpeg,png,webp,svg',
            'content_eng' => 'nullable|string',
            'content_bur' => 'nullable|string',
        ]);

        $coverUrl = null;

        // Handle file upload
        if ($request->hasFile('cover_url')) {

            if ($movement->cover_url) {
                $oldPath = str_replace('/storage/', 'movement/', $movement->cover_url);
                if (Storage::exists($oldPath)) {
                    Storage::delete($oldPath);
                }
            }

            $coverUrl = null;
            // Store new image


            $file = $request->file('cover_url');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/movement', $filename); // stored in storage/app/public/covers

            // Force permission for web access
            chmod(storage_path('app/public/movement/' . $filename), 0644);

            $coverUrl = Storage::url($path); // generates /storage/covers/xxxx.jpg
            // Optional: full URL if needed
            $coverUrl = asset($coverUrl);
        }


        $movement->update([
            'title_eng' => $validated['title_eng'],
            'title_bur' => $validated['title_bur'],
            'story_date' => $validated['story_date'],
            'content_eng' => $validated['content_eng'] ?? $movement->content_eng,
            'content_bur' => $validated['content_bur'] ?? $movement->content_bur,
            'cover_url' => $coverUrl ?? $movement->cover_url,
        ]);

        return response()->json([
            'success' => true,
            'movement' => $movement,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Movement $movement)
    {
        // Delete cover image if exists
        if ($movement->cover_url) {
            $coverPath = str_replace(asset('/storage/'), '', $movement->cover_url);
            Storage::disk('public')->delete($coverPath);
        }

        // Delete course
        $movement->delete();

        return response()->json([
            'success' => true,
            'message' => 'story have been deleted.',
        ], 200);
    }
}
