<div>
    @foreach ($noticiasDestacadas as $noticia)
        <x-featured-article :noticia="$noticia" />
    @endforeach
</div>
