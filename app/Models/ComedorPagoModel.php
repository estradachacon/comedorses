<?php

namespace App\Models;

use CodeIgniter\Model;

class ComedorPagoModel extends Model
{
    protected $table         = 'comedor_pagos';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['pedido_id', 'cliente_id', 'monto', 'notas', 'created_by'];
    protected $useTimestamps = false;
    protected $createdField  = 'created_at';

    public function delPedido(int $pedidoId): array
    {
        return $this->where('pedido_id', $pedidoId)->findAll();
    }
}
