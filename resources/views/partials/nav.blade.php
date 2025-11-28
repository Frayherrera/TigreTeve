{{-- Navbar HTML --}}
<nav class="nav">
    

    {{-- Menú hamburguesa (solo visible en móvil) --}}
    <button class="hamburger" id="hamburger">
        <span></span>
        <span></span>
        <span></span>
    </button>

    {{-- Menú principal --}}
    <div class="nav-wrapper" id="navWrapper">
        <ul class="nav-menu">
            <li><a href="{{ route('noticias.home') }}">Inicio</a></li>

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
                    <a href="#" class="dropdown-toggle">Más ▾</a>
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

        {{-- Buscador --}}
        <div class="search-box">
            <form action="{{ route('noticias.home') }}" method="GET">
                <input type="text" name="q" placeholder="Buscar noticias..." value="{{ request('q') }}">
                <button type="submit" style="background-color: rgb(182, 111, 70)">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        {{-- Botones de autenticación --}}
        <div class="auth-buttons">
            @auth
                @role('Administrator')
                    <a class="a2" href="{{ url('/noticias') }}">Dashboard</a>
                @endrole
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="a2 logout-btn">Cerrar sesión</button>
                </form>
            @else
                <a class="a2" href="{{ route('login') }}">Entrar</a>
                @if (Route::has('register'))
                    <a class="a2" href="{{ route('register') }}">Registrar</a>
                @endif
            @endauth
        </div>
    </div>
</nav>

{{-- CSS Completo --}}
<style>
    .header {
        background: linear-gradient(135deg, #f5ad1d 0%, #f5ad1d 100%);
        color: white;
        padding: 1rem 0;
        position: sticky;
        top: 0;
        z-index: 1000;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
    }

    /* Logo */
    .logo {
        display: flex;
        align-items: center;
        z-index: 1001;
    }

    .logo img {
        max-width: 150px;
        height: auto;
    }

    /* Menú hamburguesa */
    .hamburger {
        display: none;
        flex-direction: column;
        gap: 5px;
        background: none;
        border: none;
        cursor: pointer;
        padding: 10px;
        z-index: 1001;
    }

    .hamburger span {
        width: 25px;
        height: 3px;
        background: white;
        transition: all 0.3s ease;
        border-radius: 3px;
    }

    .hamburger.active span:nth-child(1) {
        transform: rotate(45deg) translate(8px, 8px);
    }

    .hamburger.active span:nth-child(2) {
        opacity: 0;
    }

    .hamburger.active span:nth-child(3) {
        transform: rotate(-45deg) translate(7px, -7px);
    }

    /* Wrapper del menú */
    .nav-wrapper {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .nav-menu {
        display: flex;
        list-style: none;
        gap: 10px;
        margin: 0;
        padding: 0;
    }

    .nav-menu li {
        position: relative;
    }

    .nav-menu a {
        color: rgb(253, 253, 253);
        text-decoration: none;
        font-weight: 500;
        padding: 8px 16px;
        border-radius: 25px;
        transition: all 0.3s ease;
        display: block;
        white-space: nowrap;
    }

    .nav-menu a:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: translateY(-2px);
    }

    /* Dropdown */
    .dropdown {
        position: relative;
    }

    .dropdown-toggle::after {
        content: '';
    }

    .dropdown-menu {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        background: white;
        min-width: 200px;
        border-radius: 8px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        list-style: none;
        padding: 10px 0;
        margin-top: 5px;
        z-index: 1000;
    }

    .dropdown:hover .dropdown-menu {
        display: block;
    }

    .dropdown-menu li {
        padding: 0;
    }

    .dropdown-menu a {
        color: #333;
        padding: 10px 20px;
        border-radius: 0;
    }

    .dropdown-menu a:hover {
        background: #f5ad1d;
        color: white;
        transform: none;
    }

    /* Buscador */
    .search-box {
        position: relative;
    }

    .search-box form {
        display: flex;
        align-items: center;
    }

    .search-box input {
        padding: 10px 40px 10px 15px;
        border: none;
        border-radius: 25px;
        outline: none;
        width: 250px;
    }

    .search-box button {
        position: absolute;
        right: 5px;
        top: 50%;
        transform: translateY(-50%);
        background: rgb(182, 111, 70);
        color: white;
        border: none;
        padding: 8px 12px;
        border-radius: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .search-box button:hover {
        background: rgb(162, 91, 50);
    }

    /* Botones de autenticación */
    .auth-buttons {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .a2 {
        text-decoration: none;
        color: white;
        padding: 8px 16px;
        border-radius: 25px;
        transition: all 0.3s ease;
        white-space: nowrap;
        border: none;
        background: none;
        cursor: pointer;
        font-size: 1rem;
        font-family: inherit;
    }

    .a2:hover {
        background: rgb(182, 111, 70);
        transform: translateY(-2px);
    }

    .logout-btn {
        display: inline-block;
    }

    /* ================================
   RESPONSIVE - MOBILE
   ================================ */

    @media (max-width: 992px) {
        .hamburger {
            display: flex;
        }

        .nav-wrapper {
            position: fixed;
            top: 0;
            right: -100%;
            width: 300px;
            height: 100vh;
            background: #f5ad1d;
            flex-direction: column;
            align-items: flex-start;
            padding: 80px 20px 20px;
            transition: right 0.3s ease;
            overflow-y: auto;
            box-shadow: -5px 0 15px rgba(0, 0, 0, 0.2);
        }

        .nav-wrapper.active {
            right: 0;
        }

        .nav-menu {
            flex-direction: column;
            width: 100%;
            gap: 0;
        }

        .nav-menu li {
            width: 100%;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .nav-menu a {
            padding: 15px 20px;
            border-radius: 0;
        }

        /* Dropdown en móvil */
        .dropdown-menu {
            position: static;
            display: none;
            background: rgba(255, 255, 255, 0.1);
            box-shadow: none;
            border-radius: 0;
            margin: 0;
            padding: 0;
        }

        .dropdown.active .dropdown-menu {
            display: block;
        }

        .dropdown-toggle {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .dropdown-menu a {
            padding-left: 40px;
            color: white;
        }

        /* Buscador en móvil */
        .search-box {
            width: 100%;
            margin: 20px 0;
        }

        .search-box form {
            width: 100%;
        }

        .search-box input {
            width: 100%;
        }

        /* Botones de auth en móvil */
        .auth-buttons {
            flex-direction: column;
            width: 100%;
            gap: 10px;
        }

        .a2 {
            width: 100%;
            text-align: center;
            padding: 12px 20px;
        }

        .logout-btn {
            width: 100%;
        }
    }

    @media (max-width: 768px) {
        .logo img {
            max-width: 120px;
        }

        .nav-wrapper {
            width: 280px;
        }
    }

    @media (max-width: 480px) {
        .header {
            padding: 0.5rem 0;
        }

        .logo img {
            max-width: 100px;
        }

        .nav-wrapper {
            width: 100%;
            right: -100%;
        }

        .container {
            padding: 0 15px;
        }
    }

    /* Overlay para cerrar el menú */
    .nav-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
    }

    .nav-overlay.active {
        display: block;
    }
</style>

{{-- JavaScript --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const hamburger = document.getElementById('hamburger');
        const navWrapper = document.getElementById('navWrapper');
        const dropdowns = document.querySelectorAll('.dropdown');

        // Crear overlay
        const overlay = document.createElement('div');
        overlay.className = 'nav-overlay';
        document.body.appendChild(overlay);

        // Toggle menú móvil
        hamburger.addEventListener('click', function() {
            this.classList.toggle('active');
            navWrapper.classList.toggle('active');
            overlay.classList.toggle('active');
            document.body.style.overflow = navWrapper.classList.contains('active') ? 'hidden' : '';
        });

        // Cerrar menú al hacer click en overlay
        overlay.addEventListener('click', function() {
            hamburger.classList.remove('active');
            navWrapper.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        });

        // Dropdown en móvil
        dropdowns.forEach(dropdown => {
            const toggle = dropdown.querySelector('.dropdown-toggle');
            if (toggle) {
                toggle.addEventListener('click', function(e) {
                    if (window.innerWidth <= 992) {
                        e.preventDefault();
                        dropdown.classList.toggle('active');
                    }
                });
            }
        });

        // Cerrar menú al hacer click en un enlace (móvil)
        const navLinks = document.querySelectorAll('.nav-menu a:not(.dropdown-toggle)');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 992) {
                    hamburger.classList.remove('active');
                    navWrapper.classList.remove('active');
                    overlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        });

        // Cerrar dropdowns al cambiar tamaño de ventana
        window.addEventListener('resize', function() {
            if (window.innerWidth > 992) {
                hamburger.classList.remove('active');
                navWrapper.classList.remove('active');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
                dropdowns.forEach(dropdown => dropdown.classList.remove('active'));
            }
        });
    });
</script>
