<?php

declare(strict_types=1);

namespace App\Livewire\Profile;

use App\Models\CompanyProfile;
use App\Models\Follow;
use App\Services\NsfwScanner;
use App\Support\Media;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Empresa · SocialJobs')]
class CompanyShow extends Component
{
    use WithFileUploads;

    /** Perfil resolvido pelo binding {profile:slug}. */
    public ?CompanyProfile $profile = null;

    /** Aba ativa. */
    public string $tab = 'about';

    /** Uploads instantâneos (só disponíveis para o dono). */
    #[Validate('nullable|image|mimes:jpg,jpeg,png,webp|max:2048')]
    public $avatarUpload = null;

    #[Validate('nullable|image|mimes:jpg,jpeg,png,webp|max:5120')]
    public $coverUpload = null;

    /** State para responder review (empresa dona). */
    public ?int $respondingToReviewId = null;
    public string $responseText = '';

    public function mount(CompanyProfile $profile): void
    {
        $this->profile = $profile->loadMissing([
            'user',
            'jobListings' => fn ($q) => $q->where('status', 'open')->latest('published_at'),
        ]);

        // Registra visualização do perfil da empresa (não conta o próprio dono)
        if ($this->profile->user) {
            app(\App\Services\ViewTrackerService::class)->trackProfile($this->profile->user->id);
        }
    }

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, ['about', 'jobs', 'posts', 'reviews'], true)
            ? $tab
            : 'about';
    }

    public function follow(): void
    {
        if (! auth()->check() || ! $this->profile?->user) {
            return;
        }

        $follow = Follow::query()->firstOrCreate([
            'follower_id' => auth()->id(),
            'followed_id' => $this->profile->user->id,
        ]);

        if ($follow->wasRecentlyCreated && auth()->id() !== $this->profile->user->id) {
            $this->profile->user->notify(new \App\Notifications\NewFollowerNotification(auth()->user()));
        }
    }

    public function unfollow(): void
    {
        if (! auth()->check() || ! $this->profile?->user) {
            return;
        }

        Follow::query()
            ->where('follower_id', auth()->id())
            ->where('followed_id', $this->profile->user->id)
            ->delete();
    }

    /** Bloqueia o dono do perfil de empresa. */
    public function blockUser(): void
    {
        if (! auth()->check() || ! $this->profile?->user || auth()->id() === $this->profile->user->id) return;
        \App\Models\UserBlock::firstOrCreate([
            'blocker_id' => auth()->id(),
            'blocked_id' => $this->profile->user->id,
        ]);
        Follow::where('follower_id', auth()->id())->where('followed_id', $this->profile->user->id)->delete();
        Follow::where('follower_id', $this->profile->user->id)->where('followed_id', auth()->id())->delete();
    }

    /** Desbloqueia o dono do perfil de empresa. */
    public function unblockUser(): void
    {
        if (! auth()->check() || ! $this->profile?->user) return;
        \App\Models\UserBlock::where('blocker_id', auth()->id())
            ->where('blocked_id', $this->profile->user->id)
            ->delete();
    }

    /* ============================================================
     |  Uploads (só para o dono)
     |============================================================ */

    private function ensureOwner(): bool
    {
        return auth()->check()
            && $this->profile?->user
            && auth()->id() === $this->profile->user->id;
    }

    public function updatedAvatarUpload(): void
    {
        if (! $this->ensureOwner()) {
            $this->avatarUpload = null;
            return;
        }

        $this->validateOnly('avatarUpload');

        // Bloqueio NSFW (Oanor) — mesma proteção usada em outros uploads
        if (! app(NsfwScanner::class)->isSafe($this->avatarUpload)) {
            $this->avatarUpload = null;
            $this->addError('avatarUpload', 'Imagem bloqueada por conter conteúdo impróprio.');
            return;
        }

        $user = $this->profile->user;

        if ($user->avatar_path && Media::exists($user->avatar_path)) {
            Media::delete($user->avatar_path);
        }

        $user->avatar_path = Media::store($this->avatarUpload, 'avatars');
        $user->save();

        $this->avatarUpload = null;
        $this->profile->load('user'); // recarrega pra atualizar a URL na tela
        session()->flash('status', 'Logo atualizado!');
    }

    public function updatedCoverUpload(): void
    {
        if (! $this->ensureOwner()) {
            $this->coverUpload = null;
            return;
        }

        $this->validateOnly('coverUpload');

        if (! app(NsfwScanner::class)->isSafe($this->coverUpload)) {
            $this->coverUpload = null;
            $this->addError('coverUpload', 'Capa bloqueada por conter conteúdo impróprio.');
            return;
        }

        $user = $this->profile->user;

        if ($user->cover_path && Media::exists($user->cover_path)) {
            Media::delete($user->cover_path);
        }

        $user->cover_path = Media::store($this->coverUpload, 'covers');
        $user->save();

        $this->coverUpload = null;
        $this->profile->load('user');
        session()->flash('status', 'Capa atualizada!');
    }

    public function render(): View
    {
        $user = $this->profile?->user;

        // Endereço estruturado (json array) — formatamos "Cidade/UF" para exibir
        $address = $this->profile?->address ?? [];
        $city   = $address['municipio'] ?? null;
        $uf     = $address['uf']        ?? null;
        $location = $city && $uf ? "{$city}/{$uf}" : ($city ?: $uf);

        $isOwner = auth()->check() && $user && auth()->id() === $user->id;

        // Reviews (dados da nova aba)
        $reviews = $this->profile
            ? $this->profile->reviews()->where('is_published', true)->with('user')->latest()->limit(20)->get()
            : collect();
        $reviewsCount = $this->profile
            ? $this->profile->reviews()->where('is_published', true)->count()
            : 0;
        $averageRating = $this->profile?->averageRating() ?? 0.0;
        $recommendationRate = $this->profile?->recommendationRate() ?? 0;

        // O candidato logado pode escrever review nessa empresa?
        $canReview = false;
        $hasReviewed = false;
        if (auth()->check() && (auth()->user()->type ?? '') === 'candidate' && $this->profile) {
            $hasReviewed = \App\Models\CompanyReview::where('user_id', auth()->id())
                ->where('company_profile_id', $this->profile->id)
                ->exists();
            if (! $hasReviewed) {
                $canReview = \App\Models\Application::whereHas('jobListing', fn ($q) => $q->where('company_profile_id', $this->profile->id))
                    ->where('user_id', auth()->id())
                    ->whereIn('status', ['interview', 'offer', 'hired', 'rejected'])
                    ->exists();
            }
        }

        return view('livewire.profile.company-show', [
            'user'            => $user,
            'openJobs'        => $this->profile?->jobListings ?? collect(),
            'totalJobs'       => $this->profile?->jobListings()->count() ?? 0,
            'posts'           => $user
                ? $user->posts()->latest()->limit(10)->get()
                : collect(),
            'postsCount'      => $user ? $user->posts()->count() : 0,
            'followersCount'  => $user?->followers()->count() ?? 0,
            'location'        => $location,
            'isFollowing'     => auth()->check() && $user
                ? Follow::query()
                    ->where('follower_id', auth()->id())
                    ->where('followed_id', $user->id)
                    ->exists()
                : false,
            'isBlocked'       => auth()->check() && $user
                ? auth()->user()->hasBlocked($user)
                : false,
            'isOwner'         => $isOwner,
            'reviews'             => $reviews,
            'reviewsCount'        => $reviewsCount,
            'averageRating'       => $averageRating,
            'recommendationRate'  => $recommendationRate,
            'canReview'           => $canReview,
            'hasReviewed'         => $hasReviewed,
        ]);
    }

    /* ============================================================
     |  Reviews — resposta da empresa (só dono)
     |============================================================ */

    public function startResponse(int $reviewId): void
    {
        if (! $this->ensureOwner()) {
            return;
        }
        $this->respondingToReviewId = $reviewId;
        $this->responseText = '';
    }

    public function cancelResponse(): void
    {
        $this->respondingToReviewId = null;
        $this->responseText = '';
    }

    public function respondToReview(int $reviewId): void
    {
        if (! $this->ensureOwner() || ! $this->profile) {
            return;
        }

        $text = trim($this->responseText);
        if ($text === '') {
            $this->addError('responseText', 'Escreva uma resposta antes de enviar.');
            return;
        }

        $r = \App\Models\CompanyReview::where('id', $reviewId)
            ->where('company_profile_id', $this->profile->id)
            ->first();

        if (! $r) {
            return;
        }

        $r->company_response = $text;
        $r->company_responded_at = now();
        $r->save();

        $this->respondingToReviewId = null;
        $this->responseText = '';
        session()->flash('status', 'Resposta publicada.');
    }
}
