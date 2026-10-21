<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 max-w-3xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('usuarios.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700 mb-3">
                <i class="fas fa-arrow-left"></i> Volver a usuarios
            </a>
            <h1 class="text-2xl font-semibold text-slate-800">Nuevo usuario</h1>
            <p class="text-sm text-slate-500 mt-1">Crea una cuenta y asígnale un rol.</p>
        </div>

        @include('usuarios._form', [
            'user' => null,
            'roles' => $roles,
            'action' => route('usuarios.store'),
            'method' => 'POST',
        ])
    </div>
</x-app-layout>
