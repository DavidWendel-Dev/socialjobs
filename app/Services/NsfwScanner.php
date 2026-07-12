<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Moderação de imagens NSFW usando a API Oanor.
 *
 * A API tem 2 endpoints úteis:
 *  - /check     → retorna boolean com base num threshold (mais barato/rápido)
 *  - /classify  → retorna scores por categoria (nudity, porn, sexy, hentai, etc.)
 *
 * Este service aceita 3 tipos de input:
 *  - UploadedFile (arquivo recém-enviado pelo Livewire/form)
 *  - path absoluto de arquivo local
 *  - URL pública já hospedada
 *
 * Estratégia de envio para a Oanor:
 *  - Se for URL pública → envia como `?url=` (Oanor baixa por conta própria; mais barato)
 *  - Se for arquivo local → converte para base64 e envia como `?base64=`
 *
 * Estratégia de cache:
 *  - Hash SHA1 do conteúdo binário como chave. Se um usuário tentar subir 3x a
 *    mesma imagem, chamamos a Oanor só na primeira vez. TTL: 24h.
 *
 * Estratégia de falha (fail-open vs fail-closed):
 *  - Por padrão fail-open: se a API estiver fora/lenta, APROVA o upload (não
 *    trava o UX do usuário). O erro é logado para revisão manual depois.
 *  - Pode ser trocado para fail-closed via config('services.oanor.fail_closed').
 */
class NsfwScanner
{
    /**
     * Verificação booleana rápida — retorna TRUE se a imagem é segura (SFW),
     * FALSE se detectou NSFW acima do threshold.
     *
     * @param UploadedFile|string $image UploadedFile, caminho local ou URL pública.
     */
    public function isSafe(UploadedFile|string $image, ?float $threshold = null): bool
    {
        $threshold ??= (float) config('services.oanor.threshold', 0.6);
        $key = (string) config('services.oanor.key');

        // Sem chave configurada → aprova tudo (evita quebrar dev/CI)
        if ($key === '') {
            Log::info('[NsfwScanner] OANOR_API_KEY vazia — pulando verificação (aprovando).');
            return true;
        }

        [$queryParam, $queryValue, $hash] = $this->buildQuery($image);
        if ($queryParam === null) {
            Log::warning('[NsfwScanner] Não foi possível preparar a imagem para envio.');
            return true;
        }

        // Cache por conteúdo (não gasta chamada Oanor se já checou antes)
        $cacheKey = "nsfw:check:{$hash}:t={$threshold}";
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return (bool) $cached;
        }

        try {
            $response = Http::withHeaders(['x-oanor-key' => $key])
                ->timeout(10)
                ->connectTimeout(5)
                ->get(rtrim((string) config('services.oanor.endpoint'), '/') . '/check', [
                    $queryParam => $queryValue,
                    'threshold' => $threshold,
                ]);

            if (! $response->successful()) {
                Log::warning('[NsfwScanner] HTTP ' . $response->status() . ' na Oanor', [
                    'body' => substr($response->body(), 0, 300),
                ]);
                return true; // fail-open
            }

            $data = $response->json();
            // Formato real da Oanor: {"success":true, "data":{"safe":true|false, "nsfw_score":0.99, ...}}
            // Preferimos o campo "safe" dentro de "data" (booleano oficial da API).
            // Fallbacks: nsfw_score < threshold, ou verdict === "safe".
            $inner = is_array($data['data'] ?? null) ? $data['data'] : $data;

            if (isset($inner['safe'])) {
                $isSafe = (bool) $inner['safe'];
            } elseif (isset($inner['nsfw_score'])) {
                $isSafe = (float) $inner['nsfw_score'] < $threshold;
            } elseif (isset($inner['verdict'])) {
                $isSafe = strtolower((string) $inner['verdict']) === 'safe';
            } else {
                $isSafe = true; // fail-open se resposta inesperada
            }

            Cache::put($cacheKey, $isSafe, now()->addHours(24));
            return $isSafe;
        } catch (\Throwable $e) {
            Log::error('[NsfwScanner] Exception: ' . $e->getMessage());
            return true; // fail-open
        }
    }

    /**
     * Classificação detalhada — retorna scores por categoria (para logs/moderação).
     *
     * @return array<string,float>
     */
    public function classify(UploadedFile|string $image): array
    {
        $key = (string) config('services.oanor.key');
        if ($key === '') return [];

        [$queryParam, $queryValue, $hash] = $this->buildQuery($image);
        if ($queryParam === null) return [];

        $cacheKey = "nsfw:classify:{$hash}";
        $cached = Cache::get($cacheKey);
        if (is_array($cached)) return $cached;

        try {
            $response = Http::withHeaders(['x-oanor-key' => $key])
                ->timeout(15)
                ->connectTimeout(5)
                ->get(rtrim((string) config('services.oanor.endpoint'), '/') . '/classify', [
                    $queryParam => $queryValue,
                ]);

            if (! $response->successful()) {
                Log::warning('[NsfwScanner::classify] HTTP ' . $response->status());
                return [];
            }

            $data = $response->json();
            // Formato real da Oanor: {"data":{"classes":{"neutral":0.99,"porn":0.01,...},
            //                                 "nsfw_score":0.02, "top_class":"Neutral", "safe":true}}
            $inner   = is_array($data['data'] ?? null) ? $data['data'] : $data;
            $classes = is_array($inner['classes'] ?? null) ? $inner['classes'] : [];

            // Monta resultado com todas as categorias + nsfw_score global (chave "_nsfw")
            $result = [];
            foreach ($classes as $k => $v) {
                if (is_numeric($v)) $result[(string) $k] = (float) $v;
            }
            if (isset($inner['nsfw_score'])) {
                $result['_nsfw'] = (float) $inner['nsfw_score'];
            }
            if (isset($inner['top_class'])) {
                $result['_top'] = strtolower((string) $inner['top_class']);
            }

            Cache::put($cacheKey, $result, now()->addHours(24));
            return $result;
        } catch (\Throwable $e) {
            Log::error('[NsfwScanner::classify] Exception: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Converte o input em (queryParam, queryValue, hash).
     *
     * @return array{0:?string, 1:?string, 2:string}
     */
    private function buildQuery(UploadedFile|string $image): array
    {
        // Caso 1: UploadedFile → lê conteúdo e converte pra base64
        if ($image instanceof UploadedFile) {
            $binary = @file_get_contents($image->getRealPath());
            if ($binary === false) return [null, null, ''];
            return ['base64', base64_encode($binary), sha1($binary)];
        }

        // Caso 2: URL pública (começa com http:// ou https://)
        if (preg_match('~^https?://~i', $image)) {
            return ['url', $image, sha1($image)];
        }

        // Caso 3: caminho local
        if (is_file($image)) {
            $binary = @file_get_contents($image);
            if ($binary === false) return [null, null, ''];
            return ['base64', base64_encode($binary), sha1($binary)];
        }

        // Caso 4: já é base64 puro
        return ['base64', $image, sha1($image)];
    }
}
