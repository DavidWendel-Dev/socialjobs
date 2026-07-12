<?php

declare(strict_types=1);

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Utils;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Serviço de voz — chama endpoints compatíveis OpenAI para STT/TTS.
 * Configurável em config('ai.stt') e config('ai.tts').
 */
class AiVoiceService
{
    public function __construct(private ?Client $http = null)
    {
        $this->http ??= new Client([
            'timeout' => (int) config('ai.timeout', 60),
        ]);
    }

    /**
     * Transcreve um arquivo de áudio para texto.
     *
     * @param string $audioPath Caminho no disco default do Storage
     */
    public function transcribe(string $audioPath): string
    {
        $cfg  = (array) config('ai.stt');
        $url  = rtrim((string) ($cfg['base_url'] ?? ''), '/') . '/audio/transcriptions';
        $key  = (string) ($cfg['api_key'] ?? '');
        $model = (string) ($cfg['model'] ?? 'whisper-1');

        // Resolve o caminho absoluto/stream do áudio (aceita path relativo ao disco default)
        $stream = Storage::exists($audioPath)
            ? Storage::readStream($audioPath)
            : Utils::tryFopen($audioPath, 'r');

        try {
            $resp = $this->http->post($url, [
                'headers'   => ['Authorization' => 'Bearer ' . $key],
                'multipart' => [
                    ['name' => 'model', 'contents' => $model],
                    ['name' => 'file',  'contents' => $stream, 'filename' => basename($audioPath)],
                ],
            ]);

            $payload = json_decode((string) $resp->getBody(), true);

            return (string) ($payload['text'] ?? '');
        } catch (Throwable $e) {
            report($e);

            return '';
        }
    }

    /**
     * Sintetiza texto em voz (mp3) e devolve o path salvo no storage.
     */
    public function synthesize(string $text, ?string $voice = null): string
    {
        $cfg   = (array) config('ai.tts');
        $url   = rtrim((string) ($cfg['base_url'] ?? ''), '/') . '/audio/speech';
        $key   = (string) ($cfg['api_key'] ?? '');
        $model = (string) ($cfg['model']    ?? 'tts-1');
        $voice = $voice ?? (string) ($cfg['voice'] ?? 'alloy');

        $filename = 'tts/' . Str::uuid()->toString() . '.mp3';

        try {
            $resp = $this->http->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $key,
                    'Content-Type'  => 'application/json',
                ],
                'json'    => [
                    'model' => $model,
                    'voice' => $voice,
                    'input' => $text,
                    'format' => 'mp3',
                ],
            ]);

            Storage::put($filename, (string) $resp->getBody());

            return $filename;
        } catch (Throwable $e) {
            report($e);

            return '';
        }
    }
}
