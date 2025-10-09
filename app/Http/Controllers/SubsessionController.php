<?php

namespace App\Http\Controllers;

use App\Models\Session;
use App\Models\Subsession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubsessionController extends Controller
{
    //

    /**
     * Fetch subsessions by Session ID excluding large content fields
     */
    public function subsessionData($sessionId)
    {
        $subsessions = Subsession::where('session_id', $sessionId)
            ->select('id', 'number', 'session_id', 'title_eng', 'title_bur', 'created_at', 'updated_at') // exclude content
            ->orderBy('number', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'subsessions' => $subsessions,
        ], 200);
    }

    public function subSessionAll($sessionId)
    {
        $subsessions = Subsession::where('session_id', $sessionId)
            ->orderBy('number', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'subsessions' => $subsessions,
        ], 200);
    }

    /**
     * Fetch full subsession by ID (with content)
     */
    public function getSubsession($subsessionId)
    {
        $subsession = Subsession::findOrFail($subsessionId);

        Log::info("Subsessions : ", [$subsession]);
        return response()->json([
            'success' => true,
            'subsession' => $subsession,
        ], 200);
    }


    // Store new subsession
    public function store(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'required|exists:sessions,id',
            'subSessions' => 'nullable|string', // JSON string from frontend
        ]);

        $session = Session::findOrFail($validated['session_id']);

        $subSessionsData = json_decode($request->subSessions, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['error' => 'Invalid JSON format for subSessions'], 422);
        }

        $created = [];

        if (is_array($subSessionsData)) {
            foreach ($subSessionsData as $sub) {
                $created[] = $session->subsessions()->create([
                    'title_eng' => $sub['title_eng'] ?? '',
                    'title_bur' => $sub['title_bur'] ?? '',
                    'content_eng' => $sub['content_eng'] ?? '',
                    'content_bur' => $sub['content_bur'] ?? '',
                    'number' => $sub['number'] ?? 1,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Subsessions created successfully',
            'subsessions' => $created,
        ]);
    }

    // Update subsession
    public function update(Request $request, $sessionId)
    {
        $validated = $request->validate([
            'subSessions' => 'nullable|string', // JSON string
        ]);

        $session = Session::findOrFail($sessionId);
        $subSessionsData = json_decode($request->subSessions, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['error' => 'Invalid JSON format for subSessions'], 422);
        }

        $updated = [];
        $new = [];

        if (is_array($subSessionsData)) {
            foreach ($subSessionsData as $sub) {
                // Update existing
                if (!empty($sub['id'])) {
                    $existing = Subsession::find($sub['id']);
                    if ($existing) {
                        $existing->update([
                            'title_eng' => $sub['title_eng'] ?? $existing->title_eng,
                            'title_bur' => $sub['title_bur'] ?? $existing->title_bur,
                            'content_eng' => $sub['content_eng'] ?? $existing->content_eng,
                            'content_bur' => $sub['content_bur'] ?? $existing->content_bur,
                            'number' => $sub['number'] ?? $existing->number,
                        ]);
                        $updated[] = $existing;
                    }
                }
                // Create new
                else {
                    $new[] = $session->subsessions()->create([
                        'title_eng' => $sub['title_eng'] ?? '',
                        'title_bur' => $sub['title_bur'] ?? '',
                        'content_eng' => $sub['content_eng'] ?? '',
                        'content_bur' => $sub['content_bur'] ?? '',
                        'number' => $sub['number'] ?? 1,
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Subsessions updated successfully',
            'updated' => $updated,
            'new' => $new,
        ]);
    }
}
