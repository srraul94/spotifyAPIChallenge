<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlbumResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'album' => [
                'id' => $this['id'],
                'name' => $this['name'],
                'release_date' => $this['release_date'],
                'url' => $this['external_urls']['spotify'],
                'image' => $this['images'][0]['url'] ?? '',
                'artist' => new ArtistForAlbumResource($this['artists'][0]),
            ],
            'message' => 'Datos del album obtenidos correctamente'
        ];
    }
}
