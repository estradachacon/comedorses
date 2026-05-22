<?php

namespace App\Controllers;

class ComedorReportesController extends BaseController
{
    private function db() { return \Config\Database::connect(); }

    public function pedidos()
    {
        if (!tienePermiso('ver_pedidos_comedor')) {
            return redirect()->back()->with('permiso_error', 'Sin permiso.');
        }

        $desde  = $this->request->getGet('desde')     ?? date('Y-m-01');
        $hasta  = $this->request->getGet('hasta')     ?? date('Y-m-d');
        $estado = $this->request->getGet('estado')    ?? 'todos';
        $pago   = $this->request->getGet('tipo_pago') ?? 'todos';

        $db = $this->db();
        $b  = $db->table('comedor_pedidos_head ph')
                 ->select('ph.*, u.user_name AS cajero_nombre')
                 ->join('users u', 'u.id = ph.created_by', 'left')
                 ->where('ph.fecha >=', $desde)
                 ->where('ph.fecha <=', $hasta)
                 ->whereNotIn('ph.estado', ['solicitud']);

        if ($estado !== 'todos') $b->where('ph.estado',    $estado);
        if ($pago   !== 'todos') $b->where('ph.tipo_pago', $pago);

        $pedidos = $b->orderBy('ph.fecha', 'DESC')->orderBy('ph.id', 'DESC')->get()->getResultArray();

        $resumen = [
            'pedidos'   => count($pedidos),
            'vendido'   => array_sum(array_column($pedidos, 'total')),
            'cobrado'   => array_sum(array_column($pedidos, 'monto_pagado')),
            'pendiente' => array_sum(array_column($pedidos, 'saldo')),
        ];

        return view('comedor/reportes/pedidos', compact('pedidos', 'resumen', 'desde', 'hasta', 'estado', 'pago') + ['title' => 'Reporte de Pedidos']);
    }

    public function ventas()
    {
        if (!tienePermiso('ver_pedidos_comedor')) {
            return redirect()->back()->with('permiso_error', 'Sin permiso.');
        }

        $desde = $this->request->getGet('desde') ?? date('Y-m-01');
        $hasta = $this->request->getGet('hasta') ?? date('Y-m-d');
        $db    = $this->db();

        $resumen = $db->query("
            SELECT
                COUNT(*)                                                          AS pedidos,
                COALESCE(SUM(total), 0)                                           AS vendido,
                COALESCE(SUM(monto_pagado), 0)                                    AS cobrado,
                COALESCE(SUM(saldo), 0)                                           AS pendiente,
                COALESCE(SUM(CASE WHEN tipo_pago='contado' THEN total    ELSE 0 END), 0) AS contado,
                COALESCE(SUM(CASE WHEN tipo_pago='fiado'   THEN total    ELSE 0 END), 0) AS fiado_total,
                COALESCE(SUM(CASE WHEN tipo_pago='fiado'   THEN monto_pagado ELSE 0 END), 0) AS fiado_cobrado
            FROM comedor_pedidos_head
            WHERE fecha BETWEEN ? AND ?
              AND anulado = 0
              AND estado NOT IN ('anulado','solicitud')
        ", [$desde, $hasta])->getRow();

        $porDia = $db->query("
            SELECT
                fecha,
                COUNT(*)                     AS pedidos,
                COALESCE(SUM(total), 0)      AS vendido,
                COALESCE(SUM(monto_pagado), 0) AS cobrado,
                COALESCE(SUM(saldo), 0)      AS pendiente
            FROM comedor_pedidos_head
            WHERE fecha BETWEEN ? AND ?
              AND anulado = 0
              AND estado NOT IN ('anulado','solicitud')
            GROUP BY fecha
            ORDER BY fecha DESC
        ", [$desde, $hasta])->getResultArray();

        $topItems = $db->query("
            SELECT
                pd.item_nombre,
                SUM(pd.cantidad)  AS total_unidades,
                SUM(pd.subtotal)  AS total_vendido
            FROM comedor_pedidos_detalles pd
            JOIN comedor_pedidos_head ph ON ph.id = pd.pedido_id
            WHERE ph.fecha BETWEEN ? AND ?
              AND ph.anulado = 0
              AND ph.estado NOT IN ('anulado','solicitud')
            GROUP BY pd.item_nombre
            ORDER BY total_vendido DESC
            LIMIT 10
        ", [$desde, $hasta])->getResultArray();

        return view('comedor/reportes/ventas', compact('resumen', 'porDia', 'topItems', 'desde', 'hasta') + ['title' => 'Reporte de Ventas']);
    }

    public function deudas()
    {
        if (!tienePermiso('ver_deudores_comedor')) {
            return redirect()->back()->with('permiso_error', 'Sin permiso.');
        }

        $db = $this->db();

        $deudores = $db->query("
            SELECT
                cc.id, cc.nombre, cc.telefono,
                cc.saldo_pendiente,
                COUNT(ph.id)   AS pedidos_pendientes,
                MIN(ph.fecha)  AS primer_vencimiento,
                MAX(ph.fecha)  AS ultimo_pedido
            FROM comedor_clientes cc
            LEFT JOIN comedor_pedidos_head ph
                   ON ph.cliente_id = cc.id AND ph.saldo > 0 AND ph.anulado = 0
            WHERE cc.saldo_pendiente > 0 AND cc.activo = 1
            GROUP BY cc.id
            ORDER BY cc.saldo_pendiente DESC
        ")->getResultArray();

        $totalDeuda    = array_sum(array_column($deudores, 'saldo_pendiente'));
        $totalClientes = count($deudores);

        return view('comedor/reportes/deudas', compact('deudores', 'totalDeuda', 'totalClientes') + ['title' => 'Reporte de Deudas']);
    }
}
