<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 max-w-6xl mx-auto">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800">Usuarios</h1>
                <p class="text-sm text-slate-500 mt-1">Administra quién puede entrar al panel y qué rol tiene.</p>
            </div>
            <a href="{{ route('usuarios.create') }}"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white shadow-sm transition-colors"
                style="background-color: #3b7fa6;">
                <i class="fas fa-user-plus"></i>
                Nuevo usuario
            </a>
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

        @php
            $initialsFor = function ($name) {
                $parts = preg_split('/\s+/', trim($name));
                $first = mb_substr($parts[0] ?? '', 0, 1);
                $last = count($parts) > 1 ? mb_substr(end($parts), 0, 1) : '';
                return strtoupper($first . $last);
            };

            $roleStyles = [
                'Administrator' => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
                'Editor' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                'Reportero' => 'bg-amber-50 text-amber-800 ring-amber-200',
                'Lector' => 'bg-slate-100 text-slate-600 ring-slate-200',
            ];
        @endphp

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            @if ($users->isEmpty())
                <div class="px-6 py-16 text-center">
                    <i class="fas fa-users text-3xl text-slate-300"></i>
                    <p class="mt-3 text-sm text-slate-500">No hay usuarios registrados.</p>
                    <a href="{{ route('usuarios.create') }}" class="mt-4 inline-flex text-sm font-medium" style="color: #3b7fa6;">
                        Crear el primero
                    </a>
                </div>
            @else
                {{-- Escritorio --}}
                <table class="hidden md:table w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-500">
                            <th class="px-6 py-3 font-semibold">Usuario</th>
                            <th class="px-6 py-3 font-semibold">Rol</th>
                            <th class="px-6 py-3 font-semibold">Registrado</th>
                            <th class="px-6 py-3 font-semibold text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($users as $user)
                            @php
                                $role = $user->roles->first();
                                $badge = $roleStyles[$role?->name] ?? 'bg-slate-100 text-slate-600 ring-slate-200';
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-100 text-sm font-semibold text-slate-700">
                                            {{ $initialsFor($user->name) }}
                                        </span>
                                        <div class="min-w-0">
                                            <p class="font-medium text-slate-800 truncate">{{ $user->name }}</p>
                                            <p class="text-slate-500 truncate">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $badge }}">
                                        {{ $role?->name ?? 'Sin rol' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-500">{{ $user->created_at->format('d/m/Y') }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('usuarios.edit', $user) }}"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-100">
                                            <i class="fas fa-pen"></i> Editar
                                        </a>
                                        <form method="POST" action="{{ route('usuarios.destroy', $user) }}"
                                            onsubmit="return confirm('¿Eliminar este usuario? Esta acción no se puede deshacer.');">
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
                        @endforeach
                    </tbody>
                </table>

                {{-- Móvil --}}
                <ul class="md:hidden divide-y divide-slate-100">
                    @foreach ($users as $user)
                        @php
                            $role = $user->roles->first();
                            $badge = $roleStyles[$role?->name] ?? 'bg-slate-100 text-slate-600 ring-slate-200';
                        @endphp
                        <li class="p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-100 text-sm font-semibold text-slate-700">
                                        {{ $initialsFor($user->name) }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="font-medium text-slate-800 truncate">{{ $user->name }}</p>
                                        <p class="text-sm text-slate-500 truncate">{{ $user->email }}</p>
                                    </div>
                                </div>
                                <span class="inline-flex shrink-0 items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $badge }}">
                                    {{ $role?->name ?? 'Sin rol' }}
                                </span>
                            </div>
                            <div class="mt-3 flex items-center gap-2">
                                <a href="{{ route('usuarios.edit', $user) }}"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-100">
                                    <i class="fas fa-pen"></i> Editar
                                </a>
                                <form method="POST" action="{{ route('usuarios.destroy', $user) }}"
                                    onsubmit="return confirm('¿Eliminar este usuario? Esta acción no se puede deshacer.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-50">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="mt-6">{{ $users->links() }}</div>
    </div>
</x-app-layout>
