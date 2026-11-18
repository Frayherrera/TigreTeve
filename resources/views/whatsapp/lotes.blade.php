<x-app-layout>
    <style>
        .lt-fila,
        .lt-fila:hover,
        .lt-volver,
        .lt-volver:hover {
            text-decoration: none;
        }

        .lt-fila:focus-visible {
            outline: 2px solid #3b7fa6;
            outline-offset: -2px;
        }

        .lt-num {
            font-variant-numeric: tabular-nums;
        }

        /* Barra de progreso del lote: entregados / solo enviados / fallidos / pendientes */
        .lt-barra {
            display: flex;
            height: 6px;
            border-radius: 9999px;
            overflow: hidden;
            background: #e2e8f0;
        }
    </style>

    @php $zona = config('app.zona_editorial'); @endphp

    <div class="px-4 sm:px-6 lg:px-8 py-8 max-w-5xl mx-auto">
        <a href="{{ route('whatsapp.masivovista') }}"
            class="lt-volver mb-4 inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-slate-800">
            <i class="fas fa-arrow-left text-xs"></i> Envío masivo
        </a>

        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-slate-800">Envíos de WhatsApp</h1>
            <p class="text-sm text-slate-500 mt-1">Cada envío masivo crea un lote. Entra a uno para ver el resultado por número.</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <ul class="divide-y divide-slate-100">
                @forelse ($lotes as $lote)
                    @php
                        $creado = \Illuminate\Support\Carbon::parse($lote->creado)->timezone($zona)->locale('es');
                        $pct = fn ($n) => $lote->total ? round($n * 100 / $lote->total, 1) : 0;
                        $soloEnviados = $lote->enviados - $lote->entregados;
                    @endphp
                    <li>
                        <a href="{{ route('whatsapp.resultados', $lote->batch_id) }}"
                            class="lt-fila block px-4 sm:px-5 py-4 hover:bg-slate-50 transition-colors">
                            <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                                <span class="font-semibold text-slate-800">
                                    {{ $creado->isoFormat('D MMM YYYY, h:mm a') }}
                                    <span class="ml-1 font-normal text-xs text-slate-400">Lote {{ $lote->batch_id }}</span>
                                </span>
                                <span class="lt-num text-sm text-slate-600">
                                    {{ trans_choice(':count número|:count números', $lote->total, ['count' => number_format($lote->total)]) }}
                                </span>
                            </div>
                            <div class="lt-barra mt-3" role="img"
                                aria-label="{{ $lote->entregados }} entregados, {{ $soloEnviados }} enviados sin confirmar, {{ $lote->fallidos }} con error, {{ $lote->pendientes }} pendientes">
                                <span style="width: {{ $pct($lote->entregados) }}%; background:#0f9d58"></span>
                                <span style="width: {{ $pct($soloEnviados) }}%; background:#7cc5a1"></span>
                                <span style="width: {{ $pct($lote->fallidos) }}%; background:#dc2626"></span>
                                <span style="width: {{ $pct($lote->pendientes) }}%; background:#cbd5e1"></span>
                            </div>
                            <div class="lt-num mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
                                <span><span class="font-semibold text-slate-700">{{ number_format($lote->entregados) }}</span> entregados</span>
                                <span><span class="font-semibold text-slate-700">{{ number_format($lote->leidos) }}</span> leídos</span>
                                @if ($lote->fallidos)
                                    <span class="text-red-600"><span class="font-semibold">{{ number_format($lote->fallidos) }}</span> con error</span>
                                @endif
                                @if ($lote->pendientes)
                                    <span><span class="font-semibold text-slate-700">{{ number_format($lote->pendientes) }}</span> pendientes</span>
                                @endif
                            </div>
                        </a>
                    </li>
                @empty
                    <li class="px-6 py-14 text-center">
                        <span class="mx-auto mb-3 flex h-11 w-11 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                            <i class="fa-brands fa-whatsapp"></i>
                        </span>
                        <p class="font-medium text-slate-700">Aún no has hecho envíos</p>
                        <p class="text-sm text-slate-500 mt-1">Los envíos masivos aparecerán aquí con su resultado.</p>
                    </li>
                @endforelse
            </ul>
        </div>

        @if ($lotes->hasPages())
            <div class="mt-6">{{ $lotes->links() }}</div>
        @endif

        <p class="mt-6 text-xs text-slate-500">
            "Entregados" y "leídos" se actualizan solos cuando el webhook de WhatsApp está conectado en Meta.
        </p>
    </div>
</x-app-layout>
