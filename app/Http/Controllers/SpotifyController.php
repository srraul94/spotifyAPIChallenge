<?php

namespace App\Http\Controllers;

use App\Services\SpotifyService;

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
}
