<?php

namespace App\Services;

use App\Http\Resources\AlbumResource;
use App\Http\Resources\ArtistResource;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SpotifyService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    private const CACHE_KEY = 'spotify_access_token';
    private const ARTISTS_ENDPOINT = 'https://api.spotify.com/v1/artists/';
    private const ALBUM_ENDPOINT = 'https://api.spotify.com/v1/albums/';


    /**
     * Retrieves the Spotify access token, either from cache or by making a request
     * to the Spotify API using client credentials.
     *
     * @return array An array containing the success status, access token, error code, and message.
     *
     * @throws \RuntimeException if Spotify API credentials are missing in the environment configuration.
     */
    public function getSpotifyAccessToken(): array
    {
        $accessToken = self::getCacheSpotifyAccessToken();

        if (!is_null($accessToken)) {
            return [
                'success' => true,
                'access_token' => $accessToken,
                'error_code' => null,
                'message' => 'Token obtenido desde caché',
            ];
        }

        $spotifyAPI = env('SPOTIFY_API_TOKEN_URI');
        $clientId = env('SPOTIFY_CLIENT_ID');
        $clientSecret = env('SPOTIFY_CLIENT_SECRET');

        if (!$clientId || !$clientSecret || !$spotifyAPI) {
            throw new \RuntimeException('Faltan credenciales de Spotify en .env');
        }

        try {
            $responseSpotify = Http::asForm()->post($spotifyAPI, [
                'grant_type' => 'client_credentials',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ]);

            $accessTokenResponse = self::prepareSpotifyAccessTokenResponse($responseSpotify);

            return $accessTokenResponse;

        } catch (\Exception $e) {
            Log::error('Exception while requesting Spotify token', [
                'message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'access_token' => null,
                'error_code' => $responseSpotify->status() ?? 500,
                'message' => 'Error del servidor de Spotify, intenta más tarde.'
            ];
        }

    }

    /**
     * Retrieves detailed information about a Spotify artist by their ID.
     *
     * @param string $artistId The Spotify ID of the artist to fetch.
     *
     * @return array|ArtistResource The artist information as an array or a resource,
     *                              depending on the response and processing logic.
     *
     * @throws \RuntimeException if the Spotify access token has expired or if API credentials
     *                           are missing in the environment configuration.
     */
    public function getSpotifyArtistByID($artistId): array|ArtistResource
    {
        $accessToken = self::getCacheSpotifyAccessToken();

        if (is_null($accessToken)) {
            throw new \RuntimeException('El token de Spotify ha caducado. Visita /api/get-spotify-access-token para obtener uno nuevo.');
        }

        $spotifyAPI = env('SPOTIFY_API_TOKEN_URI');
        $clientId = env('SPOTIFY_CLIENT_ID');
        $clientSecret = env('SPOTIFY_CLIENT_SECRET');

        if (!$clientId || !$clientSecret || !$spotifyAPI) {
            throw new \RuntimeException('Faltan credenciales de Spotify en .env');
        }

        try {
            $responseSpotify =  Http::withToken($accessToken)
                ->get(self::ARTISTS_ENDPOINT.$artistId);


            $artistResponse = self::prepareSpotifyArtistResponse($responseSpotify);

            return $artistResponse;

        } catch (\Exception $e) {
            Log::error('Exception while requesting Spotify token', [
                'message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'access_token' => null,
                'error_code' => $responseSpotify->status() ?? 500,
                'message' => $e->getMessage()
            ];
        }

    }

    /**
     * Retrieves the Spotify album details by its ID, either formatting the response
     * as an AlbumResource or returning an array with the result.
     *
     * @param string $albumId The Spotify album's unique identifier.
     *
     * @return array|AlbumResource An array containing the success status, album data,
     * error code, and message, or an AlbumResource object with formatted album details.
     *
     * @throws \RuntimeException if the Spotify access token has expired or if Spotify API credentials
     * are missing in the environment configuration.
     */
    public function getSpotifyAlbumByID($albumId): array|AlbumResource
    {
        $accessToken = self::getCacheSpotifyAccessToken();

        if (is_null($accessToken)) {
            throw new \RuntimeException('El token de Spotify ha caducado. Visita /api/get-spotify-access-token para obtener uno nuevo.');
        }

        $spotifyAPI = env('SPOTIFY_API_TOKEN_URI');
        $clientId = env('SPOTIFY_CLIENT_ID');
        $clientSecret = env('SPOTIFY_CLIENT_SECRET');

        if (!$clientId || !$clientSecret || !$spotifyAPI) {
            throw new \RuntimeException('Faltan credenciales de Spotify en .env');
        }

        try {
            $responseSpotify =  Http::withToken($accessToken)
                ->get(self::ALBUM_ENDPOINT.$albumId);


            $albumResponse = self::prepareSpotifyAlbumResponse($responseSpotify);

            return $albumResponse;

        } catch (\Exception $e) {
            Log::error('Exception while requesting Spotify token', [
                'message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'access_token' => null,
                'error_code' => $responseSpotify->status() ?? 500,
                'message' => $e->getMessage()
            ];
        }

    }

    /**
     * Prepares and formats the response data obtained from the Spotify API access token request.
     * Handles and processes the response based on the HTTP status code, logs errors when necessary,
     * and stores the access token in the cache if available.
     *
     * @param Response $responseSpotify The HTTP response object returned by the Spotify API.
     *
     * @return array An array containing the success status, access token (if applicable), error code, and message.
     *
     * @throws \RuntimeException if an unexpected status code or error occurs in the response from the Spotify API.
     */
    private function prepareSpotifyAccessTokenResponse($responseSpotify): array
    {
        switch ($responseSpotify->status()) {
            case 200:
                $data = $responseSpotify->json();
                $expiresIn = $data['expires_in'] ?? 3600;

                Cache::put(self::CACHE_KEY, $data['access_token'], now()->addSeconds($expiresIn - 60));

                return [
                    'success' => true,
                    'access_token' => $data['access_token'] ?? null,
                    'error_code' => null,
                    'message' => 'Token obtenido correctamente'
                ];

            case 400:
                $error = $responseSpotify->json();
                Log::warning('Spotify API: Bad Request', ['response' => $error]);
                return [
                    'success' => false,
                    'access_token' => null,
                    'error_code' => 400,
                    'message' => 'Solicitud incorrecta: ' . ($error['error_description'] ?? 'Bad Request')
                ];

            case 401:
                $error = $responseSpotify->json();
                Log::error('Spotify API: Unauthorized', ['response' => $error]);
                return [
                    'success' => false,
                    'access_token' => null,
                    'error_code' => 401,
                    'message' => 'No autorizado: revisa tu client_id y client_secret'
                ];

            case 500:
            case 502:
            case 503:
                Log::error('Spotify API: Server Error', [
                    'status' => $responseSpotify->status(),
                    'body' => $responseSpotify->body()
                ]);
                return [
                    'success' => false,
                    'access_token' => null,
                    'error_code' => $responseSpotify->status(),
                    'message' => 'Error del servidor de Spotify, intenta más tarde.'
                ];

            default:
                Log::error('Spotify API: Unknown status code', [
                    'status' => $responseSpotify->status(),
                    'body' => $responseSpotify->body()
                ]);
                return [
                    'success' => false,
                    'access_token' => null,
                    'error_code' => $responseSpotify->status(),
                    'message' => 'Error desconocido al solicitar el token.'
                ];
        }
    }

    /**
     * Processes the response from the Spotify API for artist-related requests and
     * returns either a formatted resource or an error array depending on the response status.
     *
     * @param Response $responseSpotify The response instance from the Spotify API.
     *
     * @return array|ArtistResource An ArtistResource instance if the response is successful (HTTP 200),
     *                               or an array containing the success status, error code, and message for other cases.
     *
     * @throws \RuntimeException if the Spotify API response structure is invalid or unexpected.
     */
    private function prepareSpotifyArtistResponse($responseSpotify): array|ArtistResource
    {
        switch ($responseSpotify->status()) {
            case 200:
                $data = $responseSpotify->json();
                return new ArtistResource($data);

            case 400:
                $error = $responseSpotify->json();
                Log::warning('Spotify API - Artists: Bad Request', ['response' => $error]);
                return [
                    'success' => false,
                    'error_code' => 400,
                    'message' => 'Solicitud incorrecta: ' . ($error['error_description'] ?? 'Bad Request')
                ];

            case 401:
                $error = $responseSpotify->json();
                Log::error('Spotify API - Artists: Unauthorized', ['response' => $error]);
                return [
                    'success' => false,
                    'error_code' => 401,
                    'message' => 'No autorizado: revisa tu client_id y client_secret'
                ];

            case 500:
            case 502:
            case 503:
                Log::error('Spotify API - Artists: Server Error', [
                    'status' => $responseSpotify->status(),
                    'body' => $responseSpotify->body()
                ]);
                return [
                    'success' => false,
                    'error_code' => $responseSpotify->status(),
                    'message' => 'Error del servidor de Spotify, intenta más tarde.'
                ];

            default:
                Log::error('Spotify API - Artists : Unknown status code', [
                    'status' => $responseSpotify->status(),
                    'body' => $responseSpotify->body()
                ]);
                return [
                    'success' => false,
                    'error_code' => $responseSpotify->status(),
                    'message' => 'Error desconocido al solicitar el token.'
                ];
        }
    }

    /**
     * Processes the Spotify API response for an album request, handling various HTTP
     * status codes and constructing the appropriate response based on the status.
     *
     * @param Response $responseSpotify The response object received from the Spotify API.
     *
     * @return array|AlbumResource Returns an AlbumResource if the request is successful (status 200),
     *                             otherwise returns an array containing success status, error code,
     *                             and an appropriate error message.
     *
     * @throws \Exception May log details of unexpected or erroneous responses but does not throw errors directly.
     */
    private function prepareSpotifyAlbumResponse($responseSpotify): array|AlbumResource
    {
        switch ($responseSpotify->status()) {
            case 200:
                $data = $responseSpotify->json();
                return new AlbumResource($data);

            case 400:
                $error = $responseSpotify->json();
                Log::warning('Spotify API - Albums: Bad Request', ['response' => $error]);
                return [
                    'success' => false,
                    'error_code' => 400,
                    'message' => 'Solicitud incorrecta: ' . ($error['error_description'] ?? 'Bad Request')
                ];

            case 401:
                $error = $responseSpotify->json();
                Log::error('Spotify API - Albums: Unauthorized', ['response' => $error]);
                return [
                    'success' => false,
                    'error_code' => 401,
                    'message' => 'No autorizado: revisa tu client_id y client_secret'
                ];

            case 500:
            case 502:
            case 503:
                Log::error('Spotify API - Albums: Server Error', [
                    'status' => $responseSpotify->status(),
                    'body' => $responseSpotify->body()
                ]);
                return [
                    'success' => false,
                    'error_code' => $responseSpotify->status(),
                    'message' => 'Error del servidor de Spotify, intenta más tarde.'
                ];

            default:
                Log::error('Spotify API - Albums: Unknown status code', [
                    'status' => $responseSpotify->status(),
                    'body' => $responseSpotify->body()
                ]);
                return [
                    'success' => false,
                    'error_code' => $responseSpotify->status(),
                    'message' => 'Error desconocido al solicitar el token.'
                ];
        }
    }

    /**
     * Retrieves the cached Spotify access token if available.
     *
     * @return string|null The cached access token, or null if it is not present in the cache.
     */
    private function getCacheSpotifyAccessToken()
    {
        if (!Cache::has(self::CACHE_KEY)) {
            return null;
        }

        return Cache::get(self::CACHE_KEY);
    }
}
