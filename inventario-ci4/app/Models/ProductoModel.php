<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductoModel extends Model
{
    protected $table         = 'productos';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'origen_id', 'nombre', 'pendiente', 'serie', 'numero', 'color', 'anio', 'cantidad',
        'precio', 'precio_antes', 'dueno', 'notas', 'foto', 'mini', 'huella', 'es_hot_wheels', 'vendidos',
        'created_at', 'updated_at',
    ];

    /** Forma que usa el JavaScript (mismos nombres que la app original). */
    public static function aJson(array $p, bool $conNotas = true): array
    {
        $fotos = base_url('fotos') . '/';
        $precio = $p['precio'] !== null ? (float) $p['precio'] : null;
        $antes  = $p['precio_antes'] !== null ? (float) $p['precio_antes'] : null;
        $out = [
            'id'          => (int) $p['id'],
            'nombre'      => (string) $p['nombre'],
            'pendiente'   => (bool) $p['pendiente'],
            'serie'       => (string) $p['serie'],
            'numero'      => (string) $p['numero'],
            'color'       => (string) $p['color'],
            'anio'        => (string) $p['anio'],
            'cantidad'    => (int) $p['cantidad'],
            'precio'      => $precio > 0 ? $precio : null,
            'precioAntes' => $antes > 0 ? $antes : null,
            'dueno'       => $p['dueno'] === 'L' ? 'L' : 'V',
            'foto'        => $p['foto'] ? $fotos . $p['foto'] : null,
            'mini'        => $p['mini'] ? $fotos . $p['mini'] : ($p['foto'] ? $fotos . $p['foto'] : null),
            'esHotWheels' => (bool) $p['es_hot_wheels'],
            'vendidos'    => (int) $p['vendidos'],
            'creado'      => $p['created_at'] ? strtotime($p['created_at']) * 1000 : 0,
            'huella'      => $p['huella'] ?? null,
        ];
        if ($conNotas) {
            $out['notas'] = (string) ($p['notas'] ?? '');
        }
        return $out;
    }
}
