<?php

namespace App\Controllers;

use App\Models\ComedorPedidoHeadModel;
use App\Models\ComedorPedidoDetalleModel;
use App\Models\ComedorPagoModel;

class ComedorEntregasController extends BaseController
{
    protected ComedorPedidoHeadModel    $headModel;
    protected ComedorPedidoDetalleModel $detalleModel;
    protected ComedorPagoModel          $pagoModel;

    public function __construct()
    {
        $this->headModel    = new ComedorPedidoHeadModel();
        $this->detalleModel = new ComedorPedidoDetalleModel();
        $this->pagoModel    = new ComedorPagoModel();
    }

    public function index()
    {
        if (!tienePermiso('gestionar_entregas_comedor')) {
            return redirect()->back()->with('permiso_error', 'Sin permiso.');
        }
        $horaActual = date('H:i');
        $horarios   = horariosServicioComedor();
        $default    = 'almuerzo';
        foreach ($horarios as $servicio => $limite) {
            if ($horaActual < $limite) {
                $default = $servicio;
                break;
            }
        }

        $data['title']           = 'Entregas';
        $data['defaultServicio'] = $default;
        return view('comedor/entregas/index', $data);
    }

    // Todos los pedidos de hoy (confirmados o no) pendientes de entregar, con items de ese
    // horario o sin restricción de horario (item "todo el día").
    public function llamar()
    {
        if (!tienePermiso('gestionar_entregas_comedor')) {
            return $this->response->setJSON([]);
        }

        $servicio = $this->request->getGet('servicio');
        if (!in_array($servicio, ['desayuno', 'refrigerio', 'almuerzo'], true)) {
            return $this->response->setJSON([]);
        }

        $fecha = date('Y-m-d');

        $idsPedidos = $this->detalleModel
            ->distinct()
            ->select('comedor_pedidos_detalles.pedido_id')
            ->join('comedor_pedidos_head', 'comedor_pedidos_head.id = comedor_pedidos_detalles.pedido_id')
            ->groupStart()
                ->where('comedor_pedidos_detalles.servicio', $servicio)
                ->orWhere('comedor_pedidos_detalles.servicio', null)
            ->groupEnd()
            ->where('comedor_pedidos_head.fecha', $fecha)
            ->where('comedor_pedidos_head.anulado', 0)
            ->where('comedor_pedidos_head.entregado_at', null)
            ->findColumn('pedido_id');

        if (empty($idsPedidos)) {
            return $this->response->setJSON([]);
        }

        $rows = $this->headModel
            ->select('comedor_pedidos_head.*, comedor_pedidos_detalles.item_nombre,
                      comedor_pedidos_detalles.cantidad, comedor_pedidos_detalles.servicio')
            ->join('comedor_pedidos_detalles', 'comedor_pedidos_detalles.pedido_id = comedor_pedidos_head.id', 'left')
            ->whereIn('comedor_pedidos_head.id', $idsPedidos)
            ->orderBy('comedor_pedidos_head.id', 'ASC')
            ->findAll();

        $agrupado = [];
        foreach ($rows as $row) {
            $id = $row['id'];
            if (!isset($agrupado[$id])) {
                $agrupado[$id] = $row;
                $agrupado[$id]['numero_formateado'] = formatearNumeroPedido($row['numero']);
                $agrupado[$id]['items'] = [];
            }
            if ($row['item_nombre']) {
                $agrupado[$id]['items'][] = [
                    'nombre'   => $row['item_nombre'],
                    'cantidad' => $row['cantidad'],
                    'servicio' => $row['servicio'],
                ];
            }
        }

        return $this->response->setJSON(array_values($agrupado));
    }

    public function marcarEntregado(int $id)
    {
        if (!tienePermiso('gestionar_entregas_comedor')) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Sin permiso.']);
        }

        $pedido = $this->headModel->find($id);
        if (!$pedido || $pedido['anulado']) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Pedido no válido.']);
        }
        if ($pedido['entregado_at']) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Este pedido ya fue entregado.']);
        }

        $tipoPagoPost      = $this->request->getPost('tipo_pago');
        $clienteIdPost     = $this->request->getPost('cliente_id');
        $montoRecibidoPost = $this->request->getPost('monto_recibido');

        $tipoPago        = $tipoPagoPost ?: $pedido['tipo_pago'];
        $clienteId       = ($clienteIdPost !== null && $clienteIdPost !== '') ? (int) $clienteIdPost : $pedido['cliente_id'];
        $montoRecibido   = ($montoRecibidoPost !== null && $montoRecibidoPost !== '') ? (float) $montoRecibidoPost : null;
        $vueltoPendiente = (bool) $this->request->getPost('vuelto_pendiente');

        if ($tipoPago === 'fiado' && !$clienteId) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Para fiado debes asociar un comensal.']);
        }

        if ($tipoPago === 'contado' && $montoRecibido !== null && $montoRecibido < (float) $pedido['total']) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'El monto con el que pagó no puede ser menor al total.']);
        }

        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            $total = (float) $pedido['total'];

            // Si ya se le había asignado fiado antes (confirmado), hay que revertir ese saldo antes
            // de aplicar el pago definitivo de la entrega (que pudo haber cambiado, ej. a contado).
            // Si el pedido seguía como "solicitud" nunca se sumó nada, así que no hay nada que revertir.
            if ($pedido['tipo_pago'] === 'fiado' && $pedido['cliente_id'] && (float) $pedido['saldo'] > 0 && $pedido['estado'] !== 'solicitud') {
                $db->table('comedor_clientes')
                    ->where('id', $pedido['cliente_id'])
                    ->set('saldo_pendiente', "GREATEST(0, saldo_pendiente - {$pedido['saldo']})", false)
                    ->update();
            }

            $montoPagado = ($tipoPago === 'contado') ? $total : 0.0;
            $saldo       = $total - $montoPagado;
            $estado      = ($tipoPago === 'contado') ? 'pagado' : 'pendiente';

            $this->headModel->update($id, [
                'tipo_pago'      => $tipoPago,
                'monto_pagado'   => $montoPagado,
                'saldo'          => $saldo,
                'estado'         => $estado,
                'cliente_id'     => $clienteId,
                'monto_recibido' => $tipoPago === 'contado' ? $montoRecibido : null,
                'entregado_at'   => date('Y-m-d H:i:s'),
                'entregado_por'  => session()->get('id'),
            ]);

            if ($tipoPago === 'contado') {
                $this->pagoModel->insert([
                    'pedido_id'  => $id,
                    'cliente_id' => $clienteId,
                    'monto'      => $total,
                    'notas'      => 'Pago al momento de entrega',
                    'created_by' => session()->get('id'),
                ]);

                // Si el cajero marcó que no tenía el vuelto a mano, ese monto queda pendiente de
                // darle al cliente, visible y liquidable desde /comedor/deudores.
                if ($vueltoPendiente && $montoRecibido !== null && $montoRecibido > $total && $clienteId) {
                    $vuelto = round($montoRecibido - $total, 2);
                    $db->table('comedor_clientes')
                        ->where('id', $clienteId)
                        ->set('vuelto_pendiente', "vuelto_pendiente + {$vuelto}", false)
                        ->update();
                }
            } elseif ($clienteId) {
                $db->table('comedor_clientes')
                    ->where('id', $clienteId)
                    ->set('saldo_pendiente', "saldo_pendiente + {$saldo}", false)
                    ->update();
            }

            $db->transCommit();
            return $this->response->setJSON(['ok' => true]);
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->response->setJSON(['ok' => false, 'msg' => $e->getMessage()]);
        }
    }
}
