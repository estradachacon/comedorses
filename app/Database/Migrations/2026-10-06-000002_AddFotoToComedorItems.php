<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFotoToComedorItems extends Migration
{
    public function up()
    {
        $this->db->query("
            ALTER TABLE comedor_items
            ADD COLUMN IF NOT EXISTS foto VARCHAR(255) NULL DEFAULT NULL AFTER descripcion
        ");
    }

    public function down()
    {
        $this->db->query("
            ALTER TABLE comedor_items
            DROP COLUMN IF EXISTS foto
        ");
    }
}
