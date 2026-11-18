<x-app-layout>
    <style>
        .cmt-titulo {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-decoration: none;
        }

        .cmt-fila {
            text-decoration: none;
        }

        .cmt-fila:hover {
            text-decoration: none;
        }

        .cmt-fila:hover .cmt-titulo {
            color: #3b7fa6;
        }

        .cmt-fila:focus-visible {
            outline: 2px solid #3b7fa6;
            outline-offset: -2px;
        }

        .cmt-num {
            font-variant-numeric: tabular-nums;
        }
    </style>

    @php $zona = config('app.zona_editorial'); @endphp

    <div class="px-4 sm:px-6 lg:px-8 py-8 max-w-5xl mx-auto">
        {{-- Encabezado --}}
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800">Comentarios</h1>
                <p class="text-sm text-slate-500 mt-1">
                    {{ trans_choice(':count comentario|:count comentarios', $totalComentarios, ['count' => number_format($totalComentarios)]) }}
                    en {{ trans_choice(':count noticia|:count noticias', $noticias->total(), ['count' => number_format($noticias->total())]) }}
                    @if ($buscar)
                        que coinciden con “{{ $buscar }}”
                    @endif
                </p>
            </div>
            <form method="GET" action="{{ route('comentarios.index') }}" class="w-full sm:w-72">
                <div class="relative">
                    <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input type="search" name="q" value="{{ $buscar }}" placeholder="Buscar noticia por título…"
                        class="w-full rounded-xl border border-slate-300 bg-white pl-10 pr-3 py-2.5 text-sm text-slate-800 focus:border-[#3b7fa6] focus:ring-[#3b7fa6]">
                </div>
            </form>
        </div>

        {{-- Listado de noticias con comentarios --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <ul class="divide-y divide-slate-100">
                @forelse ($noticias as $noticia)
                    @php
                        $ultimo = \Illuminate\Support\Carbon::parse($noticia->comentarios_max_created_at)->timezone($zona)->locale('es');
                    @endphp
                    <li>
                        <a href="{{ route('comentarios.noticia', $noticia) }}"
                            class="cmt-fila flex items-center gap-4 px-4 sm:px-5 py-4 hover:bg-slate-50 transition-colors">
                            @if ($noticia->portada_path)
                                <img src="{{ $noticia->miniaturaUrl() }}" loading="lazy" alt=""
                                    loading="lazy" class="hidden sm:block h-14 w-20 shrink-0 rounded-lg object-cover bg-slate-100">
                            @else
                                <span class="hidden sm:flex h-14 w-20 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-400">
                                    <i class="fa-regular fa-image"></i>
                                </span>
                            @endif

                            <div class="min-w-0 flex-1">
                                <span class="cmt-titulo font-semibold leading-snug text-slate-800">{{ $noticia->titulo }}</span>
                                <span class="mt-1 block text-xs text-slate-500">
                                    Último comentario
                                    <time datetime="{{ $ultimo->toIso8601String() }}" title="{{ $ultimo->isoFormat('D [de] MMMM YYYY, h:mm a') }}">
                                        {{ $ultimo->diffForHumans() }}
                                    </time>
                                </span>
                            </div>

                            <div class="flex shrink-0 flex-col items-end gap-1">
                                <span class="cmt-num inline-flex items-center gap-1.5 text-sm font-semibold text-slate-700">
                                    <i class="fa-regular fa-comment text-slate-400"></i>{{ number_format($noticia->comentarios_count) }}
                                </span>
                                @if ($noticia->ocultos_count)
                                    <span class="cmt-num rounded-md bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700">
                                        {{ trans_choice(':count oculto|:count ocultos', $noticia->ocultos_count, ['count' => $noticia->ocultos_count]) }}
                                    </span>
                                @endif
                            </div>
                        </a>
                    </li>
                @empty
                    <li class="px-6 py-14 text-center">
                        <span class="mx-auto mb-3 flex h-11 w-11 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                            <i class="fa-regular fa-comments"></i>
                        </span>
                        @if ($buscar)
                            <p class="font-medium text-slate-700">Ninguna noticia con comentarios coincide con “{{ $buscar }}”</p>
                            <p class="text-sm text-slate-500 mt-1">Prueba con otras palabras.</p>
                        @else
                            <p class="font-medium text-slate-700">Aún no hay comentarios</p>
                            <p class="text-sm text-slate-500 mt-1">Cuando los lectores comenten una noticia, aparecerá aquí.</p>
                        @endif
                    </li>
                @endforelse
            </ul>
        </div>

        @if ($noticias->hasPages())
            <div class="mt-6">
                {{ $noticias->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
