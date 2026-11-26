<?php

namespace App\Controllers;

use App\Libraries\Avisos;
use App\Libraries\Esquema;

/**
 * Pedidos del catálogo público.
 * POST /pedido (cualquiera): guarda el pedido y avisa al dueño.
 * /api/pedidos... (solo admin): lista, cambiar estado, configurar y probar avisos.
 */
class Pedidos extends BaseController
{
    public function crear()
    {
        Esquema::asegurar();
        $r = $this->request->getJSON(true) ?? [];

        // trampa para robots: campo invisible que una persona deja vacío
        if (! empty($r['sitio_web'])) {
            return $this->response->setJSON(['ok' => true, 'codigo' => '0000']);
        }
        $cliente  = mb_substr(trim(strip_tags((string) ($r['cliente'] ?? ''))), 0, 120);
        $telefono = mb_substr(trim(preg_replace('/[^0-9+ ()-]/', '', (string) ($r['telefono'] ?? ''))), 0, 40);
        $nota     = mb_substr(trim(strip_tags((string) ($r['nota'] ?? ''))), 0, 500);
        if ($cliente === '' || strlen(preg_replace('/\D/', '', $telefono)) < 8) {
            return $this->error('Escribe tu nombre y un teléfono de contacto.');
        }
        if (empty($r['acepta_aviso'])) {
            return $this->error('Para enviar tu pedido acepta el Aviso de privacidad.');
        }

        $db = db_connect();
        $ip = $this->request->getIPAddress();
        $recientes = $db->table('pedidos')->where('ip', $ip)->where('created_at >', date('Y-m-d H:i:s', time() - 600))->countAllResults();
        if ($recientes >= 5) {
            return $this->error('Ya enviaste varios pedidos. Espera unos minutos o contáctanos directamente.', 429);
        }

        // los precios y existencias salen de la base de datos, nunca del navegador
        $pedidos = [];
        foreach ((array) ($r['items'] ?? []) as $it) {
            $id = (int) ($it['id'] ?? 0); $n = (int) ($it['cantidad'] ?? 0);
            if ($id > 0 && $n > 0) {
                $pedidos[$id] = min(99, ($pedidos[$id] ?? 0) + $n);
            }
        }
        if (! $pedidos) {
            return $this->error('Tu pedido está vacío.');
        }
        $filas = $db->table('productos')->whereIn('id', array_keys($pedidos))->where('cantidad >', 0)->get()->getResultArray();
        $items = []; $total = 0.0; $sinPrecio = 0;
        foreach ($filas as $p) {
            $n = min($pedidos[(int) $p['id']], (int) $p['cantidad']);
            $precio = $p['precio'] !== null && (float) $p['precio'] > 0 ? (float) $p['precio'] : null;
            $items[] = ['id' => (int) $p['id'], 'nombre' => $p['nombre'], 'numero' => $p['numero'], 'dueno' => $p['dueno'], 'cantidad' => $n, 'precio' => $precio];
            if ($precio === null) { $sinPrecio++; } else { $total += $precio * $n; }
        }
        if (! $items) {
            return $this->error('Los productos de tu pedido ya no están disponibles.');
        }

        $codigo = strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
        $db->table('pedidos')->insert([
            'codigo' => $codigo, 'cliente' => $cliente, 'telefono' => $telefono, 'nota' => $nota,
            'items' => json_encode($items, JSON_UNESCAPED_UNICODE), 'total' => round($total, 2),
            'estado' => 'nuevo', 'ip' => $ip, 'created_at' => date('Y-m-d H:i:s'), 'aviso_aceptado' => date('Y-m-d H:i:s'),
        ]);

        $texto = self::textoPedido($codigo, $cliente, $telefono, $nota, $items, $total, $sinPrecio, true);
        Avisos::enviar('Pedido ' . $codigo . ' de ' . $cliente . ' · ' . self::dinero($total), $texto);
        // lo que ve el cliente no lleva la letra del dueño
        $paraCliente = self::textoPedido($codigo, $cliente, $telefono, $nota, $items, $total, $sinPrecio, false);
        $itemsCliente = array_map(static function ($it) { unset($it['dueno']); return $it; }, $items);

        return $this->response->setJSON([
            'ok' => true, 'codigo' => $codigo, 'total' => round($total, 2), 'sinPrecio' => $sinPrecio,
            'items' => $itemsCliente, 'whatsapp' => Avisos::enlaceWhatsApp($paraCliente),
        ]);
    }

    // ---------------- administración ----------------

    public function lista()
    {
        Esquema::asegurar();
        $filas = db_connect()->table('pedidos')->orderBy('created_at', 'DESC')->limit(100)->get()->getResultArray();
        return $this->response->setJSON(array_map(static fn ($p) => [
            'id' => (int) $p['id'], 'codigo' => $p['codigo'], 'cliente' => $p['cliente'], 'telefono' => $p['telefono'],
            'nota' => $p['nota'], 'items' => json_decode($p['items'], true) ?: [], 'total' => (float) $p['total'],
            'estado' => $p['estado'], 'fecha' => strtotime($p['created_at']) * 1000,
        ], $filas));
    }

    public function estado(int $id)
    {
        $e = (string) ($this->request->getJSON(true)['estado'] ?? '');
        if (! in_array($e, ['nuevo', 'atendido', 'cancelado'], true)) {
            return $this->error('Estado no válido.');
        }
        db_connect()->table('pedidos')->where('id', $id)->update(['estado' => $e]);
        return $this->lista();
    }

    public function avisos()
    {
        $c = Avisos::config();
        return $this->response->setJSON([
            'email' => $c['email'], 'whatsapp' => $c['whatsapp'], 'tgChat' => $c['tgChat'],
            'tgToken' => $c['tgToken'] !== '' ? '••••' . substr($c['tgToken'], -4) : '',
        ]);
    }

    public function guardarAvisos()
    {
        $r = $this->request->getJSON(true) ?? [];
        $a = new \App\Models\AjusteModel();
        $email = trim((string) ($r['email'] ?? ''));
        if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->error('Ese correo no parece válido.');
        }
        $a->guardar('aviso_email', $email);
        $a->guardar('aviso_whatsapp', preg_replace('/[^0-9+]/', '', (string) ($r['whatsapp'] ?? '')));
        $token = trim((string) ($r['tgToken'] ?? ''));
        if ($token !== '' && ! str_starts_with($token, '••••')) {
            if (! preg_match('/^\d+:[A-Za-z0-9_-]{20,}$/', $token)) {
                return $this->error('El token de Telegram no tiene el formato correcto (números:letras).');
            }
            $a->guardar('aviso_tg_token', $token);
        } elseif ($token === '') {
            $a->guardar('aviso_tg_token', '');
        }
        $a->guardar('aviso_tg_chat', preg_replace('/[^0-9-]/', '', (string) ($r['tgChat'] ?? '')));
        return $this->avisos();
    }

    public function detectarChat()
    {
        $c = Avisos::config();
        $chat = Avisos::detectarChat($c['tgToken']);
        if (! $chat) {
            return $this->error('No encontré tu chat. Abre tu bot en Telegram, toca Iniciar y mándale "hola"; luego intenta otra vez.');
        }
        (new \App\Models\AjusteModel())->guardar('aviso_tg_chat', $chat['id']);
        return $this->response->setJSON(['tgChat' => $chat['id'], 'nombre' => $chat['nombre']]);
    }

    public function probar()
    {
        $res = Avisos::enviar('Prueba de avisos · Catálogo PETUS', "Prueba de avisos del catálogo de Juguetería PETUS.\nSi lees esto, los pedidos te van a llegar aquí.");
        return $this->response->setJSON($res);
    }

    // ---------------------------------------------------------------

    private static function dinero(float $v): string
    {
        return '$' . number_format($v, $v == floor($v) ? 0 : 2);
    }

    private static function textoPedido(string $codigo, string $cliente, string $tel, string $nota, array $items, float $total, int $sinPrecio, bool $conDueno): string
    {
        $l = ["🛒 Pedido {$codigo} · Juguetería PETUS", '', "Cliente: {$cliente}", "Teléfono: {$tel}"];
        if ($nota !== '') {
            $l[] = "Nota: {$nota}";
        }
        $l[] = '';
        foreach ($items as $it) {
            $sub = $it['precio'] !== null ? self::dinero($it['precio'] * $it['cantidad']) : 'precio a consultar';
            $l[] = "• {$it['cantidad']} × {$it['nombre']}" . ($it['numero'] ? " ({$it['numero']})" : '') . ($conDueno ? " [{$it['dueno']}]" : '') . " — {$sub}";
        }
        $l[] = '';
        $l[] = 'Total: ' . self::dinero($total) . ($sinPrecio ? " + {$sinPrecio} producto(s) con precio a consultar" : '');
        return implode("\n", $l);
    }

    private function error(string $msg, int $code = 422)
    {
        return $this->response->setStatusCode($code)->setJSON(['error' => $msg]);
    }
}
