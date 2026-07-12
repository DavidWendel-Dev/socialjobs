<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonCompletion;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Gerencia matrícula, progresso e correção de quizzes.
 */
class CourseProgressService
{
    public function __construct(
        private PointsService $points,
        private CertificateService $certificates,
    ) {
    }

    public function enroll(User $user, Course $course): Enrollment
    {
        return Enrollment::firstOrCreate(
            ['user_id' => $user->id, 'course_id' => $course->id],
            ['progress_percent' => 0]
        );
    }

    /**
     * Marca uma lição como concluída. Atualiza percentual de progresso do curso,
     * dispara XP e, quando 100%, gera certificado.
     */
    public function completeLesson(User $user, Lesson $lesson): LessonCompletion
    {
        return DB::transaction(function () use ($user, $lesson) {
            $completion = LessonCompletion::firstOrCreate(
                ['user_id' => $user->id, 'lesson_id' => $lesson->id],
                ['completed_at' => now()]
            );

            // Só concede XP quando é a primeira vez (wasRecentlyCreated)
            if ($completion->wasRecentlyCreated) {
                $this->points->award(
                    $user,
                    'lesson.completed',
                    $lesson,
                    'lesson.completed:' . $user->id . ':' . $lesson->id
                );
            }

            $courseId = $lesson->module->course_id;
            $totalLessons = Lesson::whereHas('module', fn ($q) => $q->where('course_id', $courseId))->count();
            $doneLessons  = LessonCompletion::where('user_id', $user->id)
                ->whereIn('lesson_id', function ($q) use ($courseId) {
                    $q->select('lessons.id')
                        ->from('lessons')
                        ->join('course_modules', 'course_modules.id', '=', 'lessons.module_id')
                        ->where('course_modules.course_id', $courseId);
                })
                ->count();

            $percent = $totalLessons > 0 ? round(($doneLessons / $totalLessons) * 100, 2) : 0.0;

            $enrollment = Enrollment::firstOrCreate(
                ['user_id' => $user->id, 'course_id' => $courseId],
                ['progress_percent' => 0]
            );
            $enrollment->progress_percent = $percent;

            if ($percent >= 100.0 && $enrollment->completed_at === null) {
                $enrollment->completed_at = now();
                $course = Course::find($courseId);
                if ($course) {
                    $this->points->award($user, 'course.completed', $course, 'course.completed:' . $user->id . ':' . $courseId);
                    $this->certificates->issue($user, $course);
                }
            }
            $enrollment->save();

            return $completion;
        });
    }

    /**
     * Corrige um quiz e concede XP se passou.
     *
     * @param array<int,int> $answers ["question_id" => índice escolhido]
     */
    public function submitQuiz(User $user, Quiz $quiz, array $answers): QuizAttempt
    {
        $questions = $quiz->questions()->get();
        $total     = $questions->count();
        $correct   = 0;

        foreach ($questions as $q) {
            $chosen = $answers[$q->id] ?? null;
            if ($chosen !== null && (int) $chosen === (int) $q->correct_index) {
                $correct++;
            }
        }

        $score  = $total > 0 ? (int) round(($correct / $total) * 100) : 0;
        $passed = $score >= (int) $quiz->passing_score;

        $attempt = QuizAttempt::create([
            'user_id'    => $user->id,
            'quiz_id'    => $quiz->id,
            'score'      => $score,
            'passed'     => $passed,
            'answers'    => $answers,
            'created_at' => now(),
        ]);

        if ($passed) {
            $this->points->award(
                $user,
                'module.passed',
                $quiz,
                'module.passed:' . $user->id . ':' . $quiz->id
            );
        }

        return $attempt;
    }
}
