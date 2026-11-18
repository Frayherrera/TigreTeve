<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Noticia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SeoYCompartirTest extends TestCase
{
    use RefreshDatabase;

    private Noticia $noticia;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('s3');
        Http::fake();
        config(['cache.default' => 'array', 'app.timezone' => 'UTC']);
        Carbon::setTestNow('2026-11-18 15:00:00');

        $user = User::factory()->create(['name' => 'Ana']);
        $cat = Categoria::create(['nombre' => 'Deportes', 'slug' => 'deportes']);
        $base = ['user_id' => $user->id, 'cuerpo' => 'Cuerpo de la nota', 'category_id' => $cat->id];

        $this->noticia = Noticia::create($base + [
            'titulo' => 'Gana el equipo local', 'slug' => 'gana-equipo', 'resumen' => 'Resumen corto',
            'estado' => 'publicada', 'portada_path' => 'imagenes/foto.jpg',
        ]);
        Noticia::create($base + ['titulo' => 'Otra de deportes', 'slug' => 'otra-deportes', 'estado' => 'publicada']);
        Noticia::create($base + ['titulo' => 'Borrador secreto', 'slug' => 'borrador-secreto', 'estado' => 'borrador']);
        Noticia::create($base + ['titulo' => 'Sin fecha', 'slug' => 'prog-sin-fecha', 'estado' => 'programada']);
    }

    public function test_la_noticia_tiene_titulo_propio_y_metadatos_para_compartir(): void
    {
        $this->get(route('noticias.show2', 'gana-equipo'))
            ->assertOk()
            ->assertSee('<title>Gana el equipo local | TigreTeve</title>', false)
            ->assertSee('<meta property="og:title" content="Gana el equipo local">', false)
            ->assertSee('<meta property="og:description" content="Resumen corto">', false)
            ->assertSee('property="og:image" content="'.Storage::disk('s3')->url('imagenes/foto.jpg').'"', false)
            ->assertSee('"@type":"NewsArticle"', false)
            ->assertSee('https://wa.me/?text=', false)
            ->assertSee('Te puede interesar')
            ->assertSee('Otra de deportes')
            ->assertDontSee('Borrador secreto');
    }

    public function test_borradores_y_programadas_sin_fecha_no_son_publicos(): void
    {
        $this->get(route('noticias.show2', 'borrador-secreto'))->assertNotFound();
        $this->get(route('noticias.show2', 'prog-sin-fecha'))->assertNotFound();
    }

    public function test_sitemaps_robots_y_feed(): void
    {
        $this->get('/sitemap.xml')->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('noticias.show2', 'gana-equipo'), false)
            ->assertSee(route('noticias.home', 'deportes'), false)
            ->assertDontSee('borrador-secreto');

        $this->get('/sitemap-noticias.xml')->assertOk()
            ->assertSee('<news:name>TigreTeve</news:name>', false)
            ->assertSee('Gana el equipo local');

        $this->get('/robots.txt')->assertOk()->assertSee('Sitemap: '.route('seo.sitemap'));

        $this->get('/feed')->assertOk()->assertSee('<title>Gana el equipo local</title>', false);
    }

    public function test_la_franja_de_ultima_hora_muestra_urgentes_recientes(): void
    {
        $this->noticia->update(['urgente' => true]);

        $this->get('/')->assertOk()->assertSee('Última hora')->assertSee('uh-franja', false);

        Carbon::setTestNow('2026-11-20 15:00:00'); // más de 24 h después
        $this->get('/')->assertOk()->assertDontSee('uh-franja', false);
    }
}
