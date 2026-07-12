@extends('layouts.app', ['title' => 'Política de Privacidade · SocialJobs'])

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    @include('legal._header', [
        'active'   => 'privacy',
        'title'    => 'Política de Privacidade',
        'subtitle' => 'Seus dados são seus. Explicamos aqui o que coletamos, por quê e como você mantém o controle — sem juridiquês.',
        'updated'  => '2026-07-09',
    ])

    {{-- ============================================================
         Destaque: LGPD + gratuidade
         ============================================================ --}}
    <div class="grid gap-3 sm:grid-cols-2">
        <div class="rounded-2xl bg-emerald-50 p-4 ring-1 ring-emerald-100 dark:bg-emerald-500/10 dark:ring-emerald-500/20">
            <div class="mb-2 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500 text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2Zm10-10V7a4 4 0 0 0-8 0v4h8Z"/>
                </svg>
            </div>
            <h3 class="font-semibold text-emerald-900 dark:text-emerald-100">Nunca vendemos seus dados</h3>
            <p class="mt-1 text-xs text-emerald-800/80 dark:text-emerald-200/80">
                Nem para anunciantes, nem para “parceiros”, nem para ninguém. Seu perfil serve para
                conectar você a oportunidades — e ponto.
            </p>
        </div>

        <div class="rounded-2xl bg-sky-50 p-4 ring-1 ring-sky-100 dark:bg-sky-500/10 dark:ring-sky-500/20">
            <div class="mb-2 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-sky-500 text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
            </div>
            <h3 class="font-semibold text-sky-900 dark:text-sky-100">Em conformidade com a LGPD</h3>
            <p class="mt-1 text-xs text-sky-800/80 dark:text-sky-200/80">
                Lei 13.709/2018 aplicada de ponta a ponta: bases legais, minimização, transparência
                e seus direitos garantidos.
            </p>
        </div>
    </div>

    {{-- ============================================================
         Corpo
         ============================================================ --}}
    <article class="card space-y-8 !p-6 sm:!p-10">
        <section>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">1. Que dados coletamos</h2>
            <div class="mt-3 space-y-3 text-sm leading-relaxed text-slate-700 dark:text-slate-300">
                <div>
                    <p class="font-semibold text-slate-900 dark:text-white">Dados que você fornece:</p>
                    <ul class="mt-1 list-disc pl-5 space-y-1">
                        <li>Cadastro: nome, e-mail, senha (hash), telefone (opcional).</li>
                        <li>Perfil: foto, capa, experiências, formação, habilidades, links, localização.</li>
                        <li>Posts, mensagens, comentários e reações.</li>
                        <li>Candidaturas, respostas de testes e avaliações.</li>
                        <li>Empresas: CNPJ, razão social, logotipo, informações de vagas.</li>
                    </ul>
                </div>
                <div>
                    <p class="font-semibold text-slate-900 dark:text-white">Dados coletados automaticamente:</p>
                    <ul class="mt-1 list-disc pl-5 space-y-1">
                        <li>Endereço IP (para segurança e prevenção a fraude).</li>
                        <li>Tipo de dispositivo, navegador e sistema operacional.</li>
                        <li>Logs de acesso, ações e cliques dentro da plataforma.</li>
                        <li>Cookies essenciais e de preferência — veja a <a href="{{ route('legal.cookies') }}" class="text-brand-600 hover:underline">Política de Cookies</a>.</li>
                    </ul>
                </div>
            </div>
        </section>

        <section>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">2. Como usamos seus dados</h2>
            <ul class="mt-3 space-y-2 text-sm leading-relaxed text-slate-700 dark:text-slate-300 list-disc pl-5">
                <li>Manter sua conta funcionando (autenticação, perfil, mensagens).</li>
                <li>Conectar você a vagas relevantes e a outras pessoas da comunidade.</li>
                <li>Personalizar seu feed com posts que fazem sentido pra você.</li>
                <li>Enviar notificações da plataforma (você controla o quê no <a href="{{ url('/settings') }}" class="text-brand-600 hover:underline">Configurações</a>).</li>
                <li>Melhorar a plataforma com métricas agregadas e anonimizadas.</li>
                <li>Cumprir obrigações legais e proteger a comunidade contra abuso.</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">3. Com quem compartilhamos</h2>
            <p class="mt-3 text-sm leading-relaxed text-slate-700 dark:text-slate-300">
                <strong>Nada de venda de dados.</strong> Compartilhamos o mínimo, apenas com:
            </p>
            <ul class="mt-3 space-y-2 text-sm leading-relaxed text-slate-700 dark:text-slate-300 list-disc pl-5">
                <li><strong>Empresas às quais você se candidatou</strong> — recebem seu currículo e mensagens.</li>
                <li><strong>Provedores técnicos</strong> (hospedagem, CDN, e-mail transacional) — sob contrato de confidencialidade.</li>
                <li><strong>Autoridades públicas</strong> — apenas mediante ordem judicial válida.</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">4. Bases legais (LGPD)</h2>
            <p class="mt-3 text-sm leading-relaxed text-slate-700 dark:text-slate-300">
                Tratamos seus dados com base em:
                <strong>execução de contrato</strong> (Art. 7º, V) para operar sua conta,
                <strong>consentimento</strong> (Art. 7º, I) para dados sensíveis ou opcionais,
                <strong>legítimo interesse</strong> (Art. 7º, IX) para segurança e melhorias, e
                <strong>obrigação legal</strong> (Art. 7º, II) quando exigido.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">5. Seus direitos</h2>
            <p class="mt-3 text-sm leading-relaxed text-slate-700 dark:text-slate-300">
                Você tem o direito de, a qualquer momento:
            </p>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                @php
                    $rights = [
                        ['Confirmar', 'Saber se tratamos algum dado seu'],
                        ['Acessar', 'Obter cópia dos dados que temos'],
                        ['Corrigir', 'Atualizar informações incorretas'],
                        ['Anonimizar', 'Anonimizar, bloquear ou eliminar dados desnecessários'],
                        ['Portar', 'Receber os dados em formato estruturado'],
                        ['Excluir', 'Eliminar sua conta e dados'],
                        ['Revogar', 'Retirar o consentimento a qualquer momento'],
                        ['Reclamar', 'Reclamar à ANPD (autoridade nacional)'],
                    ];
                @endphp
                @foreach ($rights as [$title, $desc])
                    <div class="flex gap-3 rounded-xl bg-slate-50 p-3 dark:bg-slate-800/50">
                        <div class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-brand-500 text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $title }}</p>
                            <p class="text-xs text-slate-600 dark:text-slate-400">{{ $desc }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
            <p class="mt-4 text-sm text-slate-700 dark:text-slate-300">
                Para exercer qualquer direito, mande e-mail para
                <a href="mailto:privacidade@SocialJobs.com.br" class="font-semibold text-brand-600 hover:underline">privacidade@SocialJobs.com.br</a>.
                Respondemos em até <strong>15 dias</strong>.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">6. Segurança</h2>
            <p class="mt-3 text-sm leading-relaxed text-slate-700 dark:text-slate-300">
                Aplicamos medidas técnicas e organizacionais razoáveis: senhas com hash bcrypt/argon2,
                HTTPS obrigatório, criptografia em anexos sensíveis (mensagens), backups automatizados,
                controle de acesso interno e logs de auditoria. Se acontecer um incidente relevante,
                comunicamos você e a ANPD conforme a lei.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">7. Retenção</h2>
            <p class="mt-3 text-sm leading-relaxed text-slate-700 dark:text-slate-300">
                Mantemos seus dados enquanto sua conta estiver ativa. Ao excluir a conta, apagamos ou
                anonimizamos os dados em até <strong>30 dias</strong>, ressalvado o cumprimento de
                obrigações legais (ex.: logs de acesso pelo prazo do Marco Civil).
            </p>
        </section>

        <section>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">8. Menores de idade</h2>
            <p class="mt-3 text-sm leading-relaxed text-slate-700 dark:text-slate-300">
                Não coletamos deliberadamente dados de menores de 16 anos. Se você é responsável e
                identificou um cadastro indevido, avise em
                <a href="mailto:privacidade@SocialJobs.com.br" class="text-brand-600 hover:underline">privacidade@SocialJobs.com.br</a>
                que removemos rapidamente.
            </p>
        </section>

        <section class="rounded-2xl bg-slate-50 p-5 dark:bg-slate-800/50">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Encarregado (DPO)</h2>
            <p class="mt-2 text-sm text-slate-700 dark:text-slate-300">
                Contato do Encarregado de Proteção de Dados:
                <a href="mailto:privacidade@SocialJobs.com.br" class="font-semibold text-brand-600 hover:underline">privacidade@SocialJobs.com.br</a>
            </p>
        </section>
    </article>
</div>
@endsection
