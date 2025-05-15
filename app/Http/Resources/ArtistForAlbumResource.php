<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtistForAlbumResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'artist' => [
                'id' => $this['id'],
                'name' => $this['name'],
                'url' => $this['external_urls']['spotify'],
            ],
        ];
    }
}
