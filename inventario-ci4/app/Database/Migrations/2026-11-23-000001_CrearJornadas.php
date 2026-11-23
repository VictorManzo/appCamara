<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Jornadas de venta: cada vendedor abre su jornada al entrar (con o sin cambio) y la
 * finaliza al terminar el día. Las ventas quedan ligadas a la jornada.
 */
class CrearJornadas extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'usuario_id'   => ['type' => 'INT', 'unsigned' => true],
            'fondo'        => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'inicio'       => ['type' => 'DATETIME'],
            'fin'          => ['type' => 'DATETIME', 'null' => true],
            'total_ventas' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'piezas'       => ['type' => 'INT', 'default' => 0],
            'num_ventas'   => ['type' => 'INT', 'default' => 0],
            'estado'       => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'abierta'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['usuario_id', 'estado']);
        $this->forge->addKey('inicio');
        $this->forge->createTable('jornadas', true);

        if (! $this->db->fieldExists('jornada_id', 'ventas')) {
            $this->forge->addColumn('ventas', [
                'jornada_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('jornada_id', 'ventas')) {
            $this->forge->dropColumn('ventas', 'jornada_id');
        }
        $this->forge->dropTable('jornadas', true);
    }
}
