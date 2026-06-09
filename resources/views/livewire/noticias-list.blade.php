<div>
    <div class="blog-grid" id="noticias-container">
        @foreach ($noticias as $noticia)
            <x-blog-card :noticia="$noticia" />
        @endforeach
    </div>

    <div class="text-center mt-4">
        @if ($hasMore)
            <button wire:click="loadMore" class="btn btn-primary">
                Cargar más
            </button>
        @else
            <p>No hay más noticias.</p>
        @endif
    </div>
</div>
