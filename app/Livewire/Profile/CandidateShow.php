<?php

declare(strict_types=1);

namespace App\Livewire\Profile;

use App\Models\Follow;
use App\Models\User;
use App\Support\Media;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Perfil · SocialJobs')]
class CandidateShow extends Component
{
    use WithFileUploads;

    /** Usuário resolvido a partir do parâmetro de rota (username). */
    public ?User $user = null;

    /** Aba ativa. */
    public string $tab = 'about';

    /**
     * Upload temporário do novo avatar (persistido logo em seguida).
     * Regras: PNG/JPEG/WebP, máx 2 MB (bom para foto de perfil).
     */
    #[Validate('nullable|image|mimes:jpg,jpeg,png,webp|max:2048')]
    public $avatarUpload = null;

    /**
     * Upload temporário da nova capa. Regras mais permissivas (5 MB).
     */
    #[Validate('nullable|image|mimes:jpg,jpeg,png,webp|max:5120')]
    public $coverUpload = null;

    public function mount(User $user): void
    {
        $this->user = $user->loadMissing([
            'candidateProfile.skills',
            'candidateProfile.experiences' => fn ($q) => $q->orderByDesc('current')->orderByDesc('start_date'),
            'candidateProfile.educations'  => fn ($q) => $q->orderByDesc('start_date'),
            'candidateProfile.portfolioItems',
            'stats',
        ]);

        // Registra visualização do perfil (não conta o próprio dono)
        $tracked = app(\App\Services\ViewTrackerService::class)->trackProfile($this->user->id);
        // Se realmente incrementou, recarrega o campo pra exibir o valor novo
        if ($tracked) {
            $this->user->refresh();
        }
    }

    public function setTab(string $tab): void
    {
        // Aceita todas as abas do perfil (assessments = testes de habilidades feitos)
        $this->tab = in_array($tab, ['about', 'skills', 'experience', 'education', 'portfolio', 'posts', 'assessments', 'curriculum'], true)
            ? $tab
            : 'about';
    }

    /**
     * Chamado automaticamente pelo Livewire quando o `avatarUpload` muda
     * (o usuário seleciona um arquivo). Salva imediatamente.
     */
    public function updatedAvatarUpload(): void
    {
        if (! $this->canEditProfile()) {
            $this->avatarUpload = null;
            return;
        }

        $this->validateOnly('avatarUpload');

        // Remove o avatar anterior do disco (se houver e for um arquivo local)
        if ($this->user->avatar_path && Media::exists($this->user->avatar_path)) {
            Media::delete($this->user->avatar_path);
        }

        $path = Media::store($this->avatarUpload, 'avatars');

        $this->user->avatar_path = $path;
        $this->user->save();

        $this->avatarUpload = null;
        $this->dispatch('avatar-updated');
        session()->flash('status', 'Foto de perfil atualizada!');
    }

    /**
     * Chamado automaticamente quando o `coverUpload` muda.
     */
    public function updatedCoverUpload(): void
    {
        if (! $this->canEditProfile()) {
            $this->coverUpload = null;
            return;
        }

        $this->validateOnly('coverUpload');

        if ($this->user->cover_path && Media::exists($this->user->cover_path)) {
            Media::delete($this->user->cover_path);
        }

        $path = Media::store($this->coverUpload, 'covers');

        $this->user->cover_path = $path;
        $this->user->save();

        $this->coverUpload = null;
        $this->dispatch('cover-updated');
        session()->flash('status', 'Foto de capa atualizada!');
    }

    /**
     * Remove o avatar atual (volta para as iniciais coloridas).
     */
    public function removeAvatar(): void
    {
        if (! $this->canEditProfile()) {
            return;
        }

        if ($this->user->avatar_path && Media::exists($this->user->avatar_path)) {
            Media::delete($this->user->avatar_path);
        }

        $this->user->avatar_path = null;
        $this->user->save();

        session()->flash('status', 'Foto de perfil removida.');
    }

    /**
     * Remove a capa atual (volta para o gradiente).
     */
    public function removeCover(): void
    {
        if (! $this->canEditProfile()) {
            return;
        }

        if ($this->user->cover_path && Media::exists($this->user->cover_path)) {
            Media::delete($this->user->cover_path);
        }

        $this->user->cover_path = null;
        $this->user->save();

        session()->flash('status', 'Capa removida.');
    }

    /**
     * Somente o dono do perfil pode editar suas fotos.
     */
    private function canEditProfile(): bool
    {
        return auth()->check()
            && $this->user
            && auth()->id() === $this->user->id;
    }

    public function follow(): void
    {
        if (! auth()->check() || ! $this->user || auth()->id() === $this->user->id) {
            return;
        }

        $follow = Follow::query()->firstOrCreate([
            'follower_id' => auth()->id(),
            'followed_id' => $this->user->id,
        ]);

        // Notifica só se acabou de criar (evita spam se clicar várias vezes)
        if ($follow->wasRecentlyCreated) {
            $this->user->notify(new \App\Notifications\NewFollowerNotification(auth()->user()));
        }
    }

    public function unfollow(): void
    {
        if (! auth()->check() || ! $this->user) {
            return;
        }

        Follow::query()
            ->where('follower_id', auth()->id())
            ->where('followed_id', $this->user->id)
            ->delete();
    }

    /** Bloqueia o usuário do perfil (e remove follow mútuo). */
    public function blockUser(): void
    {
        if (! auth()->check() || ! $this->user || auth()->id() === $this->user->id) return;
        \App\Models\UserBlock::firstOrCreate([
            'blocker_id' => auth()->id(),
            'blocked_id' => $this->user->id,
        ]);
        // Remove follow mútuo — quem bloqueia não segue nem é seguido
        Follow::where('follower_id', auth()->id())->where('followed_id', $this->user->id)->delete();
        Follow::where('follower_id', $this->user->id)->where('followed_id', auth()->id())->delete();
    }

    /** Desbloqueia o usuário do perfil. */
    public function unblockUser(): void
    {
        if (! auth()->check() || ! $this->user) return;
        \App\Models\UserBlock::where('blocker_id', auth()->id())
            ->where('blocked_id', $this->user->id)
            ->delete();
    }

    /**
     * Formata números grandes de forma compacta.
     */
    public function formatCount(int|float|null $value): string
    {
        $n = (int) ($value ?? 0);

        if ($n < 1000) {
            return (string) $n;
        }
        if ($n < 10000) {
            return rtrim(rtrim(number_format($n / 1000, 1, ',', ''), '0'), ',') . 'k';
        }
        if ($n < 1000000) {
            return round($n / 1000) . 'k';
        }
        return rtrim(rtrim(number_format($n / 1000000, 1, ',', ''), '0'), ',') . 'M';
    }

    public function render(): View
    {
        $profile = $this->user?->candidateProfile;

        // Monta os dados do Currículo Digital (só quando a aba estiver ativa)
        // Sempre carregamos rápido — o service usa loadMissing pra evitar N+1.
        $cvData = [];
        if ($this->user && $this->tab === 'curriculum') {
            $cvData = app(\App\Services\CurriculumService::class)->buildFor($this->user);
        }

        /*
         * Testes de habilidade que este candidato realizou.
         * Pegamos apenas a MELHOR tentativa (maior score) de cada teste,
         * porque o mesmo teste pode ter sido feito várias vezes. Só listamos
         * tentativas finalizadas (finished_at != null).
         */
        $attempts = collect();
        if ($this->user) {
            $attempts = \App\Models\SkillAssessmentAttempt::query()
                ->where('user_id', $this->user->id)
                ->whereNotNull('finished_at')
                ->with('assessment:id,title,slug,category,passing_score,duration_minutes,difficulty')
                ->orderByDesc('score')
                ->get()
                // 1 registro por skill_assessment_id — o de maior score
                ->unique('skill_assessment_id')
                ->sortByDesc('finished_at')
                ->values();
        }

        return view('livewire.profile.candidate-show', [
            'profile'         => $profile,
            'experiences'     => $profile?->experiences ?? collect(),
            'educations'      => $profile?->educations ?? collect(),
            'skills'          => $profile?->skills ?? collect(),
            'portfolioItems'  => $profile?->portfolioItems ?? collect(),
            'posts'           => $this->user
                ? $this->user->posts()->latest()->limit(10)->get()
                : collect(),
            'followersCount'  => $this->user?->followers()->count() ?? 0,
            'followingCount'  => $this->user?->follows()->count() ?? 0,
            'isFollowing'     => auth()->check() && $this->user
                ? Follow::query()
                    ->where('follower_id', auth()->id())
                    ->where('followed_id', $this->user->id)
                    ->exists()
                : false,
            'isBlocked'       => auth()->check() && $this->user
                ? auth()->user()->hasBlocked($this->user)
                : false,
            'isOwner'         => $this->canEditProfile(),
            'cvData'          => $cvData,
            'attempts'        => $attempts,
        ]);
    }
}
