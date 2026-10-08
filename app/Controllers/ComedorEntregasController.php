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

    // Pedidos de hoy (confirmados o no) con items pendientes de entregar para este horario —
    // solo items de ese horario, o sin restricción de horario (item "todo el día"), y que ya
    // no se hayan entregado antes. Un mismo pedido puede tener items de varios horarios: cada
    // uno se "llama" y se entrega por separado, así que aquí solo van los que tocan a este llamado.
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

        $rows = $this->detalleModel
            ->select('comedor_pedidos_detalles.*, comedor_pedidos_head.numero,
                      comedor_pedidos_head.cliente_id, comedor_pedidos_head.cliente_nombre,
                      comedor_pedidos_head.tipo_pago, comedor_pedidos_head.estado,
                      comedor_pedidos_head.total AS pedido_total, comedor_pedidos_head.monto_recibido,
                      comedor_pedidos_head.created_at')
            ->join('comedor_pedidos_head', 'comedor_pedidos_head.id = comedor_pedidos_detalles.pedido_id')
            ->where('comedor_pedidos_head.fecha', $fecha)
            ->where('comedor_pedidos_head.anulado', 0)
            ->where('comedor_pedidos_detalles.entregado_at', null)
            ->groupStart()
                ->where('comedor_pedidos_detalles.servicio', $servicio)
                ->orWhere('comedor_pedidos_detalles.servicio', null)
            ->groupEnd()
            ->orderBy('comedor_pedidos_detalles.pedido_id', 'ASC')
            ->findAll();

        if (empty($rows)) {
            return $this->response->setJSON([]);
        }

        // Pedidos que YA tuvieron una entrega parcial antes (algún item suyo quedó entregado en
        // un llamado anterior): a esos ya no hay que volver a resolverles el pago, solo entregar
        // lo que falta.
        $idsPedidos      = array_unique(array_column($rows, 'pedido_id'));
        $idsConEntregaDb = $this->detalleModel
            ->distinct()
            ->select('pedido_id')
            ->whereIn('pedido_id', $idsPedidos)
            ->where('entregado_at IS NOT NULL')
            ->findAll();
        $idsConEntregaPrevia = array_column($idsConEntregaDb, 'pedido_id');

        $agrupado = [];
        foreach ($rows as $row) {
            $id = $row['pedido_id'];
            if (!isset($agrupado[$id])) {
                $agrupado[$id] = [
                    'id'                   => $id,
                    'numero'               => $row['numero'],
                    'numero_formateado'    => formatearNumeroPedido($row['numero']),
                    'cliente_id'           => $row['cliente_id'],
                    'cliente_nombre'       => $row['cliente_nombre'],
                    'tipo_pago'            => $row['tipo_pago'],
                    'estado'               => $row['estado'],
                    'total'                => $row['pedido_total'],
                    'monto_recibido'       => $row['monto_recibido'],
                    'created_at'           => $row['created_at'],
                    'ya_entregado_parcial' => in_array($id, $idsConEntregaPrevia, true),
                    'items'                => [],
                    'subtotal_llamado'     => 0,
                ];
            }
            $agrupado[$id]['items'][] = [
                'id'       => $row['id'],
                'nombre'   => $row['item_nombre'],
                'cantidad' => $row['cantidad'],
                'servicio' => $row['servicio'],
            ];
            $agrupado[$id]['subtotal_llamado'] += (float) $row['subtotal'];
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

        $servicio = $this->request->getPost('servicio');
        if (!in_array($servicio, ['desayuno', 'refrigerio', 'almuerzo'], true)) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Horario inválido.']);
        }

        // Items de ESTE pedido que corresponden a este llamado y aún no se han entregado
        // (de ese horario, o sin restricción). Si no hay ninguno, no hay nada que hacer aquí.
        $detallesPorEntregar = $this->detalleModel
            ->where('pedido_id', $id)
            ->where('entregado_at', null)
            ->groupStart()
                ->where('servicio', $servicio)
                ->orWhere('servicio', null)
            ->groupEnd()
            ->findAll();

        if (empty($detallesPorEntregar)) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'No hay items pendientes de este horario para este pedido.']);
        }

        // ¿Ya se le había entregado algo antes (otro horario de este mismo pedido)? Si es así,
        // el pago ya se resolvió en esa primera entrega: aquí solo se marcan los items como
        // entregados, sin volver a preguntar tipo de pago/comensal/vuelto.
        $yaTieneEntregaPrevia = $this->detalleModel
            ->where('pedido_id', $id)
            ->where('entregado_at IS NOT NULL')
            ->countAllResults() > 0;

        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            if (!$yaTieneEntregaPrevia) {
                $tipoPagoPost      = $this->request->getPost('tipo_pago');
                $clienteIdPost     = $this->request->getPost('cliente_id');
                $montoRecibidoPost = $this->request->getPost('monto_recibido');

                $tipoPago        = $tipoPagoPost ?: $pedido['tipo_pago'];
                $clienteId       = ($clienteIdPost !== null && $clienteIdPost !== '') ? (int) $clienteIdPost : $pedido['cliente_id'];
                $montoRecibido   = ($montoRecibidoPost !== null && $montoRecibidoPost !== '') ? (float) $montoRecibidoPost : null;
                $vueltoPendiente = (bool) $this->request->getPost('vuelto_pendiente');

                if ($tipoPago === 'fiado' && !$clienteId) {
                    $db->transRollback();
                    return $this->response->setJSON(['ok' => false, 'msg' => 'Para fiado debes asociar un comensal.']);
                }
                if ($tipoPago === 'contado' && $montoRecibido !== null && $montoRecibido < (float) $pedido['total']) {
                    $db->transRollback();
                    return $this->response->setJSON(['ok' => false, 'msg' => 'El monto con el que pagó no puede ser menor al total.']);
                }

                $total       = (float) $pedido['total'];
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
                }

                // Recalcular el saldo_pendiente (deuda real) del cliente directamente desde sus
                // pedidos fiado aún pendientes, tanto para el cliente nuevo como para el anterior
                // si el cajero lo cambió al entregar. Evita desincronías sin importar si antes se
                // había "confirmado" o no.
                foreach (array_unique(array_filter([$clienteId, $pedido['cliente_id']])) as $cid) {
                    $suma = $db->table('comedor_pedidos_head')
                        ->selectSum('saldo')
                        ->where('cliente_id', $cid)
                        ->where('tipo_pago', 'fiado')
                        ->where('estado', 'pendiente')
                        ->where('anulado', 0)
                        ->get()->getRow();
                    $db->table('comedor_clientes')
                        ->where('id', $cid)
                        ->update(['saldo_pendiente' => $suma->saldo ?? 0]);
                }
            }

            // Marcar entregados únicamente los items de este llamado.
            $idsDetalle = array_column($detallesPorEntregar, 'id');
            $this->detalleModel
                ->whereIn('id', $idsDetalle)
                ->set('entregado_at', date('Y-m-d H:i:s'))
                ->update();

            // Si con esto ya no queda ningún item pendiente del pedido, se marca el pedido
            // completo como entregado (esto es lo que lo quita por completo de cualquier llamado).
            $pendientes = $this->detalleModel
                ->where('pedido_id', $id)
                ->where('entregado_at', null)
                ->countAllResults();

            $finalizado = $pendientes === 0;
            if ($finalizado) {
                $this->headModel->update($id, [
                    'entregado_at'  => date('Y-m-d H:i:s'),
                    'entregado_por' => session()->get('id'),
                ]);
            }

            $db->transCommit();
            return $this->response->setJSON(['ok' => true, 'finalizado' => $finalizado]);
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->response->setJSON(['ok' => false, 'msg' => $e->getMessage()]);
        }
    }
}
