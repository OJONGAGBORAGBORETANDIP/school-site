<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Uses the same dashboard layout as parent and teacher (sidebar + header + main).
 * Any view using <x-app-layout> gets the unified dashboard layout.
 */
class AppLayout extends Component
{
    public function render(): View
    {
        return view('layouts.dashboard', [
            'headerTitle' => 'Dashboard',
        ]);
    }
}
