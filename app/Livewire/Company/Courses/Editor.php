<?php

declare(strict_types=1);

namespace App\Livewire\Company\Courses;

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\JobListing;
use App\Models\Lesson;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Editor de curso · SocialJobs')]
class Editor extends Component
{
    public ?int $courseId = null;

    /* -------------------- Info Geral -------------------- */
    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('nullable|string|max:120')]
    public string $category = 'Onboarding';

    #[Validate('required|in:beginner,intermediate,advanced')]
    public string $level = 'beginner';

    #[Validate('required|integer|min:1|max:500')]
    public int $duration_hours = 1;

    #[Validate('nullable|string|max:500')]
    public string $short_description = '';

    #[Validate('nullable|string|max:10000')]
    public string $description = '';

    #[Validate('nullable|string|max:500')]
    public string $thumbnail_url = '';

    #[Validate('required|in:public,invite_only')]
    public string $visibility = 'invite_only';

    #[Validate('nullable|integer|exists:job_listings,id')]
    public ?int $job_listing_id = null;

    /* -------------------- Módulos & Aulas (inline) -------------------- */
    /**
     * Array em memória:
     * [
     *   ['id' => null|int, 'title' => '', 'position' => 0, 'lessons' => [
     *      ['id' => null|int, 'title' => '', 'content' => '', 'video_url' => '', 'position' => 0]
     *   ]]
     * ]
     */
    public array $modules = [];

    /** UI: aba ativa (info|modules|publish) */
    public string $tab = 'info';

    public function mount(?Course $course = null): void
    {
        $cp = auth()->user()?->companyProfile;
        abort_unless($cp !== null, 403);

        if ($course && $course->exists) {
            abort_unless(
                $course->owner_type === 'company'
                    && (int) $course->company_profile_id === (int) $cp->id,
                403
            );

            $this->courseId          = $course->id;
            $this->title             = (string) $course->title;
            $this->category          = (string) ($course->category ?? 'Onboarding');
            $this->level             = (string) $course->level;
            $this->duration_hours    = max(1, (int) round(((int) $course->total_minutes) / 60));
            $this->short_description = (string) ($course->summary ?? '');
            $this->description       = (string) ($course->description ?? '');
            $this->thumbnail_url     = (string) ($course->thumbnail_path ?? '');
            $this->visibility        = (string) ($course->visibility ?? 'invite_only');
            $this->job_listing_id    = $course->job_listing_id;

            $this->modules = $course->modules()
                ->with(['lessons' => fn ($q) => $q->orderBy('position')])
                ->orderBy('position')
                ->get()
                ->map(fn (CourseModule $m) => [
                    'id'       => $m->id,
                    'title'    => (string) $m->title,
                    'position' => (int) $m->position,
                    'lessons'  => $m->lessons->map(fn (Lesson $l) => [
                        'id'        => $l->id,
                        'title'     => (string) $l->title,
                        'content'   => (string) ($l->content_markdown ?? ''),
                        'video_url' => (string) ($l->video_ref ?? ''),
                        'position'  => (int) $l->position,
                    ])->all(),
                ])
                ->all();
        }
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['info', 'modules', 'publish'], true)) {
            $this->tab = $tab;
        }
    }

    /* -------------------- Módulos -------------------- */
    public function addModule(): void
    {
        $this->modules[] = [
            'id'       => null,
            'title'    => 'Novo módulo',
            'position' => count($this->modules),
            'lessons'  => [],
        ];
    }

    public function removeModule(int $idx): void
    {
        if (isset($this->modules[$idx])) {
            unset($this->modules[$idx]);
            $this->modules = array_values($this->modules);
        }
    }

    public function addLesson(int $moduleIdx): void
    {
        if (! isset($this->modules[$moduleIdx])) {
            return;
        }
        $this->modules[$moduleIdx]['lessons'][] = [
            'id'        => null,
            'title'     => 'Nova aula',
            'content'   => '',
            'video_url' => '',
            'position'  => count($this->modules[$moduleIdx]['lessons']),
        ];
    }

    public function removeLesson(int $moduleIdx, int $lessonIdx): void
    {
        if (isset($this->modules[$moduleIdx]['lessons'][$lessonIdx])) {
            unset($this->modules[$moduleIdx]['lessons'][$lessonIdx]);
            $this->modules[$moduleIdx]['lessons'] = array_values($this->modules[$moduleIdx]['lessons']);
        }
    }

    /* -------------------- Persistência -------------------- */
    public function save(): void
    {
        $this->validate();

        $cp = auth()->user()?->companyProfile;
        abort_unless($cp !== null, 403);

        if ($this->courseId) {
            $course = Course::query()
                ->where('id', $this->courseId)
                ->where('owner_type', 'company')
                ->where('company_profile_id', $cp->id)
                ->firstOrFail();
        } else {
            $course              = new Course();
            $course->author_id   = auth()->id();
            $course->owner_type  = 'company';
            $course->company_profile_id = $cp->id;
            // slug + access_token gerados no booted() do model
        }

        $course->title             = $this->title;
        $course->summary           = $this->short_description ?: null;
        $course->description       = $this->description ?: null;
        $course->thumbnail_path    = $this->thumbnail_url ?: null;
        $course->category          = $this->category ?: null;
        $course->level             = $this->level;
        $course->total_minutes     = $this->duration_hours * 60;
        $course->visibility        = $this->visibility;
        $course->job_listing_id    = $this->job_listing_id ?: null;
        $course->status            = 'published';
        if (! $course->published_at) {
            $course->published_at = now();
        }

        DB::transaction(function () use ($course) {
            $course->save();

            // Sincroniza módulos e aulas
            $existingModuleIds = $course->modules()->pluck('id')->all();
            $keepModuleIds     = [];

            foreach (array_values($this->modules) as $mIdx => $m) {
                $mTitle = trim((string) ($m['title'] ?? ''));
                if ($mTitle === '') {
                    continue;
                }

                if (! empty($m['id']) && in_array((int) $m['id'], $existingModuleIds, true)) {
                    $module = CourseModule::query()
                        ->where('id', $m['id'])
                        ->where('course_id', $course->id)
                        ->first();
                } else {
                    $module = new CourseModule();
                    $module->course_id = $course->id;
                }
                if (! $module) {
                    continue;
                }
                $module->title    = $mTitle;
                $module->position = $mIdx;
                $module->save();
                $keepModuleIds[] = $module->id;

                $existingLessonIds = $module->lessons()->pluck('id')->all();
                $keepLessonIds     = [];

                foreach (array_values($m['lessons'] ?? []) as $lIdx => $l) {
                    $lTitle = trim((string) ($l['title'] ?? ''));
                    if ($lTitle === '') {
                        continue;
                    }

                    if (! empty($l['id']) && in_array((int) $l['id'], $existingLessonIds, true)) {
                        $lesson = Lesson::query()
                            ->where('id', $l['id'])
                            ->where('module_id', $module->id)
                            ->first();
                    } else {
                        $lesson = new Lesson();
                        $lesson->module_id = $module->id;
                    }
                    if (! $lesson) {
                        continue;
                    }

                    $lesson->title            = $lTitle;
                    $lesson->slug             = Str::slug($lTitle) . '-' . substr(uniqid(), -5);
                    $lesson->content_markdown = trim((string) ($l['content'] ?? '')) ?: null;
                    $videoUrl                 = trim((string) ($l['video_url'] ?? ''));
                    if ($videoUrl !== '') {
                        $lesson->video_ref      = $videoUrl;
                        $lesson->video_provider = str_contains($videoUrl, 'vimeo') ? 'vimeo' : 'youtube';
                    } else {
                        $lesson->video_ref = null;
                    }
                    $lesson->position = $lIdx;
                    $lesson->save();
                    $keepLessonIds[] = $lesson->id;
                }

                // Remove aulas que sumiram
                Lesson::query()
                    ->where('module_id', $module->id)
                    ->whereNotIn('id', $keepLessonIds ?: [0])
                    ->delete();
            }

            // Remove módulos que sumiram
            CourseModule::query()
                ->where('course_id', $course->id)
                ->whereNotIn('id', $keepModuleIds ?: [0])
                ->delete();
        });

        session()->flash('status', $this->courseId ? 'Curso atualizado!' : 'Curso criado!');
        $this->redirectRoute('company.courses.edit', ['course' => $course->id], navigate: true);
    }

    public function render(): View
    {
        $cp = auth()->user()?->companyProfile;

        $jobs = $cp
            ? JobListing::query()
                ->where('company_profile_id', $cp->id)
                ->orderByDesc('id')
                ->limit(50)
                ->get(['id', 'title'])
            : collect();

        return view('livewire.company.courses.editor', [
            'jobs' => $jobs,
        ]);
    }
}
