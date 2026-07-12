<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Response;

/**
 * Gera um sitemap.xml dinâmico com as rotas públicas do SocialJobs.
 * Inclui páginas estáticas + vagas ativas + perfis públicos recentes.
 *
 * Cacheado por 6h para reduzir carga (o cronjob pode chamar via HEAD/GET).
 */
class SitemapController extends Controller
{
    public function index(): Response
    {
        $xml = cache()->remember('sitemap.xml.v1', now()->addHours(6), function () {
            return $this->build();
        });

        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    protected function build(): string
    {
        $base = rtrim(url('/'), '/');
        $now  = now()->toIso8601String();

        // Páginas estáticas
        $urls = [
            ['loc' => $base . '/',                'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => $base . '/jobs',            'priority' => '0.9', 'changefreq' => 'hourly'],
            ['loc' => $base . '/skill-assessments','priority' => '0.6', 'changefreq' => 'weekly'],
            ['loc' => $base . '/leaderboard',     'priority' => '0.5', 'changefreq' => 'daily'],
            ['loc' => $base . '/legal/terms',     'priority' => '0.3', 'changefreq' => 'monthly'],
            ['loc' => $base . '/legal/privacy',   'priority' => '0.3', 'changefreq' => 'monthly'],
            ['loc' => $base . '/legal/cookies',   'priority' => '0.3', 'changefreq' => 'monthly'],
        ];

        // Vagas ativas
        try {
            if (class_exists(\App\Models\JobListing::class)) {
                \App\Models\JobListing::query()
                    ->whereNull('closed_at')
                    ->latest('updated_at')
                    ->limit(2000)
                    ->get(['slug', 'id', 'updated_at'])
                    ->each(function ($job) use (&$urls, $base) {
                        $slug = $job->slug ?? $job->id;
                        $urls[] = [
                            'loc'        => $base . '/jobs/' . $slug,
                            'priority'   => '0.7',
                            'changefreq' => 'weekly',
                            'lastmod'    => optional($job->updated_at)->toIso8601String(),
                        ];
                    });
            }
        } catch (\Throwable $e) {
            // Silencia se DB / model não estiver disponível
        }

        // Perfis públicos de empresa (páginas SEO-friendly)
        try {
            if (class_exists(\App\Models\CompanyProfile::class)) {
                \App\Models\CompanyProfile::query()
                    ->whereNotNull('slug')
                    ->latest('updated_at')
                    ->limit(2000)
                    ->get(['slug', 'updated_at'])
                    ->each(function ($c) use (&$urls, $base) {
                        $urls[] = [
                            'loc'        => $base . '/c/' . $c->slug,
                            'priority'   => '0.5',
                            'changefreq' => 'weekly',
                            'lastmod'    => optional($c->updated_at)->toIso8601String(),
                        ];
                    });
            }
        } catch (\Throwable $e) {
            //
        }

        // Perfis públicos de candidatos com username
        try {
            \App\Models\User::query()
                ->whereNotNull('username')
                ->latest('updated_at')
                ->limit(2000)
                ->get(['username', 'updated_at'])
                ->each(function ($u) use (&$urls, $base) {
                    $urls[] = [
                        'loc'        => $base . '/u/' . $u->username,
                        'priority'   => '0.4',
                        'changefreq' => 'weekly',
                        'lastmod'    => optional($u->updated_at)->toIso8601String(),
                    ];
                });
        } catch (\Throwable $e) {
            //
        }

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $u) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . htmlspecialchars($u['loc'], ENT_XML1 | ENT_QUOTES) . "</loc>\n";
            $xml .= '    <lastmod>' . ($u['lastmod'] ?? $now) . "</lastmod>\n";
            $xml .= '    <changefreq>' . $u['changefreq'] . "</changefreq>\n";
            $xml .= '    <priority>' . $u['priority'] . "</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }
}
