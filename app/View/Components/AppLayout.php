<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * <x-app-layout> de Breeze (perfil del usuario): usa el layout público
 * para que todo el sitio se vea igual.
 */
class AppLayout extends Component
{
    public function render(): View
    {
        return view('components.layouts.publico', ['titulo' => null]);
    }
}
