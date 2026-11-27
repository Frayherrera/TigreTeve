<?php

namespace Tests\Feature;

use App\Models\Comentario;
use App\Models\Noticia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ComentariosTest extends TestCase
{
    use RefreshDatabase;

    private Noticia $noticia;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('s3');
        Http::fake();
        config(['cache.default' => 'array']);

        $this->admin = tap(User::factory()->create())->assignRole(Role::create(['name' => 'Administrator']));
        $this->noticia = Noticia::create([
            'user_id' => $this->admin->id, 'titulo' => 'Nota comentada', 'slug' => 'nota-comentada',
            'cuerpo' => 'Texto', 'estado' => 'publicada',
        ]);
    }

    public function test_un_lector_publica_un_comentario_visible(): void
    {
        $this->post(route('comentarios.store', $this->noticia->slug), [
            'autor_nombre' => 'Lucía', 'contenido' => "Muy buena nota\ncon salto",
        ])->assertRedirect(route('noticias.show2', $this->noticia->slug).'#comentarios');

        $this->assertDatabaseHas('comentarios', ['noticia_id' => $this->noticia->id, 'autor_nombre' => 'Lucía', 'aprobado' => true]);

        $this->get(route('noticias.show2', $this->noticia->slug))
            ->assertSee('Comentarios')
            ->assertSee('Lucía')
            ->assertSee('Muy buena nota<br />', false);
    }

    public function test_valida_nombre_y_contenido(): void
    {
        $this->post(route('comentarios.store', $this->noticia->slug), ['contenido' => ''])
            ->assertSessionHasErrors(['autor_nombre', 'contenido']);

        $this->assertSame(0, Comentario::count());
    }

    public function test_el_honeypot_descarta_bots_sin_guardar(): void
    {
        $this->post(route('comentarios.store', $this->noticia->slug), [
            'autor_nombre' => 'Bot', 'contenido' => 'spam', 'sitio_web' => 'http://spam.test',
        ])->assertRedirect();

        $this->assertSame(0, Comentario::count());
    }

    public function test_no_se_comenta_un_borrador(): void
    {
        $this->noticia->update(['estado' => 'borrador']);

        $this->post(route('comentarios.store', $this->noticia->slug), ['autor_nombre' => 'A', 'contenido' => 'Hola'])
            ->assertNotFound();
    }

    public function test_el_admin_ve_oculta_y_elimina(): void
    {
        $c = $this->noticia->comentarios()->create(['autor_nombre' => 'Pedro', 'contenido' => 'Comentario de Pedro']);

        $this->actingAs($this->admin)->get(route('comentarios.index'))->assertOk()->assertSee('Nota comentada');
        $this->actingAs($this->admin)->get(route('comentarios.noticia', $this->noticia))->assertOk()->assertSee('Comentario de Pedro');

        $this->actingAs($this->admin)->patch(route('comentarios.alternar', $c))->assertRedirect();
        $this->assertFalse($c->fresh()->aprobado);
        $this->get(route('noticias.show2', $this->noticia->slug))->assertDontSee('Comentario de Pedro');

        $this->actingAs($this->admin)->delete(route('comentarios.destroy', $c))->assertRedirect();
        $this->assertModelMissing($c);
    }

    public function test_el_panel_de_comentarios_es_solo_para_admin(): void
    {
        $this->get(route('comentarios.index'))->assertForbidden();
    }
}
