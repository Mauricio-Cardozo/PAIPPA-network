<?php

namespace Tests\Feature;

use App\Enums\OrigenConsulta;
use App\Models\ConsultaProducto;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** HU15: la tabla de consultas por producto y sus reglas de datos. */
class ConsultaProductoTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guarda_la_consulta_con_su_origen_y_fecha(): void
    {
        $consulta = ConsultaProducto::factory()->whatsapp()->create(['fecha' => '2026-11-20']);

        $consulta = $consulta->fresh();
        $this->assertSame(OrigenConsulta::Whatsapp, $consulta->origen);
        $this->assertSame('2026-11-20', $consulta->fecha->toDateString());
        $this->assertNotNull($consulta->created_at);
        $this->assertInstanceOf(Producto::class, $consulta->producto);
    }

    #[Test]
    public function la_misma_consulta_no_se_cuenta_dos_veces_en_el_dia(): void
    {
        $datos = ConsultaProducto::factory()->make()->getAttributes();

        ConsultaProducto::create($datos);

        $this->expectException(UniqueConstraintViolationException::class);
        ConsultaProducto::create($datos);
    }

    #[Test]
    public function insert_or_ignore_descarta_el_duplicado_sin_error(): void
    {
        $fila = ConsultaProducto::factory()->make()->getAttributes();

        ConsultaProducto::insertOrIgnore([$fila, $fila]);

        $this->assertSame(1, ConsultaProducto::count());
    }

    #[Test]
    public function otro_dia_u_otro_origen_cuentan_como_otra_consulta(): void
    {
        $primera = ConsultaProducto::factory()->create(['fecha' => '2026-11-20']);
        $igual = ['producto_id' => $primera->producto_id, 'visitante' => $primera->visitante];

        ConsultaProducto::factory()->create($igual + ['fecha' => '2026-11-21']);
        ConsultaProducto::factory()->whatsapp()->create($igual + ['fecha' => '2026-11-20']);

        $this->assertSame(3, ConsultaProducto::count());
    }

    #[Test]
    public function el_visitante_nunca_es_el_id_de_sesion_crudo(): void
    {
        $sesion = 'abc123sesion';
        $user = User::factory()->create();

        $this->assertSame(hash('sha256', $sesion), ConsultaProducto::visitante(null, $sesion));
        $this->assertNotSame($sesion, ConsultaProducto::visitante(null, $sesion));
        $this->assertSame('user:'.$user->id, ConsultaProducto::visitante($user, $sesion));
    }

    #[Test]
    public function si_se_borra_el_usuario_la_consulta_queda_sin_usuario(): void
    {
        $user = User::factory()->create();
        $consulta = ConsultaProducto::factory()->create(['user_id' => $user->id]);

        $user->delete();

        $this->assertNull($consulta->fresh()->user_id);
    }

    #[Test]
    public function si_se_elimina_el_producto_se_eliminan_sus_consultas(): void
    {
        $consulta = ConsultaProducto::factory()->create();

        // La baja lógica no borra nada; solo el borrado definitivo dispara el cascade.
        $consulta->producto->forceDelete();

        $this->assertSame(0, ConsultaProducto::count());
    }
}
