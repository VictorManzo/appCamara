<?php

namespace App\Models;

use CodeIgniter\Model;

class AjusteModel extends Model
{
    protected $table         = 'ajustes';
    protected $primaryKey    = 'clave';
    protected $returnType    = 'array';
    protected $useAutoIncrement = false;
    protected $allowedFields = ['clave', 'valor'];

    public function leer(string $clave, string $porDefecto = ''): string
    {
        $fila = $this->find($clave);
        return $fila ? (string) $fila['valor'] : $porDefecto;
    }

    public function guardar(string $clave, string $valor): void
    {
        $this->db->table($this->table)->replace(['clave' => $clave, 'valor' => $valor]);
    }
}
