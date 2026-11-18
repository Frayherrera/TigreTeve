<x-app-layout>
    <style>
        .rs-volver,
        .rs-volver:hover,
        .rs-tab,
        .rs-tab:hover {
            text-decoration: none;
        }

        .rs-tab.is-active {
            background: #1e293b;
            border-color: #1e293b;
            color: #fff;
        }

        .rs-tab:focus-visible {
            outline: 2px solid #3b7fa6;
            outline-offset: 2px;
        }

        .rs-num {
            font-variant-numeric: tabular-nums;
        }
    </style>

    @php
        $zona = config('app.zona_editorial');
        $total = $conteos->sum();
        $estilos = [
            'pending' => 'bg-slate-100 text-slate-600',
            'sent' => 'bg-sky-50 text-sky-700',
            'delivered' => 'bg-emerald-50 text-emerald-700',
            'read' => 'bg-emerald-100 text-emerald-800',
            'failed' => 'bg-red-50 text-red-700',
            'no_whatsapp' => 'bg-amber-50 text-amber-700',
            'baja' => 'bg-slate-200 text-slate-700',
            'cancelado' => 'bg-slate-100 text-slate-500',
        ];
        $creadoLocal = \Illuminate\Support\Carbon::parse($creado)->timezone($zona)->locale('es');
    @endphp

    <div class="px-4 sm:px-6 lg:px-8 py-8 max-w-5xl mx-auto">
        <a href="{{ route('whatsapp.lotes') }}"
            class="rs-volver mb-4 inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-slate-800">
            <i class="fas fa-arrow-left text-xs"></i> Todos los envíos
        </a>

        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-slate-800">Lote {{ $batchId }}</h1>
            <p class="rs-num text-sm text-slate-500 mt-1">
                {{ trans_choice(':count número|:count números', $total, ['count' => number_format($total)]) }},
                creado el {{ $creadoLocal->isoFormat('D [de] MMMM YYYY, h:mm a') }}
            </p>
        </div>

        {{-- Filtro por estado, con su conteo --}}
        <nav class="mb-4 flex flex-wrap gap-2" aria-label="Filtrar por estado">
            <a href="{{ route('whatsapp.resultados', $batchId) }}"
                class="rs-tab rs-num inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 {{ $filtro ? '' : 'is-active' }}"
                @unless ($filtro) aria-current="page" @endunless>
                Todos <span class="opacity-70">{{ number_format($total) }}</span>
            </a>
            @foreach ($estados as $clave => $nombre)
                @if ($conteos[$clave] ?? 0)
                    <a href="{{ route('whatsapp.resultados', [$batchId, 'estado' => $clave]) }}"
                        class="rs-tab rs-num inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 {{ $filtro === $clave ? 'is-active' : '' }}"
                        @if ($filtro === $clave) aria-current="page" @endif>
                        {{ $nombre }} <span class="opacity-70">{{ number_format($conteos[$clave]) }}</span>
                    </a>
                @endif
            @endforeach
        </nav>

        @if (($conteos['pending'] ?? 0) > 0)
            <div class="mb-4 flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                <i class="fa-regular fa-clock mt-0.5"></i>
                <span>Hay mensajes pendientes. Se envían mientras el procesador de la cola esté encendido; recarga la página para ver el avance.</span>
            </div>
        @endif

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold text-slate-500">
                    <tr>
                        <th scope="col" class="px-4 sm:px-5 py-3">Número</th>
                        <th scope="col" class="px-4 py-3">Estado</th>
                        <th scope="col" class="px-4 py-3">Detalle</th>
                        <th scope="col" class="px-4 sm:px-5 py-3 text-right">Actualizado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($logs as $log)
                        <tr>
                            <td class="rs-num px-4 sm:px-5 py-3 font-medium text-slate-800 whitespace-nowrap">{{ $log->phone }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-md px-2 py-0.5 text-xs font-medium whitespace-nowrap {{ $estilos[$log->status] ?? 'bg-slate-100 text-slate-600' }}">
                                    {{ $estados[$log->status] ?? $log->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-500 min-w-[12rem]">{{ $log->error ?: '—' }}</td>
                            <td class="rs-num px-4 sm:px-5 py-3 text-right text-slate-500 whitespace-nowrap">
                                {{ $log->updated_at->copy()->timezone($zona)->locale('es')->isoFormat('D MMM, h:mm a') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-slate-500">No hay números con este estado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="mt-6">{{ $logs->links() }}</div>
        @endif
    </div>
</x-app-layout>
