<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Seeder;

/**
 * Categorías y productos de ejemplo. Se reemplazan por los reales
 * cuando esté el relevamiento con el PAIPPA (PAI-11).
 */
class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $catalogo = [
            'Frutas' => [['Banana', 'caja'], ['Naranja', 'bolsa'], ['Mandarina', 'bolsa'], ['Pomelo', 'bolsa'], ['Sandía', 'unidad']],
            'Verduras' => [['Tomate', 'caja'], ['Zapallo', 'unidad'], ['Mandioca', 'bolsa'], ['Lechuga', 'atado'], ['Cebolla de verdeo', 'atado'], ['Pimiento', 'caja'], ['Batata', 'bolsa']],
            'Huevos' => [['Huevos de campo', 'maple']],
            'Lácteos' => [['Queso criollo', 'kilo'], ['Dulce de leche casero', 'frasco']],
            'Miel' => [['Miel pura', 'frasco']],
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
