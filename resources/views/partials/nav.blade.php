<nav class="nav">
    {{-- <div class="logo">
                    <img width="100%" src="{{ asset('img/logoHome.png') }}" alt="">
                </div> --}}
    <ul class="nav-menu">
        {{-- Primeras 4 categorías --}}
        @foreach ($categorias->take(4) as $categoria)
            <li>
                <a href="">
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
                            <a href="">
                                {{ $categoria->nombre }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </li>
        @endif
    </ul>

    <div class="search-box">
        <input type="text" placeholder="Buscar noticias...">
        <button><i class="fas fa-search"></i></button>
    </div>@auth
        @role('Administrator')
            <a class="a2" href="{{ url('/dashboard') }}"
                class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white">
                Dashboard
            </a>
        @endrole
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

