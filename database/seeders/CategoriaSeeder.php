<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Seeder;

/**
 * Categorías y productos del relevamiento con el PAIPPA:
 * - Categorías de la ERS (RF-007): verduras de hoja, hortalizas, frutas,
 *   huevos, elaborados, y carnes y granja (feriantes 2).
 * - Productos más vendidos según el PAIPPA (PAIPPA 4): mandioca, batata,
 *   banana, lechuga, acelga, verdeos y zapallito de tronco, más los que
 *   nombraron los feriantes (huevos, elaborados, pollo casero y cerdo).
 * - Unidades de venta de la encuesta (feriantes 3): kilo, atado, bolsa,
 *   caja, docena y unidad.
 */
class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $catalogo = [
            'Verduras de hoja' => [['Lechuga', 'atado'], ['Acelga', 'atado'], ['Cebolla de verdeo', 'atado']],
            'Hortalizas' => [['Mandioca', 'kilo'], ['Batata', 'kilo'], ['Zapallito de tronco', 'kilo'], ['Tomate', 'caja']],
            'Frutas' => [['Banana', 'caja'], ['Mandarina', 'bolsa']],
            'Huevos' => [['Huevos de campo', 'docena']],
            'Elaborados' => [['Dulce de leche casero', 'frasco'], ['Queso criollo', 'kilo']],
            'Carnes y granja' => [['Pollo casero', 'unidad'], ['Carne de cerdo', 'kilo']],
        ];

        foreach ($catalogo as $nombreCategoria => $productos) {
            $categoria = Categoria::firstOrCreate(['nombre' => $nombreCategoria]);

            foreach ($productos as [$nombre, $unidad]) {
                Producto::firstOrCreate(
                    ['categoria_id' => $categoria->id, 'nombre' => $nombre],
                    ['unidad' => $unidad],
                );
            }
        }
    }
}
