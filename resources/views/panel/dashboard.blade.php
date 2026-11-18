<x-app-layout>
    <style>
        .ent-tab {
            white-space: nowrap;
            text-decoration: none;
            transition: color 0.15s ease, border-color 0.15s ease;
        }

        .ent-tab:hover {
            text-decoration: none;
        }

        .ent-tab.is-active {
            color: #1e293b;
            border-color: #3b7fa6;
        }

        .ent-tabs {
            scrollbar-width: none;
        }

        .ent-tabs::-webkit-scrollbar {
            display: none;
        }

        .ent-titulo {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-decoration: none;
        }

        .ent-titulo:hover {
            color: #3b7fa6;
            text-decoration: none;
        }

        .ent-acciones {
            opacity: 0;
            transition: opacity 0.15s ease;
        }

        .ent-fila:hover .ent-acciones,
        .ent-acciones:focus-within {
            opacity: 1;
        }

        @media (hover: none) {
            .ent-acciones {
                opacity: 1;
            }
        }

        .ent-accion {
            text-decoration: none;
        }

        .ent-accion:hover {
            text-decoration: none;
        }

        .ent-num {
            font-variant-numeric: tabular-nums;
        }
    </style>

    @php
        $zona = config('app.zona_editorial');
        $total = $conteos->sum();
        $pestanas = [
            '' => ['Todas', $total],
            'publicadas' => ['Publicadas', $conteos['publicada'] ?? 0],
            'programadas' => ['Programadas', $conteos['programada'] ?? 0],
            'borradores' => ['Borradores', $conteos['borrador'] ?? 0],
        ];
    @endphp

    <div class="px-4 sm:px-6 lg:px-8 py-8 max-w-5xl mx-auto" >
        {{-- Encabezado --}}
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800">Entradas</h1>
                <p class="text-sm text-slate-500 mt-1">
                    {{ trans_choice(':count noticia|:count noticias', $total, ['count' => number_format($total)]) }}
                    @if ($buscar)
                        que coinciden con “{{ $buscar }}”
                    @endif
                </p>
            </div>
            <form method="GET" action="{{ route('noticias.index') }}" class="w-full sm:w-72">
                @if ($filtro)
                    <input type="hidden" name="estado" value="{{ $filtro }}">
                @endif
                <div class="relative">
                    <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input type="search" name="q" value="{{ $buscar }}" placeholder="Buscar por título…"
                        class="w-full rounded-xl border border-slate-300 bg-white pl-10 pr-3 py-2.5 text-sm text-slate-800 focus:border-[#3b7fa6] focus:ring-[#3b7fa6]">
                </div>
            </form>
        </div>

        {{-- Avisos --}}
        @foreach (['ok' => 'emerald', 'success' => 'emerald', 'error' => 'red'] as $clave => $color)
            @if (session($clave))
                <div class="mb-5 flex items-start gap-3 rounded-xl border px-4 py-3 text-sm
                    {{ $color === 'red' ? 'border-red-200 bg-red-50 text-red-800' : 'border-emerald-200 bg-emerald-50 text-emerald-800' }}">
                    <i class="fas {{ $color === 'red' ? 'fa-exclamation-circle' : 'fa-check-circle' }} mt-0.5"></i>
                    <span>{{ session($clave) }}</span>
                </div>
            @endif
        @endforeach

        {{-- Pestañas por estado --}}
        <nav class="ent-tabs flex gap-6 overflow-x-auto border-b border-slate-200 mb-4" aria-label="Filtrar por estado">
            @foreach ($pestanas as $clave => [$nombre, $cuenta])
                <a href="{{ route('noticias.index', array_filter(['estado' => $clave, 'q' => $buscar])) }}"
                    class="ent-tab -mb-px inline-flex items-center gap-2 border-b-2 border-transparent pb-3 text-sm font-medium text-slate-500 hover:text-slate-800 {{ ($filtro ?? '') === $clave ? 'is-active' : '' }}"
                    @if (($filtro ?? '') === $clave) aria-current="page" @endif>
                    {{ $nombre }}
                    <span class="ent-num rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">{{ number_format($cuenta) }}</span>
                </a>
            @endforeach
        </nav>

        {{-- Listado --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <ul class="divide-y divide-slate-100">
                @forelse ($noticias as $noticia)
                    @php
                        $programadaFutura = $noticia->estado === 'programada' && $noticia->publicado_en?->isFuture();
                        $visible = $noticia->estado === 'publicada' || ($noticia->estado === 'programada' && ! $programadaFutura);
                        $estado = match (true) {
                            $programadaFutura => ['Programada', 'bg-sky-50 text-sky-700', 'fa-regular fa-clock'],
                            $visible => ['Publicada', 'bg-emerald-50 text-emerald-700', 'fas fa-circle'],
                            default => ['Borrador', 'bg-amber-50 text-amber-700', 'fas fa-pen'],
                        };
                        $creada = $noticia->created_at->copy()->timezone($zona)->locale('es');
                    @endphp
                    <li class="ent-fila flex flex-wrap sm:flex-nowrap items-center gap-x-4 gap-y-2 px-4 sm:px-5 py-4 hover:bg-slate-50 transition-colors">
                        {{-- Miniatura --}}
                        <a href="{{ route('noticias.edit', $noticia) }}" class="hidden sm:block shrink-0" tabindex="-1" aria-hidden="true">
                            @if ($noticia->portada_path)
                                <img src="{{ $noticia->miniaturaUrl() }}" loading="lazy" alt=""
                                    loading="lazy" class="h-16 w-24 rounded-lg object-cover bg-slate-100">
                            @else
                                <span class="flex h-16 w-24 items-center justify-center rounded-lg bg-slate-100 text-slate-400">
                                    <i class="fa-regular fa-image"></i>
                                </span>
                            @endif
                        </a>

                        {{-- Título y metadatos --}}
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="inline-flex items-center gap-1.5 rounded-md px-2 py-0.5 text-xs font-medium {{ $estado[1] }}">
                                    <i class="{{ $estado[2] }}" style="font-size: {{ $estado[0] === 'Publicada' ? '0.4rem' : '0.65rem' }}"></i>
                                    {{ $estado[0] }}
                                </span>
                                @if ($noticia->is_featured)
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-amber-600" title="Destacada">
                                        <i class="fas fa-star" style="font-size: 0.65rem"></i>
                                        <span class="hidden md:inline">Destacada</span>
                                    </span>
                                @endif
                            </div>

                            <a href="{{ route('noticias.edit', $noticia) }}"
                                class="ent-titulo font-semibold leading-snug text-slate-800">
                                {{ $noticia->titulo }}
                            </a>

                            <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-0.5 text-xs text-slate-500">
                                @if ($noticia->categoria)
                                    <span class="font-medium text-slate-600">{{ $noticia->categoria->nombre }}</span>
                                    <span aria-hidden="true">·</span>
                                @endif
                                <span>{{ $noticia->user->name ?? 'Sin autor' }}</span>
                                <span aria-hidden="true">·</span>
                                @if ($programadaFutura)
                                    <span class="text-sky-700">
                                        Sale el {{ $noticia->publicado_en->copy()->timezone($zona)->locale('es')->isoFormat('D MMM, h:mm a') }}
                                    </span>
                                @else
                                    <time datetime="{{ $creada->toIso8601String() }}" title="{{ $creada->isoFormat('D [de] MMMM YYYY, h:mm a') }}">
                                        {{ $creada->diffForHumans() }}
                                    </time>
                                @endif
                            </div>
                        </div>

                        {{-- Métricas --}}
                        <div class="hidden md:flex items-center gap-5 shrink-0 text-sm text-slate-500">
                            <span class="ent-num inline-flex items-center gap-1.5 w-16 justify-end" title="Vistas">
                                <i class="fa-regular fa-eye text-slate-400"></i>{{ number_format($noticia->vistas) }}
                            </span>
                            @hasrole('Administrator')
                                <a href="{{ route('comentarios.noticia', $noticia) }}"
                                    class="ent-accion ent-num inline-flex items-center gap-1.5 w-10 justify-end text-slate-500 hover:text-[#3b7fa6]"
                                    title="Ver comentarios" aria-label="Ver {{ $noticia->comentarios_count }} comentarios">
                                    <i class="fa-regular fa-comment text-slate-400"></i>{{ number_format($noticia->comentarios_count) }}
                                </a>
                            @else
                                <span class="ent-num inline-flex items-center gap-1.5 w-10 justify-end" title="Comentarios">
                                    <i class="fa-regular fa-comment text-slate-400"></i>{{ number_format($noticia->comentarios_count) }}
                                </span>
                            @endhasrole
                        </div>

                        {{-- Acciones --}}
                        {{-- En móvil van en su propia línea, debajo del título --}}
                        <div class="ent-acciones flex w-full sm:w-auto items-center gap-1 shrink-0 -ml-2 sm:ml-0">
                            @if ($visible)
                                <a href="{{ route('noticias.show', $noticia->slug) }}" target="_blank" rel="noopener"
                                    class="ent-accion flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-200 hover:text-slate-800"
                                    title="Ver en el sitio" aria-label="Ver en el sitio">
                                    <i class="fas fa-arrow-up-right-from-square text-sm"></i>
                                </a>
                            @endif
                            <a href="{{ route('noticias.edit', $noticia) }}"
                                class="ent-accion flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-200 hover:text-slate-800"
                                title="Editar" aria-label="Editar">
                                <i class="fas fa-pen text-sm"></i>
                            </a>
                            @role('Administrator')
                                <form action="{{ route('noticias.destroy', $noticia) }}" method="POST"
                                    onsubmit="return confirm('¿Eliminar «{{ addslashes($noticia->titulo) }}»? Esta acción no se puede deshacer.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-red-50 hover:text-red-600"
                                        title="Eliminar" aria-label="Eliminar">
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                </form>
                            @endrole
                        </div>
                    </li>
                @empty
                    <li class="px-6 py-14 text-center">
                        <span class="mx-auto mb-3 flex h-11 w-11 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                            <i class="fa-regular fa-newspaper"></i>
                        </span>
                        @if ($buscar)
                            <p class="font-medium text-slate-700">Ninguna noticia coincide con “{{ $buscar }}”</p>
                            <p class="text-sm text-slate-500 mt-1">Prueba con otras palabras o revisa otra pestaña.</p>
                        @else
                            <p class="font-medium text-slate-700">Aún no hay noticias aquí</p>
                            <p class="text-sm text-slate-500 mt-1">Crea una entrada nueva o importa una desde Noticias externas.</p>
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
