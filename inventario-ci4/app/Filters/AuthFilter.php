<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Exige sesión iniciada. Con el argumento "admin" (auth:admin) solo deja pasar al dueño.
 * Las rutas /api responden JSON 401/403; las páginas redirigen al login.
 */
class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $s = session();
        $esApi = str_starts_with(trim($request->getUri()->getPath(), '/'), 'api') || str_contains($request->getUri()->getPath(), '/api/');

        if (! $s->get('usuario_id')) {
            return $esApi
                ? service('response')->setStatusCode(401)->setJSON(['error' => 'Tu sesión terminó. Vuelve a entrar.'])
                : redirect()->to(site_url('entrar'));
        }
        if ($arguments && in_array('admin', $arguments, true) && $s->get('rol') !== 'admin') {
            return $esApi
                ? service('response')->setStatusCode(403)->setJSON(['error' => 'Solo el administrador puede hacer esto.'])
                : redirect()->to(site_url('panel') . '#vender');
        }
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
