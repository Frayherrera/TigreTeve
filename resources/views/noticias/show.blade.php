@extends('layouts.home')

@section('title', 'Inicio')

@section('content')
    <script src="https://cdn.tailwindcss.com"></script>


    <div class="container mx-auto py-8 max-w-4xl px-4 sm:px-6">
        <article class="bg-white rounded-2xl shadow-lg overflow-hidden fade-in gradient-border m-4">
            {{-- Portada --}}
            @if ($noticia->portada_path)
                <div class="relative overflow-hidden">
                    
                    <img src="{{ Storage::disk('s3')->url($noticia->portada_path) }}" alt="Portada"
                        class="w-full object-cover transition-transform duration-500 hover:scale-105 foto-fija">
                    <div class="absolute top-4 right-4">
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

            <div class="p-6 sm:p-8">
                {{-- Título y resumen --}}
                <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4 leading-tight">{{ $noticia->titulo }}</h1>
                @if ($noticia->resumen)
                    <p
                        class="text-lg text-gray-600 mb-6 leading-relaxed bg-blue-50 p-4 rounded-lg border-l-4 border-blue-500">
                        {{ $noticia->resumen }}</p>
                @endif

                {{-- Autor y categoría --}}
                <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 mb-6 pb-4 border-b border-gray-100">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span>Por <strong class="text-gray-700">{{ $noticia->user->name }}</strong></span>
                    </div>

                    @if ($noticia->category)
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
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
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                            <span>{{ $noticia->publicado_en->format('d M Y H:i') }}</span>
                        </div>
                    @endif

                    <div class="flex items-center ml-auto">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                            </path>
                        </svg>
                        <span>{{ $noticia->vistas }} vistas</span>
                    </div>
                </div>

                {{-- Cuerpo --}}
                <div class="prose max-w-none mb-8 text-gray-700 leading-relaxed">
                    {!! nl2br(e($noticia->cuerpo)) !!}
                </div>

                {{-- Fuente --}}
                @if ($noticia->fuente)
                    <div class="mt-8 pt-6 border-t border-gray-100">
                        <p class="text-sm text-gray-500">Fuente:
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
        <div class="mt-6 text-center">
            <a href="{{ route('noticias.home') }}"
                class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium transition-colors duration-200">
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

        .prose img {
            border-radius: 8px;
            margin: 1.5rem 0;
        }

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

        .tag {
            display: inline-block;
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .foto-fija {
            width: 100%;
            height: 500px;
            object-fit: cover;
            /* recorta pero mantiene proporción */
            border-radius: 8px;
            /* opcional: bordes redondeados */
        }
    </style>
@endsection
