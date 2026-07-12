<div class="mx-auto max-w-2xl card space-y-4">
    <div>
        <h1 class="font-display text-2xl font-bold">Simulador de entrevistas</h1>
        <p class="text-sm text-slate-500">Treine com uma IA que se adapta ao cargo e à senioridade.</p>
    </div>

    <form wire:submit.prevent="start" class="space-y-3">
        <div>
            <label class="mb-1 block text-sm font-medium">Cargo *</label>
            <input type="text" wire:model="role" class="input" placeholder="Ex: Desenvolvedor Full-Stack">
            @error('role') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Senioridade</label>
            <select wire:model="seniority" class="input">
                <option>Estágio</option><option>Júnior</option><option>Pleno</option><option>Sênior</option>
            </select>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Modo</label>
            <div class="grid grid-cols-2 gap-2">
                <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 p-3 dark:border-slate-700 {{ $mode === 'text' ? 'ring-2 ring-brand-500' : '' }}">
                    <input type="radio" wire:model="mode" value="text" class="text-brand-500"> Texto
                </label>
                <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 p-3 dark:border-slate-700 {{ $mode === 'voice' ? 'ring-2 ring-brand-500' : '' }}">
                    <input type="radio" wire:model="mode" value="voice" class="text-brand-500"> Voz
                </label>
            </div>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Vinculado a uma vaga (opcional)</label>
            <input type="number" wire:model="job_id" placeholder="ID da vaga" class="input">
        </div>

        <button type="submit" class="btn-primary w-full">Iniciar entrevista</button>
    </form>
</div>
