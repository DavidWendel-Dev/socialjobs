<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Minifica o HTML de respostas 2xx/3xx para reduzir bytes trafegados.
 * Preserva integralmente o conteúdo de <pre>, <textarea>, <script> e <style>
 * (que dependem de whitespace exato). Comentários HTML são removidos exceto
 * os condicionais do IE e Blade markers "<!--[if ...]-->".
 *
 * Só roda em produção. Em dev deixa HTML "pretty" pra debugging.
 * Ignora respostas AJAX/JSON, streaming (Livewire updates), e binários.
 */
class MinifyHtml
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Só minifica em produção
        if (! app()->environment('production')) {
            return $response;
        }

        // Skip: sem body, status não-sucesso, streaming, JSON, arquivos, etc.
        if (! $this->shouldMinify($request, $response)) {
            return $response;
        }

        $html = $response->getContent();
        if (! is_string($html) || $html === '') {
            return $response;
        }

        $response->setContent($this->minify($html));
        return $response;
    }

    protected function shouldMinify(Request $request, Response $response): bool
    {
        // Só GET/HEAD; POST costuma retornar JSON ou redirect
        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            return false;
        }

        // Só status 200/206/304-like
        $status = $response->getStatusCode();
        if ($status < 200 || $status >= 400) {
            return false;
        }

        // Content-Type precisa ser HTML
        $ct = $response->headers->get('Content-Type', '');
        if (stripos($ct, 'text/html') === false) {
            return false;
        }

        // Requests do Livewire são JSON/streaming — nunca minificar
        if ($request->header('X-Livewire') || $request->is('livewire/*')) {
            return false;
        }

        return true;
    }

    /**
     * Faz a minificação HTML preservando blocos sensíveis a whitespace.
     */
    protected function minify(string $html): string
    {
        // 1) Extrai e substitui por placeholders os blocos que NÃO podem ser mexidos
        $placeholders = [];
        $index = 0;

        $preserve = function (string $tag) use (&$html, &$placeholders, &$index) {
            $html = preg_replace_callback(
                '#<' . $tag . '\b[^>]*>[\s\S]*?</' . $tag . '>#i',
                function ($m) use (&$placeholders, &$index) {
                    $key = "\x00PRESERVE_{$index}\x00";
                    $placeholders[$key] = $m[0];
                    $index++;
                    return $key;
                },
                $html
            );
        };

        $preserve('pre');
        $preserve('textarea');
        $preserve('script');
        $preserve('style');

        // 2) Remove comentários HTML comuns, MAS preserva:
        //    - Condicionais do IE (<!--[if IE]>...<![endif]-->)
        //    - Markers do Livewire 3 morph-aware (<!--[if BLOCK]><![endif]-->,
        //      <!--[if ENDBLOCK]><![endif]-->). Sem esses markers a reatividade
        //      quebra: @if/@foreach param de atualizar corretamente após ações
        //      Livewire (ex: preview de imagens, comentários, reações).
        $html = preg_replace('/<!--(?!\s*\[if )(?:(?!-->).)*-->/s', '', $html);

        // 3) Colapsa whitespace fora de tags — mas preserva espaço entre tags inline
        //    Regra prática: substitui runs de whitespace por 1 espaço só
        $html = preg_replace('/\s+/', ' ', $html);

        // 4) Remove espaço entre tags block-level (>< pattern)
        $html = preg_replace('/>\s+</', '><', $html);

        // 5) Espaço no início/fim
        $html = trim($html);

        // 6) Restaura os blocos preservados
        if (! empty($placeholders)) {
            $html = strtr($html, $placeholders);
        }

        return $html;
    }
}
