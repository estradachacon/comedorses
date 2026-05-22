<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ComedorExtras extends Migration
{
    public function up()
    {
        // ── comedor_menu_dia (si no existe aún) ──────────────────────────────
        $this->db->query("
            CREATE TABLE IF NOT EXISTS comedor_menu_dia (
                id         INT UNSIGNED     NOT NULL AUTO_INCREMENT,
                item_id    INT UNSIGNED     NOT NULL,
                fecha      DATE             NOT NULL,
                created_at DATETIME         NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_item_fecha (item_id, fecha),
                KEY idx_fecha (fecha)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // ── origen en comedor_pedidos_head ───────────────────────────────────
        $this->db->query("
            ALTER TABLE comedor_pedidos_head
            ADD COLUMN IF NOT EXISTS origen ENUM('cajero','cliente') NOT NULL DEFAULT 'cajero'
        ");

        // ── solicitud en estado ENUM ─────────────────────────────────────────
        $this->db->query("
            ALTER TABLE comedor_pedidos_head
            MODIFY COLUMN estado ENUM('pendiente','pagado','anulado','solicitud') NOT NULL DEFAULT 'pendiente'
        ");

        // ── tarea diaria de deudores comedor ─────────────────────────────────
        $exists = $this->db->table('tareas_sistema')
            ->where('nombre', 'notificacion_deudores_comedor')
            ->countAllResults();

        if (!$exists) {
            $this->db->table('tareas_sistema')->insert([
                'nombre'          => 'notificacion_deudores_comedor',
                'ultima_ejecucion' => null,
            ]);
        }
    }

    public function down()
    {
        $this->db->query("DROP TABLE IF EXISTS comedor_menu_dia");

        $this->db->query("
            ALTER TABLE comedor_pedidos_head
            DROP COLUMN IF EXISTS origen
        ");

        $this->db->query("
            ALTER TABLE comedor_pedidos_head
            MODIFY COLUMN estado ENUM('pendiente','pagado','anulado') NOT NULL DEFAULT 'pendiente'
        ");

        $this->db->table('tareas_sistema')
            ->where('nombre', 'notificacion_deudores_comedor')
            ->delete();
    }
}
