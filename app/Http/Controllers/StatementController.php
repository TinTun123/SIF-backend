<?php

namespace App\Http\Controllers;

use App\Models\Statement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StatementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $statements = Statement::select('id', 'title', 'date', 'tags', 'images', 'updated_at')->get();

        // Add individual record etags
        $statements->transform(function ($s) {
            $s->etag = md5($s->updated_at);
            return $s;
        });

        // Compute global last modified
        $lastModified = optional($statements->max('updated_at'))->toRfc7231String();

        // If client sends If-Modified-Since and nothing changed
        if ($request->hasHeader('If-Modified-Since')) {
            $clientTime = \Carbon\Carbon::parse($request->header('If-Modified-Since'));
            $serverTime = \Carbon\Carbon::parse($lastModified);

            if ($clientTime->equalTo($serverTime) || $clientTime->greaterThan($serverTime)) {
                return response()->noContent(304); // Not Modified
            }
        }

        return response()->json([
            'success' => true,
            'statements' => $statements,
        ], 200)->header('Last-Modified', $lastModified);
    }

    public function getStatement(Request $request, Statement $statement)
    {
        return response()->json([
            'success' => true,
            'statement' => $statement,
        ], 200);
    }

    public function deltaSync(Request $request)
    {
        $clientRecords = $request->json()->all(); // [{id, etag}]
        $clientMap = collect($clientRecords)->pluck('etag', 'id');

        // Get all existing statements
        $allStatements = Statement::select('id', 'title', 'date', 'tags', 'images', 'updated_at')->get();

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
    public function create(Request $request)
    {
        //
        $validated = $request->validate([
            'title' => 'required|string',
            'tags' => 'required|array',
            'tags.*' => 'required|string',
            'images.*' => 'nullable|file|mimes:jpg,jpeg,png,webp,svg',
            'date' => 'required|date|before_or_equal:today',
        ]);

        $images = [];

        if ($request->hasFile('images')) {

            foreach ($request->file('images') as $file) {
                $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('public/statement', $filename);

                // Force permission for web access
                chmod(storage_path('app/public/statement/' . $filename), 0644);

                $images[] = asset(Storage::url($path)); // convert to public URL
            }
        }

        $statement = Statement::create([
            'title' => $validated['title'],
            'date' => $validated['date'],
            'tags' => json_encode($validated['tags']),
            'images' => json_encode($images),
        ]);

        return response()->json([
            'success' => true,
            'statement' => $statement,
        ], 200);
    }

    public function update(Request $request, Statement $statement)
    {
        //
        $validated = $request->validate([
            'title' => 'required|string',
            'date' => 'required|date|before_or_equal:today',
            'tags' => 'required|array',
            'tags.*' => 'required|string',
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
                $path = $file->storeAs('public/statement', $filename);

                // Force permission for web access
                chmod(storage_path('app/public/statement/' . $filename), 0644);

                $images[] = asset(Storage::url($path));
            }
        }

        // Step 3: Delete images user removed
        $oldImages = json_decode($statement->images) ?? [];
        $removed = array_diff($oldImages, $images);

        foreach ($removed as $oldUrl) {
            // Convert full URL (e.g., https://app.test/storage/images/abc.jpg)
            // back to storage path (e.g., public/images/abc.jpg)
            $relativePath = str_replace(asset('storage/'), 'public/', $oldUrl);
            Storage::delete($relativePath);
        }


        $statement->update([
            'title' => $validated['title'],
            'date' => $validated['date'],
            'tags' => $validated['tags'],
            'images' => $images,
        ]);

        return response()->json([
            'success' => true,
            'statement' => $statement->fresh(),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Statement $statement)
    {
        // Step 1: Delete each stored image
        if (is_array(json_decode($statement->images))) {
            foreach (json_decode($statement->images) as $url) {
                // Convert full URL like "https://example.com/storage/images/xyz.jpg"
                // back to storage path like "public/images/xyz.jpg"
                $relativePath = str_replace(asset('storage/'), 'public/', $url);
                Storage::delete($relativePath);
            }
        }

        // Step 2: Delete the record itself
        $statement->delete();

        // Step 3: Respond success
        return response()->json([
            'success' => true,
            'message' => 'Statement and its images have been deleted.',
        ]);
    }
}
