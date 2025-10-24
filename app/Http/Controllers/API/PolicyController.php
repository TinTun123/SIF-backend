<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\PolicyResource;
use App\Models\Policy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PolicyController extends Controller
{
    public function index()
    {
        $policies = Policy::latest()->get();
        return response()->json(
            PolicyResource::collection($policies)->resolve()
        );
    }

    public function show($id)
    {
        $policy = Policy::findOrFail($id);
        return new PolicyResource($policy);
    }

    //
    /**
     * Store a new policy record.
     */
    public function store(Request $request)
    {
        // ✅ Step 1: Validate input
        $validated = $request->validate([
            'title_eng' => 'required|string',
            'title_bur' => 'nullable|string',
            'date' => 'nullable|date',
            'organizations' => 'nullable|string',
            'content_eng' => 'nullable|string',
            'content_bur' => 'nullable|string',
            'logos.*' => 'nullable|file|mimes:jpg,jpeg,png,webp,svg', // validate each file
        ]);

        // ✅ Step 2: Handle logo uploads
        $logos = [];

        if ($request->hasFile('logos')) {
            foreach ($request->file('logos') as $file) {
                $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('public/logos', $filename);
                $logos[] = asset(Storage::url($path)); // convert to public URL
            }
        }

        // ✅ Step 3: Create the policy record
        $policy = Policy::create([
            'title_eng' => $validated['title_eng'],
            'title_bur' => $validated['title_bur'] ?? null,
            'date' => $validated['date'] ?? null,
            'organizations' => $validated['organizations'] ?? null,
            'logos' => implode('#', $logos),
            'content_eng' => $validated['content_eng'] ?? null,
            'content_bur' => $validated['content_bur'] ?? null,
        ]);

        // ✅ Step 4: Respond
        return response()->json([
            'success' => true,
            'policy' => $policy,
        ], 201);
    }

    /**
     * Delete a policy record.
     */
    public function destroy($id)
    {
        $policy = Policy::findOrFail($id);

        // Optional: delete logo files from storage
        if ($policy->logos) {
            $logos = explode('#', $policy->logos);
            foreach ($logos as $url) {
                $path = str_replace(asset('storage'), 'public', $url);
                Storage::delete($path);
            }
        }

        $policy->delete();

        return response()->json([
            'success' => true,
            'message' => 'Policy deleted successfully.',
        ]);
    }

    /**
     * Update an existing policy record.
     */
    public function update(Request $request, $id)
    {
        $policy = Policy::findOrFail($id);

        $validated = $request->validate([
            'title_eng' => 'required|string',
            'title_bur' => 'nullable|string',
            'date' => 'nullable|date',
            'organizations' => 'nullable|string',
            'content_eng' => 'nullable|string',
            'content_bur' => 'nullable|string',
            'logos.*' => 'nullable|file|mimes:jpg,jpeg,png,webp,svg',
            'existing_logos' => 'nullable|array',
            'existing_logos.*' => 'nullable|string',
        ]);

        // Step 1: Start from logos user kept
        $logos = $validated['existing_logos'] ?? [];

        // Step 2: Handle newly uploaded files
        if ($request->hasFile('logos')) {
            foreach ($request->file('logos') as $file) {
                $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('public/logos', $filename);
                $logos[] = asset(Storage::url($path));
            }
        }

        // Step 3: Delete logos that were removed by the user
        $oldLogos = explode('#', $policy->logos ?? '');
        $removed = array_diff($oldLogos, $logos);
        foreach ($removed as $old) {
            $relative = str_replace(asset('storage/'), 'public/', $old);
            Storage::delete($relative);
        }

        // Step 4: Update record
        $policy->update([
            'title_eng' => $validated['title_eng'],
            'title_bur' => $validated['title_bur'] ?? $policy->title_bur,
            'date' => $validated['date'] ?? $policy->date,
            'organizations' => $validated['organizations'] ?? $policy->organizations,
            'logos' => implode('#', $logos),
            'content_eng' => $validated['content_eng'] ?? $policy->content_eng,
            'content_bur' => $validated['content_bur'] ?? $policy->content_bur,
        ]);

        return response()->json([
            'success' => true,
            'policy' => $policy->fresh(),
        ]);
    }
}
