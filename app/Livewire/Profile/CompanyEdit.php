<?php

declare(strict_types=1);

namespace App\Livewire\Profile;

use App\Models\CompanyProfile;
use App\Services\NsfwScanner;
use App\Support\Media;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Edição do perfil da EMPRESA logada.
 *
 * Reaproveita `users.avatar_path` (usado como logo) e `users.cover_path`
 * (capa da empresa) — mesmo mecanismo do candidato. Os demais campos
 * ficam em `company_profiles`.
 */
#[Layout('layouts.app')]
#[Title('Editar empresa · SocialJobs')]
class CompanyEdit extends Component
{
    use WithFileUploads;

    // ==== Campos do usuário (empresa) ====
    #[Validate('required|string|min:2|max:255')]
    public string $name = '';

    // ==== Campos do CompanyProfile ====
    #[Validate('required|string|min:2|max:255')]
    public string $legal_name = '';

    #[Validate('nullable|string|max:255')]
    public string $trade_name = '';

    #[Validate('nullable|string|max:255')]
    public string $industry = '';

    #[Validate('nullable|string|in:1-10,11-50,51-200,201-500,501+')]
    public string $size = '';

    #[Validate('nullable|url|max:191')]
    public string $website = '';

    #[Validate('nullable|string|max:30')]
    public string $phone = '';

    #[Validate('nullable|string|max:2000')]
    public string $about = '';

    // ==== Uploads (logo = avatar; capa = cover) ====
    #[Validate('nullable|image|mimes:jpg,jpeg,png,webp|max:2048')]
    public $avatarUpload = null;

    #[Validate('nullable|image|mimes:jpg,jpeg,png,webp|max:5120')]
    public $coverUpload = null;

    public function mount(): void
    {
        $user = auth()->user();
        abort_unless($user && ($user->type ?? '') === 'company', 403, 'Acesso restrito a empresas.');

        // Garante que o CompanyProfile exista (o observer já cria no cadastro)
        $profile = $user->companyProfile ?? CompanyProfile::firstOrCreate(
            ['user_id' => $user->id],
            ['legal_name' => $user->name ?? 'Empresa', 'slug' => Str::slug(($user->name ?? 'empresa').'-'.$user->id)]
        );

        $this->name       = $user->name ?? '';
        $this->legal_name = $profile->legal_name ?? '';
        $this->trade_name = $profile->trade_name ?? '';
        $this->industry   = $profile->industry ?? '';
        $this->size       = $profile->size ?? '';
        $this->website    = $profile->website ?? '';
        $this->phone      = $profile->phone ?? '';
        $this->about      = $profile->about ?? '';
    }

    /* ============================================================
     |  Save — grava campos textuais
     |============================================================ */

    public function save(): void
    {
        $this->validate();

        $user    = auth()->user();
        $profile = $user->companyProfile;

        // Nome visível (do user) — usado no header, avatar, notificações, etc
        $user->name = $this->name;
        $user->save();

        // Dados da empresa
        $profile->fill([
            'legal_name' => $this->legal_name,
            'trade_name' => $this->trade_name ?: null,
            'industry'   => $this->industry ?: null,
            'size'       => $this->size ?: null,
            'website'    => $this->website ?: null,
            'phone'      => $this->phone ?: null,
            'about'      => $this->about ?: null,
        ]);
        $profile->save();

        session()->flash('status', 'Perfil da empresa atualizado!');
    }

    /* ============================================================
     |  Uploads instantâneos — logo (avatar) e capa
     |============================================================ */

    public function updatedAvatarUpload(): void
    {
        $this->validateOnly('avatarUpload');

        if (! app(NsfwScanner::class)->isSafe($this->avatarUpload)) {
            $this->avatarUpload = null;
            $this->addError('avatarUpload', 'Imagem bloqueada por conter conteúdo impróprio.');
            return;
        }

        $user = auth()->user();

        if ($user->avatar_path && Media::exists($user->avatar_path)) {
            Media::delete($user->avatar_path);
        }

        $user->avatar_path = Media::store($this->avatarUpload, 'avatars');
        $user->save();

        $this->avatarUpload = null;
        session()->flash('status', 'Logo atualizado!');
    }

    public function updatedCoverUpload(): void
    {
        $this->validateOnly('coverUpload');

        if (! app(NsfwScanner::class)->isSafe($this->coverUpload)) {
            $this->coverUpload = null;
            $this->addError('coverUpload', 'Capa bloqueada por conter conteúdo impróprio.');
            return;
        }

        $user = auth()->user();

        if ($user->cover_path && Media::exists($user->cover_path)) {
            Media::delete($user->cover_path);
        }

        $user->cover_path = Media::store($this->coverUpload, 'covers');
        $user->save();

        $this->coverUpload = null;
        session()->flash('status', 'Capa atualizada!');
    }

    public function removeAvatar(): void
    {
        $user = auth()->user();
        if ($user->avatar_path && Media::exists($user->avatar_path)) {
            Media::delete($user->avatar_path);
        }
        $user->avatar_path = null;
        $user->save();
        session()->flash('status', 'Logo removido.');
    }

    public function removeCover(): void
    {
        $user = auth()->user();
        if ($user->cover_path && Media::exists($user->cover_path)) {
            Media::delete($user->cover_path);
        }
        $user->cover_path = null;
        $user->save();
        session()->flash('status', 'Capa removida.');
    }

    public function render(): View
    {
        return view('livewire.profile.company-edit', [
            'user'    => auth()->user(),
            'profile' => auth()->user()->companyProfile,
        ]);
    }
}
