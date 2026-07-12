<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\LandingController::class, 'index'])->name('landing');

/* ============================================================
 |  Rotas PÚBLICAS (sem login) — Currículo Digital
 |  Empresas podem abrir /cv/{username} sem estar cadastradas.
 |============================================================ */
Route::get('/cv/{username}', \App\Livewire\Profile\CurriculumPublic::class)
    ->name('cv.public');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    // Feed
    Route::get('/feed', \App\Livewire\Feed\Timeline::class)->name('feed');
    Route::get('/posts/{post}', \App\Livewire\Feed\ShowPost::class)->name('posts.show');

    // Perfis
    Route::get('/u/{user:username}', \App\Livewire\Profile\CandidateShow::class)->name('profile.candidate');
    Route::get('/c/{profile:slug}', \App\Livewire\Profile\CompanyShow::class)->name('profile.company');

    // Vagas — visualização (aberta a candidatos e empresas)
    Route::get('/jobs', \App\Livewire\Jobs\Browse::class)->name('jobs.index');
    // Nota: /jobs/{job} fica logo abaixo do grupo empresa (para não colidir
    // com /jobs/create). Definido no final do arquivo.

    // Mensagens
    Route::get('/messages', \App\Livewire\Messaging\Inbox::class)->name('messages.index');
    Route::get('/messages/{conversation}', \App\Livewire\Messaging\Thread::class)->name('messages.show');

    // Notificações
    Route::get('/notifications', \App\Livewire\Notifications\Index::class)->name('notifications.index');

    // Chat dock (widget flutuante) — endpoints JSON
    Route::prefix('chat-dock')->group(function () {
        Route::get('/conversations',           [\App\Http\Controllers\ChatDockController::class, 'conversations'])->name('chat-dock.list');
        Route::get('/conversations/{id}',      [\App\Http\Controllers\ChatDockController::class, 'messages'])->name('chat-dock.messages');
        Route::post('/conversations/{id}/send',[\App\Http\Controllers\ChatDockController::class, 'send'])->name('chat-dock.send');
        Route::post('/start-dm',               [\App\Http\Controllers\ChatDockController::class, 'startDm'])->name('chat-dock.start');
    });

    // IA, Configurações
    Route::get('/ai', \App\Livewire\Ai\Assistant::class)->name('ai');
    Route::get('/settings/{tab?}', \App\Livewire\Settings\Index::class)->name('settings');

    // Cursos (visualização de catálogo)
    Route::get('/courses', \App\Livewire\Courses\Catalog::class)->name('courses.index');
    Route::get('/courses/{course:slug}', \App\Livewire\Courses\Show::class)->name('courses.show');

    // Busca global
    Route::get('/search', \App\Livewire\Search\Results::class)->name('search');

    // Certificados (público autenticado)
    Route::get('/certificates/{code}', [\App\Http\Controllers\CertificateController::class, 'show'])->name('certificates.show');

    /* --------------------------------------------------------
     |  Rotas EXCLUSIVAS de CANDIDATO
     |-------------------------------------------------------- */
    Route::middleware('user.type:candidate')->group(function () {
        Route::get('/onboarding', \App\Livewire\Onboarding::class)->name('onboarding');
        Route::get('/profile/edit', \App\Livewire\Profile\Edit::class)->name('profile.edit');

        Route::get('/applications', \App\Livewire\Jobs\MyApplications::class)->name('applications.mine');

        // Testes de proficiência (Skill Assessments) — badges verificados no CV
        Route::get('/skill-assessments', \App\Livewire\SkillAssessments\Browse::class)
            ->name('skill-assessments.index');
        Route::get('/skill-assessments/{slug}', \App\Livewire\SkillAssessments\Take::class)
            ->name('skill-assessments.take');

        // Entrevistas
        Route::get('/interviews', \App\Livewire\Interviews\Setup::class)->name('interviews.setup');
        Route::get('/interviews/history', \App\Livewire\Interviews\History::class)->name('interviews.history');
        Route::get('/interviews/{session}', \App\Livewire\Interviews\Room::class)->name('interviews.room');

        // Pontos / Ranking
        Route::get('/points', \App\Livewire\Points\Dashboard::class)->name('points');
        Route::get('/leaderboard', \App\Livewire\Points\Leaderboard::class)->name('leaderboard');

        // Cursos — aprendizado (só candidato consome cursos)
        Route::get('/learn/{course:slug}/{lesson}', \App\Livewire\Courses\Player::class)->name('courses.learn');
        Route::get('/my/courses', \App\Livewire\Courses\Mine::class)->name('courses.mine');

        // Upload de foto do currículo (via fetch, não usa Livewire — evita conflito de snapshot)
        Route::post('/ai/resume-photo', [\App\Http\Controllers\ResumePhotoUploadController::class, 'upload'])->name('ai.resume-photo');

        // Avaliar empresa (Glassdoor-like)
        Route::get('/reviews/{company:slug}/new', \App\Livewire\Reviews\Create::class)->name('reviews.create');
    });

    /* --------------------------------------------------------
     |  Rotas EXCLUSIVAS de EMPRESA
     |-------------------------------------------------------- */
    Route::middleware('user.type:company')->group(function () {
        Route::get('/company/dashboard', \App\Livewire\Company\Dashboard::class)->name('company.dashboard');
        Route::get('/jobs/create', \App\Livewire\Jobs\Create::class)->name('jobs.create');
        Route::get('/company/kanban', \App\Livewire\Jobs\Kanban::class)->name('company.kanban');
        Route::get('/company/talents', \App\Livewire\Company\Talents::class)->name('company.talents');
        Route::get('/company/edit', \App\Livewire\Profile\CompanyEdit::class)->name('company.edit');

        // Assistente IA — versão empresa / RH (separado do assistente de candidato)
        Route::get('/company/ai', \App\Livewire\Company\AiAssistant::class)->name('company.ai');

        // Skill Assessments customizados da empresa
        Route::get('/company/assessments',                    \App\Livewire\Company\Assessments\Manage::class)->name('company.assessments.index');
        Route::get('/company/assessments/create',             \App\Livewire\Company\Assessments\Editor::class)->name('company.assessments.create');
        Route::get('/company/assessments/{assessment}/edit',  \App\Livewire\Company\Assessments\Editor::class)->name('company.assessments.edit');
        Route::get('/company/assessments/{assessment}/results', \App\Livewire\Company\Assessments\Results::class)->name('company.assessments.results');

        // Cursos internos da empresa (onboarding/treinamento)
        Route::get('/company/courses',                       \App\Livewire\Company\Courses\Manage::class)->name('company.courses.index');
        Route::get('/company/courses/create',                \App\Livewire\Company\Courses\Editor::class)->name('company.courses.create');
        Route::get('/company/courses/{course}/edit',         \App\Livewire\Company\Courses\Editor::class)->name('company.courses.edit');
        Route::get('/company/courses/{course}/enrollments',  \App\Livewire\Company\Courses\Enrollments::class)->name('company.courses.enrollments');
    });

    // Detalhe da vaga — DEVE ficar depois de /jobs/create para não colidir
    // no matching (Laravel casa a primeira rota registrada).
    Route::get('/jobs/{job}', \App\Livewire\Jobs\Show::class)->name('jobs.show');
});

// Convite de teste — rota pública com token (candidato pode não estar logado ainda)
Route::get('/take/{token}', \App\Livewire\Assessments\TakeInvite::class)
    ->middleware('web')
    ->name('assessments.take-invite');

// Convite de curso interno — rota pública com token
Route::get('/enroll/{token}', \App\Livewire\Courses\EnrollByToken::class)
    ->middleware('web')
    ->name('courses.enroll-by-token');

Route::get('/legal/privacy', fn () => view('legal.privacy'))->name('legal.privacy');
Route::get('/legal/terms', fn () => view('legal.terms'))->name('legal.terms');
Route::get('/legal/cookies', fn () => view('legal.cookies'))->name('legal.cookies');

// Sitemap XML — indexado por buscadores. Cacheado 6h no controller.
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');

require __DIR__.'/auth.php';
