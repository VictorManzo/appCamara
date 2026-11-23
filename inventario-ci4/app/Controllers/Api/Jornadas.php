<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\Avisos;
use App\Libraries\Esquema;

/**
 * Jornadas de venta: abrir (con o sin cambio), ver la jornada actual con sus ventas,
 * finalizar (avisa por Telegram/correo) e historial.
 */
class Jornadas extends BaseController
{
    /** Jornada abierta del usuario, con sus ventas y totales (null si no tiene). */
    public function actual()
    {
        Esquema::asegurar();
        $j = self::abierta((int) session()->get('usuario_id'));
        return $this->response->setJSON(['jornada' => $j ? self::conVentas($j) : null]);
    }

    public function abrir()
    {
        Esquema::asegurar();
        $uid = (int) session()->get('usuario_id');
        if ($j = self::abierta($uid)) {
            return $this->response->setJSON(['jornada' => self::conVentas($j)]);
        }
        $r = $this->request->getJSON(true) ?? [];
        $fondo = round(max(0, min(1000000, (float) str_replace([',', '$', ' '], ['', '', ''], (string) ($r['fondo'] ?? 0)))), 2);
        $db = db_connect();
        $db->table('jornadas')->insert(['usuario_id' => $uid, 'fondo' => $fondo, 'inicio' => date('Y-m-d H:i:s'), 'estado' => 'abierta']);
        return $this->response->setJSON(['jornada' => self::conVentas(self::abierta($uid))]);
    }

    public function cerrar()
    {
        $s = session();
        $uid = (int) $s->get('usuario_id');
        $j = self::abierta($uid);
        if (! $j) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'No tienes una jornada abierta.']);
        }
        $j = self::conVentas($j);
        $fin = date('Y-m-d H:i:s');
        db_connect()->table('jornadas')->where('id', $j['id'])->update([
            'fin' => $fin, 'estado' => 'cerrada', 'total_ventas' => $j['total'], 'piezas' => $j['piezas'], 'num_ventas' => $j['numVentas'],
        ]);
        $j['fin'] = strtotime($fin) * 1000; $j['estado'] = 'cerrada';

        $texto = self::textoCierre($j, (string) $s->get('nombre'));
        $avisos = Avisos::enviar('Jornada finalizada · ' . $s->get('nombre') . ' · ' . self::dinero($j['total']), $texto);
        return $this->response->setJSON(['jornada' => $j, 'avisos' => $avisos]);
    }

    /**
     * Historial de jornadas (admin: todas; vendedor: las suyas), las más recientes primero.
     * ?dias=30 limita el periodo.
     */
    public function historial()
    {
        Esquema::asegurar();
        $s = session();
        $dias = max(1, min(366, (int) ($this->request->getGet('dias') ?? 31)));
        $q = db_connect()->table('jornadas j')
            ->select('j.*, u.nombre AS vendedor')
            ->join('usuarios u', 'u.id = j.usuario_id', 'left')
            ->where('j.inicio >=', date('Y-m-d 00:00:00', strtotime('-' . ($dias - 1) . ' days')))
            ->orderBy('j.inicio', 'DESC')->limit(400);
        if ($s->get('rol') !== 'admin') {
            $q->where('j.usuario_id', (int) $s->get('usuario_id'));
        }
        $filas = $q->get()->getResultArray();
        $out = [];
        foreach ($filas as $j) {
            // las abiertas se calculan al momento
            if ($j['estado'] === 'abierta') {
                $t = self::totales((int) $j['id']);
                $j['total_ventas'] = $t['total']; $j['piezas'] = $t['piezas']; $j['num_ventas'] = $t['numVentas'];
            }
            $out[] = [
                'id' => (int) $j['id'], 'vendedor' => $j['vendedor'] ?: 'Usuario eliminado', 'usuarioId' => (int) $j['usuario_id'],
                'fondo' => (float) $j['fondo'], 'inicio' => strtotime($j['inicio']) * 1000, 'fin' => $j['fin'] ? strtotime($j['fin']) * 1000 : null,
                'total' => (float) $j['total_ventas'], 'piezas' => (int) $j['piezas'], 'numVentas' => (int) $j['num_ventas'], 'estado' => $j['estado'],
                'porDueno' => self::porDueno((int) $j['id']),
            ];
        }
        return $this->response->setJSON($out);
    }

    public function detalle(int $id)
    {
        $s = session();
        $j = db_connect()->table('jornadas j')->select('j.*, u.nombre AS vendedor')->join('usuarios u', 'u.id = j.usuario_id', 'left')
            ->where('j.id', $id)->get()->getRowArray();
        if (! $j || ($s->get('rol') !== 'admin' && (int) $j['usuario_id'] !== (int) $s->get('usuario_id'))) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'No encontré esa jornada.']);
        }
        $out = self::conVentas($j);
        $out['vendedor'] = $j['vendedor'] ?: 'Usuario eliminado';
        return $this->response->setJSON($out);
    }

    // ---------------------------------------------------------------

    public static function abierta(int $uid): ?array
    {
        if (! $uid) {
            return null;
        }
        return db_connect()->table('jornadas')->where('usuario_id', $uid)->where('estado', 'abierta')
            ->orderBy('id', 'DESC')->get(1)->getRowArray() ?: null;
    }

    /** Lo vendido de cada dueño en la jornada: ['L' => 0.0, 'V' => 0.0]. */
    public static function porDueno(int $jid): array
    {
        $out = ['L' => 0.0, 'V' => 0.0];
        $filas = db_connect()->table('ventas v')
            ->select("COALESCE(v.dueno, p.dueno, 'V') AS d, COALESCE(SUM(v.total),0) AS t, COALESCE(SUM(v.cantidad),0) AS n", false)
            ->join('productos p', 'p.id = v.producto_id', 'left')
            ->where('v.jornada_id', $jid)->groupBy("COALESCE(v.dueno, p.dueno, 'V')", false)->get()->getResultArray();
        foreach ($filas as $f) {
            $out[$f['d'] === 'L' ? 'L' : 'V'] += round((float) $f['t'], 2);
        }
        return $out;
    }

    private static function totales(int $jid): array
    {
        $r = db_connect()->table('ventas')->select('COALESCE(SUM(total),0) AS total, COALESCE(SUM(cantidad),0) AS piezas, COUNT(DISTINCT grupo) AS num')
            ->where('jornada_id', $jid)->get()->getRowArray();
        return ['total' => round((float) $r['total'], 2), 'piezas' => (int) $r['piezas'], 'numVentas' => (int) $r['num']];
    }

    private static function conVentas(array $j): array
    {
        $ventas = db_connect()->table('ventas')->where('jornada_id', $j['id'])->orderBy('created_at', 'DESC')->orderBy('id', 'DESC')->get()->getResultArray();
        $t = self::totales((int) $j['id']);
        return [
            'id' => (int) $j['id'], 'fondo' => (float) $j['fondo'],
            'inicio' => strtotime($j['inicio']) * 1000, 'fin' => $j['fin'] ? strtotime($j['fin']) * 1000 : null,
            'estado' => $j['estado'], 'total' => $t['total'], 'piezas' => $t['piezas'], 'numVentas' => $t['numVentas'],
            'porDueno' => self::porDueno((int) $j['id']),
            'ventas' => array_map(static fn ($v) => [
                'id' => (int) $v['id'], 'grupo' => $v['grupo'], 'nombre' => $v['nombre'], 'cantidad' => (int) $v['cantidad'],
                'precio' => $v['precio'] !== null ? (float) $v['precio'] : null, 'total' => $v['total'] !== null ? (float) $v['total'] : null,
                'fecha' => strtotime($v['created_at']) * 1000,
            ], $ventas),
        ];
    }

    private static function dinero(float $v): string
    {
        return '$' . number_format($v, $v == floor($v) ? 0 : 2);
    }

    private static function textoCierre(array $j, string $vendedor): string
    {
        $h = static fn ($ms) => date('d/m/Y H:i', (int) ($ms / 1000));
        $l = [
            '✅ Jornada finalizada · Juguetería PETUS', '',
            "Vendedor: {$vendedor}",
            'Inicio: ' . $h($j['inicio']), 'Fin: ' . $h($j['fin']), '',
            "Ventas: {$j['numVentas']} · Piezas: {$j['piezas']}",
            'Vendido: ' . self::dinero($j['total']),
            '   De Lupita (L): ' . self::dinero($j['porDueno']['L']),
            '   De Victor (V): ' . self::dinero($j['porDueno']['V']),
            'Cambio con el que empezó: ' . self::dinero($j['fondo']),
            'Total que debe entregar: ' . self::dinero($j['total'] + $j['fondo']),
        ];
        if ($j['ventas']) {
            $l[] = '';
            $l[] = 'Detalle:';
            $res = [];
            foreach (array_reverse($j['ventas']) as $v) {
                $k = $v['nombre'];
                $res[$k] = ($res[$k] ?? ['n' => 0, 't' => 0.0]);
                $res[$k]['n'] += $v['cantidad']; $res[$k]['t'] += (float) $v['total'];
            }
            foreach ($res as $nombre => $x) {
                $l[] = "• {$x['n']} × {$nombre} — " . self::dinero($x['t']);
            }
        }
        return implode("\n", $l);
    }
}
