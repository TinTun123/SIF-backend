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

        $subsessions->transform(function ($s) {
            $s->etag = md5($s->updated_at);
            return $s;
        });

        return response()->json([
            'success' => true,
            'subsessions' => $subsessions,
        ], 200);
    }

    public function deltaSync(Request $request)
    {
        $clientRecords = $request->json()->all(); // [{id, etag}]
        $clientMap = collect($clientRecords)->pluck('etag', 'id');

        // Get all existing statements
        $allSubSessions = Subsession::get();

        // Determine new and updated separately
        $added = $allSubSessions->filter(function ($stmt) use ($clientMap) {
            return !isset($clientMap[$stmt->id]); // new to client
        })->values();

        $updated = $allSubSessions->filter(function ($stmt) use ($clientMap) {
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

        $deletedIds = $clientMap->keys()->diff($allSubSessions->pluck('id'));

        return response()->json([
            'addedSubSec' => $added->values(),
            'updatedSubSec' => $updated->values(),
            'deletedSubSec' => $deletedIds->values(),
        ]);
    }

    /**
     * Fetch full subsession by ID (with content)
     */
    public function getSubsession($subsessionId)
    {
        $subsession = Subsession::findOrFail($subsessionId);

        return response()->json([
            'success' => true,
            'subsession' => $subsession,
        ], 200);
    }

    public function storeSubSession(Request $request, Session $session)
    {
        $validated = $request->validate([
            'title_eng' => 'required|string',
            'title_bur' => 'required|string',
            'content_eng' => 'required|string',
            'content_bur' => 'required|string',
            'number' => 'required|numeric|min:0',
        ]);

        $session->subsessions()->create([
            'title_eng' => $validated['title_eng'],
            'title_bur' => $validated['title_bur'],
            'content_eng' => $validated['content_eng'],
            'content_bur' => $validated['content_bur'],
            'number' => $validated['number']
        ]);

        return response()->json([
            'success' => true,
            'session' => $session,
        ], 200);
    }

    public function updateSubSession(Request $request, Session $session, Subsession $subSession)
    {
        $validated = $request->validate([
            'title_eng' => 'required|string',
            'title_bur' => 'required|string',
            'content_eng' => 'required|string',
            'content_bur' => 'required|string',
            'number' => 'required|numeric|min:0',
        ]);

        $subSession->update([
            'title_eng' => $validated['title_eng'],
            'title_bur' => $validated['title_bur'],
            'content_eng' => $validated['content_eng'],
            'content_bur' => $validated['content_bur'],
            'number' => $validated['number']
        ]);

        Log::info('Subsession : ', [$subSession]);
        return response()->json([
            'success' => true,
            'session' => $session,
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

    public function destroy(Subsession $subsession)
    {
        // Get course ID and the deleted session's number before deleting
        $sessionId = $subsession->session_id;
        $deletedNumber = $subsession->number;

        // Delete the session
        $subsession->delete();

        // Shift down all sessions with higher numbers in the same course
        Subsession::where('session_id', $sessionId)
            ->where('number', '>', $deletedNumber)
            ->decrement('number');

        return response()->json([
            'message' => 'Subsession deleted and order updated successfully.',
        ]);
    }
}
