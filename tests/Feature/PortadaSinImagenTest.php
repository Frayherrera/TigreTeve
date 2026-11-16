<?php

namespace Tests\Feature;

use App\Models\Noticia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PortadaSinImagenTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_portada_carga_con_noticias_sin_imagen(): void
    {
        // Adaptador S3 real (Storage::fake acepta rutas null y no reproduciría el error).
        // Generar la URL no hace llamadas de red.
        config(['filesystems.disks.s3' => [
            'driver' => 's3', 'key' => 'test', 'secret' => 'test',
            'region' => 'us-east-1', 'bucket' => 'tigreteve-test',
        ]]);
        Storage::forgetDisk('s3');
        Http::fake(); // clima de wttr.in

        $user = User::factory()->create();
        foreach ([true, false] as $destacada) {
            Noticia::create([
                'user_id' => $user->id,
                'titulo' => $destacada ? 'Destacada sin foto' : 'Normal sin foto',
                'slug' => $destacada ? 'destacada-sin-foto' : 'normal-sin-foto',
                'cuerpo' => 'Texto',
                'estado' => 'publicada',
                'is_featured' => $destacada,
                'portada_path' => null,
            ]);
        }

        $this->get('/')
            ->assertOk()
            ->assertSee('Destacada sin foto')
            ->assertSee('Normal sin foto');
    }
}
