<?php

namespace Tests\Feature;

use App\Jobs\EnviarWhatsAppJob;
use App\Models\Configuracion;
use App\Models\User;
use App\Models\WhatsappBaja;
use App\Models\WhatsappLog;
use App\Services\EnvioWhatsApp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WhatsAppWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Configuracion::create(['parametro' => 'webhook_token', 'valor' => 'secreto-verificacion']);
        Configuracion::create(['parametro' => 'app_secret', 'valor' => 'clave-app']);
    }

    private function avisar(array $valor)
    {
        $cuerpo = json_encode(['entry' => [['changes' => [['value' => $valor]]]]]);
        $firma = 'sha256='.hash_hmac('sha256', $cuerpo, 'clave-app');

        return $this->call('POST', '/webhooks/whatsapp', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_HUB_SIGNATURE_256' => $firma,
        ], $cuerpo);
    }

    public function test_verificacion_de_meta(): void
    {
        $this->get('/webhooks/whatsapp?hub.mode=subscribe&hub.verify_token=secreto-verificacion&hub.challenge=12345')
            ->assertOk()->assertSee('12345');

        $this->get('/webhooks/whatsapp?hub.mode=subscribe&hub.verify_token=otro&hub.challenge=1')->assertForbidden();
    }

    public function test_rechaza_firma_invalida(): void
    {
        $this->call('POST', '/webhooks/whatsapp', [], [], [], [
            'CONTENT_TYPE' => 'application/json', 'HTTP_X_HUB_SIGNATURE_256' => 'sha256=mala',
        ], '{}')->assertStatus(401);
    }

    public function test_actualiza_estados_sin_retroceder(): void
    {
        $log = WhatsappLog::create(['phone' => '573001112233', 'status' => 'sent', 'batch_id' => 'L1', 'wamid' => 'wamid.X']);

        $this->avisar(['statuses' => [['id' => 'wamid.X', 'status' => 'read']]])->assertOk();
        $this->assertSame('read', $log->fresh()->status);

        $this->avisar(['statuses' => [['id' => 'wamid.X', 'status' => 'delivered']]])->assertOk();
        $this->assertSame('read', $log->fresh()->status);
    }

    public function test_marca_fallidos_con_el_detalle(): void
    {
        $log = WhatsappLog::create(['phone' => '573001112233', 'status' => 'sent', 'batch_id' => 'L1', 'wamid' => 'wamid.F']);

        $this->avisar(['statuses' => [['id' => 'wamid.F', 'status' => 'failed', 'errors' => [['title' => 'Message undeliverable']]]]]);

        $this->assertSame('failed', $log->fresh()->status);
        $this->assertSame('Message undeliverable', $log->fresh()->error);
    }

    public function test_salir_da_de_baja_y_se_omite_en_envios(): void
    {
        Queue::fake();

        $this->avisar(['messages' => [['from' => '573001112233', 'type' => 'text', 'text' => ['body' => ' SALIR ']]]]);
        $this->avisar(['messages' => [['from' => '573009998877', 'type' => 'text', 'text' => ['body' => 'Hola, salir a comer?']]]]);

        $this->assertDatabaseHas('whatsapp_bajas', ['telefono' => '573001112233']);
        $this->assertSame(1, WhatsappBaja::count());

        // El CSV trae el número sin indicativo: igual se reconoce.
        $lote = app(EnvioWhatsApp::class)->encolar(['3001112233', '3104445566'], ['titulo' => 't', 'cuerpo' => 'c', 'link' => 'l', 'image' => 'i']);

        $this->assertSame(1, $lote['encolados']);
        $this->assertSame(1, $lote['omitidos']);
        Queue::assertPushed(EnviarWhatsAppJob::class, 1);
    }

    public function test_el_job_guarda_el_wamid_y_no_revienta_con_un_link_invalido(): void
    {
        Configuracion::create(['parametro' => 'idnumero', 'valor' => '123']);
        Configuracion::create(['parametro' => 'tokenw', 'valor' => 'tok']);
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.NUEVO']]])]);

        WhatsappLog::create(['phone' => '573001112233', 'status' => 'pending', 'batch_id' => 'B']);
        (new EnviarWhatsAppJob('573001112233', [
            'titulo' => "Título\ncon salto", 'cuerpo' => 'Cuerpo', 'image' => 'https://x.test/a.jpg',
            'link' => 'https://www.facebook.com/pagina/posts/987', 'batch_id' => 'B',
        ]))->handle();

        $log = WhatsappLog::where('batch_id', 'B')->first();
        $this->assertSame('sent', $log->status);
        $this->assertSame('wamid.NUEVO', $log->wamid);
        Http::assertSent(fn ($r) => $r['template']['components'][2]['parameters'][0]['text'] === 'posts/987'
            && $r['template']['components'][1]['parameters'][1]['text'] === 'Título con salto');

        // Link del sitio con la plantilla de Facebook: antes reventaba con "Undefined array key 5".
        WhatsappLog::create(['phone' => '573001112244', 'status' => 'pending', 'batch_id' => 'C']);
        (new EnviarWhatsAppJob('573001112244', ['titulo' => 't', 'cuerpo' => 'c', 'link' => 'https://tigreteve.com/noticias/slug', 'batch_id' => 'C']))->handle();
        $this->assertSame('failed', WhatsappLog::where('batch_id', 'C')->value('status'));
    }

    public function test_resultados_y_lista_de_envios(): void
    {
        $admin = tap(User::factory()->create())->assignRole(Role::create(['name' => 'Administrator']));
        WhatsappLog::create(['phone' => '573001112233', 'status' => 'read', 'batch_id' => 'LOTE1']);
        WhatsappLog::create(['phone' => '573001112244', 'status' => 'failed', 'batch_id' => 'LOTE1', 'error' => 'Número inválido']);

        $this->actingAs($admin)->get(route('whatsapp.lotes'))->assertOk()->assertSee('Lote LOTE1');
        $this->actingAs($admin)->get(route('whatsapp.resultados', 'LOTE1'))->assertOk()
            ->assertSee('573001112244')->assertSee('Número inválido')->assertSee('Leídos');
        $this->actingAs($admin)->get(route('whatsapp.resultados', 'NOEXISTE'))->assertNotFound();
    }
}
