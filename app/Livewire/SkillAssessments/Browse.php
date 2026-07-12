<?php

declare(strict_types=1);

namespace App\Livewire\SkillAssessments;

use App\Models\SkillAssessment;
use App\Services\SkillAssessmentService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Catálogo de testes de proficiência.
 * URL: /skill-assessments
 */
#[Layout('layouts.app')]
#[Title('Testes de Proficiência · SocialJobs')]
class Browse extends Component
{
    #[Url(except: '')]
    public string $q = '';

    #[Url(except: 'all')]
    public string $category = 'all';

    #[Url(except: 'all')]
    public string $difficulty = 'all';

    public function updated($property): void
    {
        // Nada especial — os wire:model atualizam o Url e o render() re-executa.
    }

    public function clearFilters(): void
    {
        $this->q          = '';
        $this->category   = 'all';
        $this->difficulty = 'all';
    }

    public function render(): View
    {
        $assessments = SkillAssessment::query()
            ->where('is_active', true)
            ->where(function ($q) {
                // Catálogo da plataforma OU testes de empresa marcados como "public"
                $q->where('owner_type', 'platform')
                  ->orWhere(function ($qq) {
                      $qq->where('owner_type', 'company')
                         ->where('visibility', 'public');
                  });
            })
            ->when($this->q !== '', fn ($qb) => $qb->where(function ($w) {
                $w->where('title', 'like', '%' . $this->q . '%')
                  ->orWhere('short_description', 'like', '%' . $this->q . '%');
            }))
            ->when($this->category !== 'all', fn ($qb) => $qb->where('category', $this->category))
            ->when($this->difficulty !== 'all', fn ($qb) => $qb->where('difficulty', $this->difficulty))
            ->orderBy('category')
            ->orderBy('title')
            ->get();

        $categories = SkillAssessment::query()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('owner_type', 'platform')
                  ->orWhere(function ($qq) {
                      $qq->where('owner_type', 'company')
                         ->where('visibility', 'public');
                  });
            })
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        // Melhores scores do usuário atual (para marcar cards já aprovados)
        $bestScores = [];
        if (auth()->check()) {
            $bestScores = app(SkillAssessmentService::class)
                ->bestScoresFor(auth()->user())
                ->keyBy(fn ($row) => $row['assessment']->id)
                ->map(fn ($row) => $row['best_score'])
                ->all();
        }

        return view('livewire.skill-assessments.browse', [
            'assessments' => $assessments,
            'categories'  => $categories,
            'bestScores'  => $bestScores,
        ]);
    }
}
