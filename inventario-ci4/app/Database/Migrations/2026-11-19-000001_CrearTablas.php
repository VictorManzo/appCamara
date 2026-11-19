<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablas extends Migration
{
    public function up()
    {
        // Usuarios: el administrador (dueño) y los vendedores.
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'nombre'        => ['type' => 'VARCHAR', 'constraint' => 120],
            'usuario'       => ['type' => 'VARCHAR', 'constraint' => 60],
            'password_hash' => ['type' => 'VARCHAR', 'constraint' => 255],
            'rol'           => ['type' => 'VARCHAR', 'constraint' => 12, 'default' => 'vendedor'],
            'activo'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('usuario');
        $this->forge->createTable('usuarios', true);

        // Productos del inventario.
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'origen_id'     => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'nombre'        => ['type' => 'VARCHAR', 'constraint' => 200, 'default' => ''],
            'pendiente'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'serie'         => ['type' => 'VARCHAR', 'constraint' => 150, 'default' => ''],
            'numero'        => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => ''],
            'color'         => ['type' => 'VARCHAR', 'constraint' => 200, 'default' => ''],
            'anio'          => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => ''],
            'cantidad'      => ['type' => 'INT', 'default' => 0],
            'precio'        => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
            'precio_antes'  => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
            'dueno'         => ['type' => 'CHAR', 'constraint' => 1, 'default' => 'V'],
            'notas'         => ['type' => 'TEXT', 'null' => true],
            'foto'          => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'mini'          => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'es_hot_wheels' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'vendidos'      => ['type' => 'INT', 'default' => 0],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('origen_id');
        $this->forge->addKey('nombre');
        $this->forge->createTable('productos', true);

        // Ventas: una fila por producto; las filas de una misma venta comparten "grupo".
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'grupo'       => ['type' => 'VARCHAR', 'constraint' => 40],
            'producto_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'nombre'      => ['type' => 'VARCHAR', 'constraint' => 200],
            'cantidad'    => ['type' => 'INT'],
            'precio'      => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
            'total'       => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
            'usuario_id'  => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('grupo');
        $this->forge->addKey('created_at');
        $this->forge->createTable('ventas', true);

        // Ajustes sueltos (contacto del catálogo).
        $this->forge->addField([
            'clave' => ['type' => 'VARCHAR', 'constraint' => 50],
            'valor' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('clave', true);
        $this->forge->createTable('ajustes', true);
    }

    public function down()
    {
        $this->forge->dropTable('ajustes', true);
        $this->forge->dropTable('ventas', true);
        $this->forge->dropTable('productos', true);
        $this->forge->dropTable('usuarios', true);
    }
}
