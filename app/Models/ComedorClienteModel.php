<?php

namespace App\Models;

use CodeIgniter\Model;

class ComedorClienteModel extends Model
{
    protected $table         = 'comedor_clientes';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['nombre', 'identificacion', 'telefono', 'password', 'notas', 'activo', 'saldo_pendiente', 'vuelto_pendiente'];
    protected $useTimestamps = true;

    public function deudores(): array
    {
        return $this->where('saldo_pendiente >', 0)
            ->where('activo', 1)
            ->orderBy('saldo_pendiente', 'DESC')
            ->findAll();
    }

    public function todos(): array
    {
        return $this->where('activo', 1)
            ->orderBy('saldo_pendiente', 'DESC')
            ->orderBy('nombre', 'ASC')
            ->findAll();
    }

    public function buscarPorIdentificacion(string $identificacion): ?array
    {
        return $this->where('identificacion', trim($identificacion))->first();
    }
}
