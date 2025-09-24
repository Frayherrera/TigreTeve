<x-app-layout>

    <div class="padre">
        <div class="header2 flex">
            <div  class="dropdown me-auto">
                <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    {{ ucfirst($filtro ?? 'Todas') }}
                </button>

                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <a class="dropdown-item"
                        href="{{ route('noticias.index', ['estado' => 'publicadas']) }}">Publicadas</a>
                    <a class="dropdown-item"
                        href="{{ route('noticias.index', ['estado' => 'borradores']) }}">Borradores</a>
                    <a class="dropdown-item"
                        href="{{ route('noticias.index', ['estado' => 'programadas']) }}">Programadas</a>
                    <a class="dropdown-item"
                        href="{{ route('noticias.index') }}">Todas</a>
                </div>

            </div>

        </div>

        @forelse ($noticias as $noticia)
            <x-draft-card :titulo="$noticia->titulo" :estado="$noticia->estado" :fecha="$noticia->created_at->format('d M')" :autor="$noticia->user->name" :comentarios="$noticia->comentarios"
                :vistas="$noticia->vistas" :id="$noticia->id" :is_featured="$noticia->is_featured" />
        @empty
            <div class="col-12 text-center mt-4">
                <p class="text-muted">No hay noticias disponibles en este estado.</p>
            </div>
        @endforelse
</x-app-layout>
</div>
<style>
    .padre {
        margin: 10px;
        margin-left: 25%;
        margin-right: 25%;
        display: grid;
        place-items: center;
    }

    .header2 {
        width: 100%;
        margin-bottom: 20px
    }
</style>
