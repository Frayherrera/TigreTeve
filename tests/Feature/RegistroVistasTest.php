<?php

namespace Tests\Feature;

use App\Models\Noticia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RegistroVistasTest extends TestCase
{
    use RefreshDatabase;

    private const NAVEGADOR = 'Mozilla/5.0 (Linux; Android 14) AppleWebKit/537.36 Chrome/130.0 Mobile Safari/537.36';

    private Noticia $noticia;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'app.timezone' => 'UTC',
            'app.zona_editorial' => 'America/Bogota',
            'cache.default' => 'array',
            'filesystems.disks.s3' => [
                'driver' => 's3', 'key' => 'test', 'secret' => 'test',
                'region' => 'us-east-1', 'bucket' => 'tigreteve-test',
            ],
        ]);
        Storage::forgetDisk('s3');
        Http::fake();

        $this->noticia = Noticia::create([
            'user_id' => User::factory()->create()->id,
            'titulo' => 'Nota de prueba',
            'slug' => 'nota-de-prueba',
            'cuerpo' => 'Texto',
            'estado' => 'publicada',
        ]);
    }

    private function visitar(string $agente = self::NAVEGADOR, string $ip = '10.0.0.1', array $headers = [])
    {
        return $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->withHeaders(['User-Agent' => $agente] + $headers)
            ->get(route('noticias.show2', $this->noticia->slug));
    }

    private function vistasDelDia(string $fecha): int
    {
        return (int) DB::table('noticia_vistas')
            ->where('noticia_id', $this->noticia->id)
            ->where('fecha', $fecha)
            ->value('vistas');
    }

    public function test_la_noticia_carga_y_cuenta_la_lectura(): void
    {
        Carbon::setTestNow('2026-11-18 15:00:00');

        $this->visitar()->assertOk()->assertSee('Nota de prueba');

        $this->assertSame(1, $this->noticia->fresh()->vistas);
        $this->assertSame(1, $this->vistasDelDia('2026-11-18'));
    }

    public function test_las_recargas_del_mismo_visitante_no_suman(): void
    {
        $this->visitar();
        $this->visitar();
        $this->visitar(ip: '10.0.0.2');

        $this->assertSame(2, $this->noticia->fresh()->vistas);
    }

    public function test_bots_y_prefetch_no_cuentan(): void
    {
        $this->visitar('Googlebot/2.1 (+http://www.google.com/bot.html)')->assertOk();
        $this->visitar('WhatsApp/2.23.20.0 A', '10.0.0.3')->assertOk();
        $this->visitar(ip: '10.0.0.4', headers: ['Sec-Purpose' => 'prefetch'])->assertOk();

        $this->assertSame(0, $this->noticia->fresh()->vistas);
        $this->assertSame(0, DB::table('noticia_vistas')->count());
    }

    public function test_el_dia_se_cuenta_en_hora_de_colombia(): void
    {
        // 3:00 a. m. UTC del 19 = 10:00 p. m. del 18 en Bogotá.
        Carbon::setTestNow('2026-11-19 03:00:00');
        $this->visitar();

        $this->assertSame(1, $this->vistasDelDia('2026-11-18'));
    }

    public function test_un_borrador_da_404(): void
    {
        $this->noticia->update(['estado' => 'borrador']);

        $this->visitar()->assertNotFound();
        $this->assertSame(0, $this->noticia->fresh()->vistas);
    }
}
