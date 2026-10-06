<?php

namespace App\Controllers;

use App\Models\ComedorPedidoHeadModel;
use App\Models\ComedorPedidoDetalleModel;
use App\Models\ComedorItemModel;
use App\Models\ComedorClienteModel;
use App\Models\ComedorPagoModel;

class ComedorPedidosController extends BaseController
{
    protected ComedorPedidoHeadModel    $headModel;
    protected ComedorPedidoDetalleModel $detalleModel;
    protected ComedorItemModel          $itemModel;
    protected ComedorClienteModel       $clienteModel;
    protected ComedorPagoModel          $pagoModel;

    public function __construct()
    {
        $this->headModel    = new ComedorPedidoHeadModel();
        $this->detalleModel = new ComedorPedidoDetalleModel();
        $this->itemModel    = new ComedorItemModel();
        $this->clienteModel = new ComedorClienteModel();
        $this->pagoModel    = new ComedorPagoModel();
    }

    public function index()
    {
        if (!tienePermiso('ver_pedidos_comedor')) {
            return redirect()->back()->with('permiso_error', 'Sin permiso.');
        }
        $fecha = $this->request->getGet('fecha') ?? date('Y-m-d');
        $data['pedidos'] = $this->headModel->delDia($fecha);
        $data['resumen'] = $this->headModel->resumenDia($fecha);
        $data['fecha']   = $fecha;
        $data['title']   = 'Pedidos del Comedor';
        return view('comedor/pedidos/index', $data);
    }

    public function nuevo()
    {
        if (!tienePermiso('tomar_pedido_comedor')) {
            return redirect()->back()->with('permiso_error', 'Sin permiso.');
        }
        $items = $this->itemModel->disponibles();

        // Agrupar por categoría para mostrar en el POS
        $porCategoria = [];
        foreach ($items as $item) {
            $cat = $item['categoria_nombre'] ?? 'Sin categoría';
            $porCategoria[$cat][] = $item;
        }

        $data['porCategoria'] = $porCategoria;
        $data['title']        = 'Tomar Pedido';
        return view('comedor/pedidos/nuevo', $data);
    }

    public function guardar()
    {
        if (!tienePermiso('tomar_pedido_comedor')) {
            return $this->response->setStatusCode(403);
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $itemsJson     = $this->request->getPost('items_json');
            $items         = json_decode($itemsJson, true);
            $tipoPago      = $this->request->getPost('tipo_pago');
            $clienteId     = (int)($this->request->getPost('cliente_id')) ?: null;
            $clienteNombre = trim($this->request->getPost('cliente_nombre'));
            $notas         = $this->request->getPost('notas');

            if (empty($items)) {
                throw new \Exception('No hay items en el pedido.');
            }
            if (!$clienteNombre) {
                throw new \Exception('El nombre del cliente es requerido.');
            }

            $total       = array_sum(array_column($items, 'subtotal'));
            $estado      = ($tipoPago === 'contado') ? 'pagado' : 'pendiente';
            $montoPagado = ($tipoPago === 'contado') ? $total : 0.0;
            $saldo       = $total - $montoPagado;

            $numero   = $this->headModel->generarNumero();
            $pedidoId = $this->headModel->insert([
                'numero'         => $numero,
                'cliente_id'     => $clienteId,
                'cliente_nombre' => $clienteNombre,
                'fecha'          => date('Y-m-d'),
                'total'          => $total,
                'monto_pagado'   => $montoPagado,
                'saldo'          => $saldo,
                'tipo_pago'      => $tipoPago,
                'estado'         => $estado,
                'notas'          => $notas,
                'created_by'     => session()->get('id'),
            ]);

            foreach ($items as $item) {
                $this->detalleModel->insert([
                    'pedido_id'       => $pedidoId,
                    'item_id'         => $item['item_id'] ?? null,
                    'item_nombre'     => $item['nombre'],
                    'precio_unitario' => $item['precio'],
                    'cantidad'        => $item['cantidad'],
                    'subtotal'        => $item['subtotal'],
                ]);
            }

            if ($tipoPago === 'contado') {
                $this->pagoModel->insert([
                    'pedido_id'  => $pedidoId,
                    'cliente_id' => $clienteId,
                    'monto'      => $total,
                    'notas'      => 'Pago contado',
                    'created_by' => session()->get('id'),
                ]);
            }

            // Si es fiado y hay cliente registrado, acumular saldo
            if ($tipoPago === 'fiado' && $clienteId) {
                $db->table('comedor_clientes')
                    ->where('id', $clienteId)
                    ->set('saldo_pendiente', "saldo_pendiente + {$saldo}", false)
                    ->update();
            }

            $db->transCommit();
            return redirect()->to('/comedor/pedidos/ver/' . $pedidoId)
                ->with('success', "Pedido {$numero} guardado correctamente.");

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function ver(int $id)
    {
        if (!tienePermiso('ver_pedidos_comedor')) {
            return redirect()->back()->with('permiso_error', 'Sin permiso.');
        }
        $data['pedido'] = $this->headModel->find($id);
        if (!$data['pedido']) {
            return redirect()->to('/comedor/pedidos')->with('error', 'Pedido no encontrado.');
        }
        $data['detalles'] = $this->detalleModel->delPedido($id);
        $data['pagos']    = $this->pagoModel->delPedido($id);
        $data['title']    = 'Pedido ' . $data['pedido']['numero'];
        return view('comedor/pedidos/ver', $data);
    }

    public function solicitudes()
    {
        if (!tienePermiso('confirmar_solicitud_comedor')) {
            return $this->response->setJSON([]);
        }
        $pedidos = $this->headModel
            ->select('comedor_pedidos_head.*')
            ->where('estado', 'solicitud')
            ->where('anulado', 0)
            ->orderBy('id', 'ASC')
            ->findAll();
        return $this->response->setJSON($pedidos);
    }

    public function listaSolicitudes()
    {
        if (!tienePermiso('confirmar_solicitud_comedor')) {
            return redirect()->back()->with('permiso_error', 'Sin permiso.');
        }
        $data['solicitudes'] = $this->headModel
            ->select('comedor_pedidos_head.*, comedor_pedidos_detalles.item_nombre, comedor_pedidos_detalles.cantidad')
            ->join('comedor_pedidos_detalles', 'comedor_pedidos_detalles.pedido_id = comedor_pedidos_head.id', 'left')
            ->where('comedor_pedidos_head.estado', 'solicitud')
            ->where('comedor_pedidos_head.anulado', 0)
            ->orderBy('comedor_pedidos_head.id', 'DESC')
            ->findAll();

        // Agrupar detalles por pedido
        $agrupado = [];
        foreach ($data['solicitudes'] as $row) {
            $id = $row['id'];
            if (!isset($agrupado[$id])) {
                $agrupado[$id] = $row;
                $agrupado[$id]['items'] = [];
            }
            if ($row['item_nombre']) {
                $agrupado[$id]['items'][] = $row['cantidad'] . '× ' . $row['item_nombre'];
            }
        }
        $data['solicitudes'] = array_values($agrupado);
        $data['title'] = 'Solicitudes de Clientes';
        return view('comedor/pedidos/solicitudes_lista', $data);
    }

    public function confirmar(int $id)
    {
        if (!tienePermiso('confirmar_solicitud_comedor')) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Sin permiso.']);
        }

        $pedido = $this->headModel->find($id);
        if (!$pedido || $pedido['estado'] !== 'solicitud') {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Solicitud no válida.']);
        }

        $tipoPagoPost  = $this->request->getPost('tipo_pago');
        $clienteIdPost = $this->request->getPost('cliente_id');

        // Si el cajero no envía valores, se respeta lo que el cliente ya eligió al pedir.
        $tipoPago  = $tipoPagoPost ?: $pedido['tipo_pago'];
        $clienteId = ($clienteIdPost !== null && $clienteIdPost !== '') ? (int) $clienteIdPost : $pedido['cliente_id'];

        if ($tipoPago === 'fiado' && !$clienteId) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Para confirmar un pedido fiado debes asociar un comensal.']);
        }

        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            $total       = (float) $pedido['total'];
            $montoPagado = ($tipoPago === 'contado') ? $total : 0.0;
            $saldo       = $total - $montoPagado;
            $estado      = ($tipoPago === 'contado') ? 'pagado' : 'pendiente';

            $this->headModel->update($id, [
                'tipo_pago'    => $tipoPago,
                'monto_pagado' => $montoPagado,
                'saldo'        => $saldo,
                'estado'       => $estado,
                'cliente_id'   => $clienteId,
                'created_by'   => session()->get('id'),
            ]);

            if ($tipoPago === 'contado') {
                $this->pagoModel->insert([
                    'pedido_id'  => $id,
                    'cliente_id' => $clienteId,
                    'monto'      => $total,
                    'notas'      => 'Pago confirmado por cajero',
                    'created_by' => session()->get('id'),
                ]);
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

    public function anular(int $id)
    {
        if (!tienePermiso('anular_pedido_comedor')) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Sin permiso.']);
        }

        $pedido = $this->headModel->find($id);
        if (!$pedido || $pedido['anulado']) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Pedido no válido o ya anulado.']);
        }

        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            $this->headModel->update($id, [
                'anulado'         => 1,
                'estado'          => 'anulado',
                'anulado_por'     => session()->get('id'),
                'fecha_anulacion' => date('Y-m-d H:i:s'),
            ]);

            // Revertir saldo si era fiado y aún tenía saldo pendiente
            if ($pedido['tipo_pago'] === 'fiado' && $pedido['cliente_id'] && $pedido['saldo'] > 0) {
                $db->table('comedor_clientes')
                    ->where('id', $pedido['cliente_id'])
                    ->set('saldo_pendiente', "GREATEST(0, saldo_pendiente - {$pedido['saldo']})", false)
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
