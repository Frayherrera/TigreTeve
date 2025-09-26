    @props([
        'id' => '1',
        'titulo' => '(Sin título)',
        'estado' => 'Borrador',
        'fecha' => now()->format('d M Y'),
        'autor' => 'Autor',
        'comentarios' => 0,
        'vistas' => 0,
        'is_featured' => 1,
    ])

    <div style="width: 100%; height: 100px; margin: 10px"
        class="flex items-center p-4 bg-white shadow rounded-lg hover:bg-gray-50">
        <!-- Icono inicial -->
        <div class="flex items-center justify-center w-10 h-10 bg-gray-200 rounded-full text-gray-600 font-bold">
            {{ strtoupper(substr($titulo, 0, 1)) }}
        </div>

        <!-- Texto principal -->
        <div class="ml-4 flex-1">
            <div class="font-semibold text-gray-800">
                {{ implode(' ', array_slice(explode(' ', $titulo), 0, 2)) }}
            </div>
            <div class="text-sm text-gray-500">
                <span class="text-orange-500">{{ $estado }}</span> • {{ $fecha }}
            </div>
        </div>

        <!-- Autor -->
        <div class="text-sm text-gray-700 mr-4">
            Autor: {{ $autor }}
        </div>

        <!-- Stats -->
        <div class="flex items-center space-x-4 text-gray-500 text-sm">
            <span class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M17 8h2a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V10a2 2 0 012-2h2"></path>
                    <path d="M12 12v.01"></path>
                </svg>
                {{ $comentarios }}
            </span>
            <span class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M5 15l7-7 7 7"></path>
                </svg>
                {{ $vistas }}
            </span>
            @if ($is_featured)
                <svg class="inline-block w-5 h-5 text-yellow-500 ml-1" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.966a1 1 0 00.95.69h4.174c.969 0 1.371 1.24.588 1.81l-3.38 2.455a1 1 0 00-.364 1.118l1.287 3.966c.3.921-.755 1.688-1.538 1.118l-3.38-2.455a1 1 0 00-1.175 0l-3.38 2.455c-.783.57-1.838-.197-1.538-1.118l1.287-3.966a1 1 0 00-.364-1.118L2.049 9.393c-.783-.57-.38-1.81.588-1.81h4.174a1 1 0 00.95-.69l1.286-3.966z" />
                </svg>
            @endif
            <a href="{{ route('noticias.edit', $id) }}" class="btn btn-sm btn-primary">Editar</a>
            @role('Administrator')
                <form action="{{ route('noticias.destroy', $id) }}" method="POST"
                    onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta noticia?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">
                        Eliminar
                    </button>
                </form>
            @endrole


        </div>
    </div>
