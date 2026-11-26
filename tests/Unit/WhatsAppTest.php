<?php

namespace Tests\Unit;

use App\Models\Feriante;
use App\Models\Oferta;
use App\Models\Producto;
use App\Models\PuntoRetiro;
use App\Support\WhatsApp;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** PAI-74: enlace wa.me con mensaje prearmado (y RN-06). No usa la base de datos. */
class WhatsAppTest extends TestCase
{
    private function oferta(bool $autoriza = true): Oferta
    {
        $feriante = new Feriante(['nombre' => 'José Peña', 'telefono_whatsapp' => '5493704123456', 'autoriza_whatsapp' => $autoriza]);
        $oferta = new Oferta(['precio' => 12500]);
        $oferta->setRelation('feriante', $feriante);
        $oferta->setRelation('producto', new Producto(['nombre' => 'Limón & Mandarina', 'unidad' => 'caja']));
        $oferta->setRelation('puntoRetiro', new PuntoRetiro(['nombre' => 'Plaza San Martín']));

        return $oferta;
    }

    #[Test]
    public function arma_el_enlace_con_el_telefono_y_el_mensaje_codificado(): void
    {
        $oferta = $this->oferta();
        $enlace = WhatsApp::enlace($oferta->feriante, $oferta);

        $this->assertStringStartsWith('https://wa.me/5493704123456?text=', $enlace);
        // Acentos, eñes, & y ¿ quedan codificados y no rompen la URL.
        $this->assertStringContainsString('Jos%C3%A9%20Pe%C3%B1a', $enlace);
        $this->assertStringContainsString('Lim%C3%B3n%20%26%20Mandarina', $enlace);
        $this->assertStringContainsString('%C2%BFSigue%20disponible%3F', $enlace);
        $this->assertStringNotContainsString(' ', $enlace);
    }

    #[Test]
    public function el_mensaje_incluye_producto_precio_unidad_y_punto_de_retiro(): void
    {
        $oferta = $this->oferta();

        $this->assertSame(
            'Hola José Peña, vi en PAIPPA network tu oferta de Limón & Mandarina a $12.500,00 por caja (retiro en Plaza San Martín). ¿Sigue disponible?',
            WhatsApp::mensaje($oferta->feriante, $oferta),
        );
    }

    #[Test]
    public function sin_oferta_arma_un_mensaje_general(): void
    {
        $feriante = new Feriante(['nombre' => 'Ana', 'telefono_whatsapp' => '5493704000000', 'autoriza_whatsapp' => true]);

        $this->assertStringContainsString('Hola Ana', WhatsApp::mensaje($feriante));
    }

    #[Test]
    public function si_no_autoriza_el_enlace_va_a_comercializacion(): void
    {
        config(['services.paippa.whatsapp_comercializacion' => '5493700000000']);
        $oferta = $this->oferta(autoriza: false);

        $enlace = WhatsApp::enlace($oferta->feriante, $oferta);

        $this->assertStringStartsWith('https://wa.me/5493700000000?text=', $enlace);
        $this->assertStringNotContainsString('5493704123456', $enlace); // el número del feriante no aparece
        $this->assertStringContainsString('quiero contactar a José Peña', WhatsApp::mensaje($oferta->feriante, $oferta));
        $this->assertFalse(WhatsApp::esDirecto($oferta->feriante));
    }

    #[Test]
    public function sin_numero_de_comercializacion_no_hay_enlace(): void
    {
        config(['services.paippa.whatsapp_comercializacion' => null]);

        $this->assertNull(WhatsApp::enlace($this->oferta(autoriza: false)->feriante));
    }
}
