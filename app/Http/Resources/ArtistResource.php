<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtistResource extends JsonResource
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
                'followers' => $this['followers']['total'],
                'url' => $this['external_urls']['spotify'],
                'profile_image' => $this['images'][0]['url'] ?? ''
            ],
            'message' => 'Datos del artista obtenidos correctamente'
        ];
    }
}
