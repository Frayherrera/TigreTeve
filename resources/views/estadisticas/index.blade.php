<x-app-layout>
    <style>
        .est-num {
            font-variant-numeric: tabular-nums;
        }

        .est-periodo,
        .est-periodo:hover,
        .est-enlace,
        .est-enlace:hover {
            text-decoration: none;
        }

        .est-periodo.is-active {
            background: #1e293b;
            border-color: #1e293b;
            color: #fff;
        }

        .est-periodo:focus-visible,
        .est-col:focus-visible {
            outline: 2px solid #2b7bbd;
            outline-offset: 2px;
        }

        /* Gráfico de columnas: lecturas por día */
        .est-grafico {
            position: relative;
            height: 220px;
            margin-left: 2.75rem;
        }

        .est-guia {
            position: absolute;
            left: 0;
            right: 0;
            border-top: 1px solid #eef2f6;
        }

        .est-guia span {
            position: absolute;
            right: calc(100% + 8px);
            top: -0.55rem;
            font-size: 0.7rem;
            color: #94a3b8;
            white-space: nowrap;
        }

        .est-base {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            border-top: 1px solid #cbd5e1;
        }

        .est-columnas {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: flex-end;
            gap: 2px;
        }

        /* Cada columna ocupa su franja completa como zona de hover; la barra visible va dentro. */
        .est-col {
            position: relative;
            flex: 1 1 0;
            height: 100%;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            cursor: default;
            background: transparent;
            border: 0;
            padding: 0;
        }

        .est-barra {
            width: 100%;
            max-width: 24px;
            min-height: 0;
            background: #2b7bbd;
            border-radius: 4px 4px 0 0;
        }

        .est-col:hover .est-barra,
        .est-col:focus-visible .est-barra {
            background: #1d5f96;
        }

        .est-col:hover,
        .est-col:focus-visible {
            background: rgba(43, 123, 189, 0.06);
        }

        .est-tooltip {
            position: absolute;
            z-index: 10;
            pointer-events: none;
            padding: 6px 10px;
            border-radius: 8px;
            background: #1e293b;
            color: #fff;
            font-size: 0.75rem;
            line-height: 1.35;
            white-space: nowrap;
            transform: translate(-50%, -100%);
        }

        .est-ejes {
            display: flex;
            justify-content: space-between;
            margin: 6px 0 0 2.75rem;
            font-size: 0.7rem;
            color: #94a3b8;
        }

        /* Barras horizontales de los rankings */
        .est-rank-barra {
            height: 6px;
            border-radius: 0 3px 3px 0;
            background: #2b7bbd;
        }

        .est-titulo {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .est-enlace:hover .est-titulo {
            color: #2b7bbd;
        }
    </style>

    @php
        $max = max(1, $serie->max('total'));
        // Tope "redondo" para las guías del eje.
        $magnitud = 10 ** max(0, strlen((string) $max) - 1);
        $tope = (int) (ceil($max / $magnitud) * $magnitud);
        $cambio = $resumen['lecturas_anterior'] > 0
            ? round(($resumen['lecturas'] - $resumen['lecturas_anterior']) * 100 / $resumen['lecturas_anterior'])
            : null;
        $maxCat = max(1, $topCategorias->max('lecturas'));
        $maxAut = max(1, $topAutores->max('lecturas'));
    @endphp

    <div class="px-4 sm:px-6 lg:px-8 py-8 max-w-5xl mx-auto">
        {{-- Encabezado y periodo --}}
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800">Estadísticas</h1>
                <p class="text-sm text-slate-500 mt-1">Lecturas reales: sin bots y sin contar recargas de la misma persona.</p>
            </div>
            <nav class="flex gap-2" aria-label="Periodo">
                @foreach ($periodos as $valor => $nombre)
                    <a href="{{ route('estadisticas.index', ['dias' => $valor]) }}"
                        class="est-periodo rounded-full border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 {{ $dias === $valor ? 'is-active' : '' }}"
                        @if ($dias === $valor) aria-current="page" @endif>
                        {{ $valor }} días
                    </a>
                @endforeach
            </nav>
        </div>

        {{-- Cifras del periodo --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                <p class="text-sm text-slate-500">Lecturas</p>
                <p class="est-num mt-1 text-3xl font-semibold text-slate-800">{{ number_format($resumen['lecturas']) }}</p>
                @if ($cambio !== null)
                    <p class="est-num mt-1 text-xs {{ $cambio >= 0 ? 'text-emerald-700' : 'text-red-600' }}">
                        <i class="fas {{ $cambio >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }}" aria-hidden="true"></i>
                        {{ abs($cambio) }} % frente a los {{ $dias }} días anteriores
                    </p>
                @endif
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                <p class="text-sm text-slate-500">Noticias publicadas</p>
                <p class="est-num mt-1 text-3xl font-semibold text-slate-800">{{ number_format($resumen['publicadas']) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                <p class="text-sm text-slate-500">Comentarios</p>
                <p class="est-num mt-1 text-3xl font-semibold text-slate-800">{{ number_format($resumen['comentarios']) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                <p class="text-sm text-slate-500">Suscritos a avisos push</p>
                <p class="est-num mt-1 text-3xl font-semibold text-slate-800">{{ number_format($resumen['suscriptores']) }}</p>
                <p class="mt-1 text-xs text-slate-500">Total actual</p>
            </div>
        </div>

        {{-- Lecturas por día --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 mb-6" aria-labelledby="est-dia">
            <h2 id="est-dia" class="text-base font-semibold text-slate-800">Lecturas por día</h2>
            <p class="text-xs text-slate-500 mb-5">
                {{ $periodos[$dias] }}
                @if ($primerDia)
                    · se cuentan desde el {{ $primerDia->isoFormat('D [de] MMMM YYYY') }}
                @endif
            </p>

            @if ($resumen['lecturas'] === 0)
                <p class="py-10 text-center text-sm text-slate-500">Todavía no hay lecturas en este periodo.</p>
            @else
                <div class="est-grafico" id="estGrafico">
                    @foreach ([1, 0.5] as $f)
                        <div class="est-guia" style="bottom: {{ $f * 100 }}%"><span class="est-num">{{ number_format($tope * $f) }}</span></div>
                    @endforeach
                    <div class="est-base"></div>
                    <div class="est-columnas">
                        @foreach ($serie as $punto)
                            <button type="button" class="est-col"
                                data-fecha="{{ $punto['fecha']->isoFormat('ddd D [de] MMM') }}"
                                data-total="{{ number_format($punto['total']) }}"
                                aria-label="{{ $punto['fecha']->isoFormat('D [de] MMMM') }}: {{ number_format($punto['total']) }} lecturas">
                                <span class="est-barra" style="height: {{ $punto['total'] / $tope * 100 }}%"></span>
                            </button>
                        @endforeach
                    </div>
                    <div class="est-tooltip" id="estTooltip" hidden></div>
                </div>
                <div class="est-ejes">
                    <span>{{ $serie->first()['fecha']->isoFormat('D MMM') }}</span>
                    <span>{{ $serie->last()['fecha']->isoFormat('D MMM') }}</span>
                </div>

                <details class="mt-4 text-sm">
                    <summary class="cursor-pointer text-slate-600">Ver como tabla</summary>
                    <div class="mt-2 max-h-64 overflow-y-auto">
                        <table class="w-full text-sm">
                            <thead class="text-left text-xs text-slate-500">
                                <tr><th scope="col" class="py-1">Día</th><th scope="col" class="py-1 text-right">Lecturas</th></tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($serie->reverse() as $punto)
                                    <tr>
                                        <td class="py-1 text-slate-700">{{ $punto['fecha']->isoFormat('dddd D [de] MMMM') }}</td>
                                        <td class="est-num py-1 text-right text-slate-800">{{ number_format($punto['total']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </details>
            @endif
        </section>

        <div class="grid gap-6 lg:grid-cols-5">
            {{-- Lo más leído --}}
            <section class="lg:col-span-3 rounded-2xl border border-slate-200 bg-white overflow-hidden" aria-labelledby="est-top">
                <h2 id="est-top" class="px-4 sm:px-5 pt-4 pb-2 text-base font-semibold text-slate-800">Lo más leído</h2>
                <ol class="divide-y divide-slate-100">
                    @forelse ($topNoticias as $i => $n)
                        <li>
                            <a href="{{ route('noticias.show2', $n->slug) }}" target="_blank" rel="noopener"
                                class="est-enlace flex items-center gap-3 px-4 sm:px-5 py-3 hover:bg-slate-50">
                                <span class="est-num w-5 shrink-0 text-right text-sm font-semibold text-slate-400">{{ $i + 1 }}</span>
                                <span class="est-titulo min-w-0 flex-1 text-sm font-medium text-slate-800">{{ $n->titulo }}</span>
                                <span class="est-num shrink-0 text-sm text-slate-600">{{ number_format($n->lecturas) }}</span>
                            </a>
                        </li>
                    @empty
                        <li class="px-5 py-8 text-center text-sm text-slate-500">Sin lecturas en este periodo.</li>
                    @endforelse
                </ol>
            </section>

            <div class="lg:col-span-2 space-y-6">
                {{-- Por categoría --}}
                <section class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5" aria-labelledby="est-cat">
                    <h2 id="est-cat" class="mb-3 text-base font-semibold text-slate-800">Por categoría</h2>
                    <ul class="space-y-3">
                        @forelse ($topCategorias as $c)
                            <li>
                                <div class="flex justify-between gap-3 text-sm">
                                    <span class="truncate text-slate-700">{{ $c->nombre }}</span>
                                    <span class="est-num text-slate-600">{{ number_format($c->lecturas) }}</span>
                                </div>
                                <div class="est-rank-barra mt-1" style="width: {{ max(2, $c->lecturas / $maxCat * 100) }}%"></div>
                            </li>
                        @empty
                            <li class="text-sm text-slate-500">Sin datos todavía.</li>
                        @endforelse
                    </ul>
                </section>

                {{-- Por autor --}}
                <section class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5" aria-labelledby="est-aut">
                    <h2 id="est-aut" class="mb-3 text-base font-semibold text-slate-800">Por autor</h2>
                    <ul class="space-y-3">
                        @forelse ($topAutores as $a)
                            <li>
                                <div class="flex justify-between gap-3 text-sm">
                                    <span class="truncate text-slate-700">
                                        {{ $a->nombre }}
                                        <span class="text-xs text-slate-400">{{ trans_choice(':count noticia|:count noticias', $a->noticias, ['count' => $a->noticias]) }}</span>
                                    </span>
                                    <span class="est-num text-slate-600">{{ number_format($a->lecturas) }}</span>
                                </div>
                                <div class="est-rank-barra mt-1" style="width: {{ max(2, $a->lecturas / $maxAut * 100) }}%"></div>
                            </li>
                        @empty
                            <li class="text-sm text-slate-500">Sin datos todavía.</li>
                        @endforelse
                    </ul>
                </section>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var grafico = document.getElementById('estGrafico');
            if (!grafico) return;
            var tooltip = document.getElementById('estTooltip');

            function mostrar(col) {
                tooltip.innerHTML = '<strong>' + col.dataset.total + '</strong> lecturas<br>' + col.dataset.fecha;
                var g = grafico.getBoundingClientRect();
                var c = col.getBoundingClientRect();
                var barra = col.querySelector('.est-barra').getBoundingClientRect();
                var x = c.left - g.left + c.width / 2;
                // Mantiene el tooltip dentro del gráfico en los extremos.
                x = Math.max(60, Math.min(g.width - 60, x));
                tooltip.style.left = x + 'px';
                tooltip.style.top = (barra.top - g.top - 8) + 'px';
                tooltip.hidden = false;
            }

            grafico.querySelectorAll('.est-col').forEach(function (col) {
                col.addEventListener('mouseenter', function () { mostrar(col); });
                col.addEventListener('focus', function () { mostrar(col); });
                col.addEventListener('mouseleave', function () { tooltip.hidden = true; });
                col.addEventListener('blur', function () { tooltip.hidden = true; });
            });
        })();
    </script>
</x-app-layout>
