<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEntregadoAtADetallesComedor extends Migration
{
    public function up()
    {
        // Entrega por item: un mismo pedido puede tener items de distintos horarios (p. ej. un
        // refrigerio y un almuerzo), y cada uno se entrega en su propio llamado. entregado_at a
        // nivel de pedido (comedor_pedidos_head) pasa a significar "ya se entregaron TODOS sus
        // items"; este nuevo campo a nivel de detalle es el que se marca en cada llamado.
        $this->db->query("
            ALTER TABLE comedor_pedidos_detalles
            ADD COLUMN IF NOT EXISTS entregado_at DATETIME NULL DEFAULT NULL AFTER subtotal
        ");
    }

    public function down()
    {
        $this->db->query("
            ALTER TABLE comedor_pedidos_detalles
            DROP COLUMN IF EXISTS entregado_at
        ");
    }
}
