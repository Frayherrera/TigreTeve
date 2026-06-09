<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class BlogCard extends Component
{
    /**
     * Create a new component instance.
     */

    public $noticia;
    
    public function __construct($noticia)
    {
        $this->noticia = $noticia;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.blog-card');
    }
}
