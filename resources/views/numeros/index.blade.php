<x-app-layout>
    <style>
        .num-modal {
            position: fixed;
            inset: 0;
            z-index: 60;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .num-modal.is-open {
            display: flex;
        }

        .num-modal__backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(2px);
        }

        .num-modal__panel {
            position: relative;
            width: 100%;
            max-width: 26rem;
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.25);
            animation: num-pop 0.18s ease-out;
        }

        @keyframes num-pop {
            from { opacity: 0; transform: translateY(8px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .num-row-actions {
            opacity: 0;
            transition: opacity 0.15s ease;
        }

        tr:hover .num-row-actions,
        .num-row-actions:focus-within {
            opacity: 1;
        }

        @media (hover: none) {
            .num-row-actions { opacity: 1; }
        }

        @media (prefers-reduced-motion: reduce) {
            .num-modal__panel { animation: none; }
        }
    </style>

    <div class="px-4 sm:px-6 lg:px-8 py-8 max-w-4xl mx-auto">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800">Números de WhatsApp</h1>
                <p class="text-sm text-slate-500 mt-1">
                    {{ trans_choice(':count contacto en la lista|:count contactos en la lista', $numeros->total(), ['count' => $numeros->total()]) }}.
                    Se usan en el envío masivo.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" data-num-open="import"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-100">
                    <i class="fas fa-file-import"></i>
                    Importar CSV
                </button>
                <button type="button" data-num-open="create"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white shadow-sm transition-colors"
                    style="background-color: #3b7fa6;">
                    <i class="fas fa-plus"></i>
                    Agregar número
                </button>
            </div>
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

        <form method="GET" action="{{ route('numeros.index') }}" class="mb-4">
            <div class="relative max-w-sm">
                <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" name="q" value="{{ $buscar }}" placeholder="Buscar número…"
                    class="w-full rounded-xl border border-slate-300 pl-10 pr-3 py-2.5 text-sm text-slate-800 focus:border-[#3b7fa6] focus:ring-[#3b7fa6]">
            </div>
        </form>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            {{-- Escritorio --}}
            <table class="hidden sm:table w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-500">
                        <th class="px-6 py-3 font-semibold">Número</th>
                        <th class="px-6 py-3 font-semibold">Agregado</th>
                        <th class="px-6 py-3 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($numeros as $item)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                                        <i class="fab fa-whatsapp text-sm"></i>
                                    </span>
                                    <span class="font-medium text-slate-800 tracking-wide">+{{ $item->numero }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-500">{{ $item->created_at->format('d/m/Y') }}</td>
                            <td class="px-6 py-4">
                                <div class="num-row-actions flex items-center justify-end gap-2">
                                    <button type="button" data-num-open="edit"
                                        data-num-id="{{ $item->id }}"
                                        data-num-numero="{{ $item->numero }}"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-100">
                                        <i class="fas fa-pen"></i> Editar
                                    </button>
                                    <form method="POST" action="{{ route('numeros.destroy', $item) }}"
                                        onsubmit="return confirm('¿Eliminar el número +{{ $item->numero }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-50">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-16 text-center">
                                <i class="fab fa-whatsapp text-3xl text-slate-300"></i>
                                <p class="mt-3 text-sm text-slate-500">
                                    {{ $buscar !== '' ? 'No hay coincidencias para «'.$buscar.'».' : 'Aún no hay números en la lista.' }}
                                </p>
                                @if ($buscar === '')
                                    <button type="button" data-num-open="create" class="mt-3 text-sm font-medium" style="color: #3b7fa6;">
                                        Agregar el primero
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Móvil --}}
            <ul class="sm:hidden divide-y divide-slate-100">
                @forelse ($numeros as $item)
                    <li class="p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                                    <i class="fab fa-whatsapp text-sm"></i>
                                </span>
                                <div class="min-w-0">
                                    <p class="font-medium text-slate-800 truncate">+{{ $item->numero }}</p>
                                    <p class="text-xs text-slate-400">{{ $item->created_at->format('d/m/Y') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 flex items-center gap-2">
                            <button type="button" data-num-open="edit"
                                data-num-id="{{ $item->id }}"
                                data-num-numero="{{ $item->numero }}"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-100">
                                <i class="fas fa-pen"></i> Editar
                            </button>
                            <form method="POST" action="{{ route('numeros.destroy', $item) }}"
                                onsubmit="return confirm('¿Eliminar el número +{{ $item->numero }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-50">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </form>
                        </div>
                    </li>
                @empty
                    <li class="px-6 py-16 text-center">
                        <i class="fab fa-whatsapp text-3xl text-slate-300"></i>
                        <p class="mt-3 text-sm text-slate-500">
                            {{ $buscar !== '' ? 'No hay coincidencias para «'.$buscar.'».' : 'Aún no hay números en la lista.' }}
                        </p>
                    </li>
                @endforelse
            </ul>
        </div>

        <div class="mt-6">{{ $numeros->links() }}</div>
    </div>

    <div class="num-modal" id="num-create" role="dialog" aria-modal="true" aria-labelledby="num-create-title">
        <div class="num-modal__backdrop" data-num-close></div>
        <div class="num-modal__panel">
            <form method="POST" action="{{ route('numeros.store') }}">
                @csrf
                <div class="p-6">
                    <h2 id="num-create-title" class="text-lg font-semibold text-slate-800">Agregar número</h2>
                    <p class="mt-1 text-sm text-slate-500">Incluye el indicativo del país, sin + ni espacios.</p>

                    <input type="hidden" name="_form" value="create">
                    <label for="num-create-numero" class="block text-sm font-medium text-slate-700 mt-5 mb-1.5">Número</label>
                    <input id="num-create-numero" name="numero" type="tel" inputmode="numeric"
                        value="{{ old('_form') === 'create' ? old('numero') : '' }}"
                        autocomplete="off" required placeholder="573001234567"
                        class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-800 focus:border-[#3b7fa6] focus:ring-[#3b7fa6]">
                    @if (old('_form') === 'create')
                        @error('numero')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    @endif
                </div>

                <div class="flex justify-end gap-2 px-6 py-4 border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                    <button type="button" data-num-close
                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-white">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-sm"
                        style="background-color: #3b7fa6;">
                        Agregar número
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="num-modal" id="num-edit" role="dialog" aria-modal="true" aria-labelledby="num-edit-title">
        <div class="num-modal__backdrop" data-num-close></div>
        <div class="num-modal__panel">
            <form method="POST" action="" id="num-edit-form">
                @csrf
                @method('PUT')
                <div class="p-6">
                    <h2 id="num-edit-title" class="text-lg font-semibold text-slate-800">Editar número</h2>
                    <p class="mt-1 text-sm text-slate-500">Corrige el número o su indicativo.</p>

                    <input type="hidden" name="_form" value="edit">
                    <label for="num-edit-numero" class="block text-sm font-medium text-slate-700 mt-5 mb-1.5">Número</label>
                    <input id="num-edit-numero" name="numero" type="tel" inputmode="numeric"
                        value="{{ old('_form') === 'edit' ? old('numero') : '' }}"
                        autocomplete="off" required
                        class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-800 focus:border-[#3b7fa6] focus:ring-[#3b7fa6]">
                    @if (old('_form') === 'edit')
                        @error('numero')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    @endif
                </div>

                <div class="flex justify-end gap-2 px-6 py-4 border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                    <button type="button" data-num-close
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

    <div class="num-modal" id="num-import" role="dialog" aria-modal="true" aria-labelledby="num-import-title">
        <div class="num-modal__backdrop" data-num-close></div>
        <div class="num-modal__panel">
            <form method="POST" action="{{ route('numeros.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="p-6">
                    <h2 id="num-import-title" class="text-lg font-semibold text-slate-800">Importar desde CSV</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        El archivo puede tener una columna con los números o varias; se limpian y se omiten los repetidos.
                    </p>

                    <input type="hidden" name="_form" value="import">
                    <label for="num-import-file" class="block text-sm font-medium text-slate-700 mt-5 mb-1.5">Archivo CSV o TXT</label>
                    <input id="num-import-file" name="file" type="file" accept=".csv,.txt" required
                        class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-slate-700">
                    @error('file')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-2 px-6 py-4 border-t border-slate-200 bg-slate-50 rounded-b-2xl">
                    <button type="button" data-num-close
                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-white">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-sm"
                        style="background-color: #3b7fa6;">
                        <i class="fas fa-file-import"></i>
                        Importar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const modals = document.querySelectorAll('.num-modal');

            function open(id) {
                modals.forEach(m => m.classList.toggle('is-open', m.id === id));
                const modal = document.getElementById(id);
                if (!modal) return;
                const field = modal.querySelector('input[type="tel"]');
                if (field) {
                    field.focus();
                    field.setSelectionRange(field.value.length, field.value.length);
                }
            }

            function closeAll() {
                modals.forEach(m => m.classList.remove('is-open'));
            }

            document.querySelectorAll('[data-num-open]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const mode = btn.dataset.numOpen;
                    if (mode === 'edit') {
                        const form = document.getElementById('num-edit-form');
                        form.action = '{{ url('numeros') }}/' + btn.dataset.numId;
                        document.getElementById('num-edit-numero').value = btn.dataset.numNumero;
                        open('num-edit');
                    } else {
                        open('num-' + mode);
                    }
                });
            });

            document.querySelectorAll('[data-num-close]').forEach(el => {
                el.addEventListener('click', closeAll);
            });

            document.addEventListener('keydown', e => {
                if (e.key === 'Escape') closeAll();
            });

            const sticky = "{{ old('_form') }}";
            if (sticky === 'create' || sticky === 'edit' || sticky === 'import') {
                @if ($errors->any())
                    open('num-' + sticky);
                @endif
            }
        })();
    </script>
</x-app-layout>
