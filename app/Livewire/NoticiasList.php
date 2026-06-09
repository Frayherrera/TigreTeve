<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Noticia;
use Illuminate\Support\Collection;

class NoticiasList extends Component
{
    public $noticias;
    public $page = 1;
    public $perPage = 2;
    public $hasMore = true;

    public function mount()
    {
        $this->noticias = collect();
        $this->loadNoticias();
    }

    public function loadNoticias()
    {
        $nuevas = Noticia::publicadas()
            ->latest()
            ->skip(($this->page - 1) * $this->perPage)
            ->take($this->perPage)->NoD()
            ->get();

        $this->noticias = $this->noticias->merge($nuevas);

        // si llegaron menos noticias de las pedidas => no hay más
        if ($nuevas->count() < $this->perPage) {
            $this->hasMore = false;
        }
    }

    public function loadMore()
    {
        if ($this->hasMore) {
            $this->page++;
            $this->loadNoticias();
        }
    }

    public function render()
    {
        return view('livewire.noticias-list');
    }
}
