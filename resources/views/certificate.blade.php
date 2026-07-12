<x-app-layout>
    <div class="mx-auto max-w-3xl">
        <div class="card relative overflow-hidden !p-10 text-center">
            <div class="pointer-events-none absolute inset-0 noise-bg opacity-70"></div>
            <div class="relative">
                <span class="chip bg-brand-100 text-brand-700">Certificado verificado</span>

                <h1 class="mt-6 font-display text-4xl font-bold">Certificado de Conclusão</h1>
                <p class="mt-2 text-slate-600 dark:text-slate-300">Emitido pela plataforma SocialJobs</p>

                <div class="mt-10">
                    <p class="text-sm uppercase tracking-widest text-slate-500">Concedido a</p>
                    <p class="mt-1 font-display text-3xl font-semibold">
                        {{ optional($certificate)->user_name ?? optional(optional($certificate)->user)->name ?? 'Aluno' }}
                    </p>
                </div>

                <div class="mt-6">
                    <p class="text-sm uppercase tracking-widest text-slate-500">Pela conclusão do curso</p>
                    <p class="mt-1 font-display text-2xl">
                        {{ optional($certificate)->course_title ?? optional(optional($certificate)->course)->title ?? 'Curso SocialJobs' }}
                    </p>
                </div>

                <div class="mt-8 flex flex-wrap items-center justify-center gap-6">
                    <div class="text-left">
                        <p class="text-xs uppercase tracking-widest text-slate-500">Código</p>
                        <p class="font-mono text-sm">{{ $code }}</p>
                        <p class="mt-2 text-xs uppercase tracking-widest text-slate-500">Emitido em</p>
                        <p class="text-sm">{{ optional(optional($certificate)->issued_at)->format('d/m/Y') ?? '—' }}</p>
                    </div>
                    @if ($qr)
                        <img src="{{ $qr }}" alt="QR Code de verificação" class="h-32 w-32 rounded-xl bg-white p-2 ring-1 ring-slate-200">
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-center">
            <button onclick="window.print()" class="btn-secondary">Imprimir / Salvar PDF</button>
        </div>
    </div>
</x-app-layout>
