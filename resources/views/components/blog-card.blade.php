@props([
    'imagen' => 'https://via.placeholder.com/500x300',
    'categoria' => 'General',
    'color' => '#999',
    'titulo' => '(Sin título)',
    'descripcion' => '',
    'autor' => 'Autor',
    'tiempo' => 'Hace un momento',
])

<article class="blog-card" onclick="window.location='{{ route('noticias.show', $noticia->slug) }}'">
    <img src="{{ $noticia->portada_path
        ? asset('storage/' . $noticia->portada_path)
        : 'https://via.placeholder.com/1000x600?text=Sin+Imagen' }}"
        alt="{{ $noticia->titulo }}">
    <div class="blog-card-content">
        <span class="category-badge" style="background: {{ $color }};">{{ $noticia->categoria->nombre }}</span>
        <h3>{{ $noticia->titulo }}</h3>
        <p>{{ $noticia->resumen }}</p>
        <div class="blog-meta">
            <span><i class="fas fa-user"></i> {{ $noticia->user->name }}</span>
            <span><i class="fas fa-clock"></i> {{ $tiempo }}</span>
        </div>
    </div>
</article>
<style>
    .blog-card {
        cursor: pointer;
    }
</style>
