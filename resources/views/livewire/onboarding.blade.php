<div class="mx-auto max-w-2xl">
    <div class="card">
        {{-- Progress --}}
        <div class="mb-6 flex items-center gap-2">
            @for ($i = 1; $i <= 4; $i++)
                <div class="h-2 flex-1 rounded-full {{ $i <= $step ? 'bg-brand-500' : 'bg-slate-200 dark:bg-slate-700' }}"></div>
            @endfor
        </div>
        <p class="mb-2 text-xs uppercase tracking-widest text-slate-500">Passo {{ $step }} de 4</p>

        @if ($step === 1)
            <h1 class="font-display text-3xl font-bold">Bem-vindo! Vamos começar 👋</h1>
            <p class="mt-2 text-slate-600 dark:text-slate-300">Uma foto profissional e uma headline chamativa fazem toda a diferença.</p>

            <div class="mt-6 space-y-4">
                <div>
                    <label class="mb-1 block text-sm font-medium">Foto de perfil</label>
                    <input type="file" wire:model="photo" accept="image/*" class="block w-full text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Headline</label>
                    <input type="text" wire:model="headline" placeholder="Ex: Desenvolvedor Full-Stack apaixonado por Laravel" class="input">
                </div>
            </div>
        @elseif ($step === 2)
            <h1 class="font-display text-3xl font-bold">Suas habilidades ⚡</h1>
            <p class="mt-2 text-slate-600 dark:text-slate-300">Adicione até 15 skills. Elas nos ajudam a te encontrar as melhores vagas.</p>

            <form wire:submit.prevent="addSkill" class="mt-6 flex gap-2">
                <input type="text" wire:model="skillInput" placeholder="Ex: PHP, Laravel, Product Design..." class="input">
                <button type="submit" class="btn-primary">+</button>
            </form>

            <div class="mt-4 flex flex-wrap gap-2">
                @foreach ($skills as $i => $s)
                    <span class="chip bg-brand-100 text-brand-700">
                        {{ $s }}
                        <button wire:click="removeSkill({{ $i }})" class="ml-1">&times;</button>
                    </span>
                @endforeach
            </div>
        @elseif ($step === 3)
            <h1 class="font-display text-3xl font-bold">Sua trajetória 💼</h1>
            <p class="mt-2 text-slate-600 dark:text-slate-300">Conte um pouco sobre sua experiência profissional atual ou mais relevante.</p>
            <textarea wire:model="experience" rows="8" class="input mt-6" placeholder="Ex: Desenvolvedor no SocialJobs desde 2023, focado em..."></textarea>
        @else
            <h1 class="font-display text-3xl font-bold">Seu objetivo 🎯</h1>
            <p class="mt-2 text-slate-600 dark:text-slate-300">O que você quer alcançar? Isso guia nossas sugestões de vagas e cursos.</p>
            <textarea wire:model="objective" rows="6" class="input mt-6" placeholder="Ex: Quero conquistar uma vaga de tech lead em produto SaaS remoto..."></textarea>
        @endif

        <div class="mt-8 flex justify-between">
            <button wire:click="back" class="btn-ghost" @if ($step === 1) disabled @endif>Voltar</button>
            @if ($step < 4)
                <button wire:click="next" class="btn-primary">Continuar <x-icon name="arrow-right" class="w-4 h-4"/></button>
            @else
                <button wire:click="finish" class="btn-primary">Concluir <x-icon name="check" class="w-4 h-4"/></button>
            @endif
        </div>
    </div>
</div>
