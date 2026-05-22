<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCuentaContableToClientes extends Migration
{
    public function up()
    {
        // Agregar columna solo si no existe
        $this->db->query("
            ALTER TABLE clientes
            ADD COLUMN IF NOT EXISTS cuenta_contable_id INT UNSIGNED NULL AFTER direccion
        ");

        // Agregar FK solo si no existe
        $fkExists = $this->db->query("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME    = 'clientes'
              AND CONSTRAINT_NAME = 'fk_clientes_cuenta_contable'
        ")->getRow();

        if (!$fkExists) {
            try {
                $this->db->query("
                    ALTER TABLE clientes
                    ADD CONSTRAINT fk_clientes_cuenta_contable
                    FOREIGN KEY (cuenta_contable_id)
                    REFERENCES cont_plan_cuentas(id)
                    ON DELETE SET NULL
                    ON UPDATE CASCADE
                ");
            } catch (\Throwable $e) {
                // cont_plan_cuentas puede no existir en entornos sin el módulo contable
            }
        }
    }

    public function down()
    {
        $fkExists = $this->db->query("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME    = 'clientes'
              AND CONSTRAINT_NAME = 'fk_clientes_cuenta_contable'
        ")->getRow();

        if ($fkExists) {
            $this->db->query("ALTER TABLE clientes DROP FOREIGN KEY fk_clientes_cuenta_contable");
        }

        $this->db->query("ALTER TABLE clientes DROP COLUMN IF EXISTS cuenta_contable_id");
    }
}