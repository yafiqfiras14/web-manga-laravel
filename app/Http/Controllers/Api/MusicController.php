<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\SoundCloudException;
use App\Http\Controllers\Controller;
use App\Services\SoundCloudService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MusicController extends Controller
{
    public function __construct(private readonly SoundCloudService $soundcloud)
    {
    }

    public function search(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q'));
        $limit = (int) min(max((int) $request->query('limit', 6), 1), 20);

        if (strlen($query) < 2) {
            return response()->json(['tracks' => []]);
        }

        try {
            $tracks = $this->soundcloud->searchTracks($query, $limit);
        } catch (SoundCloudException $exception) {
            return response()->json([
                'tracks' => [],
                'error' => $exception->getMessage(),
            ], 503);
        }

        return response()->json(['tracks' => $tracks]);
    }

    public function track(string $id): JsonResponse
    {
        $track = $this->soundcloud->track($id);

        if ($track === null) {
            return response()->json(['error' => 'Track tidak ditemukan.'], 404);
        }

        return response()->json($track);
    }

    public function stream(string $id): JsonResponse
    {
        try {
            $stream = $this->soundcloud->stream($id);
        } catch (SoundCloudException $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }

        return response()->json([
            'type' => $stream['type'],
            'is_preview' => $stream['is_preview'],
            'url' => $stream['type'] === 'hls'
                ? route('api.hls', ['id' => $id])
                : $stream['url'],
        ]);
    }

    public function hls(string $id)
    {
        try {
            $playlist = $this->soundcloud->hlsPlaylist($id);
        } catch (SoundCloudException $exception) {
            return response()->json(['error' => $exception->getMessage()], 502);
        }

        return response($playlist, 200, [
            'Content-Type' => 'application/vnd.apple.mpegurl',
            'Cache-Control' => 'no-store, max-age=0',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }

    /**
     * Proxy segmen HLS. URL segmen ditandatangani SoundCloud dan tidak
     * mengizinkan CORS, jadi harus dilewatkan lewat server ini.
     */
    public function segment(Request $request)
    {
        $url = SoundCloudService::base64UrlDecode((string) $request->query('u'));

        abort_unless(
            is_string($url) && $url !== '' && $this->soundcloud->isAllowedMediaUrl($url),
            400,
            'URL media tidak valid.'
        );

        $headers = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 '
                .'(KHTML, like Gecko) Chrome/126.0 Safari/537.36',
            'Accept' => '*/*',
        ];

        if ($range = $request->header('Range')) {
            $headers['Range'] = $range;
        }

        try {
            $response = Http::withHeaders($headers)->timeout(30)->get($url);
        } catch (\Throwable) {
            abort(502, 'Gagal mengambil audio dari SoundCloud.');
        }

        if ($response->failed() && $response->status() !== 206) {
            abort(502, 'SoundCloud menolak permintaan audio.');
        }

        $responseHeaders = [
            'Content-Type' => $response->header('Content-Type') ?: 'application/octet-stream',
            'Cache-Control' => 'public, max-age=3600',
            'Accept-Ranges' => 'bytes',
            'Access-Control-Allow-Origin' => '*',
        ];

        foreach (['Content-Length', 'Content-Range'] as $header) {
            if ($value = $response->header($header)) {
                $responseHeaders[$header] = $value;
            }
        }

        return response($response->body(), $response->status() === 206 ? 206 : 200, $responseHeaders);
    }
}
