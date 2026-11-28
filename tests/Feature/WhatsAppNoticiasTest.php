<?php

namespace Tests\Feature;

use App\Jobs\EnviarWhatsAppJob;
use App\Models\Configuracion;
use App\Models\Noticia;
use App\Models\Numero;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WhatsAppNoticiasTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('s3');
        $this->admin = tap(User::factory()->create())->assignRole(Role::create(['name' => 'Administrator']));
    }

    private function noticia(array $datos): Noticia
    {
        return Noticia::create($datos + [
            'user_id' => $this->admin->id,
            'cuerpo' => '<p>Cuerpo de la nota</p>',
            'estado' => 'publicada',
        ]);
    }

    public function test_el_selector_lista_solo_noticias_publicadas_con_los_campos_del_mensaje(): void
    {
        $this->noticia(['titulo' => 'Alcalde inaugura puente', 'slug' => 'alcalde-inaugura-puente', 'resumen' => "Resumen\ncorto"]);
        $this->noticia(['titulo' => 'Borrador oculto', 'slug' => 'borrador-oculto', 'estado' => 'borrador']);

        $r = $this->actingAs($this->admin)->getJson(route('whatsapp.noticias'))->assertOk();

        $r->assertJsonCount(1);
        $this->assertSame('Alcalde inaugura puente', $r->json('0.titulo'));
        $this->assertSame(route('noticias.show2', 'alcalde-inaugura-puente'), $r->json('0.link'));
        $this->assertSame('Resumen corto', $r->json('0.cuerpo'));
        $this->assertSame(asset('img/logo.png'), $r->json('0.image')); // sin portada usa el logo
    }

    public function test_el_selector_busca_por_titulo(): void
    {
        $this->noticia(['titulo' => 'Alcalde inaugura puente', 'slug' => 'puente']);
        $this->noticia(['titulo' => 'Resultados del fútbol', 'slug' => 'futbol']);

        $this->actingAs($this->admin)->getJson(route('whatsapp.noticias', ['q' => 'fútbol']))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.titulo', 'Resultados del fútbol');
    }

    public function test_el_selector_requiere_administrador(): void
    {
        $this->actingAs(User::factory()->create())->getJson(route('whatsapp.noticias'))->assertForbidden();
    }

    public function test_el_envio_masivo_exige_link_de_una_noticia_de_la_web(): void
    {
        Queue::fake();
        Numero::create(['numero' => '3001112233']);

        $this->actingAs($this->admin)->post(route('whatsapp.masivo'), [
            'list_from_db' => '1',
            'titulo' => 'T', 'cuerpo' => 'C', 'image' => 'https://x.test/a.jpg',
            'link' => 'https://www.facebook.com/TigreTeve/posts/123',
        ])->assertSessionHasErrors('link');

        Queue::assertNothingPushed();
    }

    public function test_el_boton_de_la_plantilla_lleva_el_slug_de_la_noticia(): void
    {
        Configuracion::create(['parametro' => 'idnumero', 'valor' => '111']);
        Configuracion::create(['parametro' => 'tokenw', 'valor' => 'token']);
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.1']]])]);

        (new EnviarWhatsAppJob('573001112233', [
            'titulo' => 'T', 'cuerpo' => 'C', 'image' => 'https://x.test/a.jpg',
            'link' => 'https://noticias.tigreteve.online/noticias/alcalde-inaugura-puente',
            'batch_id' => 'lote1',
        ]))->handle();

        Http::assertSent(function ($request) {
            $template = $request['template'];
            $boton = collect($template['components'])->firstWhere('type', 'button');

            return $template['name'] === 'mensajes'
                && $boton['parameters'][0]['text'] === 'alcalde-inaugura-puente';
        });
        $this->assertDatabaseHas('whatsapp_logs', ['batch_id' => 'lote1', 'status' => 'sent']);
    }

    private function enviarMasivo(string $imagen)
    {
        Numero::create(['numero' => '3001112233']);

        return $this->actingAs($this->admin)->post(route('whatsapp.masivo'), [
            'list_from_db' => '1',
            'titulo' => 'T', 'cuerpo' => 'C', 'image' => $imagen,
            'link' => 'https://noticias.tigreteve.online/noticias/alcalde-inaugura-puente',
        ]);
    }

    public function test_el_envio_masivo_rechaza_imagenes_webp(): void
    {
        Queue::fake();
        Http::fake(['img.test/*' => Http::response('', 200, ['Content-Type' => 'image/webp'])]);

        $this->enviarMasivo('https://img.test/portada.webp')->assertSessionHasErrors('image');

        Queue::assertNothingPushed();
    }

    public function test_el_envio_masivo_rechaza_imagenes_no_accesibles(): void
    {
        Queue::fake();
        Http::fake(['img.test/*' => Http::response('', 403)]);

        $this->enviarMasivo('https://img.test/privada.jpg')->assertSessionHasErrors('image');

        Queue::assertNothingPushed();
    }

    public function test_el_envio_masivo_acepta_imagenes_jpg(): void
    {
        Queue::fake();
        Http::fake(['img.test/*' => Http::response('', 200, ['Content-Type' => 'image/jpeg', 'Content-Length' => '120000'])]);

        $this->enviarMasivo('https://img.test/portada.jpg')->assertSessionHasNoErrors();

        Queue::assertPushed(EnviarWhatsAppJob::class, 1);
    }

    public function test_el_selector_usa_el_logo_si_la_portada_es_webp(): void
    {
        $this->noticia(['titulo' => 'Con webp', 'slug' => 'con-webp', 'portada_path' => 'imagenes/foto.webp']);

        $r = $this->actingAs($this->admin)->getJson(route('whatsapp.noticias'))->assertOk();

        $this->assertSame(asset('img/logo.png'), $r->json('0.image'));
        $this->assertStringContainsString('WEBP', $r->json('0.image_aviso'));
    }

    public function test_el_log_guarda_el_codigo_y_el_detalle_del_error_de_meta(): void
    {
        Configuracion::create(['parametro' => 'idnumero', 'valor' => '111']);
        Configuracion::create(['parametro' => 'tokenw', 'valor' => 'token']);
        Http::fake(['graph.facebook.com/*' => Http::response(['error' => [
            'message' => 'Media upload error',
            'code' => 131053,
            'error_data' => ['details' => 'Unsupported Image mime type image/webp'],
        ]], 400)]);

        (new EnviarWhatsAppJob('573001112233', [
            'titulo' => 'T', 'cuerpo' => 'C', 'image' => 'https://x.test/a.webp',
            'link' => 'https://noticias.tigreteve.online/noticias/x',
            'batch_id' => 'lote2',
        ]))->handle();

        $this->assertDatabaseHas('whatsapp_logs', [
            'batch_id' => 'lote2',
            'status' => 'failed',
            'error' => '(#131053) Media upload error: Unsupported Image mime type image/webp',
        ]);
    }
}
