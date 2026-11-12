<?php

namespace Tests\Feature;

use App\Enums\Rol;
use App\Models\Conversacion;
use App\Models\Feriante;
use App\Models\Mensaje;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MensajeriaTest extends TestCase
{
    use RefreshDatabase;

    /** Un feriante validado con cuenta propia. */
    private function ferianteConCuenta(): Feriante
    {
        $cuenta = User::factory()->feriante()->create();

        return Feriante::factory()->create(['user_id' => $cuenta->id]);
    }

    #[Test]
    public function el_admin_crea_la_cuenta_del_feriante_y_se_le_manda_el_correo(): void
    {
        Notification::fake();
        $feriante = Feriante::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.feriantes.cuenta.store', $feriante), ['email' => 'rosa@example.com'])
            ->assertRedirect(route('admin.feriantes.index'));

        $cuenta = User::where('email', 'rosa@example.com')->firstOrFail();
        $this->assertSame(Rol::Feriante, $cuenta->rol);
        $this->assertTrue($feriante->fresh()->user->is($cuenta));
        Notification::assertSentTo($cuenta, ResetPassword::class);
    }

    #[Test]
    public function el_correo_de_la_cuenta_tiene_que_ser_unico(): void
    {
        User::factory()->create(['email' => 'repetido@example.com']);

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.feriantes.cuenta.store', Feriante::factory()->create()), ['email' => 'repetido@example.com'])
            ->assertSessionHasErrors('email');
    }

    #[Test]
    public function un_revendedor_inicia_la_conversacion_y_el_feriante_responde(): void
    {
        $feriante = $this->ferianteConCuenta();
        $revendedor = User::factory()->create();

        $this->actingAs($revendedor)
            ->post(route('mensajes.iniciar', $feriante), ['cuerpo' => '¿Tenés tomate para el sábado?'])
            ->assertRedirect();

        $conversacion = Conversacion::sole();
        $this->assertSame($revendedor->id, $conversacion->revendedor_id);

        // El feriante ve la conversación con 1 sin leer, la abre y responde.
        $this->actingAs($feriante->user)->get(route('mensajes.index'))->assertSee('1 sin leer');
        $this->actingAs($feriante->user)->get(route('mensajes.show', $conversacion))->assertSee('¿Tenés tomate para el sábado?');
        $this->assertNotNull(Mensaje::first()->fresh()->leido_at);

        $this->actingAs($feriante->user)
            ->post(route('mensajes.responder', $conversacion), ['cuerpo' => 'Sí, 10 cajas.'])
            ->assertRedirect(route('mensajes.show', $conversacion));

        $this->assertSame(2, $conversacion->mensajes()->count());
    }

    #[Test]
    public function un_segundo_mensaje_usa_la_misma_conversacion(): void
    {
        $feriante = $this->ferianteConCuenta();
        $revendedor = User::factory()->create();

        $this->actingAs($revendedor)->post(route('mensajes.iniciar', $feriante), ['cuerpo' => 'Hola']);
        $this->actingAs($revendedor)->post(route('mensajes.iniciar', $feriante), ['cuerpo' => 'Hola de nuevo']);

        $this->assertSame(1, Conversacion::count());
        $this->assertSame(2, Mensaje::count());
    }

    #[Test]
    public function un_tercero_no_puede_leer_ni_responder(): void
    {
        $feriante = $this->ferianteConCuenta();
        $conversacion = Conversacion::create(['revendedor_id' => User::factory()->create()->id, 'feriante_id' => $feriante->id]);
        $intruso = User::factory()->create();

        $this->actingAs($intruso)->get(route('mensajes.show', $conversacion))->assertForbidden();
        $this->actingAs($intruso)->post(route('mensajes.responder', $conversacion), ['cuerpo' => 'hola'])->assertForbidden();
        $this->assertSame(0, Mensaje::count());
    }

    #[Test]
    public function solo_un_revendedor_inicia_y_solo_con_feriantes_con_cuenta(): void
    {
        $this->actingAs(User::factory()->feriante()->create())
            ->post(route('mensajes.iniciar', $this->ferianteConCuenta()), ['cuerpo' => 'Hola'])
            ->assertForbidden();

        $this->actingAs(User::factory()->create())
            ->post(route('mensajes.iniciar', Feriante::factory()->create()), ['cuerpo' => 'Hola'])
            ->assertNotFound();

        auth()->logout();
        $this->post(route('mensajes.iniciar', $this->ferianteConCuenta()), ['cuerpo' => 'Hola'])
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function cada_usuario_envia_hasta_10_mensajes_por_hora(): void
    {
        $feriante = $this->ferianteConCuenta();
        $revendedor = User::factory()->create();

        for ($i = 1; $i <= 10; $i++) {
            $this->actingAs($revendedor)->post(route('mensajes.iniciar', $feriante), ['cuerpo' => "Mensaje $i"])->assertRedirect();
        }

        $this->actingAs($revendedor)->post(route('mensajes.iniciar', $feriante), ['cuerpo' => 'Mensaje 11'])->assertStatus(429);
        $this->assertSame(10, Mensaje::count());
    }

    #[Test]
    public function el_perfil_muestra_el_formulario_solo_al_revendedor_si_el_feriante_tiene_cuenta(): void
    {
        $conCuenta = $this->ferianteConCuenta();
        $sinCuenta = Feriante::factory()->create();
        $revendedor = User::factory()->create();

        $this->actingAs($revendedor)->get(route('feriantes.show', $conCuenta))->assertSee('Enviar mensaje');
        $this->actingAs($revendedor)->get(route('feriantes.show', $sinCuenta))->assertDontSee('Enviar mensaje');
        $this->actingAs(User::factory()->admin()->create())->get(route('feriantes.show', $conCuenta))->assertDontSee('Enviar mensaje');
    }

    #[Test]
    public function el_mensaje_se_muestra_escapado(): void
    {
        $feriante = $this->ferianteConCuenta();
        $revendedor = User::factory()->create();

        $this->actingAs($revendedor)->post(route('mensajes.iniciar', $feriante), ['cuerpo' => '<script>alert(1)</script>']);

        $this->actingAs($feriante->user)->get(route('mensajes.show', Conversacion::sole()))
            ->assertDontSee('<script>alert(1)</script>', false);
    }
}
