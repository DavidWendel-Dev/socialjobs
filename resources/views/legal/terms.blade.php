@extends('layouts.app', ['title' => 'Termos de Uso · SocialJobs'])

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    @include('legal._header', [
        'active'   => 'terms',
        'title'    => 'Termos de Uso',
        'subtitle' => 'As regras claras que garantem que a plataforma continue justa, segura e — o mais importante — gratuita para todo mundo.',
        'updated'  => '2026-07-09',
    ])

    {{-- ============================================================
         Cards de destaque — o que importa em 3 pontos
         ============================================================ --}}
    <div class="grid gap-3 sm:grid-cols-3">
        <div class="rounded-2xl bg-emerald-50 p-4 ring-1 ring-emerald-100 dark:bg-emerald-500/10 dark:ring-emerald-500/20">
            <div class="mb-2 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500 text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
            </div>
            <h3 class="font-semibold text-emerald-900 dark:text-emerald-100">100% Gratuito</h3>
            <p class="mt-1 text-xs text-emerald-800/80 dark:text-emerald-200/80">
                Sem mensalidade, sem taxa por vaga, sem paywall. Para candidatos e empresas — hoje e sempre.
            </p>
        </div>

        <div class="rounded-2xl bg-sky-50 p-4 ring-1 ring-sky-100 dark:bg-sky-500/10 dark:ring-sky-500/20">
            <div class="mb-2 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-sky-500 text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/>
                </svg>
            </div>
            <h3 class="font-semibold text-sky-900 dark:text-sky-100">Seguro e justo</h3>
            <p class="mt-1 text-xs text-sky-800/80 dark:text-sky-200/80">
                Moderamos conteúdo, bloqueamos usuários abusivos e protegemos seus dados com criptografia.
            </p>
        </div>

        <div class="rounded-2xl bg-amber-50 p-4 ring-1 ring-amber-100 dark:bg-amber-500/10 dark:ring-amber-500/20">
            <div class="mb-2 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500 text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7Z"/>
                </svg>
            </div>
            <h3 class="font-semibold text-amber-900 dark:text-amber-100">Feito no Brasil</h3>
            <p class="mt-1 text-xs text-amber-800/80 dark:text-amber-200/80">
                Somos brasileiros e seguimos a LGPD, o Marco Civil da Internet e o CDC.
            </p>
        </div>
    </div>

    {{-- ============================================================
         Corpo do documento
         ============================================================ --}}
    <article class="card space-y-8 !p-6 sm:!p-10">
        <section>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">1. Quem somos e o que oferecemos</h2>
            <p class="mt-3 text-sm leading-relaxed text-slate-700 dark:text-slate-300">
                O <strong>SocialJobs</strong> é uma plataforma social de empregos que conecta pessoas
                candidatas a vagas com empresas contratantes. Publicamos vagas, mantemos perfis profissionais
                (currículo digital), oferecemos ferramentas de mensagem, feed social, testes de conhecimento,
                cursos internos e um assistente de IA para melhorar a sua candidatura ou o seu processo
                seletivo.
            </p>
            <div class="mt-4 rounded-2xl bg-brand-50 p-4 text-sm text-brand-800 dark:bg-brand-500/10 dark:text-brand-200">
                <strong>Nosso compromisso:</strong> o uso do SocialJobs é e sempre será gratuito, tanto
                para candidatos quanto para empresas. Não cobramos por candidaturas, publicação de vagas,
                acesso ao currículo digital ou qualquer funcionalidade essencial.
            </div>
        </section>

        <section>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">2. Cadastro e sua conta</h2>
            <ul class="mt-3 space-y-2 text-sm leading-relaxed text-slate-700 dark:text-slate-300 list-disc pl-5">
                <li>Você precisa ter <strong>16 anos ou mais</strong> para se cadastrar. Menores só podem usar com autorização e supervisão dos responsáveis.</li>
                <li>Você é responsável por manter suas credenciais seguras e por toda atividade feita na sua conta.</li>
                <li>Empresas devem se cadastrar com dados corporativos verdadeiros (CNPJ, razão social, contato válido).</li>
                <li>Você pode encerrar sua conta a qualquer momento em <a href="{{ url('/settings') }}" class="text-brand-600 hover:underline">Configurações</a>.</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">3. Conduta esperada</h2>
            <p class="mt-3 text-sm leading-relaxed text-slate-700 dark:text-slate-300">
                Ninguém curte ambiente tóxico. Ao usar o SocialJobs você concorda em <strong>não</strong>:
            </p>
            <ul class="mt-3 space-y-2 text-sm leading-relaxed text-slate-700 dark:text-slate-300 list-disc pl-5">
                <li>Publicar conteúdo ilegal, ofensivo, discriminatório, difamatório ou sexualmente explícito.</li>
                <li>Enviar spam, correntes ou propagandas fora do contexto profissional.</li>
                <li>Se passar por outra pessoa, empresa ou entidade.</li>
                <li>Cobrar taxas indevidas de candidatos (empresas: exigir depósito/pagamento antes de contratação é vedado).</li>
                <li>Coletar dados de outros usuários sem consentimento (scraping, listas etc.).</li>
                <li>Tentar burlar limites técnicos, exploits ou moderação automatizada.</li>
            </ul>
            <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">
                Contas que descumprirem podem ser suspensas ou removidas sem aviso prévio, dependendo da gravidade.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">4. Propriedade do conteúdo</h2>
            <p class="mt-3 text-sm leading-relaxed text-slate-700 dark:text-slate-300">
                O <strong>conteúdo que você publica é seu</strong> (posts, currículo, mídias). Ao publicar,
                você nos concede uma licença limitada e gratuita para exibir, distribuir e indexar esse
                conteúdo dentro da plataforma — o mínimo necessário para o serviço funcionar.
            </p>
            <p class="mt-3 text-sm leading-relaxed text-slate-700 dark:text-slate-300">
                Marca, layout, ícones, código e propriedade intelectual do SocialJobs pertencem à
                plataforma. Não copie, revenda nem crie derivadas sem autorização por escrito.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">5. Vagas e contratações</h2>
            <p class="mt-3 text-sm leading-relaxed text-slate-700 dark:text-slate-300">
                Somos apenas o meio de contato. <strong>Não somos parte no contrato de trabalho</strong> entre
                candidato e empresa. Recomendamos que ambos os lados formalizem a relação por vias oficiais
                (CLT, PJ, contrato de estágio etc.) e conheçam seus direitos e deveres trabalhistas.
            </p>
            <p class="mt-3 text-sm leading-relaxed text-slate-700 dark:text-slate-300">
                Denuncie vagas fake, exploração ou irregularidades pelo e-mail
                <a href="mailto:contato@SocialJobs.com.br" class="text-brand-600 hover:underline">contato@SocialJobs.com.br</a>.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">6. Limitação de responsabilidade</h2>
            <p class="mt-3 text-sm leading-relaxed text-slate-700 dark:text-slate-300">
                Trabalhamos duro pra manter a plataforma no ar 24/7, mas não podemos garantir 100% de
                disponibilidade. Não somos responsáveis por decisões de contratação, veracidade de dados
                de terceiros ou danos indiretos decorrentes do uso do serviço. Fazemos o melhor esforço
                dentro do que a lei permite.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">7. Mudanças nestes Termos</h2>
            <p class="mt-3 text-sm leading-relaxed text-slate-700 dark:text-slate-300">
                Podemos atualizar estes Termos para refletir novas funcionalidades, mudanças legais ou
                melhorias. Alterações relevantes serão avisadas com destaque na plataforma e por e-mail.
                Continuar usando após a atualização = aceite dos novos termos.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">8. Lei aplicável e foro</h2>
            <p class="mt-3 text-sm leading-relaxed text-slate-700 dark:text-slate-300">
                Estes Termos são regidos pela legislação brasileira. Fica eleito o foro da comarca do
                titular da conta para questões consumidoras (CDC) ou o foro do domicílio do
                SocialJobs para as demais.
            </p>
        </section>

        <section class="rounded-2xl bg-slate-50 p-5 dark:bg-slate-800/50">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Contato</h2>
            <p class="mt-2 text-sm text-slate-700 dark:text-slate-300">
                Dúvidas, sugestões ou reclamações? Fale com a gente:
                <a href="mailto:contato@SocialJobs.com.br" class="font-semibold text-brand-600 hover:underline">contato@SocialJobs.com.br</a>
            </p>
        </section>
    </article>
</div>
@endsection
