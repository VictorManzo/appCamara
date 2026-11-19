<?php

namespace App\Controllers;

use App\Models\AjusteModel;

class Panel extends BaseController
{
    public function index()
    {
        \App\Libraries\Esquema::asegurar();
        $s = session();
        return view('panel', [
            'yo' => [
                'id'     => (int) $s->get('usuario_id'),
                'nombre' => (string) $s->get('nombre'),
                'rol'    => (string) $s->get('rol'),
            ],
            'contacto' => (new AjusteModel())->leer('contacto'),
        ]);
    }
}
