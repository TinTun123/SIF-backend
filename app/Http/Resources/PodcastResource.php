<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PodcastResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return  [
            'id' => $this->id,
            'title' => $this->title,
            'podcaster' => $this->podcaster,
            'cover_url' => $this->cover_url,
            'description' => $this->description,
            'created_at' => $this->created_at,

            // include all related episodes
            'episodes' => EpisodeResource::collection($this->whenLoaded('episodes')),
        ];
    }
}
