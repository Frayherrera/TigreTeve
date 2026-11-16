<x-app-layout>
    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('noticias.update', $n->id) }}" enctype="multipart/form-data"
                class="max-w-3xl mx-auto bg-white p-4 sm:p-6 rounded-2xl shadow space-y-4">
                @csrf
                @method('PUT')
                @if (session('ok') || ($n->fuente && $n->estado === 'borrador'))
                    <div class="flex items-start gap-3 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-900">
                        <i class="fa-regular fa-newspaper mt-0.5"></i>
                        <div class="min-w-0">
                            @if (session('ok'))
                                <p class="font-medium">{{ session('ok') }}</p>
                            @endif
                            @if ($n->fuente)
                                <a href="{{ $n->fuente }}" target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1.5 text-sky-700 underline underline-offset-2 hover:text-sky-900">
                                    Leer la nota original <i class="fas fa-arrow-up-right-from-square text-xs"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
                <div>
                    <label class="block text-sm font-medium">Título</label>
                    <input name="titulo" value="{{ old('titulo', $n->titulo ?? '') }}"
                        class="w-full border rounded-xl px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-sm font-medium">Slug (opcional)</label>
                    <input name="slug"
                        value="{{ old('slug', $n->slug ?? '') }}" class="w-full border rounded-xl px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium">Resumen</label>
                    <textarea name="resumen" class="w-full border rounded-xl px-3 py-2">{{ old('resumen', $n->resumen ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium">Cuerpo</label>
                    <textarea name="cuerpo" class="w-full border rounded-xl px-3 py-2" rows="10" required>{{ old('cuerpo', $n->cuerpo ?? '') }}</textarea>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium">Categoría</label>
                        <select name="category_id" class="w-full border rounded-xl px-3 py-2">
                            <option value="">-- Ninguna --</option>
                            @foreach ($categorias as $c)
                                <option value="{{ $c->id }}" @selected(old('category_id', $n->category_id ?? '') == $c->id)>{{ $c->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Estado</label>
                        <select name="estado" class="w-full border rounded-xl px-3 py-2">
                            @foreach (['borrador', 'publicada', 'programada'] as $e)
                                <option value="{{ $e }}" @selected(old('estado', $n->estado ?? 'borrador') == $e)>{{ ucfirst($e) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @include('noticias._programacion')
                <div>
                    <label class="block text-sm font-medium">Tags</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($tags as $t)
                            <label class="inline-flex items-center gap-1 text-sm">
                                <input type="checkbox" name="tags[]" value="{{ $t->id }}"
                                    @checked(in_array($t->id, old('tags', isset($n) ? $n->tags->pluck('id')->all() : [])))>
                                <span>#{{ $t->nombre }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium">Portada</label>
                    <input type="file" name="portada" accept="image/*" class="block w-full max-w-full text-sm">
                    @if (isset($n) && $n->portada_path)
                        <img src="{{ Storage::url($n->portada_path) }}" class="mt-2 w-40 h-24 object-cover rounded-xl">
                    @endif
                </div>
                <div class="mb-4">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="is_featured" value="1"
                            {{ old('is_featured', $n->is_featured ?? false) ? 'checked' : '' }}>
                        <span class="ml-2 text-gray-700">¿Destacar esta noticia?</span>
                    </label>
                </div>
                <div class="flex justify-end gap-2">
                    <a style="text-decoration: none" href="{{ route('noticias.index') }}"
                        class="px-4 py-2 rounded-xl border">Cancelar</a>
                    <button style="color: white" class="px-4 py-2 rounded-xl bg-green-500">Guardar cambios
                        </ button>
                </div>
            </form>
        </div>
</x-app-layout>
