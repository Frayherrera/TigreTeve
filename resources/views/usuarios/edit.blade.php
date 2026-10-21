<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 max-w-3xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('usuarios.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700 mb-3">
                <i class="fas fa-arrow-left"></i> Volver a usuarios
            </a>
            <h1 class="text-2xl font-semibold text-slate-800">Editar usuario</h1>
            <p class="text-sm text-slate-500 mt-1">{{ $user->name }} · {{ $user->email }}</p>
        </div>

        @if (session('error'))
            <div class="mb-5 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <i class="fas fa-exclamation-circle mt-0.5"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @include('usuarios._form', [
            'user' => $user,
            'roles' => $roles,
            'action' => route('usuarios.update', $user),
            'method' => 'PUT',
        ])
    </div>
</x-app-layout>
