<?php

namespace Tests\Feature;

use App\Models\Oferta;
use Database\Seeders\CategoriaSeeder;
use Database\Seeders\FeriaSeeder;
use Database\Seeders\VolumenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** PAI-65: el seeder de volumen sigue andando aunque cambie el modelo. */
class VolumenSeederTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function carga_2000_ofertas_y_el_catalogo_responde(): void
    {
        $this->seed([CategoriaSeeder::class, FeriaSeeder::class]);
        $antes = Oferta::count();

        $this->seed(VolumenSeeder::class);

        $this->assertSame($antes + VolumenSeeder::OFERTAS, Oferta::count());
        $this->get(route('catalogo'))->assertOk();
    }
}
