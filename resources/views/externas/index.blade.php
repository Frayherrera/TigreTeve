<x-app-layout>
    <style>
        .ext-chips {
            scrollbar-width: none;
        }

        .ext-chips::-webkit-scrollbar {
            display: none;
        }

        .ext-chip {
            white-space: nowrap;
            transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
        }

        .ext-chip.is-active {
            background-color: #3b7fa6;
            border-color: #3b7fa6;
            color: #fff;
        }

        .ext-hora {
            font-variant-numeric: tabular-nums;
        }

        .ext-titular {
            text-decoration: none;
        }

        .ext-titular:hover {
            color: #3b7fa6;
            text-decoration: none;
        }

        .ext-importar {
            color: #3b7fa6;
            border-color: rgba(59, 127, 166, 0.45);
        }

        .ext-importar:hover {
            background-color: rgba(59, 127, 166, 0.08);
        }

        .ext-importar[disabled] {
            opacity: 0.6;
            cursor: wait;
        }
    </style>

    @php
        $zona = 'America/Bogota';
        $hoy = now($zona)->startOfDay();
    @endphp

    <div class="px-4 sm:px-6 lg:px-8 py-8 max-w-4xl mx-auto">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800">Noticias externas</h1>
                <p class="text-sm text-slate-500 mt-1">
                    Titulares de Google Noticias Colombia. Importa uno como borrador con su texto y foto.
                </p>
            </div>
            @unless ($error)
                <p class="text-xs text-slate-400 sm:text-right">
                    Actualizado {{ $actualizado->copy()->timezone($zona)->format('g:i a') }}
                    · se refresca cada 15 min
                </p>
            @endunless
        </div>

        <form method="GET" action="{{ route('externas.index') }}" class="mb-4">
            <div class="relative">
                <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="search" name="q" value="{{ $busqueda }}"
                    placeholder="Buscar en Google Noticias… (ej. Aguachica, Cesar, elecciones)"
                    class="w-full rounded-xl border border-slate-300 pl-10 pr-24 py-2.5 text-sm text-slate-800 focus:border-[#3b7fa6] focus:ring-[#3b7fa6]">
                <button type="submit"
                    class="absolute right-1.5 top-1/2 -translate-y-1/2 rounded-lg px-3 py-1.5 text-xs font-semibold text-white"
                    style="background-color: #3b7fa6;">
                    Buscar
                </button>
            </div>
        </form>

        <nav class="ext-chips flex gap-2 overflow-x-auto pb-1 mb-5 -mx-4 px-4 sm:mx-0 sm:px-0" aria-label="Secciones">
            <a href="{{ route('externas.index') }}"
                class="ext-chip rounded-full border border-slate-300 px-3.5 py-1.5 text-sm text-slate-600 hover:bg-slate-100 {{ !$busqueda && !$tema ? 'is-active' : '' }}">
                Titulares
            </a>
            @foreach ($temas as $clave => $nombre)
                <a href="{{ route('externas.index', ['tema' => $clave]) }}"
                    class="ext-chip rounded-full border border-slate-300 px-3.5 py-1.5 text-sm text-slate-600 hover:bg-slate-100 {{ !$busqueda && $tema === $clave ? 'is-active' : '' }}">
                    {{ $nombre }}
                </a>
            @endforeach
        </nav>

        @if ($busqueda)
            <p class="text-sm text-slate-500 mb-3">
                Resultados para <span class="font-semibold text-slate-700">“{{ $busqueda }}”</span>
                · <a href="{{ route('externas.index') }}" class="text-[#3b7fa6] hover:underline">volver a titulares</a>
            </p>
        @endif

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            @if ($error)
                <div class="px-6 py-12 text-center">
                    <span class="mx-auto mb-3 flex h-11 w-11 items-center justify-center rounded-full bg-amber-50 text-amber-600">
                        <i class="fas fa-plug-circle-exclamation"></i>
                    </span>
                    <p class="font-medium text-slate-700">No se pudo conectar con Google Noticias</p>
                    <p class="text-sm text-slate-500 mt-1">Revisa la conexión del servidor e inténtalo de nuevo en unos minutos.</p>
                    <a href="{{ request()->fullUrl() }}"
                        class="mt-4 inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
                        <i class="fas fa-rotate-right"></i> Reintentar
                    </a>
                </div>
            @else
                <ul class="divide-y divide-slate-100">
                    @forelse ($items as $item)
                        @php
                            $fecha = $item['publicado']?->copy()->timezone($zona)->locale('es');
                            $idImportada = $importadas[$item['enlace']] ?? null;
                        @endphp
                        <li class="flex gap-4 px-4 sm:px-6 py-4 hover:bg-slate-50 transition-colors">
                            <div class="ext-hora hidden sm:block w-14 shrink-0 pt-0.5 text-xs text-slate-400">
                                @if ($fecha)
                                    {{ $fecha->gte($hoy) ? $fecha->format('H:i') : $fecha->isoFormat('D MMM') }}
                                @endif
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                                    @if ($item['dominio'])
                                        <img src="https://www.google.com/s2/favicons?domain={{ $item['dominio'] }}&sz=32"
                                            alt="" width="16" height="16" loading="lazy" class="h-4 w-4 rounded-sm">
                                    @endif
                                    <span class="font-medium text-slate-600 truncate">{{ $item['fuente'] }}</span>
                                    @if ($fecha)
                                        <span aria-hidden="true">·</span>
                                        <time datetime="{{ $fecha->toIso8601String() }}" class="shrink-0">{{ $fecha->diffForHumans() }}</time>
                                    @endif
                                </div>
                                <a href="{{ $item['enlace'] }}" target="_blank" rel="noopener noreferrer"
                                    class="ext-titular block font-semibold leading-snug text-slate-800">
                                    {{ $item['titulo'] }}
                                </a>
                            </div>

                            <div class="shrink-0 self-center">
                                @if ($idImportada)
                                    <a href="{{ route('noticias.edit', $idImportada) }}"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700 hover:bg-emerald-100">
                                        <i class="fas fa-check"></i>
                                        <span class="hidden sm:inline">Importada</span>
                                    </a>
                                @else
                                    <form method="POST" action="{{ route('externas.importar') }}" data-ext-importar>
                                        @csrf
                                        <input type="hidden" name="titulo" value="{{ $item['titulo'] }}">
                                        <input type="hidden" name="enlace" value="{{ $item['enlace'] }}">
                                        <button type="submit" title="Crear borrador con este titular"
                                            class="ext-importar inline-flex items-center gap-1.5 rounded-lg border bg-white px-3 py-1.5 text-xs font-medium">
                                            <i class="fas fa-file-import"></i>
                                            <span class="hidden sm:inline">Importar</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </li>
                    @empty
                        <li class="px-6 py-12 text-center">
                            <p class="font-medium text-slate-700">Sin resultados</p>
                            <p class="text-sm text-slate-500 mt-1">Prueba con otra búsqueda o elige una sección.</p>
                        </li>
                    @endforelse
                </ul>
            @endif
        </div>

        <p class="mt-4 flex items-start gap-2 text-xs text-slate-500">
            <i class="fas fa-circle-info mt-0.5"></i>
            <span>
                Importar crea un borrador con el texto, el resumen y la foto del medio original (la foto se sube a S3),
                y guarda el enlace del medio como fuente. Puede tardar unos segundos.
            </span>
        </p>
    </div>

    <script>
        // Evita crear borradores duplicados por doble clic.
        document.querySelectorAll('[data-ext-importar]').forEach(function (form) {
            form.addEventListener('submit', function () {
                var boton = form.querySelector('button');
                boton.disabled = true;
                boton.querySelector('i').className = 'fas fa-spinner fa-spin';
            });
        });
    </script>
</x-app-layout>
