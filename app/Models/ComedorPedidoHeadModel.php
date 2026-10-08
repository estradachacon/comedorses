<?php

namespace App\Models;

use CodeIgniter\Model;

class ComedorPedidoHeadModel extends Model
{
    protected $table         = 'comedor_pedidos_head';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'numero', 'cliente_id', 'cliente_nombre', 'fecha',
        'total', 'monto_pagado', 'saldo', 'tipo_pago', 'monto_recibido', 'estado', 'notas',
        'anulado', 'anulado_por', 'fecha_anulacion', 'created_by',
        'entregado_at', 'entregado_por',
    ];
    protected $useTimestamps = true;

    public function generarNumero(): string
    {
        $anio = date('Y');
        $last = $this->where("numero LIKE 'P{$anio}%'")->orderBy('id', 'DESC')->first();
        $seq  = $last ? ((int) substr($last['numero'], 5)) + 1 : 1;
        return 'P' . $anio . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }

    public function delDia(?string $fecha = null): array
    {
        $fecha = $fecha ?? date('Y-m-d');
        return $this->select('comedor_pedidos_head.*, users.user_name as cajero_nombre,
                              comedor_clientes.nombre as comensal_actual_nombre')
            ->join('users', 'users.id = comedor_pedidos_head.created_by', 'left')
            ->join('comedor_clientes', 'comedor_clientes.id = comedor_pedidos_head.cliente_id', 'left')
            ->where('fecha', $fecha)
            ->where('anulado', 0)
            ->whereNotIn('estado', ['solicitud'])
            ->orderBy('comedor_pedidos_head.id', 'DESC')
            ->findAll();
    }

    public function solicitudesPendientes(): array
    {
        return $this->where('estado', 'solicitud')
            ->where('anulado', 0)
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    public function resumenDia(?string $fecha = null): array
    {
        $fecha = $fecha ?? date('Y-m-d');
        $row = $this->selectSum('total', 'ventas_total')
            ->selectSum('monto_pagado', 'cobrado_total')
            ->selectCount('id', 'cantidad_pedidos')
            ->where('fecha', $fecha)
            ->where('anulado', 0)
            ->whereNotIn('estado', ['solicitud'])
            ->first();
        $row = $row ?? [];

        // El KPI "Fiado" solo debe contar deuda real (fiado), no un contado ya confirmado
        // pero todavía sin entregar (que también queda con saldo > 0 mientras tanto).
        $fiado = $this->selectSum('saldo', 'pendiente_total')
            ->where('fecha', $fecha)
            ->where('anulado', 0)
            ->where('tipo_pago', 'fiado')
            ->whereNotIn('estado', ['solicitud'])
            ->first();
        $row['pendiente_total'] = $fiado['pendiente_total'] ?? 0;

        return $row;
    }
}
