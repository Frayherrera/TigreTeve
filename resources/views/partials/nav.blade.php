<nav class="nav">
    <ul class="nav-menu">
        <li><a href="{{ route('noticias.home') }}">
                Inicio
            </a></li>
        {{-- Primeras 4 categorías --}}
        @foreach ($categorias->take(4) as $categoria)
            <li>
                <a href="{{ route('noticias.home', $categoria->slug) }}">
                    {{ $categoria->nombre }}
                </a>
            </li>
        @endforeach
        {{-- El resto en un dropdown --}}
        @if ($categorias->count() > 4)
            <li class="dropdown">
                <a href="#">Más ▾</a>
                <ul class="dropdown-menu">
                    @foreach ($categorias->skip(4) as $categoria)
                        <li>
                            <a href="{{ route('noticias.home', $categoria->slug) }}">
                                {{ $categoria->nombre }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </li>
        @endif

    </ul>

    <div class="search-box">
        <form action="{{ route('noticias.home') }}" method="GET" class="d-flex ms-auto">
            <input type="text" name="q" class="form-control me-2" placeholder="Buscar noticias..."
                value="{{ request('q') }}">
            <button type="submit" class="btn btn-outline-primary">Buscar</button>
        </form>

    </div>

    @auth
        @role('Administrator')
            <a class="a2" href="{{ url('/noticias') }}"
                class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white">
                Dashboard
            </a>
        @endrole
        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault();
            this.closest('form').submit();">
                {{ __('Cerrar sesión') }}
            </x-dropdown-link>
        </form>
    @else
        <a class="a2" href="{{ route('login') }}"
            class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white">
            Entrar
        </a>

        @if (Route::has('register'))
            <a class="a2" href="{{ route('register') }}"
                class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white">
                Registrar
            </a>
        @endif
    @endauth

</nav>
