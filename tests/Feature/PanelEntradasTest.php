<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Comentario;
use App\Models\Noticia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PanelEntradasTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.timezone' => 'UTC', 'app.zona_editorial' => 'America/Bogota']);
        Carbon::setTestNow('2026-11-16 12:00:00');

        $this->admin = tap(User::factory()->create(['name' => 'Ana Editora']))
            ->assignRole(Role::create(['name' => 'Administrator']));
        $categoria = Categoria::create(['nombre' => 'Deportes', 'slug' => 'deportes']);

        $base = ['user_id' => $this->admin->id, 'cuerpo' => 'Texto'];
        $publicada = Noticia::create($base + [
            'titulo' => 'Un titular publicado bastante largo para comprobar que ya no se corta en dos palabras',
            'slug' => 'pub', 'estado' => 'publicada', 'category_id' => $categoria->id, 'is_featured' => true,
        ]);
        Noticia::create($base + ['titulo' => 'Nota programada', 'slug' => 'prog', 'estado' => 'programada', 'publicado_en' => '2026-11-20 15:00:00']);
        Noticia::create($base + ['titulo' => 'Borrador pendiente', 'slug' => 'bor', 'estado' => 'borrador']);

        Comentario::unguarded(fn () => Comentario::insert([
            ['noticia_id' => $publicada->id, 'user_id' => $this->admin->id, 'contenido' => 'a', 'created_at' => now(), 'updated_at' => now()],
            ['noticia_id' => $publicada->id, 'user_id' => $this->admin->id, 'contenido' => 'b', 'created_at' => now(), 'updated_at' => now()],
        ]));
    }

    public function test_lista_todas_con_conteos_y_datos_completos(): void
    {
        $this->actingAs($this->admin)
            ->get(route('noticias.index'))
            ->assertOk()
            ->assertSeeInOrder(['Todas', '3', 'Publicadas', '1', 'Programadas', '1', 'Borradores', '1'])
            ->assertSee('ya no se corta en dos palabras')
            ->assertSee('Deportes')
            ->assertSee('Ana Editora')
            ->assertSee('Sale el 20 nov., 10:00 a. m.') // 15:00 UTC = 10:00 en Colombia
            ->assertSee('Destacada')
            ->assertSee('fa-comment text-slate-400"></i>2', false);
    }

    public function test_filtra_por_estado_y_busca_por_titulo(): void
    {
        $this->actingAs($this->admin)
            ->get(route('noticias.index', ['estado' => 'borradores']))
            ->assertOk()
            ->assertSee('Borrador pendiente')
            ->assertDontSee('Nota programada');

        $this->actingAs($this->admin)
            ->get(route('noticias.index', ['q' => 'programada']))
            ->assertOk()
            ->assertSee('Nota programada')
            ->assertDontSee('Borrador pendiente')
            ->assertSee('que coinciden con “programada”', false);
    }

    public function test_muestra_aviso_tras_guardar(): void
    {
        $this->actingAs($this->admin)
            ->withSession(['success' => 'Noticia actualizada correctamente'])
            ->get(route('noticias.index'))
            ->assertSee('Noticia actualizada correctamente');
    }
}
