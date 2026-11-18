<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Noticia;
use Illuminate\Support\Facades\Cache;

/**
 * Archivos para buscadores y lectores: sitemap general, sitemap de Google Noticias,
 * robots.txt y feed RSS. Se cachean unos minutos para no golpear la base de datos.
 */
class SeoController extends Controller
{
    public function sitemap()
    {
        $xml = Cache::remember('seo.sitemap', 3600, function () {
            return view('seo.sitemap', [
                'categorias' => Categoria::select('slug', 'updated_at')->get(),
                'noticias' => Noticia::publicadas()
                    ->select('slug', 'updated_at', 'portada_path', 'titulo')
                    ->latest()
                    ->take(5000)
                    ->get(),
            ])->render();
        });

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    /** Google Noticias solo acepta artículos de los últimos 2 días (máx. 1000). */
    public function sitemapNoticias()
    {
        $xml = Cache::remember('seo.sitemap_noticias', 600, function () {
            return view('seo.sitemap-noticias', [
                'noticias' => Noticia::publicadas()
                    ->where(function ($q) {
                        $desde = now()->subDays(2);
                        $q->where('publicado_en', '>=', $desde)
                            ->orWhere(fn ($s) => $s->whereNull('publicado_en')->where('created_at', '>=', $desde));
                    })
                    ->latest()
                    ->take(1000)
                    ->get(),
            ])->render();
        });

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots()
    {
        $lineas = [
            'User-agent: *',
            'Disallow: /panel/',
            'Disallow: /noticias/create',
            'Disallow: /login',
            'Disallow: /register',
            '',
            'Sitemap: '.route('seo.sitemap'),
            'Sitemap: '.route('seo.sitemap_noticias'),
        ];

        return response(implode("\n", $lineas)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function feed()
    {
        $xml = Cache::remember('seo.feed', 600, function () {
            return view('seo.feed', [
                'noticias' => Noticia::publicadas()->with(['categoria', 'user'])->latest()->take(30)->get(),
            ])->render();
        });

        return response($xml, 200, ['Content-Type' => 'application/rss+xml; charset=UTF-8']);
    }
}
