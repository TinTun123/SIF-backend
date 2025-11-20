<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaVideoService
{
    protected string $appId;
    protected string $pageId;
    protected string $pageAccessToken;

    public function __construct()
    {
        $this->appId = config('services.facebook.app_id');
        $this->pageId = config('services.facebook.page_id');
        $this->pageAccessToken = config('services.facebook.page_access_token');
    }

    public function createVideoPost(string $videoUrl, string $message): array
    {
        $endpoint = "https://graph.facebook.com/v24.0/{$this->pageId}/videos";
        Log::info('Endpoint : ', [$endpoint]);

        try {
            $response = Http::post($endpoint, [
                'access_token' => $this->pageAccessToken,
                'file_url'     => $videoUrl,   // The public video URL
                'description'  => $message,    // Post caption
            ]);
            Log::info("Respond : ", [$response]);
            if ($response->failed()) {
                Log::error('Facebook Video Upload Failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return [
                    'success' => false,
                    'error'   => $response->json(),
                ];
            }

            return [
                'success'   => true,
                'video_id'  => $response->json()['id'] ?? null,
                'result'    => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('Facebook Video Upload Exception', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }
}
