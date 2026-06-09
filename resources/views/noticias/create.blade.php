<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ Route('noticias.store') }}" enctype="multipart/form-data" 15
                class="max-w-3xl mx-auto bg-white p-6 rounded-2xl shadow space-y-4">
                @csrf
                @if (($method ?? 'POST') === 'POST')
                @else
                    @method('PUT')
                @endif
                <div>
                    <label class="block text-sm font-medium">Título</label>
                    <input name="titulo" value="{{ old('titulo', $n->titulo ?? '') }}"
                        class="w-full border rounded-xl px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-sm font-medium">Slug (opcional)</label>
                    <input name="slug" value="{{ old('slug', $n->slug ?? '') }}"
                        class="w
full border rounded-xl px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium">Resumen</label>
                    <textarea name="resumen" class="w-full border rounded-xl px-3 
py-2">{{ old('resumen', $n->resumen ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium">Cuerpo</label>
                    <textarea name="cuerpo" class="w-full border rounded-xl px-3 py-2" rows="10" required>{{ old('cuerpo', $n->cuerpo ?? '') }}</textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium">Categoría</label>
                        <select name="category_id" class="w-full border rounded-xl px-3 py-2">
                            <option value="">-- Ninguna --</option>
                            @foreach ($categorias as $c)
                                <option value="{{ $c->id }}" @selected(old('category_id', $n -> category_id ?? '') == $c->id)>{{ $c->nombre }}
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
                <div>
                    <label class="block text-sm font-medium">Fecha/Hora de publicación (si programada)</label>
                    <input type="datetime-local" name="publicado_en"
                        value="{{ old(
                            'publicado_en',
                            optional($n->publicado_en ?? null)->format('Y-m
                                                                        d\TH:i'),
                        ) }}"
                        class="border rounded-xl px-3 py-2">
                </div>
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
                    <input type="file" name="portada" accept="image/*">
                    @if (isset($n) && $n->portada_path)
                        <img src="{{ Storage::url($n->portada_path) }}"class="mt-2 w-40 h-24 object-cover rounded-xl">
                    @endif
                </div>
                <div class="mb-4">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="is_featured" value="1"
                            {{ old('is_featured', $noticia->is_featured ?? false) ? 'checked' : '' }}
                            class="form-checkbox text-indigo-600 transition duration-150 ease-in-out">
                        <span class="ml-2 text-gray-700">¿Destacar esta noticia?</span>
                    </label>
                </div>
                <div class="flex justify-end gap-2">
                    <a style="text-decoration: none" href="{{ route('noticias.index') }}" class="px-4 py-2 rounded-xl border">Cancelar</a>
                    <button style="color: white" class="px-4 py-2 rounded-xl bg-green-500">Guardar</ button>
                </div>
            </form>
        </div>
</x-app-layout>
