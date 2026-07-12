<?php

declare(strict_types=1);

namespace Database\Seeders\Courses;

/**
 * Estrutura padronizada dos dados de um curso.
 *
 * Cada curso é definido como um array PHP com esta forma:
 *
 * [
 *   'title'        => string,
 *   'summary'      => string (uma linha, aparece no card)
 *   'description'  => string (2-4 linhas, aparece na landing do curso)
 *   'category'     => string
 *   'level'        => 'beginner'|'intermediate'|'advanced'
 *   'total_minutes'=> int
 *   'xp_reward'    => int
 *   'modules'      => array de 4 módulos com esta forma:
 *     [
 *       'title'   => string,
 *       'lessons' => [ ['title' => str, 'markdown' => str, 'duration' => int-seconds], ... ],
 *       'quiz'    => [
 *         'passing_score' => 70,
 *         'questions'     => [
 *            ['statement' => str, 'options' => [4 strings], 'correct_index' => int 0..3, 'explanation' => str],
 *            ...
 *         ]
 *       ]
 *     ]
 * ]
 *
 * Esta classe apenas descreve o contrato — as implementações reais estão em
 * CoursesData::all() abaixo, que carrega cada curso do respectivo array.
 */
final class CourseDataContract
{
    // Documentação apenas.
}
