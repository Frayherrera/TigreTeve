@extends('layouts.home')

@section('title', 'Inicio')

@section('content')
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Tu Fuente Confiable de Noticias</h1>
                <p>Mantente informado con las últimas noticias de Colombia y el mundo. Cobertura completa, análisis
                    profundo y reportajes exclusivos.</p>
            </div>
        </div>
    </section>

    <main class="main-content">
        <div class="container">
            <div class="content-grid">
                <!-- Main Content -->
                <div class="main-articles">
                    <!-- Featured Article -->
                    @foreach ($noticiasDestacadas as $noticia)
                        <x-featured-article :noticia="$noticia" />
                    @endforeach

                    <!-- Blog Grid -->
                    <div class="blog-grid">
                        @foreach ($noticias as $noticia)
                            <x-blog-card :noticia="$noticia" />
                        @endforeach
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
                            <div style="display: flex; justify-content: space-between; font-size: 0.9rem; color: #888;">
                                <span>Máx: 35°C</span>
                                <span>Mín: 24°C</span>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </main>
@endsection
