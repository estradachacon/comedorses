<?php

namespace App\Models;

use CodeIgniter\Model;

class ComedorPedidoDetalleModel extends Model
{
    protected $table         = 'comedor_pedidos_detalles';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['pedido_id', 'item_id', 'item_nombre', 'servicio', 'precio_unitario', 'cantidad', 'subtotal', 'entregado_at', 'rechazado_at'];
    protected $useTimestamps = false;
    protected $createdField  = 'created_at';

    public function delPedido(int $pedidoId): array
    {
        return $this->where('pedido_id', $pedidoId)->findAll();
    }
}
