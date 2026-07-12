<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    /**
     * @param string|null $title Título a ser passado ao <title> do layout.
     *                           Views passam via <x-app-layout :title="...">
     */
    public function __construct(public ?string $title = null) {}

    public function render(): View
    {
        return view('layouts.app');
    }
}
