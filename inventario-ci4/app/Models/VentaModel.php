<?php

namespace App\Models;

use CodeIgniter\Model;

class VentaModel extends Model
{
    protected $table         = 'ventas';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['grupo', 'producto_id', 'nombre', 'cantidad', 'precio', 'total', 'usuario_id', 'created_at'];
}
