<div class="mx-auto max-w-5xl space-y-5">
    {{-- Header --}}
    <div class="rounded-2xl bg-gradient-to-br from-brand-500 via-brand-600 to-accent p-6 text-white">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider opacity-90">
                    <x-icon name="academic" class="h-3.5 w-3.5"/>
                    Matrículas
                </div>
                <h1 class="mt-1 font-display text-2xl font-bold">
                    {{ $course->title }}
                </h1>
                <p class="mt-1 text-xs opacity-90">
                    {{ $totalLessons }} aula(s) · {{ $enrollments->count() }} matriculado(s)
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button"
                        wire:click="openInviteModal"
                        class="inline-flex items-center gap-2 rounded-xl bg-white/95 px-3 py-2 text-xs font-bold text-brand-700 shadow-soft hover:bg-white">
                    <x-icon name="sparkles" class="h-4 w-4"/>
                    Enviar convite
                </button>
                <a href="{{ route('company.courses.edit', $course) }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-white/20 px-3 py-2 text-xs font-semibold text-white hover:bg-white/30">
                    <x-icon name="pencil" class="h-4 w-4"/>
                    Editar curso
                </a>
                <a href="{{ route('company.courses.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-white/20 px-3 py-2 text-xs font-semibold text-white hover:bg-white/30">
                    <x-icon name="arrow-left" class="h-4 w-4"/>
                    Voltar
                </a>
            </div>
        </div>
    </div>

    {{-- Link de convite (caso o curso já tenha token) --}}
    @if ($course->access_token)
        <div class="card">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Link de convite (compartilhe com candidatos)</p>
            <div class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-center">
                <input type="text" readonly
                       value="{{ $course->inviteUrl() }}"
                       class="input flex-1 text-xs"
                       onclick="this.select()"/>
                <button type="button"
                        onclick="navigator.clipboard.writeText('{{ $course->inviteUrl() }}'); this.innerText = 'Copiado!'; setTimeout(() => this.innerText = 'Copiar link', 2000);"
                        class="rounded-xl bg-brand-500 px-3 py-2 text-xs font-bold text-white hover:bg-brand-600">
                    Copiar link
                </button>
            </div>
            <p class="mt-1 text-xs text-slate-500">Quem abrir o link é matriculado automaticamente após login.</p>
        </div>
    @endif

    {{-- Tabela de matriculados --}}
    @if ($enrollments->count())
        <div class="card overflow-hidden !p-0">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/60">
                        <tr class="text-left text-xs uppercase tracking-wider text-slate-500">
                            <th class="px-4 py-3">Candidato</th>
                            <th class="px-4 py-3">Progresso</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Matriculado em</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($enrollments as $e)
                            <tr wire:key="enr-{{ $e->id }}">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <x-avatar :user="$e->user" size="sm"/>
                                        <div class="min-w-0">
                                            <p class="truncate font-semibold">{{ $e->user?->name ?? 'Usuário removido' }}</p>
                                            <p class="truncate text-xs text-slate-500">{{ $e->user?->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="h-2 w-24 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                            <div class="h-full bg-brand-500" style="width: {{ $e->_progress }}%"></div>
                                        </div>
                                        <span class="text-xs font-bold">{{ $e->_progress }}%</span>
                                    </div>
                                    <p class="mt-1 text-[10px] text-slate-500">{{ $e->_completed }}/{{ $e->_total_lessons }} aulas</p>
                                </td>
                                <td class="px-4 py-3">
                                    @if ($e->completed_at)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">
                                            <x-icon name="check" class="h-3 w-3"/>
                                            Concluído
                                        </span>
                                    @elseif ($e->_progress > 0)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">
                                            Em andamento
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-700 dark:bg-slate-700 dark:text-slate-300">
                                            Não iniciado
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-500">
                                    {{ $e->created_at?->diffForHumans() }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="rounded-2xl border border-dashed border-slate-200 bg-white p-8 text-center dark:border-slate-700 dark:bg-slate-900">
            <div class="mx-auto mb-3 grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800">
                <x-icon name="user" class="h-6 w-6"/>
            </div>
            <p class="font-semibold">Nenhum candidato matriculado ainda</p>
            <p class="mt-1 text-sm text-slate-500">Envie o link de convite para começar a inscrever pessoas.</p>
            <button type="button"
                    wire:click="openInviteModal"
                    class="mt-4 inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2 text-sm font-bold text-white hover:bg-brand-600">
                <x-icon name="sparkles" class="h-4 w-4"/>
                Enviar convite
            </button>
        </div>
    @endif

    {{-- Modal de convite --}}
    @if ($showInviteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4"
             wire:click.self="closeInviteModal">
            <div class="w-full max-w-md rounded-2xl bg-white p-5 shadow-2xl dark:bg-slate-900">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="font-display text-lg font-bold">Enviar convite</h3>
                        <p class="text-xs text-slate-500">Informe o email do candidato. Se já for cadastrado, é matriculado direto.</p>
                    </div>
                    <button type="button" wire:click="closeInviteModal"
                            class="grid h-8 w-8 place-items-center rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">
                        <x-icon name="x" class="h-4 w-4"/>
                    </button>
                </div>

                <div class="mt-4 space-y-3">
                    <div>
                        <label class="mb-1 block text-xs font-semibold">Email do candidato</label>
                        <input type="email" wire:model="inviteEmail" class="input" placeholder="candidato@email.com"/>
                        @error('inviteEmail') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <button type="button" wire:click="invite"
                            class="btn-primary w-full">
                        Matricular / gerar convite
                    </button>

                    @if ($lastInviteResult)
                        <div class="rounded-xl bg-brand-50 p-3 text-xs text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
                            {{ $lastInviteResult }}
                        </div>
                    @endif

                    @if ($course->access_token)
                        <div class="border-t border-slate-100 pt-3 dark:border-slate-800">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Ou compartilhe o link direto</p>
                            <div class="mt-2 flex gap-2">
                                <input type="text" readonly
                                       value="{{ $course->inviteUrl() }}"
                                       class="input flex-1 text-xs"
                                       onclick="this.select()"/>
                                <button type="button"
                                        onclick="navigator.clipboard.writeText('{{ $course->inviteUrl() }}'); this.innerText = 'OK'; setTimeout(() => this.innerText = 'Copiar', 2000);"
                                        class="rounded-xl bg-slate-800 px-3 py-2 text-xs font-bold text-white hover:bg-slate-900">
                                    Copiar
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
