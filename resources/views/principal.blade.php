@extends('layouts.home')

@section('title', 'TigreTeve - Somos Medio Local')

@section('content')

    @php $hero = $noticiasDestacadas->first() ?? $noticias->first(); @endphp

    @if ($hero)
    <section class="hero">
        <div class="hero-bg" style="background-image: url('{{ Storage::disk('s3')->url($hero->portada_path) }}');"></div>
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <span class="category-badge">{{ $hero->categoria->nombre ?? 'Destacado' }}</span>
            <h1>{{ $hero->titulo }}</h1>
            <p>{{ Str::limit($hero->resumen ?? strip_tags($hero->cuerpo), 180) }}</p>
            <div class="hero-meta">
                <span><i class="fas fa-calendar"></i> {{ $hero->created_at->format('d M Y') }}</span>
                <span><i class="fas fa-user"></i> {{ $hero->user->name ?? 'TigreTeve' }}</span>
                <span><i class="fas fa-eye"></i> {{ number_format($hero->vistas) }} lecturas</span>
            </div>
            <a href="{{ route('noticias.show', $hero->slug) }}" class="read-more">Leer artículo completo →</a>
        </div>
    </section>
    @endif

    <main class="main-content">
        <div class="container">
            <div class="content-grid">
                <!-- Main Content -->
                <div class="main-articles">

                    <!-- Featured Articles -->
                    <div class="section-header">
                        <span class="tiger-bar"></span>
                        <h2>Destacados</h2>
                    </div>

                    @forelse ($noticiasDestacadas as $noticia)
                        <x-featured-article :noticia="$noticia" />
                    @empty
                        <p style="color: #64748b; padding: 20px 0;">No hay noticias destacadas en esta categoría.</p>
                    @endforelse

                    <!-- Blog Grid -->
                    <div class="section-header">
                        <span class="tiger-bar"></span>
                        <h2>Últimas noticias</h2>
                    </div>

                    <div class="blog-grid" id="noticias-container">
                        @foreach ($noticias as $noticia)
                            <x-blog-card :noticia="$noticia" />
                        @endforeach
                    </div>
                </div>

                <!-- Sidebar -->
                <aside class="sidebar">

                    <!-- Trending -->
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
                                    <span><i class="fas fa-eye"></i> {{ number_format($noticia->vistas) }} lecturas</span>
                                </div>
                            </div>
                        @empty
                            <p style="color: #94a3b8; font-size: 0.9rem;">No hay noticias en tendencia aún.</p>
                        @endforelse
                    </div>

                    <!-- Weather Widget -->
                    <div class="sidebar-widget"
                         x-data="{
                             cities: ['Aguachica', 'Ocaña', 'Río de Oro', 'Valledupar', 'Bucaramanga', 'Bogotá', 'Medellín', 'Barranquilla'],
                             city: 'Aguachica',
                             temp: '{{ $weather["temp"] ?? "--" }}',
                             desc: '{{ $weather["desc"] ?? "--" }}',
                             max: '{{ $weather["max"] ?? "--" }}',
                             min: '{{ $weather["min"] ?? "--" }}',
                             icon: '{{ $weather["icon"] ?? "fa-sun" }}',
                             fetchWeather() {
                                 fetch(`https://wttr.in/${this.city}?format=j1`)
                                     .then(r => r.json())
                                     .then(data => {
                                         const c = data.current_condition?.[0] || {};
                                         const f = data.weather?.[0] || {};
                                         this.temp = c.temp_C ?? '--';
                                         this.desc = c.weatherDesc?.[0]?.value ?? '--';
                                         this.max = f.maxtempC ?? '--';
                                         this.min = f.mintempC ?? '--';
                                         this.icon = this.weatherIcon(c.weatherCode ?? '');
                                     })
                                     .catch(() => {});
                             },
                             weatherIcon(code) {
                                 const n = parseInt(code);
                                 if (n >= 113 && n <= 116) return 'fa-sun';
                                 if (n >= 119 && n <= 122) return 'fa-cloud';
                                 if (n >= 176 && n <= 200) return 'fa-cloud-rain';
                                 if (n >= 227 && n <= 236) return 'fa-snowflake';
                                 if (n >= 248 && n <= 260) return 'fa-smog';
                                 if (n >= 263 && n <= 389) return 'fa-cloud-showers-heavy';
                                 if (n >= 392 && n <= 395) return 'fa-bolt';
                                 return 'fa-sun';
                             },
                         }">
                        <h3 class="widget-title">
                            <i class="fas fa-cloud-sun"></i> Clima
                            <select x-model="city" @change="fetchWeather" class="weather-select">
                                <template x-for="c in cities" :key="c">
                                    <option :value="c" x-text="c"></option>
                                </template>
                            </select>
                        </h3>
                        <div style="text-align: center;">
                            <div class="weather-icon">
                                <i class="fas" :class="icon"></i>
                            </div>
                            <div class="weather-temp" x-text="temp + '°C'"></div>
                            <div class="weather-desc" x-text="desc"></div>
                            <div class="weather-extras">
                                <span x-text="'Máx: ' + max + '°C'"></span>
                                <span x-text="'Mín: ' + min + '°C'"></span>
                            </div>
                        </div>
                    </div>

                </aside>
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

@endsection