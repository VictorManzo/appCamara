<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * - productos.huella: "huella" de 64 bits de la foto (dHash) para reconocer el mismo
 *   producto al tomarle foto otra vez, sin servicios de pago.
 * - ventas.dueno: de quién era la pieza al venderla (L/V), para el corte por dueño.
 */
class HuellaYDueno extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('huella', 'productos')) {
            $this->forge->addColumn('productos', ['huella' => ['type' => 'VARCHAR', 'constraint' => 16, 'null' => true]]);
        }
        if (! $this->db->fieldExists('dueno', 'ventas')) {
            $this->forge->addColumn('ventas', ['dueno' => ['type' => 'CHAR', 'constraint' => 1, 'null' => true]]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('huella', 'productos')) {
            $this->forge->dropColumn('productos', 'huella');
        }
        if ($this->db->fieldExists('dueno', 'ventas')) {
            $this->forge->dropColumn('ventas', 'dueno');
        }
    }
}
