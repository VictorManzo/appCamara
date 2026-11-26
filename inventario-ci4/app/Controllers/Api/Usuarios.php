<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\AjusteModel;
use App\Models\UsuarioModel;

/** Alta y baja de vendedores, y el contacto del catálogo. Solo el administrador. */
class Usuarios extends BaseController
{
    public function index()
    {
        $filas = (new UsuarioModel())->orderBy('rol')->orderBy('nombre')->findAll();
        return $this->response->setJSON(array_map(static fn ($u) => [
            'id' => (int) $u['id'], 'nombre' => $u['nombre'], 'usuario' => $u['usuario'],
            'rol' => $u['rol'], 'activo' => (bool) $u['activo'],
        ], $filas));
    }

    public function crear()
    {
        $r = $this->request->getJSON(true) ?? [];
        $nombre  = trim((string) ($r['nombre'] ?? ''));
        $usuario = trim((string) ($r['usuario'] ?? ''));
        $pass    = (string) ($r['password'] ?? '');
        if ($nombre === '' || ! preg_match('/^[A-Za-z0-9._-]{3,60}$/', $usuario)) {
            return $this->error('Escribe el nombre y un usuario de al menos 3 letras o números, sin espacios.');
        }
        if (strlen($pass) < 8) {
            return $this->error('La contraseña debe tener al menos 8 caracteres.');
        }
        $m = new UsuarioModel();
        if ($m->where('usuario', $usuario)->countAllResults() > 0) {
            return $this->error('Ese usuario ya existe. Elige otro.');
        }
        $m->insert(['nombre' => $nombre, 'usuario' => $usuario, 'password_hash' => password_hash($pass, PASSWORD_DEFAULT), 'rol' => 'vendedor', 'activo' => 1]);
        return $this->index();
    }

    public function actualizar(int $id)
    {
        $m = new UsuarioModel();
        $u = $m->find($id);
        if (! $u) {
            return $this->error('Ese usuario no existe.', 404);
        }
        $r = $this->request->getJSON(true) ?? [];
        $datos = [];
        if (isset($r['password']) && $r['password'] !== '') {
            if (strlen((string) $r['password']) < 8) {
                return $this->error('La contraseña debe tener al menos 8 caracteres.');
            }
            $datos['password_hash'] = password_hash((string) $r['password'], PASSWORD_DEFAULT);
        }
        if (isset($r['activo']) && $u['rol'] !== 'admin') {
            $datos['activo'] = $r['activo'] ? 1 : 0;
        }
        if (isset($r['nombre']) && trim((string) $r['nombre']) !== '') {
            $datos['nombre'] = trim((string) $r['nombre']);
        }
        if ($datos) {
            $m->update($id, $datos);
        }
        return $this->index();
    }

    public function eliminar(int $id)
    {
        $m = new UsuarioModel();
        $u = $m->find($id);
        if ($u && $u['rol'] !== 'admin') {
            $m->delete($id); // sus ventas se quedan; aparecerán sin nombre de vendedor
        }
        return $this->index();
    }

    public function ajustes()
    {
        $r = $this->request->getJSON(true) ?? [];
        $a = new AjusteModel();
        $limpio = static fn ($k, $max) => mb_substr(trim(strip_tags((string) ($r[$k] ?? ''))), 0, $max);
        $a->guardar('contacto', $limpio('contacto', 300));
        foreach (['aviso_responsable' => 150, 'aviso_domicilio' => 300, 'aviso_correo' => 150] as $k => $max) {
            if (array_key_exists($k, $r)) {
                $a->guardar($k, $limpio($k, $max));
            }
        }
        return $this->response->setJSON(['ok' => true]);
    }

    private function error(string $msg, int $code = 422)
    {
        return $this->response->setStatusCode($code)->setJSON(['error' => $msg]);
    }
}
