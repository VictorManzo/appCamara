<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class Ventas extends BaseController
{
    /** Últimas 60 ventas con el nombre de quien vendió. */
    public function index()
    {
        $filas = db_connect()->table('ventas v')
            ->select('v.id, v.grupo, v.producto_id, v.nombre, v.cantidad, v.total, v.usuario_id, v.created_at, u.nombre AS vendedor')
            ->join('usuarios u', 'u.id = v.usuario_id', 'left')
            ->orderBy('v.created_at', 'DESC')->orderBy('v.id', 'DESC')->limit(60)->get()->getResultArray();
        return $this->response->setJSON(array_map(static fn ($v) => [
            'id'        => (int) $v['id'],
            'grupo'     => $v['grupo'],
            'nombre'    => $v['nombre'],
            'cantidad'  => (int) $v['cantidad'],
            'total'     => $v['total'] !== null ? (float) $v['total'] : null,
            'vendedor'  => $v['vendedor'],
            'usuarioId' => $v['usuario_id'] !== null ? (int) $v['usuario_id'] : null,
            'fecha'     => strtotime($v['created_at']) * 1000,
        ], $filas));
    }

    /**
     * Registra una venta con varios productos en una sola transacción.
     * Cuerpo JSON: {"items":[{"id":12,"cantidad":2}, ...]}
     */
    public function registrar()
    {
        $body  = $this->request->getJSON(true) ?? [];
        $items = [];
        foreach ((array) ($body['items'] ?? []) as $it) {
            $id = (int) ($it['id'] ?? 0);
            $n  = (int) ($it['cantidad'] ?? 0);
            if ($id > 0 && $n > 0) {
                $items[$id] = ($items[$id] ?? 0) + $n;
            }
        }
        if (! $items) {
            return $this->response->setStatusCode(422)->setJSON(['error' => 'La venta está vacía.']);
        }

        \App\Libraries\Esquema::asegurar();
        $jornada = Jornadas::abierta((int) session()->get('usuario_id'));
        if (! $jornada) {
            return $this->response->setStatusCode(409)->setJSON(['error' => 'Primero inicia tu jornada (con o sin cambio).', 'sinJornada' => true]);
        }

        $db = db_connect();
        $grupo = 'v' . bin2hex(random_bytes(6));
        $ahora = date('Y-m-d H:i:s');
        $usuario = (int) session()->get('usuario_id');
        $piezas = 0; $total = 0.0; $faltan = [];

        $db->transStart();
        foreach ($items as $id => $n) {
            // FOR UPDATE: evita que dos vendedores vendan la misma última pieza a la vez.
            $bloqueo = $db->DBDriver === 'MySQLi' ? ' FOR UPDATE' : '';
            $p = $db->query('SELECT id, nombre, cantidad, precio FROM productos WHERE id = ?' . $bloqueo, [$id])->getRowArray();
            if (! $p) {
                continue;
            }
            $vend = min($n, (int) $p['cantidad']);
            if ($vend < $n) {
                $faltan[] = $p['nombre'];
            }
            if ($vend <= 0) {
                continue;
            }
            $db->query('UPDATE productos SET cantidad = cantidad - ?, vendidos = vendidos + ?, updated_at = ? WHERE id = ?', [$vend, $vend, $ahora, $id]);
            $precio = $p['precio'] !== null ? (float) $p['precio'] : null;
            $sub = $precio !== null ? round($precio * $vend, 2) : null;
            $db->table('ventas')->insert([
                'grupo' => $grupo, 'producto_id' => $id, 'nombre' => $p['nombre'] ?: 'Sin nombre', 'cantidad' => $vend,
                'precio' => $precio, 'total' => $sub, 'usuario_id' => $usuario ?: null, 'created_at' => $ahora,
                'jornada_id' => (int) $jornada['id'],
            ]);
            $piezas += $vend; $total += (float) $sub;
        }
        $db->transComplete();

        if (! $db->transStatus()) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'No se pudo registrar la venta. Intenta de nuevo.']);
        }
        return $this->response->setJSON(['grupo' => $grupo, 'piezas' => $piezas, 'total' => round($total, 2), 'faltan' => $faltan]);
    }

    /** Deshace una venta completa (el admin siempre; un vendedor, solo las suyas de los últimos 30 minutos). */
    public function deshacer()
    {
        $grupo = (string) ($this->request->getJSON(true)['grupo'] ?? '');
        $db = db_connect();
        $filas = $db->table('ventas')->where('grupo', $grupo)->get()->getResultArray();
        if (! $filas) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Esa venta ya no existe.']);
        }
        $s = session();
        if ($s->get('rol') !== 'admin') {
            $mia = (int) $filas[0]['usuario_id'] === (int) $s->get('usuario_id');
            $reciente = strtotime($filas[0]['created_at']) > time() - 1800;
            if (! $mia || ! $reciente) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'Solo puedes deshacer tus ventas de los últimos 30 minutos.']);
            }
        }
        // una jornada ya finalizada no se modifica
        if (! empty($filas[0]['jornada_id'])) {
            $j = $db->table('jornadas')->where('id', $filas[0]['jornada_id'])->get()->getRowArray();
            if ($j && $j['estado'] !== 'abierta') {
                return $this->response->setStatusCode(409)->setJSON(['error' => 'Esa venta es de una jornada ya finalizada; no se puede deshacer.']);
            }
        }
        $db->transStart();
        foreach ($filas as $v) {
            if ($v['producto_id']) {
                $db->query('UPDATE productos SET cantidad = cantidad + ?, vendidos = CASE WHEN vendidos > ? THEN vendidos - ? ELSE 0 END, updated_at = ? WHERE id = ?',
                    [$v['cantidad'], $v['cantidad'], $v['cantidad'], date('Y-m-d H:i:s'), $v['producto_id']]);
            }
        }
        $db->table('ventas')->where('grupo', $grupo)->delete();
        $db->transComplete();
        return $this->response->setJSON(['ok' => $db->transStatus()]);
    }
}
