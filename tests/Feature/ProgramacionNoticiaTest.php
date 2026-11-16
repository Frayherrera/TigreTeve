<?php

namespace Tests\Feature;

use App\Models\Noticia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProgramacionNoticiaTest extends TestCase
{
    use RefreshDatabase;

    private User $editor;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.timezone' => 'UTC', 'app.zona_editorial' => 'America/Bogota']);
        Carbon::setTestNow('2026-11-16 12:00:00'); // UTC = 7:00 a. m. en Colombia

        foreach (['crear noticias', 'editar noticias'] as $permiso) {
            Permission::create(['name' => $permiso]);
        }
        $this->editor = tap(User::factory()->create())->givePermissionTo(['crear noticias', 'editar noticias']);
    }

    private function datos(array $extra = []): array
    {
        return array_merge(['titulo' => 'Nota', 'cuerpo' => 'Texto', 'estado' => 'programada'], $extra);
    }

    public function test_programada_guarda_hora_de_colombia_en_utc(): void
    {
        $this->actingAs($this->editor)
            ->post(route('noticias.store'), $this->datos(['publicado_en' => '2026-11-20T10:00']))
            ->assertSessionHasNoErrors();

        $this->assertSame('2026-11-20 15:00:00', Noticia::first()->publicado_en->toDateTimeString());
    }

    public function test_programada_exige_fecha(): void
    {
        $this->actingAs($this->editor)
            ->post(route('noticias.store'), $this->datos())
            ->assertSessionHasErrors(['publicado_en' => 'Elige la fecha y hora en que se publicará la noticia.']);
    }

    public function test_programada_exige_fecha_futura_en_hora_de_colombia(): void
    {
        // 6:30 a. m. en Colombia ya pasó (son las 7:00), aunque en UTC parezca futuro.
        $this->actingAs($this->editor)
            ->post(route('noticias.store'), $this->datos(['publicado_en' => '2026-11-16T06:30']))
            ->assertSessionHasErrors('publicado_en')
            ->assertSessionHasInput('publicado_en', '2026-11-16T06:30'); // sin convertir, para no correr la hora

        $this->assertSame(0, Noticia::count());
    }

    public function test_si_no_es_programada_se_descarta_la_fecha(): void
    {
        $this->actingAs($this->editor)
            ->post(route('noticias.store'), $this->datos(['estado' => 'publicada', 'publicado_en' => '2020-01-01T10:00']))
            ->assertSessionHasNoErrors();

        $this->assertNull(Noticia::first()->publicado_en);
    }

    public function test_editar_sin_cambiar_fecha_pasada_no_obliga_a_moverla(): void
    {
        $noticia = Noticia::create($this->datos([
            'user_id' => $this->editor->id,
            'slug' => 'nota',
            'publicado_en' => '2026-11-10 15:00:00',
        ]));

        // El formulario la muestra en hora de Colombia…
        $this->actingAs($this->editor)
            ->get(route('noticias.edit', $noticia))
            ->assertOk()
            ->assertSee('value="2026-11-10T10:00"', false)
            ->assertSee('data-programacion', false);

        // …y al guardar sin tocarla no se exige fecha futura.
        $this->actingAs($this->editor)
            ->put(route('noticias.update', $noticia), $this->datos(['titulo' => 'Nota editada', 'publicado_en' => '2026-11-10T10:00']))
            ->assertSessionHasNoErrors();

        $this->assertSame('Nota editada', $noticia->fresh()->titulo);
        $this->assertSame('2026-11-10 15:00:00', $noticia->fresh()->publicado_en->toDateTimeString());
    }

    public function test_el_campo_de_fecha_empieza_oculto_si_no_es_programada(): void
    {
        $html = $this->actingAs($this->editor)->get(route('noticias.create'))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<div data-programacion\s+hidden/', $html);
        $this->assertMatchesRegularExpression('/name="publicado_en"[^>]*\sdisabled/s', $html);
    }
}
