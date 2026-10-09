<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRechazadoAtADetallesComedor extends Migration
{
    public function up()
    {
        // Permite invalidar un item puntual de un pedido que todavía no se ha entregado (p. ej.
        // si se rechaza en cocina o el cliente ya no lo quiere), sin tener que anular el pedido
        // completo. Un item rechazado deja de aparecer en cualquier llamado de /comedor/entregas
        // y su monto se descuenta del total del pedido.
        $this->db->query("
            ALTER TABLE comedor_pedidos_detalles
            ADD COLUMN IF NOT EXISTS rechazado_at DATETIME NULL DEFAULT NULL AFTER entregado_at
        ");
    }

    public function down()
    {
        $this->db->query("
            ALTER TABLE comedor_pedidos_detalles
            DROP COLUMN IF EXISTS rechazado_at
        ");
    }
}
