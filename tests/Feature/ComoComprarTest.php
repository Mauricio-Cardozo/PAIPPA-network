<?php

namespace Tests\Feature;

use App\Models\Feria;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** PAI-151 (RF-009): sección «Cómo comprar» arriba del catálogo. */
class ComoComprarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo('2026-11-27 10:00:00');
    }

    /** Solo el HTML de la sección «Cómo comprar». */
    private function seccion(): string
    {
        $html = $this->get(route('catalogo'))->assertOk()->getContent();
        $inicio = strpos($html, 'aria-labelledby="como-comprar"');

        return substr($html, $inicio, strpos($html, '</section>', $inicio) - $inicio);
    }

    #[Test]
    public function el_catalogo_muestra_los_tres_pasos(): void
    {
        $this->get(route('catalogo'))
            ->assertOk()
            ->assertSeeInOrder(['¿Cómo comprar?', 'Elegí la oferta', 'Escribile por WhatsApp', 'Retirá en la feria'])
            ->assertSee('entrega a domicilio');
    }

    #[Test]
    public function muestra_los_dias_y_horarios_de_las_ferias(): void
    {
        Feria::factory()->create([
            'nombre' => 'Feria del Centro',
            'dias' => 'Viernes',
            'hora_inicio' => '07:00',
            'hora_fin' => '12:00',
            'direccion' => 'Padre Grotti 1040',
        ]);

        $this->get(route('catalogo'))
            ->assertSee('Feria del Centro')
            ->assertSee('Viernes · 07:00 a 12:00')
            ->assertSee('Padre Grotti 1040');
    }

    #[Test]
    public function no_muestra_las_ferias_itinerantes_que_ya_pasaron(): void
    {
        Feria::factory()->itinerante()->create(['nombre' => 'Itinerante de la semana pasada', 'fecha' => '2026-11-20']);
        Feria::factory()->itinerante()->create(['nombre' => 'Itinerante de hoy', 'fecha' => '2026-11-27']);

        $seccion = $this->seccion();

        $this->assertStringContainsString('Itinerante de hoy', $seccion);
        $this->assertStringNotContainsString('Itinerante de la semana pasada', $seccion);

        // Tampoco se ofrecen en el filtro de ferias del catálogo.
        $this->get(route('catalogo'))->assertDontSee('Itinerante de la semana pasada');
    }

    #[Test]
    public function el_contacto_del_paippa_usa_el_numero_de_comercializacion(): void
    {
        config(['services.paippa.whatsapp_comercializacion' => '5493700000000']);

        $this->get(route('catalogo'))->assertSee('https://wa.me/5493700000000', false);
    }

    #[Test]
    public function sin_numero_cargado_indica_consultar_en_el_paippa(): void
    {
        config(['services.paippa.whatsapp_comercializacion' => null]);

        $this->get(route('catalogo'))
            ->assertSee('Consultá en el PAIPPA.')
            ->assertDontSee('Escribile al PAIPPA por WhatsApp');
    }
}
