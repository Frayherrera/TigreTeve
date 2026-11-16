{{-- Fecha de publicación: solo visible cuando el estado es "programada". Las horas se muestran en hora de Colombia. --}}
@php
    $zonaEditorial = config('app.zona_editorial');
    $estadoActual = old('estado', $n->estado ?? 'borrador');
    $fechaGuardada = isset($n) && $n?->publicado_en
        ? $n->publicado_en->copy()->setTimezone($zonaEditorial)->format('Y-m-d\TH:i')
        : '';
    // old() trae lo que escribió el editor (la conversión a UTC no afecta la entrada que se reenvía).
    $fechaLocal = old('publicado_en', $fechaGuardada);
    $esProgramada = $estadoActual === 'programada';
@endphp
<div data-programacion @unless ($esProgramada) hidden @endunless
    class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3">
    <label for="publicado_en" class="flex items-center gap-2 text-sm font-medium text-slate-800">
        <i class="fa-regular fa-clock text-sky-700"></i>
        Fecha y hora de publicación
    </label>
    <input type="datetime-local" id="publicado_en" name="publicado_en" value="{{ $fechaLocal }}"
        min="{{ now($zonaEditorial)->format('Y-m-d\TH:i') }}"
        class="mt-2 w-full sm:w-auto border rounded-xl px-3 py-2 bg-white"
        @disabled(! $esProgramada) @required($esProgramada)>
    <p class="mt-1.5 text-xs text-slate-500">
        Hora de Colombia. La noticia aparecerá sola en el sitio a partir de ese momento.
    </p>
    @error('publicado_en')
        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
<script>
    (function () {
        var bloque = document.currentScript.previousElementSibling;
        var select = bloque.closest('form').querySelector('select[name="estado"]');
        var input = bloque.querySelector('input');
        if (!select) return;

        function sincronizar(enfocar) {
            var programada = select.value === 'programada';
            bloque.hidden = !programada;
            input.disabled = !programada;
            input.required = programada;
            if (programada && enfocar && !input.value) input.focus();
        }

        select.addEventListener('change', function () { sincronizar(true); });
        sincronizar(false);
    })();
</script>
