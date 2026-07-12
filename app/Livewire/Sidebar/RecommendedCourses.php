<?php

declare(strict_types=1);

namespace App\Livewire\Sidebar;

use App\Models\Course;
use App\Models\Enrollment;
use Livewire\Component;

/**
 * Sidebar contextual para as páginas de cursos.
 * Mostra cursos em que o usuário ainda não está matriculado.
 */
class RecommendedCourses extends Component
{
    public int $limit = 5;

    public function render()
    {
        $user = auth()->user();
        $enrolledIds = [];

        if ($user) {
            $enrolledIds = Enrollment::query()
                ->where('user_id', $user->id)
                ->pluck('course_id')
                ->all();
        }

        $courses = Course::query()
            ->whereNotIn('id', $enrolledIds)
            ->where('status', 'published')
            ->withCount('enrollments')
            ->orderByDesc('enrollments_count')
            ->limit($this->limit)
            ->get();

        return view('livewire.sidebar.recommended-courses', [
            'courses' => $courses,
        ]);
    }
}
