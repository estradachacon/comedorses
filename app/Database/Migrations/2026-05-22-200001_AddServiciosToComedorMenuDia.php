<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddServiciosToComedorMenuDia extends Migration
{
    public function up()
    {
        $this->db->query("
            ALTER TABLE comedor_menu_dia
            ADD COLUMN IF NOT EXISTS desayuno   TINYINT(1) NOT NULL DEFAULT 0,
            ADD COLUMN IF NOT EXISTS refrigerio TINYINT(1) NOT NULL DEFAULT 0,
            ADD COLUMN IF NOT EXISTS almuerzo   TINYINT(1) NOT NULL DEFAULT 0
        ");
    }

    public function down()
    {
        $this->db->query("
            ALTER TABLE comedor_menu_dia
            DROP COLUMN IF EXISTS desayuno,
            DROP COLUMN IF EXISTS refrigerio,
            DROP COLUMN IF EXISTS almuerzo
        ");
    }
}
