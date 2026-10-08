<?php

namespace App\Controllers;

use App\Models\ComedorPedidoHeadModel;
use App\Models\ComedorPedidoDetalleModel;
use App\Models\ComedorItemModel;
use App\Models\ComedorClienteModel;
use App\Models\ComedorPagoModel;
use App\Models\ComedorMenuDiaModel;

class ComedorPedidosController extends BaseController
{
    protected ComedorPedidoHeadModel    $headModel;
    protected ComedorPedidoDetalleModel $detalleModel;
    protected ComedorItemModel          $itemModel;
    protected ComedorClienteModel       $clienteModel;
    protected ComedorPagoModel          $pagoModel;
    protected ComedorMenuDiaModel       $menuDiaModel;

    public function __construct()
    {
        $this->headModel    = new ComedorPedidoHeadModel();
        $this->detalleModel = new ComedorPedidoDetalleModel();
        $this->itemModel    = new ComedorItemModel();
        $this->clienteModel = new ComedorClienteModel();
        $this->pagoModel    = new ComedorPagoModel();
        $this->menuDiaModel = new ComedorMenuDiaModel();
    }

    // Horario de un item tomado directamente en el POS: si tiene 2 o 3 horarios asignados hoy,
    // se respeta lo que el cajero eligió en pantalla (si es válido); con un solo horario
    // asignado es automático; sin menú de hoy o sin horarios asignados, sin restricción (NULL).
    private function servicioParaItemPos(?int $itemId, string $fecha, ?string $solicitado = null): ?string
    {
        if (!$itemId) {
            return null;
        }
        $menuDia = $this->menuDiaModel->where('fecha', $fecha)->where('item_id', $itemId)->first();
        if (!$menuDia) {
            return null;
        }
        $asignados = serviciosAsignadosComedor($menuDia);
        if ($solicitado && in_array($solicitado, $asignados, true)) {
            return $solicitado;
        }
        return count($asignados) === 1 ? $asignados[0] : null;
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

        // El POS vende de todo el catálogo (no solo lo publicado en el menú de hoy), pero si un
        // item SÍ está en el menú de hoy con 2 o 3 horarios asignados, igual hay que preguntarle
        // al cajero para cuál horario es, igual que en el menú público.
        $menuHoy = array_column($this->menuDiaModel->where('fecha', date('Y-m-d'))->findAll(), null, 'item_id');
        foreach ($items as &$item) {
            $menuDia = $menuHoy[$item['id']] ?? ['desayuno' => 0, 'refrigerio' => 0, 'almuerzo' => 0];
            $item['requiere_horario']    = requiereElegirHorarioComedor($menuDia);
            $item['servicios_asignados'] = serviciosAsignadosComedor($menuDia);
        }
        unset($item);

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
            $itemsJson         = $this->request->getPost('items_json');
            $items             = json_decode($itemsJson, true);
            $tipoPago          = $this->request->getPost('tipo_pago');
            $clienteId         = (int)($this->request->getPost('cliente_id')) ?: null;
            $clienteNombre     = trim($this->request->getPost('cliente_nombre'));
            $notas             = $this->request->getPost('notas');
            $montoRecibidoPost = $this->request->getPost('monto_recibido');
            $montoRecibido     = ($montoRecibidoPost !== null && $montoRecibidoPost !== '') ? (float) $montoRecibidoPost : null;
            $vueltoPendiente   = (bool) $this->request->getPost('vuelto_pendiente');
            $entregarAhora     = (bool) $this->request->getPost('entregar_ahora');

            if (empty($items)) {
                throw new \Exception('No hay items en el pedido.');
            }
            if (!$clienteNombre) {
                throw new \Exception('El nombre del cliente es requerido.');
            }

            $total = array_sum(array_column($items, 'subtotal'));

            if ($tipoPago === 'contado' && $montoRecibido !== null && $montoRecibido < $total) {
                throw new \Exception('El monto con el que paga no puede ser menor al total.');
            }

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
                'monto_recibido' => $tipoPago === 'contado' ? $montoRecibido : null,
                'entregado_at'   => $entregarAhora ? date('Y-m-d H:i:s') : null,
                'entregado_por'  => $entregarAhora ? session()->get('id') : null,
                'created_by'     => session()->get('id'),
            ]);

            foreach ($items as $item) {
                $this->detalleModel->insert([
                    'pedido_id'       => $pedidoId,
                    'item_id'         => $item['item_id'] ?? null,
                    'item_nombre'     => $item['nombre'],
                    'servicio'        => $this->servicioParaItemPos($item['item_id'] ?? null, date('Y-m-d'), $item['servicio'] ?? null),
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
        $data['title']    = formatearNumeroPedido($data['pedido']['numero']);

        // Si el pedido se tomó a nombre de alguien pero, al momento de confirmar/entregar, el
        // cajero vinculó la cuenta a OTRO comensal (p. ej. para registrarle la deuda a él en vez
        // de a quien pidió), mostrarlo explícitamente en vez de dejarlo solo implícito en cliente_id.
        $data['comensalActual'] = null;
        if ($data['pedido']['cliente_id']) {
            $comensal = $this->clienteModel->find($data['pedido']['cliente_id']);
            if ($comensal && mb_strtolower(trim($comensal['nombre'])) !== mb_strtolower(trim($data['pedido']['cliente_nombre']))) {
                $data['comensalActual'] = $comensal;
            }
        }

        return view('comedor/pedidos/ver', $data);
    }

    public function solicitudes()
    {
        if (!tienePermiso('confirmar_solicitud_comedor')) {
            return $this->response->setJSON([]);
        }
        $rows = $this->headModel
            ->select('comedor_pedidos_head.*, comedor_pedidos_detalles.item_nombre, comedor_pedidos_detalles.cantidad')
            ->join('comedor_pedidos_detalles', 'comedor_pedidos_detalles.pedido_id = comedor_pedidos_head.id', 'left')
            ->where('comedor_pedidos_head.estado', 'solicitud')
            ->where('comedor_pedidos_head.anulado', 0)
            ->orderBy('comedor_pedidos_head.id', 'ASC')
            ->findAll();

        return $this->response->setJSON($this->agruparConItems($rows));
    }

    public function listaSolicitudes()
    {
        if (!tienePermiso('confirmar_solicitud_comedor')) {
            return redirect()->back()->with('permiso_error', 'Sin permiso.');
        }
        $rows = $this->headModel
            ->select('comedor_pedidos_head.*, comedor_pedidos_detalles.item_nombre, comedor_pedidos_detalles.cantidad')
            ->join('comedor_pedidos_detalles', 'comedor_pedidos_detalles.pedido_id = comedor_pedidos_head.id', 'left')
            ->where('comedor_pedidos_head.estado', 'solicitud')
            ->where('comedor_pedidos_head.anulado', 0)
            ->orderBy('comedor_pedidos_head.id', 'ASC')
            ->findAll();

        $data['solicitudes'] = $this->agruparConItems($rows);
        $data['title'] = 'Solicitudes de Clientes';
        return view('comedor/pedidos/solicitudes_lista', $data);
    }

    // Agrupa filas pedido+detalle (resultado de un join) en una fila por pedido con su lista de items.
    private function agruparConItems(array $rows): array
    {
        $agrupado = [];
        foreach ($rows as $row) {
            $id = $row['id'];
            if (!isset($agrupado[$id])) {
                $agrupado[$id] = $row;
                $agrupado[$id]['items'] = [];
            }
            if ($row['item_nombre']) {
                $agrupado[$id]['items'][] = $row['cantidad'] . '× ' . $row['item_nombre'];
            }
        }
        return array_values($agrupado);
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

        // Confirmar solo acepta el pedido (sale de "solicitud" y entra al control del comedor).
        // NO resuelve el pago todavía: eso se decide hasta la entrega real, en /comedor/entregas.
        // monto_pagado/saldo quedan como se crearon (0 / total) hasta ese momento.
        $this->headModel->update($id, [
            'tipo_pago'  => $tipoPago,
            'estado'     => 'pendiente',
            'cliente_id' => $clienteId,
            'created_by' => session()->get('id'),
        ]);

        return $this->response->setJSON(['ok' => true]);
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
