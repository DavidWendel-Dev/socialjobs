<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\CandidateProfile;
use App\Models\Experience;
use App\Models\Skill;
use App\Services\PointsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Onboarding · SocialJobs')]
class Onboarding extends Component
{
    use WithFileUploads;

    public int $step = 1;

    public ?string $headline = null;
    public $photo = null;

    /** @var array<int, string> */
    public array $skills = [];
    public string $skillInput = '';

    public ?string $experience = null;
    public ?string $objective = null;

    public function mount(): void
    {
        // Preenche o headline com o valor atual do usuário (se houver)
        $user = auth()->user();
        if ($user) {
            $this->headline = $user->headline;
        }
    }

    public function addSkill(): void
    {
        $skill = trim($this->skillInput);
        if ($skill !== '' && ! in_array($skill, $this->skills, true) && count($this->skills) < 15) {
            $this->skills[] = $skill;
        }
        $this->skillInput = '';
    }

    public function removeSkill(int $index): void
    {
        unset($this->skills[$index]);
        $this->skills = array_values($this->skills);
    }

    public function next(): void
    {
        $this->step = min($this->step + 1, 4);
    }

    public function back(): void
    {
        $this->step = max($this->step - 1, 1);
    }

    /**
     * Persiste tudo que foi coletado no onboarding.
     */
    public function finish(): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        DB::transaction(function () use ($user) {
            // 1. Atualiza dados do próprio usuário (headline + avatar)
            $userData = [];
            if ($this->headline !== null && $this->headline !== '') {
                $userData['headline'] = $this->headline;
            }
            if ($this->photo) {
                $userData['avatar_path'] = $this->photo->store('avatars', 'public');
            }
            if (! empty($userData)) {
                $user->fill($userData)->save();
            }

            // 2. Garante o candidate_profile e salva a bio (objetivo profissional)
            $profile = CandidateProfile::firstOrCreate(['user_id' => $user->id]);
            if ($this->objective !== null && trim($this->objective) !== '') {
                $profile->bio = trim($this->objective);
                $profile->save();
            }

            // 3. Sincroniza skills (cria as que não existem e anexa via pivô)
            if (! empty($this->skills)) {
                $skillIds = [];
                foreach ($this->skills as $name) {
                    $name = trim($name);
                    if ($name === '') {
                        continue;
                    }
                    $skill = Skill::firstOrCreate(
                        ['name' => $name],
                        ['slug' => Str::slug($name)]
                    );
                    $skillIds[] = $skill->id;
                }
                // syncWithoutDetaching preserva skills já existentes
                $profile->skills()->syncWithoutDetaching($skillIds);
            }

            // 4. Registra a primeira experiência se o usuário descreveu algo
            if ($this->experience !== null && trim($this->experience) !== '') {
                Experience::create([
                    'candidate_profile_id' => $profile->id,
                    'company_name'         => 'A definir',
                    'role'                 => $user->headline ?: 'Profissional',
                    'description'          => trim($this->experience),
                    'current'              => true,
                    'start_date'           => now()->startOfMonth(),
                ]);
            }
        });

        // 5. Concede XP de "perfil completo"
        try {
            app(PointsService::class)->award($user, 'profile.completed');
        } catch (\Throwable $e) {
            report($e);
        }

        session()->flash('status', 'Perfil configurado com sucesso! 🎉');
        $this->redirectRoute('feed', navigate: true);
    }

    public function render()
    {
        return view('livewire.onboarding');
    }
}
