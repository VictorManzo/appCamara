<?php

namespace App\Controllers;

use App\Models\UsuarioModel;

class Auth extends BaseController
{
    /** Página principal: cada quien a su lugar. */
    public function inicio()
    {
        $s = session();
        if ($s->get('usuario_id')) {
            return redirect()->to(site_url('panel') . ($s->get('rol') === 'admin' ? '#inventario' : '#vender'));
        }
        return redirect()->to(site_url('catalogo'));
    }

    public function entrar()
    {
        if (! $this->tablasListas() || (new UsuarioModel())->countAllResults() === 0) {
            return redirect()->to(site_url('instalar'));
        }
        if (session()->get('usuario_id')) {
            return redirect()->to(site_url());
        }
        return view('entrar', ['error' => session()->getFlashdata('error')]);
    }

    public function procesarEntrada()
    {
        $s = session();
        // Freno sencillo contra adivinar contraseñas: 5 intentos, luego 5 minutos de espera.
        $intentos = (array) $s->get('intentos');
        $intentos = array_filter($intentos, static fn ($t) => $t > time() - 300);
        if (count($intentos) >= 5) {
            return redirect()->back()->with('error', 'Demasiados intentos. Espera 5 minutos e intenta de nuevo.');
        }

        $usuario = trim((string) $this->request->getPost('usuario'));
        $pass    = (string) $this->request->getPost('password');
        $u = (new UsuarioModel())->where('usuario', $usuario)->where('activo', 1)->first();

        if (! $u || ! password_verify($pass, $u['password_hash'])) {
            $intentos[] = time();
            $s->set('intentos', $intentos);
            return redirect()->back()->withInput()->with('error', 'Usuario o contraseña incorrectos.');
        }

        $s->regenerate(true);
        $s->set(['usuario_id' => (int) $u['id'], 'rol' => $u['rol'], 'nombre' => $u['nombre'], 'intentos' => []]);
        return redirect()->to(site_url('panel') . ($u['rol'] === 'admin' ? '#inventario' : '#vender'));
    }

    public function salir()
    {
        session()->destroy();
        return redirect()->to(site_url('entrar'));
    }

    /**
     * Primera vez: crea las tablas y la cuenta del administrador. Se bloquea sola en cuanto
     * existe el primer usuario.
     */
    public function instalar()
    {
        if ($this->tablasListas() && (new UsuarioModel())->countAllResults() > 0) {
            return redirect()->to(site_url('entrar'));
        }
        $error = null;
        if ($this->request->getMethod() === 'POST') {
            $nombre  = trim((string) $this->request->getPost('nombre'));
            $usuario = trim((string) $this->request->getPost('usuario'));
            $pass    = (string) $this->request->getPost('password');
            if ($nombre === '' || ! preg_match('/^[A-Za-z0-9._-]{3,60}$/', $usuario) || strlen($pass) < 8) {
                $error = 'Escribe tu nombre, un usuario de al menos 3 letras o números (sin espacios) y una contraseña de al menos 8 caracteres.';
            } else {
                try {
                    service('migrations')->setNamespace('App')->latest();
                    (new UsuarioModel())->insert([
                        'nombre' => $nombre, 'usuario' => $usuario,
                        'password_hash' => password_hash($pass, PASSWORD_DEFAULT), 'rol' => 'admin', 'activo' => 1,
                    ]);
                    return redirect()->to(site_url('entrar'))->with('error', 'Listo. Ya puedes entrar con tu usuario.');
                } catch (\Throwable $e) {
                    log_message('error', 'Instalación: ' . $e->getMessage());
                    $error = 'No se pudo crear la base de datos. Revisa los datos de MySQL en el archivo .env. Detalle: ' . $e->getMessage();
                }
            }
        }
        return view('instalar', ['error' => $error]);
    }

    private function tablasListas(): bool
    {
        try {
            return db_connect()->tableExists('usuarios');
        } catch (\Throwable $e) {
            return false;
        }
    }
}
