<div class="mx-auto max-w-6xl space-y-4">
    {{-- ============================================================
         Header
         ============================================================ --}}
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('company.assessments.index') }}"
           class="inline-flex items-center gap-1 rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-600 shadow-soft ring-1 ring-slate-200 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700">
            ← Todos os testes
        </a>
        <div class="flex-1 min-w-0">
            <h1 class="truncate font-display text-xl font-bold sm:text-2xl">
                {{ $assessment->title }}
            </h1>
            <p class="truncate text-xs text-slate-500">
                {{ $assessment->category }} · {{ $assessment->difficultyLabel() }} · nota mínima {{ $assessment->passing_score }}%
            </p>
        </div>
        <a href="{{ route('company.assessments.edit', $assessment) }}"
           class="inline-flex items-center gap-1 rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
            <x-icon name="pencil" class="h-3.5 w-3.5"/> Editar
        </a>
        <button type="button" wire:click="openInviteModal"
                class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-3 py-1.5 text-xs font-bold text-white hover:bg-brand-600 sm:text-sm">
            <x-icon name="sparkles" class="h-4 w-4"/> Enviar convite
        </button>
    </div>

    {{-- ============================================================
         Estatísticas
         ============================================================ --}}
    <div class="grid grid-cols-2 gap-2 sm:grid-cols-5 sm:gap-3">
        <div class="rounded-2xl bg-white p-3 text-center shadow-soft ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-800">
            <div class="font-display text-2xl font-bold">{{ $stats['total'] }}</div>
            <p class="text-[10px] uppercase tracking-wider text-slate-500">Convites</p>
        </div>
        <div class="rounded-2xl bg-white p-3 text-center shadow-soft ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-800">
            <div class="font-display text-2xl font-bold text-brand-600">
                {{ $stats['completed'] }}
                @if ($stats['total'] > 0)
                    <span class="text-xs text-slate-400">
                        ({{ (int) round($stats['completed'] / $stats['total'] * 100) }}%)
                    </span>
                @endif
            </div>
            <p class="text-[10px] uppercase tracking-wider text-slate-500">Respondidos</p>
        </div>
        <div class="rounded-2xl bg-white p-3 text-center shadow-soft ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-800">
            <div class="font-display text-2xl font-bold">
                {{ $stats['avg_score'] !== null ? $stats['avg_score'] . '%' : '—' }}
            </div>
            <p class="text-[10px] uppercase tracking-wider text-slate-500">Média</p>
        </div>
        <div class="rounded-2xl bg-white p-3 text-center shadow-soft ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-800">
            <div class="font-display text-2xl font-bold text-emerald-600">{{ $stats['passed'] }}</div>
            <p class="text-[10px] uppercase tracking-wider text-slate-500">Aprovados</p>
        </div>
        <div class="col-span-2 rounded-2xl bg-white p-3 text-center shadow-soft ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-800 sm:col-span-1">
            <div class="font-display text-2xl font-bold text-rose-500">{{ $stats['failed'] }}</div>
            <p class="text-[10px] uppercase tracking-wider text-slate-500">Reprovados</p>
        </div>
    </div>

    {{-- ============================================================
         Tabela de convites
         ============================================================ --}}
    <div class="overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-800">
        <div class="border-b border-slate-100 px-4 py-3 dark:border-slate-800">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Convites enviados</p>
        </div>

        @if ($invitations->isEmpty())
            <div class="p-6 text-center text-sm text-slate-500">
                Nenhum convite enviado ainda. Clique em <strong>Enviar convite</strong> acima para começar.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-xs sm:text-sm">
                    <thead class="border-b border-slate-100 bg-slate-50/50 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:border-slate-800 dark:bg-slate-800/30">
                        <tr>
                            <th class="px-3 py-2">Candidato</th>
                            <th class="px-3 py-2">Status</th>
                            <th class="px-3 py-2">Score</th>
                            <th class="px-3 py-2">Integridade</th>
                            <th class="px-3 py-2">Concluído em</th>
                            <th class="px-3 py-2 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invitations as $inv)
                            @php
                                $attempt = $inv->attempt;
                                $badge = match ($inv->status) {
                                    'completed' => ['bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300', 'Concluído'],
                                    'opened'    => ['bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300', 'Iniciado'],
                                    'expired'   => ['bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400', 'Expirado'],
                                    default     => ['bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300', 'Pendente'],
                                };
                                $integrity = $attempt?->integrity_status;
                            @endphp
                            <tr wire:key="inv-{{ $inv->id }}"
                                class="border-b border-slate-50 last:border-b-0 dark:border-slate-800/50">
                                <td class="px-3 py-2.5 align-top">
                                    <div class="flex items-center gap-2">
                                        @if ($inv->candidate)
                                            <x-avatar :user="$inv->candidate" size="sm"/>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="truncate font-semibold">
                                                {{ $inv->candidate?->name ?? $inv->candidate_email }}
                                            </p>
                                            @if ($inv->candidate)
                                                <p class="truncate text-[11px] text-slate-500">{{ $inv->candidate_email }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-2.5 align-top">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $badge[0] }}">
                                        {{ $badge[1] }}
                                    </span>
                                </td>
                                <td class="px-3 py-2.5 align-top font-mono font-bold">
                                    @if ($attempt)
                                        <span class="{{ $attempt->passed ? 'text-emerald-600' : 'text-rose-500' }}">
                                            {{ $attempt->score }}/100
                                        </span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2.5 align-top">
                                    @if ($integrity === 'clean')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">
                                            ✓ Limpo
                                        </span>
                                    @elseif ($integrity === 'suspicious')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">
                                            ⚠ Suspeito
                                        </span>
                                    @elseif ($integrity === 'auto_terminated')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2 py-0.5 text-[11px] font-semibold text-rose-700 dark:bg-rose-500/15 dark:text-rose-300">
                                            ✗ Anulado
                                        </span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2.5 align-top text-slate-500">
                                    {{ $inv->completed_at?->format('d/m/Y H:i') ?? '—' }}
                                </td>
                                <td class="px-3 py-2.5 align-top text-right">
                                    <div class="flex flex-wrap items-center justify-end gap-1"
                                         x-data="{ copied: false }">
                                        @if ($inv->candidate)
                                            <a href="{{ url('/u/' . ($inv->candidate->username ?? $inv->candidate->id) . '?tab=curriculum') }}"
                                               class="rounded-lg border border-slate-200 px-2 py-1 text-[11px] font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                                                Ver CV
                                            </a>
                                        @endif
                                        @if (in_array($inv->status, ['pending', 'opened'], true))
                                            <button type="button"
                                                    x-on:click="navigator.clipboard.writeText('{{ $inv->invitationUrl() }}').then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                                                    class="rounded-lg bg-brand-100 px-2 py-1 text-[11px] font-semibold text-brand-700 hover:bg-brand-200 dark:bg-brand-500/15 dark:text-brand-300">
                                                <span x-show="!copied">Copiar convite</span>
                                                <span x-show="copied" x-cloak>✓ Copiado</span>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- ============================================================
         Modal de convite
         ============================================================ --}}
    @if ($showInviteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm"
             wire:click.self="closeInviteModal">
            <div class="w-full max-w-md rounded-2xl bg-white p-5 shadow-2xl dark:bg-slate-900 sm:p-6">
                <div class="mb-3 flex items-center justify-between">
                    <p class="font-display text-lg font-bold">Convidar candidato</p>
                    <button type="button" wire:click="closeInviteModal"
                            class="grid h-8 w-8 place-items-center rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">
                        <x-icon name="x" class="h-4 w-4"/>
                    </button>
                </div>

                @if ($lastInviteUrl)
                    <div class="mb-3 space-y-2 rounded-xl bg-emerald-50 p-3 text-xs dark:bg-emerald-500/10"
                         x-data="{ copied: false }">
                        <p class="font-semibold text-emerald-700 dark:text-emerald-300">
                            ✓ Convite criado! Copie o link e envie por email/WhatsApp:
                        </p>
                        <div class="flex items-center gap-2">
                            <input type="text" readonly
                                   value="{{ $lastInviteUrl }}"
                                   class="input flex-1 text-[11px]">
                            <button type="button"
                                    x-on:click="navigator.clipboard.writeText('{{ $lastInviteUrl }}').then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                                    class="rounded-xl bg-brand-500 px-3 py-2 text-[11px] font-bold text-white hover:bg-brand-600">
                                <span x-show="!copied">Copiar</span>
                                <span x-show="copied" x-cloak>✓</span>
                            </button>
                        </div>
                    </div>
                @endif

                <label class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-300">
                    Email do candidato
                </label>
                <input type="email" wire:model="inviteEmail" class="input"
                       placeholder="joao@empresa.com" autofocus>
                @error('inviteEmail')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror

                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" wire:click="closeInviteModal"
                            class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                        Fechar
                    </button>
                    <button type="button" wire:click="sendInvite"
                            wire:loading.attr="disabled"
                            wire:target="sendInvite"
                            class="rounded-xl bg-brand-500 px-4 py-2 text-xs font-bold text-white hover:bg-brand-600 disabled:opacity-50">
                        <span wire:loading.remove wire:target="sendInvite">Criar convite</span>
                        <span wire:loading wire:target="sendInvite">Gerando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
