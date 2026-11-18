{{-- Última hora y avisos al publicar. Se incluye en crear y editar. --}}
@php
    $noticiaForm = $n ?? null;
    $plantillaWa = \App\Services\AvisosNoticia::plantillaWhatsApp();
    $hayClavesPush = (bool) config('services.webpush.public_key');
    $suscriptores = $hayClavesPush ? \App\Models\PushSuscripcion::count() : 0;
    $numeros = \App\Models\Numero::count();
    $zonaAv = config('app.zona_editorial');
@endphp

<fieldset class="rounded-xl border border-slate-200 p-4 space-y-3">
    <legend class="px-1 text-sm font-semibold text-slate-800">Difusión</legend>

    <label class="flex items-start gap-3">
        <input type="checkbox" name="urgente" value="1" class="mt-1"
            @checked(old('urgente', $noticiaForm->urgente ?? false))>
        <span>
            <span class="block text-sm font-medium text-slate-800">Última hora</span>
            <span class="block text-xs text-slate-500">Aparece en la franja roja de la portada durante 24 horas desde su publicación.</span>
        </span>
    </label>

    @hasanyrole('Administrator|Editor')
        <label class="flex items-start gap-3">
            @if ($noticiaForm?->push_enviado_en)
                <input type="checkbox" disabled class="mt-1">
                <span>
                    <span class="block text-sm font-medium text-slate-500">Notificación push enviada</span>
                    <span class="block text-xs text-slate-500">Se avisó el {{ $noticiaForm->push_enviado_en->copy()->timezone($zonaAv)->locale('es')->isoFormat('D MMM, h:mm a') }}.</span>
                </span>
            @elseif (! $hayClavesPush)
                <input type="checkbox" disabled class="mt-1">
                <span>
                    <span class="block text-sm font-medium text-slate-500">Notificación push</span>
                    <span class="block text-xs text-slate-500">No disponible: faltan las claves VAPID en el servidor.</span>
                </span>
            @else
                <input type="checkbox" name="avisar_push" value="1" class="mt-1" @checked(old('avisar_push'))>
                <span>
                    <span class="block text-sm font-medium text-slate-800">Enviar notificación push</span>
                    <span class="block text-xs text-slate-500">
                        Llega a {{ trans_choice(':count persona suscrita|:count personas suscritas', $suscriptores, ['count' => number_format($suscriptores)]) }}
                        cuando la noticia quede publicada. Úsala para lo importante.
                    </span>
                </span>
            @endif
        </label>
    @endhasanyrole

    @role('Administrator')
        <label class="flex items-start gap-3">
            @if ($noticiaForm?->whatsapp_enviado_en)
                <input type="checkbox" disabled class="mt-1">
                <span>
                    <span class="block text-sm font-medium text-slate-500">Enviada por WhatsApp</span>
                    <span class="block text-xs text-slate-500">Se encoló el {{ $noticiaForm->whatsapp_enviado_en->copy()->timezone($zonaAv)->locale('es')->isoFormat('D MMM, h:mm a') }}. Mira el resultado en Envíos de WhatsApp.</span>
                </span>
            @elseif (! $plantillaWa)
                <input type="checkbox" disabled class="mt-1">
                <span>
                    <span class="block text-sm font-medium text-slate-500">Enviar por WhatsApp</span>
                    <span class="block text-xs text-slate-500">No disponible: falta configurar la plantilla de noticias (parámetro <code>plantilla_noticia</code>).</span>
                </span>
            @else
                <input type="checkbox" name="enviar_whatsapp" value="1" class="mt-1" @checked(old('enviar_whatsapp'))>
                <span>
                    <span class="block text-sm font-medium text-slate-800">Enviar por WhatsApp</span>
                    <span class="block text-xs text-slate-500">
                        A los {{ number_format($numeros) }} números guardados, cuando la noticia quede publicada. Se omiten los que pidieron no recibir mensajes.
                    </span>
                </span>
            @endif
        </label>
    @endrole
</fieldset>
