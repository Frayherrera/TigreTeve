<x-app-layout>
    <style>
        .cat-modal {
            position: fixed;
            inset: 0;
            z-index: 60;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .cat-modal.is-open {
            display: flex;
        }

        .cat-modal__backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(2px);
        }

        .cat-modal__panel {
            position: relative;
            width: 100%;
            max-width: 26rem;
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.25);
            animation: cat-pop 0.18s ease-out;
        }

        @keyframes cat-pop {
            from { opacity: 0; transform: translateY(8px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        @media (prefers-reduced-motion: reduce) {
            .cat-modal__panel { animation: none; }
        }

        .cat-row-actions {
            opacity: 0;
            transition: opacity 0.15s ease;
        }

        tr:hover .cat-row-actions,
        .cat-row-actions:focus-within {
            opacity: 1;
        }

        @media (hover: none) {
            .cat-row-actions { opacity: 1; }
        }
    </style>

    @php
        $canManage = auth()->user()->hasAnyRole(['Administrator', 'Editor']);
        $canDelete = auth()->user()->hasRole('Administrator');
    @endphp

    <div class="px-4 sm:px-6 lg:px-8 py-8 max-w-4xl mx-auto">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800">Categorías</h1>
                <p class="text-sm text-slate-500 mt-1">
                    {{ trans_choice(':count sección|:count secciones', $categorias->count(), ['count' => $categorias->count()]) }}
                    disponibles en el sitio.
                </p>
            </div>
            @if ($canManage)
                <button type="button" data-cat-open="create"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white shadow-sm transition-colors"
                    style="background-color: #3b7fa6;">
                    <i class="fas fa-plus"></i>
                    Nueva categoría
                </button>
            @endif
        </div>

        @if (session('ok'))
            <div class="mb-5 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                <i class="fas fa-check-circle mt-0.5"></i>
                <span>{{ session('ok') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-5 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <i class="fas fa-exclamation-circle mt-0.5"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            {{-- Escritorio --}}
            <table class="hidden sm:table w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-500">
                        <th class="px-6 py-3 font-semibold">Categoría</th>
                        <th class="px-6 py-3 font-semibold">Noticias</th>
                        <th class="px-6 py-3 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($categorias as $categoria)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                                        <i class="fas fa-tag text-xs"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="font-medium text-slate-800 truncate">{{ $categoria->nombre }}</p>
                                        <p class="text-slate-400 text-xs truncate">/{{ $categoria->slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 ring-1 ring-inset ring-slate-200">
                                    {{ $categoria->noticias_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="cat-row-actions flex items-center justify-end gap-2">
                                    @if ($canManage)
                                        <button type="button" data-cat-open="edit"
                                            data-cat-id="{{ $categoria->id }}"
                                            data-cat-nombre="{{ $categoria->nombre }}"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-100">
                                            <i class="fas fa-pen"></i> Editar
                                        </button>
                                    @endif
                                    @if ($canDelete)
                                        <form method="POST" action="{{ route('categorias.destroy', $categoria) }}"
                                            onsubmit="return confirm('¿Eliminar la categoría «{{ $categoria->nombre }}»?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-50">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-16 text-center">
                                <i class="fas fa-tags text-3xl text-slate-300"></i>
                                <p class="mt-3 text-sm text-slate-500">Todavía no hay categorías.</p>
                                @if ($canManage)
                                    <button type="button" data-cat-open="create" class="mt-3 text-sm font-medium" style="color: #3b7fa6;">
                                        Crear la primera
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Móvil --}}
            <ul class="sm:hidden divide-y divide-slate-100">
                @forelse ($categorias as $categoria)
                    <li class="p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-medium text-slate-800 truncate">{{ $categoria->nombre }}</p>
                                <p class="text-xs text-slate-400 truncate">/{{ $categoria->slug }}</p>
                            </div>
                            <span class="inline-flex shrink-0 items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 ring-1 ring-inset ring-slate-200">
                                {{ $categoria->noticias_count }}
                            </span>
                        </div>
                        @if ($canManage || $canDelete)
                            <div class="mt-3 flex items-center gap-2">
                                @if ($canManage)
                                    <button type="button" data-cat-open="edit"
                                        data-cat-id="{{ $categoria->id }}"
                                        data-cat-nombre="{{ $categoria->nombre }}"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-100">
                                        <i class="fas fa-pen"></i> Editar
                                    </button>
                                @endif
                                @if ($canDelete)
                                    <form method="POST" action="{{ route('categorias.destroy', $categoria) }}"
                                        onsubmit="return confirm('¿Eliminar la categoría «{{ $categoria->nombre }}»?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-50">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endif
                    </li>
                @empty
                    <li class="px-6 py-16 text-center">
                        <i class="fas fa-tags text-3xl text-slate-300"></i>
                        <p class="mt-3 text-sm text-slate-500">Todavía no hay categorías.</p>
                    </li>
                @endforelse
            </ul>
        </div>
    </div>

    @if ($canManage)
        <div class="cat-modal" id="cat-create" role="dialog" aria-modal="true" aria-labelledby="cat-create-title">
            <div class="cat-modal__backdrop" data-cat-close></div>
            <div class="cat-modal__panel">
                <form method="POST" action="{{ route('categorias.store') }}">
                    @csrf
                    <div class="p-6">
                        <h2 id="cat-create-title" class="text-lg font-semibold text-slate-800">Nueva categoría</h2>
                        <p class="mt-1 text-sm text-slate-500">Se usará en el menú del sitio y en los filtros.</p>

                        <input type="hidden" name="_form" value="create">
                        <label for="cat-create-nombre" class="block text-sm font-medium text-slate-700 mt-5 mb-1.5">Nombre</label>
                        <input id="cat-create-nombre" name="nombre" type="text" value="{{ old('_form') === 'create' ? old('nombre') : '' }}"
                            autocomplete="off" required maxlength="100"
                            class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-800 focus:border-[#3b7fa6] focus:ring-[#3b7fa6]">
                        @if (old('_form') === 'create')
                            @error('nombre')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>

                    <div class="flex justify-end gap-2 px-6 py-4 border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                        <button type="button" data-cat-close
                            class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-white">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-sm"
                            style="background-color: #3b7fa6;">
                            Crear categoría
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="cat-modal" id="cat-edit" role="dialog" aria-modal="true" aria-labelledby="cat-edit-title">
            <div class="cat-modal__backdrop" data-cat-close></div>
            <div class="cat-modal__panel">
                <form method="POST" action="" id="cat-edit-form">
                    @csrf
                    @method('PUT')
                    <div class="p-6">
                        <h2 id="cat-edit-title" class="text-lg font-semibold text-slate-800">Editar categoría</h2>
                        <p class="mt-1 text-sm text-slate-500">Los cambios se reflejan de inmediato.</p>

                        <input type="hidden" name="_form" value="edit">
                        <label for="cat-edit-nombre" class="block text-sm font-medium text-slate-700 mt-5 mb-1.5">Nombre</label>
                        <input id="cat-edit-nombre" name="nombre" type="text" value="{{ old('_form') === 'edit' ? old('nombre') : '' }}"
                            autocomplete="off" required maxlength="100"
                            class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-800 focus:border-[#3b7fa6] focus:ring-[#3b7fa6]">
                        @if (old('_form') === 'edit')
                            @error('nombre')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>

                    <div class="flex justify-end gap-2 px-6 py-4 border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                        <button type="button" data-cat-close
                            class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-white">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-sm"
                            style="background-color: #3b7fa6;">
                            Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            (function () {
                const modals = document.querySelectorAll('.cat-modal');

                function open(id) {
                    modals.forEach(m => m.classList.toggle('is-open', m.id === id));
                    const modal = document.getElementById(id);
                    if (!modal) return;
                    const field = modal.querySelector('input[name="nombre"]');
                    if (field) {
                        field.focus();
                        field.setSelectionRange(field.value.length, field.value.length);
                    }
                }

                function closeAll() {
                    modals.forEach(m => m.classList.remove('is-open'));
                }

                document.querySelectorAll('[data-cat-open]').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const mode = btn.dataset.catOpen;
                        if (mode === 'edit') {
                            const form = document.getElementById('cat-edit-form');
                            form.action = '{{ url('categorias') }}/' + btn.dataset.catId;
                            document.getElementById('cat-edit-nombre').value = btn.dataset.catNombre;
                            open('cat-edit');
                        } else {
                            open('cat-create');
                        }
                    });
                });

                document.querySelectorAll('[data-cat-close]').forEach(el => {
                    el.addEventListener('click', closeAll);
                });

                document.addEventListener('keydown', e => {
                    if (e.key === 'Escape') closeAll();
                });

                const sticky = "{{ old('_form') }}";
                if (sticky === 'create' || sticky === 'edit') {
                    @if ($errors->any())
                        open('cat-' + sticky);
                    @endif
                }
            })();
        </script>
    @endif
</x-app-layout>
