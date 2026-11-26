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
            'aviso'    => [
                'responsable' => (new AjusteModel())->leer('aviso_responsable'),
                'domicilio'   => (new AjusteModel())->leer('aviso_domicilio'),
                'correo'      => (new AjusteModel())->leer('aviso_correo'),
            ],
        ]);
    }
}
