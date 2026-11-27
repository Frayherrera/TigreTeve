{!! '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' !!}
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/">
    <channel>
        <title>TigreTeve</title>
        <link>{{ route('noticias.home') }}</link>
        <description>Noticias de Aguachica y el sur del Cesar.</description>
        <language>es-co</language>
        <atom:link href="{{ route('feed') }}" rel="self" type="application/rss+xml" />
@foreach ($noticias as $noticia)
        <item>
            <title>{{ $noticia->titulo }}</title>
            <link>{{ route('noticias.show2', $noticia->slug) }}</link>
            <guid isPermaLink="true">{{ route('noticias.show2', $noticia->slug) }}</guid>
            <pubDate>{{ $noticia->fechaPublicacion()->toRssString() }}</pubDate>
@if ($noticia->categoria)
            <category>{{ $noticia->categoria->nombre }}</category>
@endif
            <description>{{ \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', $noticia->resumen ?: strip_tags($noticia->cuerpo))), 300) }}</description>
@if ($noticia->portada_path)
            <media:content url="{{ $noticia->portadaUrl() }}" medium="image" />
@endif
        </item>
@endforeach
    </channel>
</rss>
