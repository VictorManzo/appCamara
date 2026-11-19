<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\Fotos;
use App\Models\ProductoModel;

class Productos extends BaseController
{
    /** Lista completa (los vendedores no reciben las notas). */
    public function index()
    {
        $esAdmin = session()->get('rol') === 'admin';
        $filas = (new ProductoModel())->orderBy('created_at', 'DESC')->findAll();
        return $this->response->setJSON(array_map(static fn ($p) => ProductoModel::aJson($p, $esAdmin), $filas));
    }

    public function crear()
    {
        $datos = $this->campos();
        $datos['cantidad'] = max(1, (int) ($this->request->getPost('cantidad') ?? 1));
        $datos['vendidos'] = 0;
        $foto = $this->fotoSubida();
        if ($foto instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $foto;
        }
        if ($foto) {
            $datos += $foto;
        }
        $m = new ProductoModel();
        $id = $m->insert($datos, true);
        return $this->response->setJSON(ProductoModel::aJson($m->find($id)));
    }

    public function actualizar(int $id)
    {
        $m = new ProductoModel();
        $p = $m->find($id);
        if (! $p) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Ese producto ya no existe.']);
        }
        $datos = $this->campos();
        if ($this->request->getPost('cantidad') !== null) {
            $datos['cantidad'] = max(0, (int) $this->request->getPost('cantidad'));
        }
        $foto = $this->fotoSubida();
        if ($foto instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $foto;
        }
        if ($foto) {
            $datos += $foto;
        }
        $m->update($id, $datos);
        if ($foto) {
            Fotos::borrar($p['foto'], $p['mini']);
        }
        return $this->response->setJSON(ProductoModel::aJson($m->find($id)));
    }

    /** "Es el mismo: sumar" al dar de alta un repetido. */
    public function sumar(int $id)
    {
        $m = new ProductoModel();
        $p = $m->find($id);
        if (! $p) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Ese producto ya no existe.']);
        }
        $n = max(1, (int) $this->request->getPost('cantidad'));
        $db = db_connect();
        $db->query('UPDATE productos SET cantidad = cantidad + ?, updated_at = ? WHERE id = ?', [$n, date('Y-m-d H:i:s'), $id]);
        $extra = [];
        $precio = $this->precio($this->request->getPost('precio'));
        if ($precio !== null) {
            $extra['precio'] = $precio;
        }
        if (! $p['foto']) {
            $foto = $this->fotoSubida();
            if (is_array($foto)) {
                $extra += $foto;
            }
        }
        if ($extra) {
            $m->update($id, $extra);
        }
        return $this->response->setJSON(ProductoModel::aJson($m->find($id)));
    }

    public function eliminar(int $id)
    {
        $m = new ProductoModel();
        $p = $m->find($id);
        if ($p) {
            $m->delete($id);
            Fotos::borrar($p['foto'], $p['mini']);
        }
        return $this->response->setJSON(['ok' => true]);
    }

    // ---------------------------------------------------------------

    private function campos(): array
    {
        $r = $this->request;
        $txt = static fn ($k, $max) => mb_substr(trim(strip_tags((string) $r->getPost($k))), 0, $max);
        $nombre = $txt('nombre', 200);
        $out = [
            'nombre'        => $nombre,
            'pendiente'     => $nombre === '' ? 1 : 0,
            'serie'         => $txt('serie', 150),
            'numero'        => $txt('numero', 40),
            'color'         => $txt('color', 200),
            'anio'          => $txt('anio', 10),
            'precio'        => $this->precio($r->getPost('precio')),
            'precio_antes'  => $this->precio($r->getPost('precioAntes')),
            'dueno'         => $r->getPost('dueno') === 'L' ? 'L' : 'V',
            'notas'         => mb_substr(trim(strip_tags((string) $r->getPost('notas'))), 0, 2000),
        ];
        if ($r->getPost('esHotWheels') !== null) {
            $out['es_hot_wheels'] = $r->getPost('esHotWheels') === '0' ? 0 : 1;
        }
        return $out;
    }

    private function precio($v): ?float
    {
        $v = str_replace([',', '$', ' '], ['.', '', ''], (string) $v);
        return is_numeric($v) && (float) $v > 0 ? round((float) $v, 2) : null;
    }

    /** @return array|null|\CodeIgniter\HTTP\ResponseInterface */
    private function fotoSubida()
    {
        $f = $this->request->getFile('foto');
        if (! $f || $f->getError() === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (! $f->isValid() || $f->getSize() > 20 * 1024 * 1024) {
            return $this->response->setStatusCode(422)->setJSON(['error' => 'No se pudo subir la foto. Intenta con otra o más pequeña.']);
        }
        try {
            return Fotos::guardarDesdeArchivo($f->getTempName());
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(422)->setJSON(['error' => $e->getMessage()]);
        }
    }
}
