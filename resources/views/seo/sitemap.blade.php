{!! '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
    <url>
        <loc>{{ route('noticias.home') }}</loc>
        <changefreq>hourly</changefreq>
        <priority>1.0</priority>
    </url>
@foreach ($categorias as $categoria)
    <url>
        <loc>{{ route('noticias.home', $categoria->slug) }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.7</priority>
    </url>
@endforeach
@foreach ($noticias as $noticia)
    <url>
        <loc>{{ route('noticias.show2', $noticia->slug) }}</loc>
        <lastmod>{{ $noticia->updated_at->toAtomString() }}</lastmod>
@if ($noticia->portada_path)
        <image:image>
            <image:loc>{{ $noticia->portadaUrl() }}</image:loc>
        </image:image>
@endif
    </url>
@endforeach
</urlset>
