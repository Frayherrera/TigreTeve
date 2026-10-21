<form method="POST" action="{{ $action }}" class="bg-white rounded-2xl border border-slate-200 shadow-sm">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="p-6 sm:p-8 space-y-8">
        <section>
            <h2 class="text-sm font-semibold text-slate-800">Datos de la cuenta</h2>
            <p class="text-xs text-slate-500 mt-1">Nombre y correo con los que el usuario inicia sesión.</p>

            <div class="mt-4 grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">Nombre</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user?->name) }}" required
                        class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-800 focus:border-[#3b7fa6] focus:ring-[#3b7fa6]">
                    @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Correo</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user?->email) }}" required
                        class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-800 focus:border-[#3b7fa6] focus:ring-[#3b7fa6]">
                    @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        <section>
            <h2 class="text-sm font-semibold text-slate-800">Contraseña</h2>
            <p class="text-xs text-slate-500 mt-1">
                @if ($user)
                    Deja los campos en blanco para mantener la contraseña actual.
                @else
                    Define una contraseña inicial segura.
                @endif
            </p>

            <div class="mt-4 grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">Contraseña</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" @required(! $user)
                        class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-800 focus:border-[#3b7fa6] focus:ring-[#3b7fa6]">
                    @error('password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1.5">Confirmar contraseña</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" @required(! $user)
                        class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-800 focus:border-[#3b7fa6] focus:ring-[#3b7fa6]">
                </div>
            </div>
        </section>

        <section>
            <h2 class="text-sm font-semibold text-slate-800">Rol</h2>
            <p class="text-xs text-slate-500 mt-1">El rol define qué acciones puede realizar dentro del panel.</p>

            <div class="mt-4 max-w-sm">
                <label for="role" class="block text-sm font-medium text-slate-700 mb-1.5">Rol del usuario</label>
                <select id="role" name="role" required
                    class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-800 focus:border-[#3b7fa6] focus:ring-[#3b7fa6]">
                    @foreach ($roles as $role)
                        <option value="{{ $role }}" @selected(old('role', $user?->roles->first()?->name) === $role)>
                            {{ $role }}
                        </option>
                    @endforeach
                </select>
                @error('role')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </section>
    </div>

    <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 px-6 sm:px-8 py-4 border-t border-slate-200 bg-slate-50 rounded-b-2xl">
        <a href="{{ route('usuarios.index') }}"
            class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-white">
            Cancelar
        </a>
        <button type="submit"
            class="inline-flex items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-sm"
            style="background-color: #3b7fa6;">
            <i class="fas fa-check"></i>
            {{ $user ? 'Guardar cambios' : 'Crear usuario' }}
        </button>
    </div>
</form>
