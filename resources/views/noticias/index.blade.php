<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Noticias') }}
        </h2>
        <button>
            <a href="{{ route('noticias.create') }}">
                Crear noticia
            </a>
        </button>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="grid md:grid-cols-4 gap-6">
                    <aside class="md:col-span-1">
                        <div class="bg-white p-4 rounded-2xl shadow">
                            <h3 class="font-semibold mb-3">Categorías</h3>
                            <ul class="space-y-2">
                                @foreach ($categorias as $c)
                                    <li><a class="hover:underline"
                                            href="?categoria={{ $c > slug }}">{{ $c->nombre }}</a></li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="bg-white p-4 rounded-2xl shadow mt-6">
                            <h3 class="font-semibold mb-3">Tags</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($tags as $t)
                                    <a class="text-sm px-3 py-1 rounded-full border"
                                        href="? tag={{ $t->slug }}">#{{ $t->nombre }}</a>
                                @endforeach
                            </div>
                        </div>
                    </aside>
                    <section class="md:col-span-3 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">

                        @forelse($q as $n)
                            <article class="bg-white rounded-2xl shadow overflow-hidden">
                                <a href="{{ route('noticias.show', $n) }}">
                                    <img src="{{ $n->portada_path ? Storage::url($n->portada_path) : 'https://picsum.photos/640/360' }}"
                                        alt="" class="w-full h-44 object-cover">
                                    <div class="p-4">
                                        <span
                                            class="text-xs text-gray-500">{{ optional($n > publicado_en)->format('d/m/Y H:i') }}</span>
                                        <h2 class="mt-1 font-semibold text-lg">{{ $n->titulo }}</h2>
                                        <p class="text-sm text-gray-600 mt-2 line-clamp-3">{{ $n > resumen }}</p>
                                    </div>
                                </a>
                            </article>
                        @empty
                            <p>No hay noticias.</p>
                        @endforelse
                    </section>
                </div>
                <div class="mt-8">{{ $q->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
