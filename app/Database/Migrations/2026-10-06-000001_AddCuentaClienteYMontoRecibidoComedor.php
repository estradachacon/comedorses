<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCuentaClienteYMontoRecibidoComedor extends Migration
{
    public function up()
    {
        // ── password en comedor_clientes (cuenta propia del comensal) ───────
        $this->db->query("
            ALTER TABLE comedor_clientes
            ADD COLUMN IF NOT EXISTS password VARCHAR(255) NULL DEFAULT NULL AFTER telefono
        ");

        // ── monto con el que paga el cliente al contado (para calcular cambio) ─
        $this->db->query("
            ALTER TABLE comedor_pedidos_head
            ADD COLUMN IF NOT EXISTS monto_recibido DECIMAL(12,2) NULL DEFAULT NULL AFTER tipo_pago
        ");
    }

    public function down()
    {
        $this->db->query("
            ALTER TABLE comedor_clientes
            DROP COLUMN IF EXISTS password
        ");

        $this->db->query("
            ALTER TABLE comedor_pedidos_head
            DROP COLUMN IF EXISTS monto_recibido
        ");
    }
}
