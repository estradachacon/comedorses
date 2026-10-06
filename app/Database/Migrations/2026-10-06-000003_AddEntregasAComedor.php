<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEntregasAComedor extends Migration
{
    public function up()
    {
        $this->db->query("
            ALTER TABLE comedor_pedidos_head
            ADD COLUMN IF NOT EXISTS entregado_at DATETIME NULL DEFAULT NULL AFTER estado,
            ADD COLUMN IF NOT EXISTS entregado_por INT UNSIGNED NULL DEFAULT NULL AFTER entregado_at
        ");

        $this->db->query("
            ALTER TABLE comedor_pedidos_detalles
            ADD COLUMN IF NOT EXISTS servicio ENUM('desayuno','refrigerio','almuerzo') NULL DEFAULT NULL AFTER item_nombre
        ");
    }

    public function down()
    {
        $this->db->query("
            ALTER TABLE comedor_pedidos_head
            DROP COLUMN IF EXISTS entregado_at,
            DROP COLUMN IF EXISTS entregado_por
        ");

        $this->db->query("
            ALTER TABLE comedor_pedidos_detalles
            DROP COLUMN IF EXISTS servicio
        ");
    }
}
