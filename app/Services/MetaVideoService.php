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

    /**
     * Upload a video to Meta (Resumable Upload API)
     * Returns the uploaded file handle (h)
     */
    public function uploadVideoToMeta(string $localPath, string $fileName): string
    {
        $fileSize = filesize($localPath);

        // Step 1: Start upload session
        $initResponse = Http::post("https://graph.facebook.com/v24.0/{$this->appId}/uploads", [
            'file_name'   => $fileName,
            'file_length' => $fileSize,
            'file_type'   => 'video/mp4',
            'access_token' => $this->pageAccessToken,
        ]);
        Log::info('initResponse : ', [$initResponse]);

        $sessionId = $initResponse->json('id'); // upload:<UPLOAD_SESSION_ID>

        if (!$sessionId) {
            throw new \Exception("Failed to start Meta upload session.");
        }

        // Step 2: Upload video binary
        $uploadResponse = Http::withHeaders([
            'Authorization' => "OAuth {$this->pageAccessToken}",
            'file_offset'   => 0,
        ])
            ->withBody(
                file_get_contents($localPath),
                'application/octet-stream'
            )->post("https://graph.facebook.com/v24.0/{$sessionId}");

        Log::info('uploadResponse : ', [$uploadResponse]);

        $fileHandle = $uploadResponse->json('h');

        if (!$fileHandle) {
            throw new \Exception("Failed to upload video to Meta.");
        }

        return $fileHandle;
    }


    /**
     * Publish a video silently (published = false)
     * Returns video_id
     */
    public function publishSilentVideo(string $filePublicURL): ?string
    {
        $response = Http::post(
            "https://graph.facebook.com/v24.0/{$this->pageId}/videos",
            [
                'access_token' => $this->pageAccessToken,
                'file_url' => $filePublicURL,
                'published' => 'false',
            ]
        );

        Log::info("Response : ", [$response]);

        $videoId = $response->json('id');

        return $videoId;
    }


    /**
     * Publish a visible video post on the Page
     */
    public function publishPublicVideo(string $fileHandle, string $title = '', string $desc = ''): ?string
    {
        $publishResponse = Http::asMultipart()->post("https://graph.facebook.com/v24.0/{$this->pageId}/videos", [
            'access_token' => $this->pageAccessToken,
            'published' => 'true',
            'title' => $title,
            'description' => $desc,
            'fbuploader_video_file_chunk' => $fileHandle,
        ]);

        return $publishResponse->json('id');
    }
}
