<?php

declare(strict_types=1);

namespace App\Livewire\Feed;

use App\Models\Post;
use App\Services\FeedService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Feed · SocialJobs')]
class Timeline extends Component
{
    use WithPagination;

    #[Url]
    public string $filter = 'foryou'; // foryou|following|global

    public function setFilter(string $filter): void
    {
        $this->filter = in_array($filter, ['foryou', 'following', 'global'], true) ? $filter : 'foryou';
        $this->resetPage();
    }

    /**
     * Escuta o evento disparado pelo Composer quando um novo post é criado
     * — reseta a paginação para o topo mostrar o post recém-criado.
     */
    #[On('post-created')]
    public function onPostCreated(): void
    {
        $this->resetPage();
    }

    /**
     * Escuta remoção de post — força re-render.
     */
    #[On('post-deleted')]
    public function onPostDeleted(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        if (auth()->check()) {
            $posts = app(FeedService::class)->timeline(auth()->user(), $this->filter);
        } else {
            $posts = Post::query()
                ->with(['user', 'media'])
                ->where('visibility', 'public')
                ->latest()
                ->cursorPaginate(15);
        }

        return view('livewire.feed.timeline', ['posts' => $posts]);
    }
}
