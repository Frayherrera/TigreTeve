@extends('layouts.home')

@section('title', 'TigreTeve - Somos Medio Local')

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
                            <select x-model="city" @change="fetchWeather"
                                    style="font-size:0.75rem; padding:2px 4px; border:1px solid #ddd; border-radius:4px; margin-left:6px; cursor:pointer;">
                                <template x-for="c in cities" :key="c">
                                    <option :value="c" x-text="c"></option>
                                </template>
                            </select>
                        </h3>
                        <div style="text-align: center;">
                            <div style="font-size: 3rem; color: #ffa502; margin: 15px 0;">
                                <i class="fas" :class="icon"></i>
                            </div>
                            <h2 style="color: #2c3e50; margin-bottom: 5px;" x-text="temp + '°C'"></h2>
                            <p style="color: #666; margin-bottom: 10px;" x-text="desc"></p>
                            <div style="display: flex; justify-content: space-between; font-size: 0.9rem; color: #888;">
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
