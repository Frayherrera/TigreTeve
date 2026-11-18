<?php

namespace App\Services;

use App\Models\Noticia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Cuenta lecturas reales: ignora bots, precargas del navegador y recargas del mismo
 * visitante (misma IP y navegador) durante 30 minutos. No depende de cookies, así que
 * también funciona en navegadores internos como el de WhatsApp o Facebook.
 * Suma al total de la noticia (noticias.vistas) y al conteo del día (noticia_vistas),
 * con el día en hora de Colombia.
 */
class RegistroVistas
{
    private const VENTANA_SEGUNDOS = 1800;

    private const BOTS = '/bot|crawl|spider|slurp|facebookexternalhit|facebot|whatsapp|telegram|'
        .'twitterbot|linkedinbot|embedly|preview|headless|lighthouse|pingdom|uptime|curl|wget|python|'
        .'go-http|java\/|okhttp|axios|node-fetch|scrapy|ahrefs|semrush|mj12|dotbot|petalbot|bingpreview/i';

    public function esBot(?string $userAgent): bool
    {
        return $userAgent === null || trim($userAgent) === '' || preg_match(self::BOTS, $userAgent) === 1;
    }

    /** Precargas especulativas (Chrome, Firefox, Safari): la persona aún no abrió la noticia. */
    public function esPrecarga(Request $request): bool
    {
        $proposito = strtolower($request->header('Sec-Purpose', '').$request->header('Purpose', '').$request->header('X-Moz', ''));

        return str_contains($proposito, 'prefetch') || str_contains($proposito, 'prerender');
    }

    public function registrar(Noticia $noticia, Request $request): bool
    {
        if ($this->esBot($request->userAgent()) || $this->esPrecarga($request)) {
            return false;
        }

        $visitante = sha1($request->ip().'|'.$request->userAgent());
        if (! Cache::add("vista:{$noticia->id}:{$visitante}", 1, self::VENTANA_SEGUNDOS)) {
            return false; // ya contada en esta ventana
        }

        $noticia->increment('vistas');

        $fecha = now(config('app.zona_editorial'))->toDateString();
        DB::table('noticia_vistas')->upsert(
            [['noticia_id' => $noticia->id, 'fecha' => $fecha, 'vistas' => 1]],
            ['noticia_id', 'fecha'],
            ['vistas' => DB::raw('vistas + 1')]
        );

        return true;
    }
}
