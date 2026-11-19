<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Pedidos que hacen los clientes desde el catálogo público. */
class CrearPedidos extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'codigo'     => ['type' => 'VARCHAR', 'constraint' => 12],
            'cliente'    => ['type' => 'VARCHAR', 'constraint' => 120],
            'telefono'   => ['type' => 'VARCHAR', 'constraint' => 40],
            'nota'       => ['type' => 'VARCHAR', 'constraint' => 500, 'default' => ''],
            'items'      => ['type' => 'TEXT'],
            'total'      => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'estado'     => ['type' => 'VARCHAR', 'constraint' => 12, 'default' => 'nuevo'],
            'ip'         => ['type' => 'VARCHAR', 'constraint' => 45, 'default' => ''],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('created_at');
        $this->forge->addKey('ip');
        $this->forge->createTable('pedidos', true);
    }

    public function down()
    {
        $this->forge->dropTable('pedidos', true);
    }
}
