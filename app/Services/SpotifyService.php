<?php

namespace App\Services;

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

    public function getSpotifyArtistByID($artistId)
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

    private function prepareSpotifyArtistResponse($responseSpotify): array
    {
        switch ($responseSpotify->status()) {
            case 200:
                $data = $responseSpotify->json();

                return [
                    'artist' => [
                        'id' => $data['id'],
                        'name' => $data['name'],
                        'followers' => $data['followers']['total'],
                        'url' => $data['external_urls']['spotify'],
                        'profile_image' => $data['images'][0]['url'] ?? ''
                    ],
                    'message' => 'Datos del artista obtenidos correctamente'
                ];

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

    private function getCacheSpotifyAccessToken()
    {
        if (!Cache::has(self::CACHE_KEY)) {
            return null;
        }

        return Cache::get(self::CACHE_KEY);
    }
}
