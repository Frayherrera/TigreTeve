<x-app-layout>
    <style>
        .cmt-volver,
        .cmt-volver:hover,
        .cmt-ver,
        .cmt-ver:hover {
            text-decoration: none;
        }

        .cmt-texto {
            overflow-wrap: anywhere;
        }

        .cmt-oculto .cmt-texto {
            color: #94a3b8;
        }

        .cmt-num {
            font-variant-numeric: tabular-nums;
        }

        .cmt-btn:focus-visible {
            outline: 2px solid #3b7fa6;
            outline-offset: 1px;
        }
    </style>

    @php
        $zona = config('app.zona_editorial');
        $tonos = ['#3b7fa6', '#7c3aed', '#db2777', '#0d9488', '#ea580c', '#4f46e5'];
        $total = $comentarios->total();
    @endphp

    <div class="px-4 sm:px-6 lg:px-8 py-8 max-w-4xl mx-auto">
        <a href="{{ route('comentarios.index') }}"
            class="cmt-volver mb-4 inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-slate-800">
            <i class="fas fa-arrow-left text-xs"></i> Todos los comentarios
        </a>

        {{-- Encabezado: la noticia --}}
        <div class="mb-6 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div class="min-w-0">
                <h1 class="text-2xl font-semibold leading-snug text-slate-800">{{ $noticia->titulo }}</h1>
                <p class="cmt-num mt-1 text-sm text-slate-500">
                    {{ trans_choice(':count comentario|:count comentarios', $total, ['count' => number_format($total)]) }}
                    @if ($ocultos)
                        ({{ trans_choice(':count oculto|:count ocultos', $ocultos, ['count' => $ocultos]) }})
                    @endif
                </p>
            </div>
            <div class="flex shrink-0 gap-2">
                <a href="{{ route('noticias.show2', $noticia->slug) }}#comentarios" target="_blank" rel="noopener"
                    class="cmt-ver cmt-btn inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    <i class="fas fa-arrow-up-right-from-square text-xs"></i> Ver en el sitio
                </a>
                <a href="{{ route('noticias.edit', $noticia) }}"
                    class="cmt-ver cmt-btn inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    <i class="fas fa-pen text-xs"></i> Editar noticia
                </a>
            </div>
        </div>

        @if (session('ok'))
            <div class="mb-5 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                <i class="fas fa-check-circle mt-0.5"></i>
                <span>{{ session('ok') }}</span>
            </div>
        @endif

        {{-- Comentarios --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <ul class="divide-y divide-slate-100">
                @forelse ($comentarios as $c)
                    @php
                        $nombre = $c->autor_nombre ?: ($c->usuario->name ?? 'Anónimo');
                        $fecha = $c->created_at->copy()->timezone($zona)->locale('es');
                    @endphp
                    <li class="flex gap-3 px-4 sm:px-5 py-4 {{ $c->aprobado ? '' : 'cmt-oculto bg-slate-50' }}">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full font-bold text-white"
                            style="background: {{ $c->aprobado ? $tonos[crc32($nombre) % count($tonos)] : '#cbd5e1' }}" aria-hidden="true">
                            {{ mb_strtoupper(mb_substr($nombre, 0, 1)) }}
                        </span>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                <strong class="text-sm text-slate-800">{{ $nombre }}</strong>
                                @if ($c->user_id)
                                    <span class="rounded-md bg-sky-50 px-1.5 py-0.5 text-xs font-medium text-sky-700">Usuario registrado</span>
                                @endif
                                @unless ($c->aprobado)
                                    <span class="rounded-md bg-amber-50 px-1.5 py-0.5 text-xs font-medium text-amber-700">Oculto en el sitio</span>
                                @endunless
                            </div>
                            <div class="mt-0.5 flex flex-wrap gap-x-3 text-xs text-slate-500">
                                <time datetime="{{ $fecha->toIso8601String() }}">{{ $fecha->isoFormat('D MMM YYYY, h:mm a') }}</time>
                                @if ($c->autor_email)
                                    <a href="mailto:{{ $c->autor_email }}" class="text-slate-500 hover:text-slate-800">{{ $c->autor_email }}</a>
                                @endif
                            </div>
                            <p class="cmt-texto mt-2 text-sm leading-relaxed text-slate-700">{!! nl2br(e($c->contenido)) !!}</p>

                            <div class="mt-3 flex gap-2">
                                <form method="POST" action="{{ route('comentarios.alternar', $c) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                        class="cmt-btn inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-100">
                                        @if ($c->aprobado)
                                            <i class="fa-regular fa-eye-slash"></i> Ocultar
                                        @else
                                            <i class="fa-regular fa-eye"></i> Mostrar
                                        @endif
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('comentarios.destroy', $c) }}"
                                    onsubmit="return confirm('¿Eliminar el comentario de {{ addslashes($nombre) }}? Esta acción no se puede deshacer.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="cmt-btn inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-medium text-slate-600 hover:border-red-200 hover:bg-red-50 hover:text-red-600">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="px-6 py-14 text-center">
                        <span class="mx-auto mb-3 flex h-11 w-11 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                            <i class="fa-regular fa-comment"></i>
                        </span>
                        <p class="font-medium text-slate-700">Esta noticia no tiene comentarios</p>
                        <p class="text-sm text-slate-500 mt-1">Los comentarios de los lectores aparecerán aquí.</p>
                    </li>
                @endforelse
            </ul>
        </div>

        @if ($comentarios->hasPages())
            <div class="mt-6">
                {{ $comentarios->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
