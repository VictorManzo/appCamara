<?php

namespace App\Controllers;

use App\Models\AjusteModel;
use App\Models\ProductoModel;

/** Catálogo público: lo ve cualquiera, sin cuenta. */
class Catalogo extends BaseController
{
    public function index()
    {
        \App\Libraries\Esquema::asegurar();
        $productos = [];
        try {
            $filas = (new ProductoModel())
                ->where('cantidad >', 0)->where('pendiente', 0)->where('nombre !=', '')
                ->orderBy('created_at', 'DESC')->findAll();
            foreach ($filas as $f) {
                $p = ProductoModel::aJson($f, false);
                unset($p['dueno'], $p['huella']);
                $productos[] = $p;
            }
            $contacto = (new AjusteModel())->leer('contacto');
        } catch (\Throwable $e) {
            $contacto = '';
        }
        return view('catalogo', [
            'productos' => $productos,
            'contacto'  => $contacto,
            'logueado'  => (bool) session()->get('usuario_id'),
        ]);
    }
}
