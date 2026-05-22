<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateComedorModule extends Migration
{
    public function up()
    {
        // ── comedor_categorias ───────────────────────────────────────────────
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'nombre'     => ['type' => 'VARCHAR', 'constraint' => 100],
            'activa'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('comedor_categorias', true);

        // ── comedor_items ────────────────────────────────────────────────────
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'categoria_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'default' => null],
            'nombre'       => ['type' => 'VARCHAR', 'constraint' => 150],
            'descripcion'  => ['type' => 'TEXT', 'null' => true],
            'precio'       => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'disponible'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('categoria_id');
        $this->forge->createTable('comedor_items', true);

        // ── comedor_clientes ─────────────────────────────────────────────────
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'nombre'          => ['type' => 'VARCHAR', 'constraint' => 150],
            'identificacion'  => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'default' => null],
            'telefono'        => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true, 'default' => null],
            'notas'           => ['type' => 'TEXT', 'null' => true],
            'activo'          => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'saldo_pendiente' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => '0.00'],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('comedor_clientes', true);

        // ── comedor_pedidos_head ─────────────────────────────────────────────
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'numero'          => ['type' => 'VARCHAR', 'constraint' => 20],
            'cliente_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'default' => null],
            'cliente_nombre'  => ['type' => 'VARCHAR', 'constraint' => 150],
            'fecha'           => ['type' => 'DATE'],
            'total'           => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => '0.00'],
            'monto_pagado'    => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => '0.00'],
            'saldo'           => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => '0.00'],
            'tipo_pago'       => ['type' => 'ENUM', 'constraint' => ['contado', 'fiado'], 'default' => 'contado'],
            'estado'          => ['type' => 'ENUM', 'constraint' => ['pendiente', 'pagado', 'anulado', 'solicitud'], 'default' => 'pendiente'],
            'origen'          => ['type' => 'ENUM', 'constraint' => ['cajero', 'cliente'], 'default' => 'cajero'],
            'notas'           => ['type' => 'TEXT', 'null' => true],
            'anulado'         => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'anulado_por'     => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'default' => null],
            'fecha_anulacion' => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'created_by'      => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'default' => null],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('numero');
        $this->forge->addKey('fecha');
        $this->forge->addKey('cliente_id');
        $this->forge->createTable('comedor_pedidos_head', true);

        // ── comedor_pedidos_detalles ─────────────────────────────────────────
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'pedido_id'       => ['type' => 'INT', 'unsigned' => true],
            'item_id'         => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'default' => null],
            'item_nombre'     => ['type' => 'VARCHAR', 'constraint' => 150],
            'precio_unitario' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'cantidad'        => ['type' => 'DECIMAL', 'constraint' => '8,2', 'default' => '1.00'],
            'subtotal'        => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('pedido_id');
        $this->forge->createTable('comedor_pedidos_detalles', true);

        // ── comedor_pagos ────────────────────────────────────────────────────
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'pedido_id'  => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'default' => null],
            'cliente_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'default' => null],
            'monto'      => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'notas'      => ['type' => 'TEXT', 'null' => true],
            'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('pedido_id');
        $this->forge->addKey('cliente_id');
        $this->forge->createTable('comedor_pagos', true);

        // ── comedor_menu_dia ─────────────────────────────────────────────────
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'item_id'    => ['type' => 'INT', 'unsigned' => true],
            'fecha'      => ['type' => 'DATE'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['item_id', 'fecha']);
        $this->forge->addKey('fecha');
        $this->forge->createTable('comedor_menu_dia', true);
    }

    public function down()
    {
        $this->forge->dropTable('comedor_menu_dia',          true);
        $this->forge->dropTable('comedor_pagos',             true);
        $this->forge->dropTable('comedor_pedidos_detalles',  true);
        $this->forge->dropTable('comedor_pedidos_head',      true);
        $this->forge->dropTable('comedor_clientes',          true);
        $this->forge->dropTable('comedor_items',             true);
        $this->forge->dropTable('comedor_categorias',        true);
    }
}
