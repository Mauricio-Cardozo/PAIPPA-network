<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Una consulta de un producto (desde una búsqueda del catálogo o desde un
 * clic de WhatsApp). Sirve para «productos más consultados» (HU15).
 */
#[Fillable(['producto_id', 'user_id', 'visitante', 'origen', 'fecha'])]
class ConsultaProducto extends Model
{
    public const ORIGEN_BUSQUEDA = 'busqueda';

    public const ORIGEN_WHATSAPP = 'whatsapp';

    protected $table = 'consultas_producto';

    const UPDATED_AT = null;

    /*
     * La fecha se guarda solo como Y-m-d (sin hora), igual que la vigencia de
     * las ofertas, para que el índice único y las comparaciones funcionen
     * igual en MySQL y en SQLite.
     */
    protected function fecha(): Attribute
    {
        return Attribute::make(
            get: fn (?string $valor) => $valor === null ? null : Carbon::parse($valor)->startOfDay(),
            set: fn ($valor) => Carbon::parse($valor)->toDateString(),
        );
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    /**
     * Registra una consulta por cada producto. insertOrIgnore() saltea las que
     * ya existen hoy para ese visitante (el índice único), sin dar error y sin
     * contar dos veces.
     *
     * @param  Collection<int, int>|array<int>  $productoIds
     */
    public static function registrar(Collection|array $productoIds, string $origen): void
    {
        $user = auth()->user();
        // Un usuario se identifica por su id; un visitante, por un hash de su sesión.
        $visitante = $user ? 'user:'.$user->id : hash('sha256', session()->getId());
        $hoy = today()->toDateString();
        $ahora = now();

        $filas = collect($productoIds)->unique()->take(12)->map(fn (int $id) => [
            'producto_id' => $id,
            'user_id' => $user?->id,
            'visitante' => $visitante,
            'origen' => $origen,
            'fecha' => $hoy,
            'created_at' => $ahora,
        ])->values()->all();

        if ($filas !== []) {
            static::insertOrIgnore($filas);
        }
    }
}
