@extends('layouts.home')

@section('title', 'Inicio')

@section('content')
    <script src="https://cdn.tailwindcss.com"></script>

    <div class="container mx-auto py-4 sm:py-8 max-w-4xl px-4 sm:px-6">
        <article class="bg-white rounded-lg sm:rounded-2xl shadow-lg overflow-hidden fade-in gradient-border m-2 sm:m-4">
            {{-- Portada --}}
            @if ($noticia->portada_path)
                <div class="relative overflow-hidden">
                    <img src="{{ Storage::disk('s3')->url($noticia->portada_path) }}" 
                         alt="Portada"
                         class="w-full foto-fija transition-transform duration-500 hover:scale-105">
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

                    @if ($noticia->category)
                        <div class="flex items-center">
                            <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                </path>
                            </svg>
                            <span class="text-indigo-600 font-medium">{{ $noticia->category->nombre }}</span>
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
                <div class="prose prose-sm sm:prose-base lg:prose-lg max-w-none mb-6 sm:mb-8 text-gray-700 leading-relaxed article-body">
                    {!! nl2br(e($noticia->cuerpo)) !!}
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
            </div>
        </article>

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

        /* Imagen de portada fija */
        .foto-fija {
            width: 100%;
            height: 250px;
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
        }

        /* ================================
           RESPONSIVE BREAKPOINTS
           ================================ */

        /* Tablets (768px+) */
        @media (min-width: 768px) {
            .foto-fija {
                height: 350px;
            }

            .tag {
                font-size: 0.875rem;
                padding: 0.375rem 1rem;
            }
        }

        /* Desktop (1024px+) */
        @media (min-width: 1024px) {
            .foto-fija {
                height: 500px;
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
@endsection