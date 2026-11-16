<?php

namespace Tests\Feature;

use App\Models\Noticia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NoticiasExternasTest extends TestCase
{
    use RefreshDatabase;

    private const ENLACE = 'https://news.google.com/rss/articles/CBMiABC123?oc=5';

    private function reportero(): User
    {
        Permission::create(['name' => 'crear noticias']);
        $rol = Role::create(['name' => 'Reportero']);
        $rol->givePermissionTo('crear noticias');

        return tap(User::factory()->create())->assignRole($rol);
    }

    private function fakeFeed(): void
    {
        $rss = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0"><channel><title>Google Noticias</title>
<item>
  <title>Gobierno anuncia nueva vía en el Cesar - El Tiempo</title>
  <link>https://news.google.com/rss/articles/CBMiABC123?oc=5</link>
  <pubDate>Mon, 16 Nov 2026 13:00:00 GMT</pubDate>
  <source url="https://www.eltiempo.com">El Tiempo</source>
</item>
</channel></rss>
XML;
        Http::fake(['news.google.com/*' => Http::response($rss, 200)]);
    }

    public function test_lista_titulares_limpios_de_google_noticias(): void
    {
        $this->fakeFeed();

        $this->actingAs($this->reportero())
            ->get(route('externas.index'))
            ->assertOk()
            ->assertSee('Gobierno anuncia nueva vía en el Cesar')
            ->assertDontSee('en el Cesar - El Tiempo')
            ->assertSee('El Tiempo');
    }

    public function test_muestra_error_si_google_falla(): void
    {
        Http::fake(['news.google.com/*' => Http::response('', 503)]);

        $this->actingAs($this->reportero())
            ->get(route('externas.index'))
            ->assertOk()
            ->assertSee('No se pudo conectar con Google Noticias');
    }

    private const ARTICULO = 'https://www.eltiempo.com/colombia/nueva-via-cesar-123';

    /** Simula Google (página del artículo + batchexecute), la página del medio y su imagen. */
    private function fakeImportacion(string $htmlArticulo, int $statusImagen = 200): void
    {
        Http::fake([
            'news.google.com/rss/articles/*' => Http::response(
                '<html><body><c-wiz><div jscontroller="x" data-n-a-sg="FIRMA123" data-n-a-ts="1763298000"></div></c-wiz></body></html>'
            ),
            'news.google.com/_/DotsSplashUi/*' => Http::response(
                ")]}'\n\n" . '[["wrb.fr","Fbv4je","[\"garturlres\",\"' . self::ARTICULO . '\",1]",null,null,null,"generic"]]'
            ),
            'www.eltiempo.com/img/*' => Http::response('bytes-de-imagen', $statusImagen, ['Content-Type' => 'image/jpeg']),
            'www.eltiempo.com/*' => Http::response($htmlArticulo),
        ]);
    }

    private function parrafo(int $n): string
    {
        return "Párrafo {$n} de la nota con suficiente texto para contar como contenido real del artículo publicado.";
    }

    public function test_importar_trae_cuerpo_foto_y_no_duplica(): void
    {
        Storage::fake('s3');
        $cuerpo = implode("\n\n", array_map(fn ($n) => $this->parrafo($n), range(1, 5)));
        $this->fakeImportacion('<html><head>
            <meta property="og:image" content="https://www.eltiempo.com/img/portada.jpg">
            <meta property="og:description" content="El Gobierno anunció la construcción de una vía.">
            <script type="application/ld+json">' . json_encode(['@context' => 'https://schema.org', '@graph' => [['@type' => 'NewsArticle', 'articleBody' => $cuerpo]]]) . '</script>
            </head><body><nav><p>Menú con un enlace largo que no debería entrar en el cuerpo de la nota.</p></nav></body></html>');

        $usuario = $this->reportero();
        $datos = ['titulo' => 'Gobierno anuncia nueva vía en el Cesar', 'enlace' => self::ENLACE];

        $this->actingAs($usuario)->post(route('externas.importar'), $datos)
            ->assertRedirect()
            ->assertSessionHas('ok', fn ($m) => str_contains($m, 'se trajo el texto') && str_contains($m, 'subió a S3'));
        $this->actingAs($usuario)->post(route('externas.importar'), $datos)->assertRedirect();

        $this->assertSame(1, Noticia::count());
        $noticia = Noticia::first();
        $this->assertSame('borrador', $noticia->estado);
        $this->assertSame($cuerpo, $noticia->cuerpo);
        $this->assertSame('El Gobierno anunció la construcción de una vía.', $noticia->resumen);
        $this->assertSame(self::ARTICULO, $noticia->fuente);
        $this->assertSame(self::ENLACE, $noticia->enlace_google);
        $this->assertSame($usuario->id, $noticia->user_id);
        $this->assertStringStartsWith('imagenes/', $noticia->portada_path);
        $this->assertStringEndsWith('.jpg', $noticia->portada_path);
        Storage::disk('s3')->assertExists($noticia->portada_path);

        // En el listado aparece como ya importada.
        $this->fakeFeed();
        $this->actingAs($usuario)->get(route('externas.index'))->assertSee('Importada');
    }

    public function test_sin_json_ld_usa_parrafos_y_tolera_imagen_rota(): void
    {
        Storage::fake('s3');
        $parrafos = implode('', array_map(fn ($n) => '<p>' . $this->parrafo($n) . '</p>', range(1, 4)));
        $this->fakeImportacion('<html><head>
            <meta property="og:image" content="/img/rota.jpg">
            </head><body>
            <header><p>Encabezado del sitio que tampoco debería aparecer en el cuerpo.</p></header>
            <article><div class="contenido">' . $parrafos . '</div></article>
            <footer><p>Todos los derechos reservados, texto de pie de página bastante largo.</p></footer>
            </body></html>', 404);

        $this->actingAs($this->reportero())
            ->post(route('externas.importar'), ['titulo' => 'Nota sin JSON-LD', 'enlace' => self::ENLACE])
            ->assertSessionHas('ok', fn ($m) => str_contains($m, 'se trajo el texto') && str_contains($m, 'no se pudo subir'));

        $noticia = Noticia::first();
        $this->assertSame(implode("\n\n", array_map(fn ($n) => $this->parrafo($n), range(1, 4))), $noticia->cuerpo);
        $this->assertNull($noticia->portada_path);
        $this->assertSame(self::ARTICULO, $noticia->fuente);
    }

    public function test_si_no_se_resuelve_el_enlace_crea_borrador_vacio(): void
    {
        Http::fake(['news.google.com/*' => Http::response('', 500)]);

        $this->actingAs($this->reportero())
            ->post(route('externas.importar'), ['titulo' => 'Nota', 'enlace' => self::ENLACE])
            ->assertSessionHas('ok', fn ($m) => str_contains($m, 'no se pudo extraer el texto'));

        $noticia = Noticia::first();
        $this->assertSame('', $noticia->cuerpo);
        $this->assertSame(self::ENLACE, $noticia->fuente);
    }

    public function test_usuario_sin_rol_no_accede(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('externas.index'))
            ->assertForbidden();
    }
}
