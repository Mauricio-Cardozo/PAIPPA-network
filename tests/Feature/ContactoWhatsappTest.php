<?php

namespace Tests\Feature;

use App\Models\ClicWhatsapp;
use App\Models\Feriante;
use App\Models\Oferta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ContactoWhatsappTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function un_visitante_va_al_login(): void
    {
        $oferta = Oferta::factory()->create();

        $this->get(route('contacto.whatsapp', $oferta))->assertRedirect(route('login'));
        $this->assertSame(0, ClicWhatsapp::count());
    }

    #[Test]
    public function el_clic_queda_registrado_sin_el_texto_y_redirige_a_whatsapp(): void
    {
        $user = User::factory()->create();
        $oferta = Oferta::factory()->create();

        $response = $this->actingAs($user)->get(route('contacto.whatsapp', $oferta));

        $response->assertRedirect();
        $this->assertStringStartsWith('https://wa.me/'.$oferta->feriante->telefono_whatsapp, $response->headers->get('Location'));

        $clic = ClicWhatsapp::sole();
        $this->assertSame($user->id, $clic->user_id);
        $this->assertSame($oferta->feriante_id, $clic->feriante_id);
        $this->assertSame($oferta->id, $clic->oferta_id);
        // La tabla no tiene ninguna columna para el texto del mensaje.
        $this->assertSame(['id', 'user_id', 'feriante_id', 'oferta_id', 'created_at'], array_keys($clic->getAttributes()));
    }

    #[Test]
    public function una_oferta_no_publicada_da_404(): void
    {
        $oferta = Oferta::factory()->sinStock()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('contacto.whatsapp', $oferta))
            ->assertNotFound();
    }

    #[Test]
    public function el_contacto_general_es_solo_para_feriantes_validados(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('contacto.whatsapp.feriante', Feriante::factory()->create()))->assertRedirect();
        $this->actingAs($user)->get(route('contacto.whatsapp.feriante', Feriante::factory()->sinValidar()->create()))->assertNotFound();
    }

    #[Test]
    public function los_clics_de_mas_de_12_meses_se_borran(): void
    {
        $oferta = Oferta::factory()->create();
        $user = User::factory()->create();

        $viejo = ClicWhatsapp::create(['user_id' => $user->id, 'feriante_id' => $oferta->feriante_id]);
        $viejo->forceFill(['created_at' => now()->subMonths(13)])->save();
        ClicWhatsapp::create(['user_id' => $user->id, 'feriante_id' => $oferta->feriante_id]);

        $this->artisan('model:prune', ['--model' => ClicWhatsapp::class])->assertSuccessful();

        $this->assertSame(1, ClicWhatsapp::count());
        $this->assertModelMissing($viejo);
    }

    #[Test]
    public function el_boton_cambia_con_y_sin_sesion(): void
    {
        $oferta = Oferta::factory()->create();

        $sinSesion = Blade::render('<x-boton-whatsapp :oferta="$oferta" />', ['oferta' => $oferta]);
        $this->assertStringContainsString('Iniciá sesión para contactar', $sinSesion);

        $this->actingAs(User::factory()->create());
        $conSesion = Blade::render('<x-boton-whatsapp :oferta="$oferta" />', ['oferta' => $oferta]);
        $this->assertStringContainsString('Contactar por WhatsApp', $conSesion);
        $this->assertStringContainsString(route('contacto.whatsapp', $oferta), $conSesion);
    }

    #[Test]
    public function despues_del_login_vuelve_a_la_pagina_de_la_oferta(): void
    {
        $user = User::factory()->create();

        $this->get(route('login', ['volver' => url('/feriantes/1')]));
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(url('/feriantes/1'));
    }

    #[Test]
    public function el_parametro_volver_no_permite_ir_a_otro_sitio(): void
    {
        $user = User::factory()->create();

        $this->get(route('login', ['volver' => 'https://sitio-malo.example/robar']));
        $response = $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->assertStringStartsWith(config('app.url'), $response->headers->get('Location'));
    }
}
