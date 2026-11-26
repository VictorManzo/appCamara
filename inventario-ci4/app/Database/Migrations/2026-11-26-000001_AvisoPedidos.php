<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Guarda cuándo aceptó el cliente el Aviso de privacidad al enviar su pedido. */
class AvisoPedidos extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('aviso_aceptado', 'pedidos')) {
            $this->forge->addColumn('pedidos', ['aviso_aceptado' => ['type' => 'DATETIME', 'null' => true]]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('aviso_aceptado', 'pedidos')) {
            $this->forge->dropColumn('pedidos', 'aviso_aceptado');
        }
    }
}
