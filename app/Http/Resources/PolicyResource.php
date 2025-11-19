<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PolicyResource extends JsonResource
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
            'title_eng' => $this->title_eng,
            'title_bur' => $this->title_bur,
            'date' => $this->date?->toDateString(),
            'logos' => $this->logos
                ? explode('#', $this->logos)
                : [],
            // Convert "OrgA#OrgB#OrgC" → ["OrgA", "OrgB", "OrgC"]
            'organizations' => $this->organizations
                ? explode('#', $this->organizations)
                : [],
            'content_eng' => $this->content_eng, // stay as string
            'content_bur' => $this->content_bur, // stay as string
            'created_at' => $this->created_at?->toDateTimeString(),
            'thumbnail' => $this->thumbnail,
            'etag' => md5(optional($this->updated_at)->toIso8601String()),
        ];
    }
}
