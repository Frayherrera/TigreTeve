<?php

namespace Tests\Feature;

use App\Jobs\EnviarPushNoticiaJob;
use App\Jobs\EnviarWhatsAppJob;
use App\Jobs\GenerarMiniaturaJob;
use App\Models\Configuracion;
use App\Models\Noticia;
use App\Models\Numero;
use App\Models\PushSuscripcion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AvisosYEstadisticasTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('s3');
        config([
            'app.timezone' => 'UTC',
            'cache.default' => 'array',
            'services.webpush.public_key' => 'clave-publica',
            'services.webpush.private_key' => 'clave-privada',
        ]);
        Carbon::setTestNow('2026-11-18 15:00:00');

        $rol = Role::create(['name' => 'Administrator']);
        foreach (['crear noticias', 'editar noticias', 'eliminar noticias'] as $p) {
            $rol->givePermissionTo(Permission::create(['name' => $p]));
        }
        Role::create(['name' => 'Editor']);
        $this->admin = tap(User::factory()->create())->assignRole($rol);
    }

    private function crear(array $datos)
    {
        return $this->actingAs($this->admin)->post(route('noticias.store'), $datos + [
            'titulo' => 'Nota con avisos', 'cuerpo' => 'Cuerpo', 'estado' => 'publicada',
        ]);
    }

    public function test_publicar_con_push_encola_el_aviso_y_marca_urgente(): void
    {
        Queue::fake();

        $this->crear(['avisar_push' => '1', 'urgente' => '1'])->assertRedirect(route('noticias.index'));

        $noticia = Noticia::first();
        $this->assertTrue($noticia->urgente);
        Queue::assertPushed(EnviarPushNoticiaJob::class, fn ($job) => $job->noticiaId === $noticia->id
            && $job->url === route('noticias.show2', $noticia->slug));
    }

    public function test_una_programada_encola_el_aviso_para_su_fecha(): void
    {
        Queue::fake();

        $this->crear(['avisar_push' => '1', 'estado' => 'programada', 'publicado_en' => '2026-11-20T08:00']);

        Queue::assertPushed(EnviarPushNoticiaJob::class, fn ($job) => $job->delay instanceof \DateTimeInterface
            && Carbon::instance($job->delay)->equalTo(Noticia::first()->publicado_en));
    }

    public function test_el_job_de_push_no_avisa_dos_veces_ni_borradores(): void
    {
        $noticia = Noticia::create(['user_id' => $this->admin->id, 'titulo' => 'B', 'slug' => 'b', 'cuerpo' => 'c', 'estado' => 'borrador']);
        (new EnviarPushNoticiaJob($noticia->id, 'https://x.test/b'))->handle();
        $this->assertNull($noticia->fresh()->push_enviado_en);

        $noticia->update(['estado' => 'publicada', 'push_enviado_en' => now()->subHour()]);
        $antes = $noticia->fresh()->push_enviado_en;
        (new EnviarPushNoticiaJob($noticia->id, 'https://x.test/b'))->handle();
        $this->assertTrue($antes->equalTo($noticia->fresh()->push_enviado_en));
    }

    public function test_whatsapp_desde_el_formulario_usa_la_plantilla_de_noticias(): void
    {
        Queue::fake();
        Configuracion::create(['parametro' => 'plantilla_noticia', 'valor' => 'noticia_sitio']);
        Numero::create(['numero' => '3001112233']);
        Numero::create(['numero' => '3004445566']);

        $this->crear(['enviar_whatsapp' => '1'])->assertSessionHas('ok', fn ($m) => str_contains($m, '2 mensajes de WhatsApp'));

        $noticia = Noticia::first();
        $this->assertNotNull($noticia->whatsapp_enviado_en);
        Queue::assertPushed(EnviarWhatsAppJob::class, 2);
        Queue::assertPushed(EnviarWhatsAppJob::class, fn ($job) => $job->payload['plantilla'] === 'noticia_sitio'
            && $job->payload['boton'] === $noticia->slug);
    }

    public function test_sin_plantilla_configurada_no_se_envia_whatsapp(): void
    {
        Queue::fake();
        Numero::create(['numero' => '3001112233']);

        $this->crear(['enviar_whatsapp' => '1']);

        Queue::assertNotPushed(EnviarWhatsAppJob::class);
    }

    public function test_suscripcion_push(): void
    {
        $datos = ['endpoint' => 'https://fcm.googleapis.com/fcm/send/abc', 'keys' => ['p256dh' => 'p', 'auth' => 'a']];

        $this->postJson(route('push.suscribir'), $datos)->assertOk();
        $this->postJson(route('push.suscribir'), $datos)->assertOk();
        $this->assertSame(1, PushSuscripcion::count());

        $this->postJson(route('push.suscribir'), ['endpoint' => 'http://inseguro.test'])->assertUnprocessable();

        $this->postJson(route('push.desuscribir'), ['endpoint' => $datos['endpoint']])->assertOk();
        $this->assertSame(0, PushSuscripcion::count());
    }

    public function test_la_miniatura_se_genera_al_subir_portada(): void
    {
        if (! function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('GD no disponible');
        }

        $this->crear(['portada' => UploadedFile::fake()->image('foto.jpg', 1600, 900)]);

        $noticia = Noticia::first();
        $this->assertNotNull($noticia->miniatura_path);
        Storage::disk('s3')->assertExists($noticia->miniatura_path);
        [$ancho] = getimagesizefromstring(Storage::disk('s3')->get($noticia->miniatura_path));
        $this->assertSame(GenerarMiniaturaJob::ANCHO, $ancho);
    }

    public function test_estadisticas_muestran_lecturas_del_periodo(): void
    {
        $n = Noticia::create(['user_id' => $this->admin->id, 'titulo' => 'La más leída', 'slug' => 'leida', 'cuerpo' => 'c', 'estado' => 'publicada']);
        DB::table('noticia_vistas')->insert([
            ['noticia_id' => $n->id, 'fecha' => '2026-11-17', 'vistas' => 40],
            ['noticia_id' => $n->id, 'fecha' => '2026-11-18', 'vistas' => 2],
            ['noticia_id' => $n->id, 'fecha' => '2026-08-01', 'vistas' => 999], // fuera del periodo
        ]);

        $this->actingAs($this->admin)->get(route('estadisticas.index', ['dias' => 7]))
            ->assertOk()
            ->assertSee('La más leída')
            ->assertSee('42')
            ->assertDontSee('1,041');
    }
}
