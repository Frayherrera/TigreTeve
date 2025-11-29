@extends('layouts.home')

@section('title', 'Inicio')

@section('content')

    <section class="hero">
    </section>
    <main class="main-content">
        <div class="container">
            <div class="content-grid">
                <!-- Main Content -->
                <div class="main-articles">
                    <!-- Featured Article -->
                    @forelse ($noticiasDestacadas as $noticia)
                        <x-featured-article :noticia="$noticia" />
                    @empty
                        <h2>No hay noticias destacadas en esta categoria!</h2>
                    @endforelse

                    <!-- Blog Grid -->
                    <div class="blog-grid" id="noticias-container">
                        @foreach ($noticias as $noticia)
                            <x-blog-card :noticia="$noticia" />
                        @endforeach
                    </div>


                </div>

                <!-- Sidebar -->
                <aside class="sidebar">
                    <!-- Trending Articles -->
                    <div class="sidebar-widget">
                        <h3 class="widget-title"><i class="fas fa-fire"></i> Tendencia</h3>

                        @forelse($topVistas as $noticia)
                            <div class="trending-item">
                                <img src="{{ Storage::disk('s3')->url($noticia->portada_path) }}"
                                    alt="{{ $noticia->titulo }}">

                                <div class="trending-content">
                                    <h4>
                                        <a style="text-decoration: none; color: inherit;" href="{{ route('noticias.show', $noticia->slug) }}">
                                            {{ $noticia->titulo }}
                                        </a>
                                    </h4>

                                    <span><i class="fas fa-eye"></i> {{ $noticia->vistas }} lecturas</span>
                                </div>
                            </div>
                        @empty
                            <p>No hay noticias en tendencia aún.</p>
                        @endforelse
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


@endsection
