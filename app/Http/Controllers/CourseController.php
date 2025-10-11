<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Session;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Course::withCount('sessions')->get();
    }

    public function get($id)
    {
        return Course::withCount('sessions')->findOrFail($id);
    }

    /**
     * Fetch full session by Session ID (including content fields)
     */
    public function getSession($sessionId)
    {
        $session = Session::findOrFail($sessionId);

        return response()->json([
            'success' => true,
            'session' => $session,
        ], 200);
    }

    // Fetch every sessions of certain course including content
    public function sessionAll(Request $request, $courseId)
    {
        $sessions = Session::where('course_id', $courseId)
            ->orderBy('number', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'sessions' => $sessions,
        ], 200);
    }

    /**
     * Fetch sessions by Course ID excluding large content fields
     */
    public function sessionsData($courseId)
    {
        $sessions = Session::where('course_id', $courseId)
            ->select('id', 'number', 'course_id', 'title_eng', 'title_bur', 'created_at', 'updated_at') // exclude large content fields
            ->orderBy('number', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'sessions' => $sessions,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    // 1. Store new course with sessions
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title_eng' => 'required|string',
            'title_bur' => 'required|string',
            'cover_url' => 'nullable|file|mimes:jpg,jpeg,png,webp,svg',
            'type' => 'required|in:Learning,Story',
            'sessions' => 'nullable|string'
        ]);

        $coverUrl = null;

        // Handle file upload
        if ($request->hasFile('cover_url')) {
            $file = $request->file('cover_url');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/covers', $filename); // stored in storage/app/public/covers
            $coverUrl = Storage::url($path); // generates /storage/covers/xxxx.jpg
            // Optional: full URL if needed
            $coverUrl = asset($coverUrl);
        }

        $course = Course::create([
            'title_eng' => $validated['title_eng'],
            'title_bur' => $validated['title_bur'],
            'type' => $validated['type'],
            'cover_url' => $coverUrl,
        ]);

        if (!empty($validated['sessions'])) {
            $sessionsArray = json_decode($validated['sessions'], true); // decode JSON string to array

            if (is_array($sessionsArray)) {
                foreach ($sessionsArray as $s) {
                    $course->sessions()->create([
                        'title_eng' => $s['title_eng'] ?? '',
                        'title_bur' => $s['title_bur'] ?? '',
                        'content_eng' => $s['content_eng'] ?? '',
                        'content_bur' => $s['content_bur'] ?? '',
                        'number' => $s['number'] ?? 0
                    ]);
                }
            }
        }

        $course['sessions_count'] = sizeof($sessionsArray);

        return response()->json([
            'success' => true,
            'course' => $course,
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        $validated = $request->validate([
            'title_eng' => 'required|string',
            'title_bur' => 'required|string',
            'cover_url' => 'nullable|file|mimes:jpg,jpeg,png,webp,svg',
            'type' => 'required|in:Learning,Story',
            'sessions' => 'nullable|string'
        ]);

        // Handle new cover image
        if ($request->hasFile('cover_url')) {
            // Delete existing image
            if ($course->cover_url) {
                $oldPath = str_replace('/storage/', 'public/', $course->cover_url);
                if (Storage::exists($oldPath)) {
                    Storage::delete($oldPath);
                }
            }

            $coverUrl = null;
            // Store new image

            if ($request->hasFile('cover_url')) {
                $file = $request->file('cover_url');
                $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

                // Save video to storage
                $path = $file->storeAs('public/covers', $filename);
                $coverUrl = asset(Storage::url($path));
            }
        }

        $course->update([
            'title_eng' => $validated['title_eng'] ?? $course->title_eng,
            'title_bur' => $validated['title_bur'] ?? $course->title_bur,
            'type' => $validated['type'] ?? $course->type,
            'cover_url' => $coverUrl ?? $course->cover_url,
        ]);

        // Update sessions if provided
        if ($request->has('sessions')) {
            $sessions = json_decode($request->sessions, true); // decode JSON string
            foreach ($sessions as $s) {
                if (isset($s['id'])) {
                    // Update existing session
                    $session = Session::find($s['id']);
                    $session?->update($s);
                } else {
                    // Create new session
                    $course->sessions()->create($s);
                }
            }
        }

        return response()->json([
            'success' => true,
            'course' => $course,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $course = Course::findOrFail($id);

        // Delete cover image if exists
        if ($course->cover_url) {
            $coverPath = str_replace(asset('/storage/'), '', $course->cover_url);
            Storage::disk('public')->delete($coverPath);
        }

        // Delete associated sessions
        $course->sessions()->delete();

        // Delete course
        $course->delete();

        return response()->json([
            'success' => true,
            'message' => 'Course and its sessions have been deleted.',
        ], 200);
    }
}
