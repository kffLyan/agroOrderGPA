<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class GuestLayout extends Component
{
    // Mengembalikan representasi tampilan view tata letak layout autentikasi tamu (guest).
    public function render(): View
    {
        return view('layouts.guest');
    }
}
