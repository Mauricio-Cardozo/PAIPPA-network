<?php

namespace Tests\Feature;

use App\Models\ClicWhatsapp;
use App\Models\Oferta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** PAI-75: el clic en «Contactar por WhatsApp» se registra y redirige a wa.me. */
class ContactoWhatsappTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function un_visitante_va_al_login(): void
    {
        $this->get(route('contacto.whatsapp', Oferta::factory()->create()))
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('clics_whatsapp', 0);
    }

    #[Test]
    public function el_comprador_va_a_whatsapp_y_el_clic_queda_registrado_sin_el_mensaje(): void
    {
        $comprador = User::factory()->create();
        $oferta = Oferta::factory()->create();

        $respuesta = $this->actingAs($comprador)->get(route('contacto.whatsapp', $oferta));

        $respuesta->assertRedirect();
        $this->assertStringStartsWith('https://wa.me/'.$oferta->feriante->telefono_whatsapp.'?text=', $respuesta->headers->get('Location'));

        $this->assertDatabaseHas('clics_whatsapp', [
            'user_id' => $comprador->id,
            'feriante_id' => $oferta->feriante_id,
            'oferta_id' => $oferta->id,
        ]);
        // La tabla no tiene columna para el texto del mensaje (RN-10).
        $this->assertSame(['id', 'user_id', 'feriante_id', 'oferta_id', 'created_at'], array_keys(ClicWhatsapp::firstOrFail()->getAttributes()));
    }

    #[Test]
    public function si_el_feriante_no_autoriza_va_al_numero_de_comercializacion(): void
    {
        config(['services.paippa.whatsapp_comercializacion' => '5493700000000']);
        $oferta = Oferta::factory()->create();
        $oferta->feriante->update(['autoriza_whatsapp' => false]);

        $this->actingAs(User::factory()->create())
            ->get(route('contacto.whatsapp', $oferta))
            ->assertRedirectContains('https://wa.me/5493700000000');
    }

    #[Test]
    public function una_oferta_no_publicada_da_404(): void
    {
        $oferta = Oferta::factory()->create(['cantidad_disponible' => 0]);

        $this->actingAs(User::factory()->create())
            ->get(route('contacto.whatsapp', $oferta))
            ->assertNotFound();

        $this->assertDatabaseCount('clics_whatsapp', 0);
    }

    #[Test]
    public function los_clics_de_mas_de_12_meses_se_borran(): void
    {
        $oferta = Oferta::factory()->create();
        $datos = ['user_id' => User::factory()->create()->id, 'feriante_id' => $oferta->feriante_id, 'oferta_id' => $oferta->id];

        $this->travelTo(now()->subMonths(13));
        ClicWhatsapp::create($datos);
        $this->travelBack();
        ClicWhatsapp::create($datos);

        $this->artisan('model:prune', ['--model' => ClicWhatsapp::class])->assertSuccessful();

        $this->assertDatabaseCount('clics_whatsapp', 1);
    }

    #[Test]
    public function la_tarjeta_muestra_el_boton_de_whatsapp_solo_con_sesion(): void
    {
        $oferta = Oferta::factory()->create();
        $enlace = route('contacto.whatsapp', $oferta);

        $this->get(route('catalogo'))->assertOk()->assertDontSee($enlace, false);

        $this->actingAs(User::factory()->create())
            ->get(route('catalogo'))
            ->assertOk()
            ->assertSee($enlace, false)
            ->assertSee('google.com/maps/search/?api=1&query='.$oferta->puntoRetiro->latitud, false);
    }
}
