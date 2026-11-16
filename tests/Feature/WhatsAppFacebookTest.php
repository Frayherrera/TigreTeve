<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WhatsAppFacebookTest extends TestCase
{
    use RefreshDatabase;

    private const IMAGEN = 'https://scontent.fbga2-1.fna.fbcdn.net/v/t39/foto.jpg?oe=6ADE2529';

    private function admin(): User
    {
        return tap(User::factory()->create())->assignRole(Role::create(['name' => 'Administrator']));
    }

    private function paginaFacebook(array $meta): string
    {
        $tags = '';
        foreach ($meta as $prop => $valor) {
            $tags .= '<meta property="' . $prop . '" content="' . htmlspecialchars($valor, ENT_QUOTES) . '" />';
        }

        return "<!DOCTYPE html><html><head><title>Facebook</title>{$tags}</head><body>Inicia sesión</body></html>";
    }

    private function fakeFacebook(array $meta, int $statusImagen = 200): void
    {
        Http::fake([
            'scontent.*' => Http::response('bytes', $statusImagen, ['Content-Type' => 'image/jpeg']),
            '*facebook.com/*' => Http::response($this->paginaFacebook($meta)),
        ]);
    }

    private function leer(string $url)
    {
        return $this->actingAs($this->admin())->postJson(route('whatsapp.facebook'), ['url' => $url]);
    }

    public function test_publicacion_de_pagina_rellena_todos_los_campos(): void
    {
        Storage::fake('s3');
        $this->fakeFacebook([
            'og:title' => 'TigreTeve',
            'og:description' => "Alcaldía anuncia pavimentación de 12 calles en Aguachica.\nLas obras empiezan el lunes en el barrio Villa Paraguay.",
            'og:image' => self::IMAGEN,
            'og:url' => 'https://www.facebook.com/TigreTeve/posts/pfbid02abc?ref=embed',
        ]);

        $r = $this->leer('https://www.facebook.com/TigreTeve/posts/pfbid02abc')->assertOk();

        $this->assertSame('Alcaldía anuncia pavimentación de 12 calles en Aguachica', $r->json('titulo'));
        $this->assertSame('Las obras empiezan el lunes en el barrio Villa Paraguay.', $r->json('cuerpo'));
        $this->assertSame('https://www.facebook.com/TigreTeve/posts/pfbid02abc', $r->json('link'));
        $this->assertStringContainsString('imagenes/', $r->json('image'));
        $this->assertSame([], $r->json('avisos'));
        $this->assertCount(1, Storage::disk('s3')->allFiles('imagenes'));
    }

    public function test_titular_propio_y_enlace_compartido_usa_la_direccion_canonica(): void
    {
        Storage::fake('s3');
        $this->fakeFacebook([
            'og:title' => 'Tigres del Cesar a la final regional | By TigreTeve | Facebook',
            'og:description' => 'Tigres del Cesar a la final regional tras vencer 3-1 en casa ante un estadio lleno.',
            'og:image' => self::IMAGEN,
            'og:url' => 'https://m.facebook.com/TigreTeve/posts/pfbid0xyz/',
        ]);

        $r = $this->leer('https://www.facebook.com/share/p/1AbCdEf/')->assertOk();

        $this->assertSame('Tigres del Cesar a la final regional', $r->json('titulo'));
        $this->assertSame('tras vencer 3-1 en casa ante un estadio lleno.', $r->json('cuerpo'));
        $this->assertSame('https://www.facebook.com/TigreTeve/posts/pfbid0xyz', $r->json('link'));
    }

    public function test_muro_de_inicio_de_sesion_pide_llenar_a_mano(): void
    {
        $this->fakeFacebook(['og:title' => 'Log in or sign up to view']);

        $this->leer('https://www.facebook.com/TigreTeve/posts/123')
            ->assertStatus(422)
            ->assertJson(['message' => 'Facebook no mostró la publicación sin iniciar sesión (o no es pública). Llena los campos a mano.']);
    }

    public function test_rechaza_enlaces_que_no_son_de_facebook(): void
    {
        Http::fake();

        $this->leer('https://www.instagram.com/p/abc/')
            ->assertStatus(422)
            ->assertJson(['message' => 'El enlace debe ser de una publicación de Facebook.']);
        Http::assertNothingSent();
    }

    public function test_si_la_imagen_no_se_puede_copiar_usa_la_de_facebook_y_avisa(): void
    {
        Storage::fake('s3');
        $this->fakeFacebook([
            'og:title' => 'TigreTeve',
            'og:description' => 'Concejo debatirá el presupuesto 2027 en sesión abierta este jueves.',
            'og:image' => self::IMAGEN,
            'og:url' => 'https://www.facebook.com/TigreTeve/posts/pfbid1',
        ], 403);

        $r = $this->leer('https://www.facebook.com/TigreTeve/posts/pfbid1')->assertOk();

        $this->assertSame(self::IMAGEN, $r->json('image'));
        $this->assertStringContainsString('caduca', $r->json('avisos.0'));
    }

    public function test_avisa_si_el_enlace_no_sirve_para_el_boton(): void
    {
        Storage::fake('s3');
        $this->fakeFacebook([
            'og:title' => 'TigreTeve',
            'og:description' => 'Nota publicada desde un enlace antiguo de Facebook con permalink.',
            'og:image' => self::IMAGEN,
            'og:url' => 'https://www.facebook.com/permalink.php?story_fbid=987&id=123',
        ]);

        $r = $this->leer('https://www.facebook.com/permalink.php?story_fbid=987&id=123')->assertOk();

        $this->assertSame('https://www.facebook.com/permalink.php?story_fbid=987&id=123', $r->json('link'));
        $this->assertStringContainsString('facebook.com/página/posts/id', $r->json('avisos.0'));
    }

    public function test_solo_administradores(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson(route('whatsapp.facebook'), ['url' => 'https://www.facebook.com/x/posts/1'])
            ->assertForbidden();
    }
}
