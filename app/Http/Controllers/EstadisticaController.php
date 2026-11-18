<?php

namespace App\Http\Controllers;

use App\Models\Comentario;
use App\Models\Noticia;
use App\Models\PushSuscripcion;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EstadisticaController extends Controller
{
    private const PERIODOS = [7 => 'Últimos 7 días', 30 => 'Últimos 30 días', 90 => 'Últimos 90 días'];

    public function index(Request $request)
    {
        $dias = array_key_exists((int) $request->get('dias'), self::PERIODOS) ? (int) $request->get('dias') : 30;
        $zona = config('app.zona_editorial');

        $hasta = Carbon::now($zona)->startOfDay();
        $desde = $hasta->copy()->subDays($dias - 1);
        $desdeAnterior = $desde->copy()->subDays($dias);
        $hastaAnterior = $desde->copy()->subDay();

        // Serie diaria con ceros en los días sin lecturas.
        $porDia = DB::table('noticia_vistas')
            ->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->selectRaw('fecha, SUM(vistas) as total')
            ->groupBy('fecha')
            ->pluck('total', 'fecha')
            ->mapWithKeys(fn ($total, $fecha) => [Carbon::parse($fecha)->toDateString() => (int) $total]);

        $serie = collect();
        for ($d = $desde->copy(); $d->lte($hasta); $d->addDay()) {
            $serie->push(['fecha' => $d->copy()->locale('es'), 'total' => $porDia[$d->toDateString()] ?? 0]);
        }

        $lecturas = $serie->sum('total');
        $lecturasAnterior = (int) DB::table('noticia_vistas')
            ->whereBetween('fecha', [$desdeAnterior->toDateString(), $hastaAnterior->toDateString()])
            ->sum('vistas');

        $rango = [$desde->copy()->utc(), $hasta->copy()->endOfDay()->utc()];

        $topNoticias = Noticia::query()
            ->join('noticia_vistas as nv', 'nv.noticia_id', '=', 'noticias.id')
            ->whereBetween('nv.fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->groupBy('noticias.id', 'noticias.titulo', 'noticias.slug', 'noticias.portada_path', 'noticias.miniatura_path')
            ->select('noticias.id', 'noticias.titulo', 'noticias.slug', 'noticias.portada_path', 'noticias.miniatura_path')
            ->selectRaw('SUM(nv.vistas) as lecturas')
            ->orderByDesc('lecturas')
            ->take(10)
            ->get();

        $topCategorias = DB::table('noticia_vistas as nv')
            ->join('noticias as n', 'n.id', '=', 'nv.noticia_id')
            ->leftJoin('categorias as c', 'c.id', '=', 'n.category_id')
            ->whereBetween('nv.fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->groupBy('c.id', 'c.nombre')
            ->selectRaw("COALESCE(c.nombre, 'Sin categoría') as nombre, SUM(nv.vistas) as lecturas")
            ->orderByDesc('lecturas')
            ->take(6)
            ->get();

        $topAutores = DB::table('noticia_vistas as nv')
            ->join('noticias as n', 'n.id', '=', 'nv.noticia_id')
            ->leftJoin('users as u', 'u.id', '=', 'n.user_id')
            ->whereBetween('nv.fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->groupBy('u.id', 'u.name')
            ->selectRaw("COALESCE(u.name, 'Sin autor') as nombre, SUM(nv.vistas) as lecturas, COUNT(DISTINCT n.id) as noticias")
            ->orderByDesc('lecturas')
            ->take(6)
            ->get();

        $resumen = [
            'lecturas' => $lecturas,
            'lecturas_anterior' => $lecturasAnterior,
            'publicadas' => Noticia::publicadas()->whereBetween('created_at', $rango)->count(),
            'comentarios' => Comentario::whereBetween('created_at', $rango)->count(),
            'suscriptores' => PushSuscripcion::count(),
        ];

        $primerDia = DB::table('noticia_vistas')->min('fecha');

        return view('estadisticas.index', [
            'dias' => $dias,
            'periodos' => self::PERIODOS,
            'serie' => $serie,
            'resumen' => $resumen,
            'topNoticias' => $topNoticias,
            'topCategorias' => $topCategorias,
            'topAutores' => $topAutores,
            'primerDia' => $primerDia ? Carbon::parse($primerDia)->locale('es') : null,
        ]);
    }
}
