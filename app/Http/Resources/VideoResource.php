<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'video_url' => $this->video_url
                ? asset($this->video_url)
                : null,
            'thumbnail' => $this->thumbnail
                ? asset($this->thumbnail)
                : null,
            'playlist' => $this->playlist,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
