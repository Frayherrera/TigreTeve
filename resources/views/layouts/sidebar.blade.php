<aside class="sidebar" id="sidebar">
    <div class="sidebar-menu">
        <div class="logo">
            <img src="{{ asset('img/logo.png') }}" alt="" srcset="">
        </div>
        <!-- From Uiverse.io by adamgiebl -->
        <div style="display: grid; place-items: center" class="flex"><button
                onclick="window.location='{{ route('noticias.create') }}'" style="color: ffffff" class="cssbuttons-io">
                <span><svg width="30px" height="30px" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M12 4a1 1 0 0 1 1 1v6h6a1 1 0 1 1 0 2h-6v6a1 1 0 1 1-2 0v-6H5a1 1 0 1 1 0-2h6V5a1 1 0 0 1 1-1z"
                            fill="#ffffff" />
                    </svg>
                    Nueva entrada</span>
            </button></div>

        <style>
            .cssbuttons-io {
                position: relative;
                font-family: inherit;
                font-weight: 500;
                font-size: 18px;
                letter-spacing: 0.05em;
                border-radius: 0.8em;
                cursor: pointer;
                border: none;
                background: linear-gradient(to right, #3b7fa6, #3b7fa6);
                color: ghostwhite;
                overflow: hidden;
            }

            .cssbuttons-io svg {
                width: 1.2em;
                height: 1.2em;
                margin-right: 0.5em;
            }

            .cssbuttons-io span {
                position: relative;
                z-index: 10;
                transition: color 0.4s;
                display: inline-flex;
                align-items: center;
                padding: 0.8em 1.2em 0.8em 1.05em;
            }

            .cssbuttons-io::before,
            .cssbuttons-io::after {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 0;
            }

            .cssbuttons-io::before {
                content: "";
                background: #0069d9;
                width: 120%;
                left: -10%;
                transform: skew(30deg);
                transition: transform 0.4s cubic-bezier(0.3, 1, 0.8, 1);
            }

            .cssbuttons-io:hover::before {
                transform: translate3d(100%, 0, 0);
            }

            .cssbuttons-io:active {
                transform: scale(0.95);
            }
        </style>

        <div class="menu-label">Principal</div>
        <a href="{{ route('noticias.index') }}" class="menu-item active">
            <i class="fa-solid fa-book"></i>
            <span>Entradas</span>
        </a>
        <a href="#" class="menu-item">
            <i class="fas fa-chart-line"></i>
            <span>Estadísticas</span>
        </a>
        <a href="#" class="menu-item">
            <i class="fa-solid fa-book"></i> <span>Notificaciones</span>
        </a>

        <div class="menu-label">Contenido</div>
        <a href="#" class="menu-item">
            <i class="fas fa-user"></i>
            <span>Usuarios</span>
        </a>
        <a href="#" class="menu-item">
            <i class="fas fa-file"></i>
            <span>Documentos</span>
        </a>
        <a href="#" class="menu-item">
            <i class="fas fa-database"></i>
            <span>Archivos</span>
        </a>

        <div class="menu-label">Configuración</div>
        <a href="#" class="menu-item">
            <i class="fas fa-cog"></i>
            <span>Ajustes</span>
        </a>
        <a href="#" class="menu-item">
            <i class="fas fa-shield-alt"></i>
            <span>Privacidad</span>
        </a>
        <a href="#" class="menu-item">
            <i class="fas fa-question-circle"></i>
            <span>Ayuda</span>
        </a>
    </div>


</aside>
<style>
    .logo {
        display: flex;
        justify-content: center;
        /* centra horizontal */
        align-items: center;
        /* centra vertical */
        height: 20%;
        /* ajusta según lo que quieras */
    }

    .logo img {
        max-width: 50%;
        height: auto;
    }

    .sidebar {
        border-top-width: 64px;
        width: 280px;
        background: linear-gradient(135deg, #ffffff 0%, #ffffff 100%);
        color: rgb(92, 63, 63);
        transition: all 0.3s ease;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
        position: relative;
        overflow: auto;
        height: 100vh;
    }

    .sidebar-header {
        padding: 25px 20px;
        text-align: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .sidebar-header h2 {
        font-weight: 600;
        font-size: 1.5rem;
        margin-bottom: 5px;
        letter-spacing: 1px;
    }

    .sidebar-header p {
        font-size: 0.85rem;
        color: #805e5e;
    }

    .sidebar-menu {
        padding: 20px 0;
        flex: 1;
    }

    .menu-item {
        display: flex;
        align-items: center;
        padding: 15px 25px;
        color: #503434;

        transition: all 0.2s ease;
        position: relative;
    }

    .menu-item:hover {
        background-color: rgba(52, 152, 219, 0.2);
        color: rgb(15, 1, 1);
        padding-left: 30px;
        text-decoration: none;
    }


    .menu-item.active {
        background-color: rgba(52, 152, 219, 0.2);
        color: rgb(0, 0, 0);
        border-left: 4px solid #3498db;
        text-decoration: none;
    }

    .menu-item i {
        width: 25px;
        font-size: 18px;
        margin-right: 15px;
    }

    .menu-label {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 15px 25px 5px;
        color: #8c8c8c;
    }
</style>
