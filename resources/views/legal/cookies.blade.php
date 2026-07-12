@extends('layouts.app', ['title' => 'Política de Cookies · SocialJobs'])

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    @include('legal._header', [
        'active'   => 'cookies',
        'title'    => 'Política de Cookies',
        'subtitle' => 'Usamos o mínimo de cookies possível — nada de rastreamento invasivo. Aqui detalhamos exatamente o que cada um faz.',
        'updated'  => '2026-07-09',
    ])

    <div class="grid gap-3 sm:grid-cols-2">
        <div class="rounded-2xl bg-emerald-50 p-4 ring-1 ring-emerald-100 dark:bg-emerald-500/10 dark:ring-emerald-500/20">
            <div class="mb-2 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500 text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
            </div>
            <h3 class="font-semibold text-emerald-900 dark:text-emerald-100">Zero cookies de publicidade</h3>
            <p class="mt-1 text-xs text-emerald-800/80 dark:text-emerald-200/80">
                Sem Google Ads, sem Meta Pixel, sem retargeting. Fim. Não somos anunciantes.
            </p>
        </div>
        <div class="rounded-2xl bg-sky-50 p-4 ring-1 ring-sky-100 dark:bg-sky-500/10 dark:ring-sky-500/20">
            <div class="mb-2 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-sky-500 text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2Zm10-10V7a4 4 0 0 0-8 0v4h8Z"/>
                </svg>
            </div>
            <h3 class="font-semibold text-sky-900 dark:text-sky-100">Só o essencial</h3>
            <p class="mt-1 text-xs text-sky-800/80 dark:text-sky-200/80">
                Cookies apenas para login, preferências (tema claro/escuro) e proteção contra CSRF.
            </p>
        </div>
    </div>

    <article class="card space-y-8 !p-6 sm:!p-10">
        <section>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">1. O que são cookies?</h2>
            <p class="mt-3 text-sm leading-relaxed text-slate-700 dark:text-slate-300">
                Cookies são pequenos arquivos que o site guarda no seu navegador para lembrar de você
                entre visitas. Alguns são <strong>estritamente necessários</strong> — sem eles o site
                nem carrega. Outros são <strong>opcionais</strong> e servem para personalizar a
                experiência.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">2. Quais cookies usamos</h2>

            <div class="mt-4 overflow-hidden rounded-2xl ring-1 ring-slate-200 dark:ring-slate-700">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                        <tr>
                            <th class="px-4 py-3">Cookie</th>
                            <th class="px-4 py-3">Finalidade</th>
                            <th class="px-4 py-3">Duração</th>
                            <th class="px-4 py-3">Tipo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 dark:text-slate-200">
                        <tr>
                            <td class="px-4 py-3 font-mono text-xs">SocialJobs_session</td>
                            <td class="px-4 py-3">Manter você logado com segurança</td>
                            <td class="px-4 py-3">Sessão</td>
                            <td class="px-4 py-3"><span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300">Essencial</span></td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-mono text-xs">XSRF-TOKEN</td>
                            <td class="px-4 py-3">Proteção contra ataques CSRF</td>
                            <td class="px-4 py-3">Sessão</td>
                            <td class="px-4 py-3"><span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300">Essencial</span></td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-mono text-xs">theme</td>
                            <td class="px-4 py-3">Lembrar sua preferência de tema (claro/escuro)</td>
                            <td class="px-4 py-3">1 ano</td>
                            <td class="px-4 py-3"><span class="rounded-full bg-sky-100 px-2 py-0.5 text-xs font-medium text-sky-800 dark:bg-sky-500/20 dark:text-sky-300">Preferência</span></td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-mono text-xs">remember_web_*</td>
                            <td class="px-4 py-3">Opção "lembrar-me" no login</td>
                            <td class="px-4 py-3">5 anos</td>
                            <td class="px-4 py-3"><span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300">Essencial</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">3. Cookies de terceiros</h2>
            <p class="mt-3 text-sm leading-relaxed text-slate-700 dark:text-slate-300">
                Não usamos ferramentas de publicidade nem redes de rastreamento. Nossa CDN
                (<strong>Cloudflare</strong>) pode gravar cookies técnicos para proteção contra ataques
                (challenge de bot) — são cookies de segurança, não de rastreio comercial.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">4. Como gerenciar</h2>
            <p class="mt-3 text-sm leading-relaxed text-slate-700 dark:text-slate-300">
                Você pode limpar ou bloquear cookies a qualquer momento nas configurações do seu
                navegador. Note que sem cookies essenciais o login e a navegação segura não funcionam.
            </p>
            <div class="mt-4 grid gap-2 sm:grid-cols-2">
                <a href="https://support.google.com/chrome/answer/95647" target="_blank" rel="noopener"
                   class="flex items-center gap-3 rounded-xl bg-slate-50 p-3 text-sm hover:bg-slate-100 dark:bg-slate-800/50 dark:hover:bg-slate-700/50">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-white ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-700">🌐</span>
                    <span class="font-medium">Chrome</span>
                </a>
                <a href="https://support.mozilla.org/pt-BR/kb/protecao-aprimorada-contra-rastreamento-firefox-desktop" target="_blank" rel="noopener"
                   class="flex items-center gap-3 rounded-xl bg-slate-50 p-3 text-sm hover:bg-slate-100 dark:bg-slate-800/50 dark:hover:bg-slate-700/50">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-white ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-700">🦊</span>
                    <span class="font-medium">Firefox</span>
                </a>
                <a href="https://support.apple.com/pt-br/guide/safari/sfri11471/mac" target="_blank" rel="noopener"
                   class="flex items-center gap-3 rounded-xl bg-slate-50 p-3 text-sm hover:bg-slate-100 dark:bg-slate-800/50 dark:hover:bg-slate-700/50">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-white ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-700">🧭</span>
                    <span class="font-medium">Safari</span>
                </a>
                <a href="https://support.microsoft.com/pt-br/microsoft-edge/excluir-cookies-no-microsoft-edge-63947406-40ac-c3b8-57b9-2a946a29ae09" target="_blank" rel="noopener"
                   class="flex items-center gap-3 rounded-xl bg-slate-50 p-3 text-sm hover:bg-slate-100 dark:bg-slate-800/50 dark:hover:bg-slate-700/50">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-white ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-700">🪟</span>
                    <span class="font-medium">Edge</span>
                </a>
            </div>
        </section>

        <section class="rounded-2xl bg-slate-50 p-5 dark:bg-slate-800/50">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Contato</h2>
            <p class="mt-2 text-sm text-slate-700 dark:text-slate-300">
                Dúvidas sobre cookies? Escreva para
                <a href="mailto:privacidade@SocialJobs.com.br" class="font-semibold text-brand-600 hover:underline">privacidade@SocialJobs.com.br</a>.
            </p>
        </section>
    </article>
</div>
@endsection
