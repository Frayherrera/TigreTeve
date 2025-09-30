<article class="featured-article">
    @if ($noticia->portada_path)
        <img src="{{ Storage::disk('s3')->url($noticia->portada_path) }}" alt="{{ $noticia->titulo }}">
    @endif
    <div class="article-content">
        <div class="article-meta">
            <span class="category-badge">{{ $noticia->categoria->nombre ?? 'Sin categoría' }}</span>
            <span><i class="fas fa-calendar"></i> {{ $noticia->created_at->format('d M Y') }}</span>
            <span><i class="fas fa-user"></i> {{ $noticia->user->name ?? 'Anónimo' }}</span>
            <span><i class="fas fa-eye"></i> {{ number_format($noticia->vistas) }} lecturas</span>
        </div>
        <h2>{{ $noticia->titulo }}</h2>
        <p>{{ Str::limit($noticia->resumen ?? strip_tags($noticia->cuerpo), 200) }}</p>
        <a href="{{ route('noticias.show', $noticia->slug) }}" class="read-more">Leer artículo completo</a>
    </div>
</article>
