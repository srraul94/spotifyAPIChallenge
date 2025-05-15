<?php

namespace App\Http\Controllers;

use App\Services\SpotifyService;
use Illuminate\Http\Request as HttpRequest;

class SpotifyController extends Controller
{
    protected SpotifyService $spotify;

    public function __construct(SpotifyService $spotify)
    {
        $this->spotify = $spotify;
    }

    /**
     * Retrieves the access token from the Spotify service.
     *
     * @return mixed The access token retrieved from Spotify.
     */
    public function getAccessToken()
    {
        $response = $this->spotify->getSpotifyAccessToken();
        return $response;
    }

    /**
     * Retrieves artist information from Spotify by the given artist ID.
     *
     * @param HttpRequest $request The HTTP request instance.
     * @param mixed $artistID The unique identifier of the artist.
     * @return mixed The artist information retrieved from Spotify.
     */
    public function getArtistByID(HttpRequest $request, $artistID)
    {
        $response = $this->spotify->getSpotifyArtistByID($artistID);
        return $response;
    }

    /**
     * Retrieves album details by its ID using Spotify's API.
     *
     * @param HttpRequest $request The HTTP request instance containing request data.
     * @param mixed $albumID The identifier of the album to be retrieved.
     * @return mixed Returns the album details retrieved from Spotify's API.
     */
    public function getAlbumByID(HttpRequest $request, $albumID)
    {
        $response = $this->spotify->getSpotifyAlbumByID($albumID);
        return $response;
    }
}
