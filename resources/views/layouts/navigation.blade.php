<nav class="bg-white border-b border-gray-100 fixed top-0 left-0 w-full z-50 lg:pl-64">
    <div class="flex h-16 items-center gap-2 sm:gap-4 px-3 sm:px-6">
        {{-- Abrir menú lateral (móvil y tablet) --}}
        <button type="button" data-adm-abrir aria-controls="adm-menu" aria-expanded="false"
            class="lg:hidden flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100"
            aria-label="Abrir menú">
            <i class="fas fa-bars text-lg"></i>
        </button>

        {{-- Menú de usuario --}}
        <div class="ml-auto flex shrink-0 items-center">
            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button
                        class="inline-flex items-center gap-2 rounded-lg px-1.5 sm:px-3 py-1.5 text-sm font-medium text-gray-600 hover:bg-slate-100 hover:text-gray-800 focus:outline-none transition"
                        aria-label="Menú de usuario">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold text-white"
                            style="background-color:#3b7fa6">
                            {{ mb_strtoupper(mb_substr(Auth::user()->name ?? 'I', 0, 1)) }}
                        </span>
                        <span class="hidden sm:inline max-w-[10rem] truncate">{{ Auth::user()->name ?? 'Invitado' }}</span>
                        <svg class="hidden sm:block fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                </x-slot>

                <x-slot name="content">
                    <div class="px-4 py-2 border-b border-gray-100 sm:hidden">
                        <div class="font-medium text-sm text-gray-800 truncate">{{ Auth::user()->name ?? 'Invitado' }}</div>
                        <div class="text-xs text-gray-500 truncate">{{ Auth::user()->email ?? '' }}</div>
                    </div>

                    <x-dropdown-link :href="route('profile.edit')">
                        {{ __('Perfil') }}
                    </x-dropdown-link>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-dropdown-link :href="route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                            {{ __('Cerrar sesión') }}
                        </x-dropdown-link>
                    </form>
                </x-slot>
            </x-dropdown>
        </div>
    </div>
</nav>
