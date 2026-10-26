<?php

namespace App\View\Components\Layouts;

use Illuminate\View\Component;
use Illuminate\View\View;

class Direktur extends Component
{
    public function __construct(
        public ?string $title = null,
        public ?string $header = null
    ) {}

    public function render(): View
    {
        return view('layouts.direktur');
    }
}
