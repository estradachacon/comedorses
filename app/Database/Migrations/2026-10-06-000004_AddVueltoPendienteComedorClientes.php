<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVueltoPendienteComedorClientes extends Migration
{
    public function up()
    {
        $this->db->query("
            ALTER TABLE comedor_clientes
            ADD COLUMN IF NOT EXISTS vuelto_pendiente DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER saldo_pendiente
        ");
    }

    public function down()
    {
        $this->db->query("
            ALTER TABLE comedor_clientes
            DROP COLUMN IF EXISTS vuelto_pendiente
        ");
    }
}
