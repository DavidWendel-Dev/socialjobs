<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use App\Models\AccountDeletionRequest;
use App\Models\DataExportRequest;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configurações · SocialJobs')]
class Index extends Component
{
    public string $tab = 'account';

    // Aba: conta
    #[Validate('required|string|max:120')]
    public string $name = '';

    #[Validate('required|email|max:191')]
    public string $email = '';

    // Aba: privacidade
    public bool $open_to_work = false;

    // Aba: LGPD — motivo da exclusão
    public string $deletionReason = '';

    /** Abas disponíveis (a de IA foi removida — chave é gerida via .env). */
    public const TABS = ['account', 'security', 'privacy', 'notifications', 'lgpd'];

    public function mount(?string $tab = null): void
    {
        $this->tab = in_array($tab, self::TABS, true) ? $tab : 'account';

        $u = auth()->user();
        $this->name         = $u->name ?? '';
        $this->email        = $u->email ?? '';
        $this->open_to_work = (bool) $u->open_to_work;
    }

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, self::TABS, true) ? $tab : 'account';
    }

    /**
     * Salva alterações na aba "Conta".
     */
    public function saveAccount(): void
    {
        $this->validateOnly('name');
        $this->validateOnly('email');

        $u = auth()->user();
        $u->name  = $this->name;
        $u->email = $this->email;
        $u->save();

        session()->flash('status', 'Conta atualizada.');
    }

    /**
     * Salva alterações na aba "Privacidade".
     */
    public function savePrivacy(): void
    {
        $u = auth()->user();
        $u->open_to_work = $this->open_to_work;
        $u->save();

        session()->flash('status', 'Preferências de privacidade salvas.');
    }

    /**
     * Cria uma solicitação de exportação de dados (LGPD).
     * O job em background prepara o zip e envia por e-mail.
     */
    public function exportData(): void
    {
        DataExportRequest::firstOrCreate(
            [
                'user_id' => auth()->id(),
                'status'  => 'pending',
            ],
            [
                'requested_at' => now(),
            ]
        );

        session()->flash('status', 'Solicitação registrada. Você receberá o arquivo por e-mail em até 24h.');
    }

    /**
     * Cria uma solicitação de exclusão de conta (LGPD).
     * A conta é agendada para exclusão em 15 dias — pode cancelar antes.
     */
    public function requestDeletion(): void
    {
        AccountDeletionRequest::firstOrCreate(
            [
                'user_id'       => auth()->id(),
                'cancelled_at'  => null,
            ],
            [
                'reason'        => trim($this->deletionReason) ?: 'Não informado',
                'scheduled_for' => now()->addDays(15),
                'created_at'    => now(),
            ]
        );

        session()->flash('status', 'Solicitação registrada. Sua conta será excluída em 15 dias (você pode cancelar antes).');
        $this->deletionReason = '';
    }

    public function render(): View
    {
        return view('livewire.settings.index');
    }
}
