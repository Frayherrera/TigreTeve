@extends('layouts.home')

@php
    $urlNoticia = route('noticias.show2', $noticia->slug);
    $descripcion = \Illuminate\Support\Str::limit(
        trim(preg_replace('/\s+/', ' ', $noticia->resumen ?: strip_tags($noticia->cuerpo))),
        200
    );
    $imagen = $noticia->portadaUrl() ?? asset('img/logo.png');
    $publicada = $noticia->fechaPublicacion();
@endphp

@section('title', $noticia->titulo . ' | TigreTeve')

@section('meta')
    <meta name="description" content="{{ $descripcion }}">
    <link rel="canonical" href="{{ $urlNoticia }}">

    {{-- Vista previa al compartir (WhatsApp, Facebook, X) --}}
    <meta property="og:site_name" content="TigreTeve">
    <meta property="og:type" content="article">
    <meta property="og:locale" content="es_CO">
    <meta property="og:title" content="{{ $noticia->titulo }}">
    <meta property="og:description" content="{{ $descripcion }}">
    <meta property="og:url" content="{{ $urlNoticia }}">
    <meta property="og:image" content="{{ $imagen }}">
    <meta property="og:image:alt" content="{{ $noticia->titulo }}">
    <meta property="article:published_time" content="{{ $publicada->toIso8601String() }}">
    <meta property="article:modified_time" content="{{ $noticia->updated_at->toIso8601String() }}">
    @if ($noticia->categoria)
        <meta property="article:section" content="{{ $noticia->categoria->nombre }}">
    @endif
    @foreach ($noticia->tags as $tag)
        <meta property="article:tag" content="{{ $tag->nombre }}">
    @endforeach
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $noticia->titulo }}">
    <meta name="twitter:description" content="{{ $descripcion }}">
    <meta name="twitter:image" content="{{ $imagen }}">

    {{-- Datos estructurados para Google Noticias --}}
    <script type="application/ld+json">
        {!! json_encode(array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $urlNoticia],
            'headline' => \Illuminate\Support\Str::limit($noticia->titulo, 110, ''),
            'description' => $descripcion,
            'image' => [$imagen],
            'datePublished' => $publicada->toIso8601String(),
            'dateModified' => $noticia->updated_at->toIso8601String(),
            'articleSection' => $noticia->categoria?->nombre,
            'inLanguage' => 'es-CO',
            'author' => [['@type' => 'Person', 'name' => $noticia->user->name ?? 'TigreTeve']],
            'publisher' => [
                '@type' => 'NewsMediaOrganization',
                'name' => 'TigreTeve',
                'url' => url('/'),
                'logo' => ['@type' => 'ImageObject', 'url' => asset('img/logo.png')],
            ],
        ]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
    </script>
@endsection

@section('content')
    <script src="https://cdn.tailwindcss.com"></script>

    <div class="container mx-auto py-4 sm:py-8 max-w-4xl px-4 sm:px-6">
        <article class="bg-white rounded-lg sm:rounded-2xl shadow-lg overflow-hidden fade-in gradient-border m-2 sm:m-4">
            {{-- Portada --}}
            @if ($noticia->portada_path)
                <div class="relative overflow-hidden">
                    <img src="{{ Storage::disk('s3')->url($noticia->portada_path) }}" 
                         alt="Portada"
                         class="foto-fija transition-transform duration-500 hover:scale-105">
                    <div class="absolute top-2 right-2 sm:top-4 sm:right-4">
                        @if ($noticia->estado == 'publicado')
                            <span class="tag bg-green-100 text-green-800">Publicado</span>
                        @elseif($noticia->estado == 'borrador')
                            <span class="tag bg-yellow-100 text-yellow-800">Borrador</span>
                        @else
                            <span class="tag bg-gray-100 text-gray-800">{{ $noticia->estado }}</span>
                        @endif
                    </div>
                </div>
            @endif

            <div class="p-4 sm:p-6 lg:p-8">
                {{-- Título y resumen --}}
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 mb-3 sm:mb-4 leading-tight">
                    {{ $noticia->titulo }}
                </h1>
                
                @if ($noticia->resumen)
                    <p class="text-base sm:text-lg text-gray-600 mb-4 sm:mb-6 leading-relaxed bg-blue-50 p-3 sm:p-4 rounded-lg border-l-4 border-blue-500">
                        {{ $noticia->resumen }}
                    </p>
                @endif

                {{-- Autor y categoría --}}
                <div class="flex flex-wrap items-center gap-2 sm:gap-4 text-xs sm:text-sm text-gray-500 mb-4 sm:mb-6 pb-3 sm:pb-4 border-b border-gray-100">
                    <div class="flex items-center">
                        <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span class="truncate max-w-[120px] sm:max-w-none">
                            Por <strong class="text-gray-700">{{ $noticia->user->name }}</strong>
                        </span>
                    </div>

                    @if ($noticia->categoria)
                        <div class="flex items-center">
                            <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                </path>
                            </svg>
                            <span class="text-indigo-600 font-medium">{{ $noticia->categoria->nombre }}</span>
                        </div>
                    @endif

                    @if ($noticia->publicado_en)
                        <div class="flex items-center">
                            <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                            <span class="hidden sm:inline">{{ $noticia->publicado_en->format('d M Y H:i') }}</span>
                            <span class="inline sm:hidden">{{ $noticia->publicado_en->format('d/m/Y') }}</span>
                        </div>
                    @endif

                    <div class="flex items-center ml-auto">
                        <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                            </path>
                        </svg>
                        <span>{{ $noticia->vistas }}</span>
                        <span class="hidden sm:inline ml-1">vistas</span>
                    </div>
                </div>

                {{-- Cuerpo --}}
                {{-- Cada bloque separado por una línea en blanco es un párrafo; los saltos simples se respetan con <br>. --}}
                <div lang="es" class="prose prose-sm sm:prose-base lg:prose-lg max-w-none mb-6 sm:mb-8 text-gray-700 leading-relaxed article-body">
                    @foreach (preg_split('/\n[ \t]*\n/', str_replace(["\r\n", "\r"], "\n", trim($noticia->cuerpo))) as $parrafo)
                        @if (trim($parrafo) !== '')
                            <p>{!! nl2br(e(trim($parrafo))) !!}</p>
                        @endif
                    @endforeach
                </div>

                {{-- Fuente --}}
                @if ($noticia->fuente)
                    <div class="mt-6 sm:mt-8 pt-4 sm:pt-6 border-t border-gray-100">
                        <p class="text-xs sm:text-sm text-gray-500 break-all sm:break-words">
                            Fuente:
                            <a href="{{ $noticia->fuente }}"
                                class="text-blue-600 hover:text-blue-800 underline transition-colors duration-200">
                                {{ $noticia->fuente }}
                            </a>
                        </p>
                    </div>
                @endif

                {{-- Compartir --}}
                @php
                    $textoCompartir = $noticia->titulo . ' ' . $urlNoticia;
                @endphp
                <div class="compartir mt-6 sm:mt-8 pt-4 sm:pt-6 border-t border-gray-100">
                    <p class="text-sm font-semibold text-gray-700 mb-3">Comparte esta noticia</p>
                    <div class="flex flex-wrap gap-2">
                        <a href="https://wa.me/?text={{ rawurlencode($textoCompartir) }}" target="_blank" rel="noopener"
                            class="compartir-btn compartir-wa">
                            <i class="fa-brands fa-whatsapp" aria-hidden="true"></i> WhatsApp
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ rawurlencode($urlNoticia) }}" target="_blank" rel="noopener"
                            class="compartir-btn compartir-fb">
                            <i class="fa-brands fa-facebook-f" aria-hidden="true"></i> Facebook
                        </a>
                        <a href="https://x.com/intent/post?text={{ rawurlencode($noticia->titulo) }}&url={{ rawurlencode($urlNoticia) }}" target="_blank" rel="noopener"
                            class="compartir-btn compartir-x">
                            <i class="fa-brands fa-x-twitter" aria-hidden="true"></i> <span class="sr-only">Compartir en</span> X
                        </a>
                        <button type="button" class="compartir-btn compartir-copiar" data-copiar="{{ $urlNoticia }}">
                            <i class="fa-regular fa-copy" aria-hidden="true"></i> <span data-copiar-texto>Copiar enlace</span>
                        </button>
                    </div>
                </div>
            </div>
        </article>

        {{-- Te puede interesar --}}
        @if ($relacionadas->isNotEmpty())
            <section class="relacionadas m-2 sm:m-4 mt-6 sm:mt-8" aria-labelledby="rel-titulo">
                <div class="flex items-center gap-3 mb-4">
                    <span class="rel-barra" aria-hidden="true"></span>
                    <h2 id="rel-titulo" class="text-xl sm:text-2xl font-bold text-gray-900">Te puede interesar</h2>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    @foreach ($relacionadas as $rel)
                        <a href="{{ route('noticias.show2', $rel->slug) }}" class="rel-item flex gap-3 bg-white rounded-xl shadow p-3">
                            @if ($rel->miniaturaUrl())
                                <img src="{{ $rel->miniaturaUrl() }}" alt="" loading="lazy"
                                    class="rel-img shrink-0 rounded-lg object-cover bg-gray-100">
                            @else
                                <span class="rel-img shrink-0 rounded-lg bg-gray-100 text-gray-400 flex items-center justify-center" aria-hidden="true">
                                    <i class="fa-regular fa-newspaper"></i>
                                </span>
                            @endif
                            <span class="min-w-0">
                                @if ($rel->categoria)
                                    <span class="block text-xs font-semibold text-orange-600 mb-0.5">{{ $rel->categoria->nombre }}</span>
                                @endif
                                <span class="rel-titulo block font-semibold leading-snug text-gray-900">{{ $rel->titulo }}</span>
                                <span class="block text-xs text-gray-500 mt-1">
                                    {{ $rel->fechaPublicacion()->copy()->timezone(config('app.zona_editorial'))->locale('es')->diffForHumans() }}
                                </span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Comentarios --}}
        @php
            $zonaCom = config('app.zona_editorial');
            $tonosAvatar = ['#2563eb', '#7c3aed', '#db2777', '#0d9488', '#ea580c', '#4f46e5'];
        @endphp
        <section id="comentarios" class="com-seccion bg-white rounded-lg sm:rounded-2xl shadow-lg m-2 sm:m-4 mt-6 sm:mt-8 p-4 sm:p-6 lg:p-8"
            aria-labelledby="com-titulo">
            <h2 id="com-titulo" class="text-xl sm:text-2xl font-bold text-gray-900 mb-1">
                Comentarios
                <span class="com-num text-gray-400 font-semibold">({{ $comentarios->count() }})</span>
            </h2>
            <p class="text-sm text-gray-500 mb-5">Opina con respeto. Los comentarios ofensivos se retiran.</p>

            @if (session('comentario_ok'))
                <div class="mb-5 flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ session('comentario_ok') }}
                </div>
            @endif

            {{-- Listado --}}
            @if ($comentarios->isNotEmpty())
                <ol class="com-lista">
                    @foreach ($comentarios as $c)
                        @php
                            $nombre = $c->autor_nombre ?: 'Anónimo';
                            $tono = $tonosAvatar[crc32($nombre) % count($tonosAvatar)];
                            $fecha = $c->created_at->copy()->timezone($zonaCom)->locale('es');
                        @endphp
                        <li class="com-item flex gap-3 py-4">
                            <span class="com-avatar shrink-0" style="background: {{ $tono }}" aria-hidden="true">
                                {{ mb_strtoupper(mb_substr($nombre, 0, 1)) }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-baseline gap-x-2">
                                    <strong class="text-gray-900 text-sm sm:text-base">{{ $nombre }}</strong>
                                    <time class="text-xs text-gray-400" datetime="{{ $fecha->toIso8601String() }}"
                                        title="{{ $fecha->isoFormat('D [de] MMMM YYYY, h:mm a') }}">
                                        {{ $fecha->diffForHumans() }}
                                    </time>
                                </div>
                                <p class="com-texto mt-1 text-gray-700 text-sm sm:text-base leading-relaxed">{!! nl2br(e($c->contenido)) !!}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            @else
                <p class="mb-6 text-gray-500 text-sm">Nadie ha comentado todavía. Deja la primera opinión.</p>
            @endif

            {{-- Formulario --}}
            <form method="POST" action="{{ route('comentarios.store', $noticia->slug) }}" class="com-form rounded-xl bg-slate-50 p-4 sm:p-5">
                @csrf
                {{-- Campo trampa para bots: oculto para personas --}}
                <div class="com-trampa" aria-hidden="true">
                    <label for="sitio_web">No llenes este campo</label>
                    <input type="text" id="sitio_web" name="sitio_web" tabindex="-1" autocomplete="off">
                </div>

                @if ($errors->any())
                    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                @auth
                    <p class="mb-3 text-sm text-gray-600">Comentas como <strong class="text-gray-800">{{ auth()->user()->name }}</strong></p>
                @else
                    <div class="grid gap-3 sm:grid-cols-2 mb-3">
                        <div>
                            <label for="autor_nombre" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                            <input type="text" id="autor_nombre" name="autor_nombre" value="{{ old('autor_nombre') }}"
                                maxlength="80" required autocomplete="name"
                                class="com-campo w-full">
                        </div>
                        <div>
                            <label for="autor_email" class="block text-sm font-medium text-gray-700 mb-1">
                                Correo <span class="font-normal text-gray-400">(opcional, no se publica)</span>
                            </label>
                            <input type="email" id="autor_email" name="autor_email" value="{{ old('autor_email') }}"
                                maxlength="120" autocomplete="email"
                                class="com-campo w-full">
                        </div>
                    </div>
                @endauth

                <label for="contenido" class="block text-sm font-medium text-gray-700 mb-1">Tu comentario</label>
                <textarea id="contenido" name="contenido" rows="4" maxlength="1000" required
                    class="com-campo w-full resize-y">{{ old('contenido') }}</textarea>

                <div class="mt-3 flex items-center justify-between gap-3">
                    <span class="text-xs text-gray-400">Máximo 1000 caracteres</span>
                    <button type="submit"
                        class="com-boton inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                        Publicar comentario
                    </button>
                </div>
            </form>
        </section>

        {{-- Botón de volver --}}
        <div class="mt-4 sm:mt-6 text-center">
            <a href="{{ route('noticias.home') }}"
                class="inline-flex items-center text-sm sm:text-base text-blue-600 hover:text-blue-800 font-medium transition-colors duration-200 px-4 py-2 sm:px-0 sm:py-0">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
                Volver a noticias
            </a>
        </div>
    </div>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }

        /* Imágenes dentro del contenido */
        .prose img,
        .article-body img {
            border-radius: 8px;
            margin: 1rem 0;
            width: 100%;
            height: auto;
        }

        /* Animación fade in */
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Borde superior gradiente */
        .gradient-border {
            position: relative;
        }

        .gradient-border::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6, #ec4899);
            border-radius: 8px 8px 0 0;
        }

        /* Tags de estado */
        .tag {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* ===== Compartir ===== */
        .compartir-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.55rem 1rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid transparent;
            cursor: pointer;
            transition: filter 0.15s ease;
        }

        .compartir-btn:hover {
            filter: brightness(0.93);
            text-decoration: none;
        }

        .compartir-btn:focus-visible {
            outline: 2px solid #1a1a2e;
            outline-offset: 2px;
        }

        .compartir-wa { background: #25d366; color: #fff; }
        .compartir-wa:hover { color: #fff; }
        .compartir-fb { background: #1877f2; color: #fff; }
        .compartir-fb:hover { color: #fff; }
        .compartir-x { background: #0f1419; color: #fff; }
        .compartir-x:hover { color: #fff; }
        .compartir-copiar { background: #fff; color: #374151; border-color: #d1d5db; }

        /* ===== Te puede interesar ===== */
        .rel-barra {
            display: inline-block;
            width: 40px;
            height: 3px;
            border-radius: 2px;
            background: linear-gradient(90deg, #f5ad1d, #e74c3c);
        }

        .rel-item {
            text-decoration: none;
            color: inherit;
        }

        .rel-item:hover {
            text-decoration: none;
            color: inherit;
        }

        .rel-item:hover .rel-titulo {
            color: #e74c3c;
        }

        .rel-item:focus-visible {
            outline: 2px solid #e74c3c;
            outline-offset: 2px;
        }

        .rel-img {
            width: 96px;
            height: 72px;
        }

        .rel-titulo {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* ===== Comentarios ===== */
        .com-lista {
            list-style: none;
            padding: 0;
            margin: 0 0 1.5rem;
        }

        .com-item:first-child {
            padding-top: 0;
        }

        .com-item + .com-item {
            border-top: 1px solid #f1f5f9;
        }

        .com-avatar {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 9999px;
            color: #fff;
            font-weight: 700;
        }

        .com-texto {
            word-wrap: break-word;
            overflow-wrap: anywhere;
        }

        .com-campo {
            border: 1px solid #cbd5e1;
            border-radius: 0.5rem;
            background: #fff;
            padding: 0.6rem 0.75rem;
            font-size: 0.95rem;
            color: #1f2937;
        }

        .com-campo:focus,
        .com-boton:focus-visible {
            outline: 2px solid #3b82f6;
            outline-offset: 1px;
        }

        .com-trampa {
            position: absolute;
            left: -9999px;
            width: 1px;
            height: 1px;
            overflow: hidden;
        }

        .com-num {
            font-variant-numeric: tabular-nums;
        }

        /* Imagen de portada fija */
        .foto-fija {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        /* Ajustes tipográficos responsive */
        .article-body {
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .article-body p {
            margin-bottom: 1rem;
            text-align: justify;
            /* Separa palabras en sílabas (lang="es") para que el justificado no deje huecos grandes en móvil. */
            -webkit-hyphens: auto;
            hyphens: auto;
        }

        /* ================================
           RESPONSIVE BREAKPOINTS
           ================================ */

        /* Tablets (768px+) */
        @media (min-width: 768px) {
            .foto-fija {
                
            }

            .tag {
                font-size: 0.875rem;
                padding: 0.375rem 1rem;
            }
        }

        /* Desktop (1024px+) */
        @media (min-width: 1024px) {
            .foto-fija {
                height: 100%;
            }

            .article-body img {
                margin: 1.5rem 0;
            }
        }

        /* Móviles pequeños (< 375px) */
        @media (max-width: 374px) {
            .foto-fija {
                height: 200px;
            }

            .tag {
                font-size: 0.65rem;
                padding: 0.2rem 0.5rem;
            }

            .gradient-border::before {
                height: 3px;
            }
        }

        /* Móviles muy pequeños (< 320px) */
        @media (max-width: 319px) {
            .foto-fija {
                height: 180px;
            }

            body {
                font-size: 14px;
            }
        }

        /* Tablets en landscape */
        @media (min-width: 768px) and (max-width: 1024px) and (orientation: landscape) {
            .foto-fija {
                height: 300px;
            }
        }

        /* Mejoras de legibilidad */
        @media (max-width: 640px) {
            .article-body {
                font-size: 0.9375rem;
                line-height: 1.7;
            }

            h1, h2, h3, h4, h5, h6 {
                word-wrap: break-word;
                overflow-wrap: break-word;
                hyphens: auto;
            }
        }

        /* Optimización para modo landscape en móviles */
        @media (max-height: 500px) and (orientation: landscape) {
            .foto-fija {
                height: 200px;
            }

            .container {
                padding-top: 1rem;
                padding-bottom: 1rem;
            }
        }

        /* Asegurar que el contenedor sea responsive */
        .container {
            width: 100%;
        }

        @media (min-width: 640px) {
            .container {
                max-width: 640px;
            }
        }

        @media (min-width: 768px) {
            .container {
                max-width: 768px;
            }
        }

        @media (min-width: 1024px) {
            .container {
                max-width: 896px;
            }
        }

        /* Fix para scroll horizontal en móviles */
        body {
            overflow-x: hidden;
        }

        /* Mejorar el espaciado en móviles */
        @media (max-width: 640px) {
            .article-body * {
                max-width: 100%;
            }

            .article-body table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
        }
    </style>

    <script>
        // Copiar enlace (con respaldo para navegadores sin Clipboard API)
        document.querySelectorAll('[data-copiar]').forEach(function (boton) {
            boton.addEventListener('click', function () {
                var url = boton.getAttribute('data-copiar');
                var etiqueta = boton.querySelector('[data-copiar-texto]');
                var listo = function () {
                    etiqueta.textContent = 'Enlace copiado';
                    setTimeout(function () { etiqueta.textContent = 'Copiar enlace'; }, 2000);
                };
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(url).then(listo);
                } else {
                    var campo = document.createElement('textarea');
                    campo.value = url;
                    document.body.appendChild(campo);
                    campo.select();
                    document.execCommand('copy');
                    campo.remove();
                    listo();
                }
            });
        });
    </script>
@endsection