<?php

namespace App\View\Components;

use Illuminate\View\Component;

class FeaturedArticle extends Component
{
    public $noticia;

    /**
     * Create a new component instance.
     */
    public function __construct($noticia)
    {
        $this->noticia = $noticia;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.featured-article');
    }
}
