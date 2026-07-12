<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Cliente da API pública do Deezer.
 * Não requer autenticação nem chave — usa o endpoint aberto de busca de tracks.
 * https://developers.deezer.com/api/search
 *
 * Este service é usado como PROXY do backend para evitar problemas de CORS
 * quando o browser tenta chamar diretamente a Deezer.
 */
class DeezerService
{
    private const BASE_URL = 'https://api.deezer.com';

    /**
     * Busca faixas por termo. Retorna array simplificado apenas com o que a UI precisa.
     * Faz cache de 30 minutos para não estourar rate limit da API pública.
     *
     * @return array<int, array{
     *     id:int, title:string, artist:string,
     *     album:string, cover:string, preview:string,
     *     duration:int, link:string
     * }>
     */
    public function searchTracks(string $query, int $limit = 8): array
    {
        $query = trim($query);
        if (mb_strlen($query) < 2) {
            return [];
        }

        $cacheKey = 'deezer:search:' . md5(mb_strtolower($query)) . ':' . $limit;

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($query, $limit) {
            try {
                $response = Http::timeout(6)
                    ->acceptJson()
                    ->withHeaders(['User-Agent' => 'SocialJobs/1.0'])
                    ->get(self::BASE_URL . '/search/track', [
                        'q'     => $query,
                        'limit' => $limit,
                    ]);

                if (! $response->successful()) {
                    return [];
                }

                $tracks = (array) ($response->json('data') ?? []);

                return array_map(fn (array $t) => [
                    'id'       => (int) ($t['id'] ?? 0),
                    'title'    => (string) ($t['title_short'] ?? $t['title'] ?? ''),
                    'artist'   => (string) ($t['artist']['name'] ?? ''),
                    'album'    => (string) ($t['album']['title'] ?? ''),
                    'cover'    => (string) ($t['album']['cover_medium'] ?? $t['album']['cover'] ?? ''),
                    'preview'  => (string) ($t['preview'] ?? ''),
                    'duration' => (int) ($t['duration'] ?? 0),
                    'link'     => (string) ($t['link'] ?? ''),
                ], array_slice($tracks, 0, $limit));
            } catch (\Throwable $e) {
                report($e);
                return [];
            }
        });
    }
}
