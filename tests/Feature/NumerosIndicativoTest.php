<?php

namespace Tests\Feature;

use App\Models\Numero;
use App\Models\User;
use App\Support\Telefono;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NumerosIndicativoTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = tap(User::factory()->create())->assignRole(Role::create(['name' => 'Administrator']));
    }

    public function test_normalizar_agrega_el_57(): void
    {
        $this->assertSame('573001234567', Telefono::normalizar('300 123 4567'));
        $this->assertSame('573001234567', Telefono::normalizar('+57 300-123-4567'));
        $this->assertNull(Telefono::normalizar('12345'));
        $this->assertNull(Telefono::normalizar('15551234567'));
    }

    public function test_al_agregar_un_celular_de_10_digitos_se_guarda_con_57(): void
    {
        $this->actingAs($this->admin)
            ->post(route('numeros.store'), ['numero' => '300 123 4567'])
            ->assertRedirect(route('numeros.index'));

        $this->assertDatabaseHas('numeros', ['numero' => '573001234567']);
    }

    public function test_si_ya_trae_el_57_no_se_duplica(): void
    {
        $this->actingAs($this->admin)->post(route('numeros.store'), ['numero' => '573001234567']);
        $this->actingAs($this->admin)
            ->post(route('numeros.store'), ['numero' => '3001234567'])
            ->assertSessionHasErrors(['numero' => 'Ese número ya está en la lista.']);

        $this->assertSame(1, Numero::count());
        $this->assertDatabaseHas('numeros', ['numero' => '573001234567']);
    }

    public function test_un_numero_invalido_se_rechaza_y_conserva_lo_escrito(): void
    {
        $this->actingAs($this->admin)
            ->from(route('numeros.index'))
            ->post(route('numeros.store'), ['numero' => '12345', '_form' => 'create'])
            ->assertSessionHasErrors('numero')
            ->assertSessionHasInput('numero', '12345');

        $this->assertSame(0, Numero::count());
    }

    public function test_editar_tambien_agrega_el_57(): void
    {
        $numero = Numero::create(['numero' => '573001234567']);

        $this->actingAs($this->admin)
            ->put(route('numeros.update', $numero), ['numero' => '3109876543'])
            ->assertRedirect(route('numeros.index'));

        $this->assertSame('573109876543', $numero->fresh()->numero);
    }

    public function test_importar_csv_agrega_el_57(): void
    {
        $csv = UploadedFile::fake()->createWithContent('numeros.csv', "telefono\n3001112233\n573004445566\n12345\n");

        $this->actingAs($this->admin)
            ->post(route('numeros.import'), ['file' => $csv])
            ->assertSessionHas('ok', '2 número(s) importado(s).');

        $this->assertEqualsCanonicalizing(['573001112233', '573004445566'], Numero::pluck('numero')->all());
    }

    public function test_para_envio_agrega_57_y_respeta_numeros_con_indicativo(): void
    {
        $this->assertSame('573001234567', Telefono::paraEnvio('3001234567'));
        $this->assertSame('573001234567', Telefono::paraEnvio('573001234567'));
        $this->assertSame('15551234567', Telefono::paraEnvio('+1 555 123 4567'));
        $this->assertNull(Telefono::paraEnvio('12345'));
    }
}
