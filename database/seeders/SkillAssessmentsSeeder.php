<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SkillAssessment;
use App\Models\SkillAssessmentQuestion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Popula 20 testes de proficiência com 20 questões cada = 400 questões totais.
 *
 * Cada teste cobre uma habilidade útil no mercado brasileiro variado
 * (não focado só em tech). As questões são de múltipla escolha (4 opções)
 * com explicação — os candidatos veem o feedback após responder.
 *
 * Todos os títulos, resumos, categorias e cores são padronizados para o
 * catálogo aparecer bem organizado na página /skill-assessments.
 */
class SkillAssessmentsSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            foreach ($this->buildAssessments() as $data) {
                $assessment = SkillAssessment::updateOrCreate(
                    ['slug' => $data['slug']],
                    [
                        'title'             => $data['title'],
                        'category'          => $data['category'],
                        'short_description' => $data['short_description'],
                        'description'       => $data['description'],
                        'difficulty'        => $data['difficulty'],
                        'icon'              => $data['icon'],
                        'color'             => $data['color'],
                        'duration_minutes'  => 15,
                        'passing_score'     => 70,
                        'xp_reward'         => 150,
                        'is_active'         => true,
                    ]
                );

                // Zera questões antigas para o seed ser idempotente
                $assessment->questions()->delete();

                foreach ($data['questions'] as $i => $q) {
                    SkillAssessmentQuestion::create([
                        'skill_assessment_id' => $assessment->id,
                        'statement'           => $q[0],
                        'options'             => $q[1],
                        'correct_index'       => $q[2],
                        'explanation'         => $q[3] ?? null,
                        'position'            => $i + 1,
                    ]);
                }
            }
        });
    }

    /**
     * @return array<int,array{
     *   title:string,slug:string,category:string,short_description:string,
     *   description:string,difficulty:string,icon:string,color:string,
     *   questions:array<int,array{0:string,1:array<int,string>,2:int,3?:string}>
     * }>
     */
    private function buildAssessments(): array
    {
        // Carregamos cada pack só se a classe existir — assim conseguimos
        // liberar packs incrementalmente sem quebrar o seeder.
        $packs = [
            \Database\Seeders\SkillsData\SkillPack1::class,
            \Database\Seeders\SkillsData\SkillPack2::class,
            \Database\Seeders\SkillsData\SkillPack3::class,
            \Database\Seeders\SkillsData\SkillPack4::class,
        ];

        $all = [];
        foreach ($packs as $pack) {
            if (class_exists($pack) && method_exists($pack, 'data')) {
                $all = array_merge($all, $pack::data());
            }
        }

        return $all;
    }
}
