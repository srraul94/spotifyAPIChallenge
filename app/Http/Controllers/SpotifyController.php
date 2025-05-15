<?php

namespace App\Http\Controllers;

use App\Services\SpotifyService;
use http\Env\Request;
use Illuminate\Http\Request as HttpRequest;

class SpotifyController extends Controller
{
    protected SpotifyService $spotify;

    public function __construct(SpotifyService $spotify)
    {
        $this->spotify = $spotify;
    }

    public function getAccessToken()
    {
        $response = $this->spotify->getSpotifyAccessToken();
        return $response;
    }

    public function getArtistByID(HttpRequest $request, $artistID)
    {
        $response = $this->spotify->getSpotifyArtistByID($artistID);
        return $response;
    }
}
