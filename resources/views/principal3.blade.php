<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Noticias Blog - Tu fuente de información</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    @vite(['resources/css/home.css', 'resources/js/app.js'])


</head>

<body>
    <!-- Header -->
    <header class="header flex">

        <div class="container">
            <nav class="nav">
                {{-- <div class="logo">
                    <img width="100%" src="{{ asset('img/logoHome.png') }}" alt="">
                </div> --}}
                <ul class="nav-menu">
                    <li><a href="#home">Inicio</a></li>
                    <li><a href="#politica">Política</a></li>
                    <li><a href="#deportes">Deportes</a></li>
                    <li><a href="#economia">Economía</a></li>
                    <li><a href="#tecnologia">Tecnología</a></li>
                    <li><a href="#cultura">Cultura</a></li>
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
        </div>
    </header>

    <!-- Breaking News -->
    {{-- <div class="breaking-news">
        <div class="container">
            <div class="breaking-content">
                <span class="breaking-badge">ÚLTIMO MOMENTO</span>
                <div class="breaking-text">
                    Nuevas medidas económicas anunciadas por el gobierno • Clasificación mundial de fútbol actualizada • Avances en tecnología de inteligencia artificial
                </div>
            </div>
        </div>
    </div> --}}

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Tu Fuente Confiable de Noticias</h1>
                <p>Mantente informado con las últimas noticias de Colombia y el mundo. Cobertura completa, análisis
                    profundo y reportajes exclusivos.</p>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="content-grid">
                <!-- Main Content -->
                <div class="main-articles">
                    <!-- Featured Article -->
                    @foreach ($noticias as $noticia)
                        <x-featured-article :noticia="$noticia" />
                    @endforeach

                    <!-- Blog Grid -->
                    <div class="blog-grid">
                        <article class="blog-card">
                            <img src="https://images.unsplash.com/photo-1461749280684-dccba630e2f6?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80"
                                alt="Tecnología">
                            <div class="blog-card-content">
                                <span class="category-badge" style="background: #ff6b6b;">Tecnología</span>
                                <h3>Inteligencia Artificial revoluciona el sector educativo</h3>
                                <p>Las nuevas herramientas de IA están transformando la manera en que estudiantes y
                                    profesores interactúan con el conocimiento...</p>
                                <div class="blog-meta">
                                    <span><i class="fas fa-user"></i> Carlos Ruiz</span>
                                    <span><i class="fas fa-clock"></i> Hace 3 horas</span>
                                </div>
                            </div>
                        </article>

                        <article class="blog-card">
                            <img src="https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80"
                                alt="Deportes">
                            <div class="blog-card-content">
                                <span class="category-badge" style="background: #2ed573;">Deportes</span>
                                <h3>Selección Colombia se prepara para eliminatorias</h3>
                                <p>El equipo nacional intensifica entrenamientos con miras a los próximos partidos
                                    clasificatorios al Mundial...</p>
                                <div class="blog-meta">
                                    <span><i class="fas fa-user"></i> Ana López</span>
                                    <span><i class="fas fa-clock"></i> Hace 5 horas</span>
                                </div>
                            </div>
                        </article>

                        <article class="blog-card">
                            <img src="https://images.unsplash.com/photo-1444653389962-8149286c578a?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80"
                                alt="Economía">
                            <div class="blog-card-content">
                                <span class="category-badge" style="background: #ffa502;">Economía</span>
                                <h3>Dólar presenta tendencia a la baja esta semana</h3>
                                <p>Expertos económicos analizan los factores que han influido en la reciente devaluación
                                    del dólar frente al peso...</p>
                                <div class="blog-meta">
                                    <span><i class="fas fa-user"></i> Roberto Silva</span>
                                    <span><i class="fas fa-clock"></i> Hace 1 día</span>
                                </div>
                            </div>
                        </article>

                        <article class="blog-card">
                            <img src="https://images.unsplash.com/photo-1524661135-423995f22d0b?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80"
                                alt="Cultura">
                            <div class="blog-card-content">
                                <span class="category-badge" style="background: #a55eea;">Cultura</span>
                                <h3>Festival de música tradicional llega a Aguachica</h3>
                                <p>La ciudad se prepara para recibir artistas de toda la región en una celebración de la
                                    música folclórica colombiana...</p>
                                <div class="blog-meta">
                                    <span><i class="fas fa-user"></i> Laura Pérez</span>
                                    <span><i class="fas fa-clock"></i> Hace 2 días</span>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>

                <!-- Sidebar -->
                <aside class="sidebar">
                    <!-- Trending Articles -->
                    <div class="sidebar-widget">
                        <h3 class="widget-title"><i class="fas fa-fire"></i> Trending</h3>
                        <div class="trending-item">
                            <img src="https://images.unsplash.com/photo-1495020689067-958852a7765e?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80"
                                alt="Trending">
                            <div class="trending-content">
                                <h4>Crisis energética: nuevas medidas gubernamentales</h4>
                                <span><i class="fas fa-eye"></i> 15k lecturas</span>
                            </div>
                        </div>
                        <div class="trending-item">
                            <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80"
                                alt="Trending">
                            <div class="trending-content">
                                <h4>Innovación en agricultura sostenible</h4>
                                <span><i class="fas fa-eye"></i> 12k lecturas</span>
                            </div>
                        </div>
                        <div class="trending-item">
                            <img src="https://images.unsplash.com/photo-1557804506-669a67965ba0?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80"
                                alt="Trending">
                            <div class="trending-content">
                                <h4>Mercado inmobiliario muestra signos de recuperación</h4>
                                <span><i class="fas fa-eye"></i> 9k lecturas</span>
                            </div>
                        </div>
                    </div>

                    <!-- Categories -->
                    <div class="sidebar-widget">
                        <h3 class="widget-title"><i class="fas fa-tags"></i> Categorías</h3>
                        <div class="categories-list">
                            <a href="#" class="category-tag">Política (45)</a>
                            <a href="#" class="category-tag">Deportes (32)</a>
                            <a href="#" class="category-tag">Economía (28)</a>
                            <a href="#" class="category-tag">Tecnología (24)</a>
                            <a href="#" class="category-tag">Cultura (19)</a>
                            <a href="#" class="category-tag">Internacional (15)</a>
                            <a href="#" class="category-tag">Salud (12)</a>
                        </div>
                    </div>

                    <!-- Newsletter -->
                    <div class="sidebar-widget">
                        <h3 class="widget-title"><i class="fas fa-envelope"></i> Newsletter</h3>
                        <p>Recibe las mejores noticias directamente en tu correo electrónico.</p>
                        <form class="newsletter-form">
                            <input type="email" placeholder="Tu correo electrónico" required>
                            <button type="submit" class="newsletter-btn">
                                <i class="fas fa-paper-plane"></i> Suscribirse
                            </button>
                        </form>
                    </div>

                    <!-- Weather Widget -->
                    <div class="sidebar-widget">
                        <h3 class="widget-title"><i class="fas fa-cloud-sun"></i> Clima - Aguachica</h3>
                        <div style="text-align: center;">
                            <div style="font-size: 3rem; color: #ffa502; margin: 15px 0;">
                                <i class="fas fa-sun"></i>
                            </div>
                            <h2 style="color: #2c3e50; margin-bottom: 5px;">32°C</h2>
                            <p style="color: #666; margin-bottom: 10px;">Soleado</p>
                            <div
                                style="display: flex; justify-content: space-between; font-size: 0.9rem; color: #888;">
                                <span>Máx: 35°C</span>
                                <span>Mín: 24°C</span>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><i class="fas fa-newspaper"></i> NoticiasBlog</h3>
                    <p>Tu fuente confiable de información. Comprometidos con la verdad y la transparencia informativa
                        desde 2020.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h3>Secciones</h3>
                    <p><a href="#">Política Nacional</a></p>
                    <p><a href="#">Deportes</a></p>
                    <p><a href="#">Economía y Finanzas</a></p>
                    <p><a href="#">Tecnología</a></p>
                    <p><a href="#">Cultura y Entretenimiento</a></p>
                </div>
                <div class="footer-section">
                    <h3>Información</h3>
                    <p><a href="#">Sobre Nosotros</a></p>
                    <p><a href="#">Contacto</a></p>
                    <p><a href="#">Políticas de Privacidad</a></p>
                    <p><a href="#">Términos de Uso</a></p>
                    <p><a href="#">Trabaja con Nosotros</a></p>
                </div>
                <div class="footer-section">
                    <h3>Contacto</h3>
                    <p><i class="fas fa-map-marker-alt"></i> Aguachica, Cesar - Colombia</p>
                    <p><i class="fas fa-phone"></i> +57 (5) 123-4567</p>
                    <p><i class="fas fa-envelope"></i> info@noticiasblog.com</p>
                    <p><i class="fas fa-globe"></i> www.noticiasblog.com</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 NoticiasBlog. Todos los derechos reservados. | Diseñado con ❤️ para informar mejor</p>
            </div>
        </div>
    </footer>

    <script>
        // Simple search functionality
        document.querySelector('.search-box button').addEventListener('click', function() {
            const searchTerm = document.querySelector('.search-box input').value;
            if (searchTerm) {
                alert('Buscando: ' + searchTerm);
                // Aquí implementarías la lógica de búsqueda real
            }
        });

        // Newsletter form
        document.querySelector('.newsletter-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;
            alert('¡Gracias por suscribirte! Te enviaremos noticias a: ' + email);
            this.reset();
        });

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                if (targetId !== '#') {
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        targetElement.scrollIntoView({
                            behavior: 'smooth'
                        });
                    }
                }
            });
        });
    </script>
</body>

</html>
