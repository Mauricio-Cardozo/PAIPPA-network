<?php

namespace Tests\Unit;

use App\Models\Feriante;
use App\Models\Oferta;
use App\Models\Producto;
use App\Models\PuntoRetiro;
use App\Support\WhatsApp;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class WhatsAppTest extends TestCase
{
    private function oferta(): Oferta
    {
        $feriante = new Feriante(['nombre' => 'José Peña', 'telefono_whatsapp' => '5493704123456']);
        $oferta = new Oferta(['precio_caja' => 12500]);
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
    public function el_mensaje_incluye_producto_precio_y_punto_de_retiro(): void
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
        $feriante = new Feriante(['nombre' => 'Ana', 'telefono_whatsapp' => '5493704000000']);

        $this->assertStringContainsString('Hola Ana', WhatsApp::mensaje($feriante));
    }
}
