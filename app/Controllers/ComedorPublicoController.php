<?php

namespace App\Controllers;

use App\Models\ComedorMenuDiaModel;
use App\Models\ComedorPedidoHeadModel;
use App\Models\ComedorPedidoDetalleModel;
use App\Models\ComedorClienteModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class ComedorPublicoController extends Controller
{
    protected ComedorMenuDiaModel       $menuModel;
    protected ComedorPedidoHeadModel    $headModel;
    protected ComedorPedidoDetalleModel $detalleModel;
    protected ComedorClienteModel       $clienteModel;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        date_default_timezone_set('America/El_Salvador');
        $this->menuModel    = new ComedorMenuDiaModel();
        $this->headModel    = new ComedorPedidoHeadModel();
        $this->detalleModel = new ComedorPedidoDetalleModel();
        $this->clienteModel = new ComedorClienteModel();
    }

    public function index()
    {
        $items = $this->menuModel->itemsDelDia(date('Y-m-d'));

        // Info de horarios de servicio (desayuno/refrigerio/almuerzo) para cada item
        foreach ($items as &$item) {
            $item['servicios_asignados'] = serviciosAsignadosComedor($item);
            $item['servicios_abiertos']  = serviciosAbiertosComedor($item);
            $item['disponible_ahora']    = itemDisponibleAhoraComedor($item);
            $item['requiere_horario']    = requiereElegirHorarioComedor($item);
        }
        unset($item);

        // Agrupar por categoría
        $porCategoria = [];
        foreach ($items as $item) {
            $cat = $item['categoria_nombre'] ?? 'Otros';
            $porCategoria[$cat][] = $item;
        }

        $data['porCategoria']  = $porCategoria;
        $data['fecha']         = date('Y-m-d');
        $data['menuVacio']     = empty($items);
        $data['clienteSesion'] = session()->get('comedor_cliente_logged_in')
            ? ['id' => session()->get('comedor_cliente_id'), 'nombre' => session()->get('comedor_cliente_nombre')]
            : null;
        return view('comedor_publico/index', $data);
    }

    public function guardar()
    {
        $itemsJson      = $this->request->getPost('items_json');
        $items          = json_decode($itemsJson, true);
        $notas          = $this->request->getPost('notas');
        $tipoPago       = $this->request->getPost('tipo_pago') === 'fiado' ? 'fiado' : 'contado';
        $montoRecibido  = $this->request->getPost('monto_recibido');
        $montoRecibido  = ($montoRecibido !== null && $montoRecibido !== '') ? (float) $montoRecibido : null;

        $clienteSesionId     = session()->get('comedor_cliente_logged_in') ? session()->get('comedor_cliente_id') : null;
        $clienteSesionNombre = session()->get('comedor_cliente_nombre');

        // Toda solicitud (contado o fiado) requiere cuenta: es lo que permite llevar
        // el control de lo pagado, lo que debe el cliente y el vuelto que se le debe.
        if (!$clienteSesionId) {
            return $this->response->setJSON([
                'ok'              => false,
                'requiere_cuenta' => true,
                'msg'             => 'Debes iniciar sesión o crear una cuenta para pedir.',
            ]);
        }

        $clienteNombre = $clienteSesionNombre;

        if (empty($items)) {
            return $this->response->setJSON([
                'ok'  => false,
                'msg' => 'Selecciona al menos un item.',
            ]);
        }

        if ($montoRecibido !== null && $montoRecibido < array_sum(array_column($items, 'subtotal'))) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'El monto con el que pagas no puede ser menor al total.']);
        }

        // Validar que los items pertenecen al menú de hoy y que su horario de servicio sigue vigente (seguridad)
        $menuHoy = array_column($this->menuModel->itemsDelDia(date('Y-m-d')), null, 'item_id');
        foreach ($items as &$item) {
            $menuItem = $menuHoy[$item['item_id']] ?? null;
            if (!$menuItem) {
                return $this->response->setJSON(['ok' => false, 'msg' => 'Item no disponible hoy.']);
            }

            if (!itemDisponibleAhoraComedor($menuItem)) {
                return $this->response->setJSON(['ok' => false, 'msg' => $menuItem['nombre'] . ' ya no está disponible a esta hora.']);
            }

            $asignados = serviciosAsignadosComedor($menuItem);
            $abiertos  = serviciosAbiertosComedor($menuItem);

            if (requiereElegirHorarioComedor($menuItem)) {
                $servicio = $item['servicio'] ?? null;
                if ($servicio && !in_array($servicio, $abiertos, true)) {
                    return $this->response->setJSON(['ok' => false, 'msg' => 'Ese horario ya no está disponible para ' . $menuItem['nombre'] . '.']);
                }
                if (!$servicio) {
                    if (count($abiertos) === 1) {
                        $servicio = $abiertos[0];
                    } else {
                        return $this->response->setJSON(['ok' => false, 'msg' => 'Indica para qué horario deseas ' . $menuItem['nombre'] . '.']);
                    }
                }
                $item['servicio_resuelto'] = $servicio;
            } elseif (count($asignados) === 1) {
                $item['servicio_resuelto'] = $asignados[0];
            } else {
                $item['servicio_resuelto'] = null;
            }
        }
        unset($item);

        $total  = array_sum(array_column($items, 'subtotal'));
        $numero = $this->headModel->generarNumero();

        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            $pedidoId = $this->headModel->insert([
                'numero'         => $numero,
                'cliente_id'     => $clienteSesionId,
                'cliente_nombre' => $clienteNombre,
                'fecha'          => date('Y-m-d'),
                'total'          => $total,
                'monto_pagado'   => 0,
                'saldo'          => $total,
                'tipo_pago'      => $tipoPago,
                'monto_recibido' => $tipoPago === 'contado' ? $montoRecibido : null,
                'estado'         => 'solicitud',
                'origen'         => 'cliente',
                'notas'          => $notas,
                'created_by'     => 1, // sistema
            ]);

            foreach ($items as $item) {
                $this->detalleModel->insert([
                    'pedido_id'       => $pedidoId,
                    'item_id'         => $item['item_id'],
                    'item_nombre'     => $item['nombre'],
                    'servicio'        => $item['servicio_resuelto'] ?? null,
                    'precio_unitario' => $item['precio'],
                    'cantidad'        => $item['cantidad'],
                    'subtotal'        => $item['subtotal'],
                ]);
            }

            $db->transCommit();
            return $this->response->setJSON([
                'ok'     => true,
                'numero' => formatearNumeroPedido($numero),
                'total'  => number_format($total, 2),
            ]);
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->response->setJSON(['ok' => false, 'msg' => 'Error al guardar. Intenta de nuevo.']);
        }
    }

    // Historial de pedidos del comensal logueado, con su saldo pendiente (debe) y vuelto pendiente (le deben).
    public function historial()
    {
        $clienteId = session()->get('comedor_cliente_logged_in') ? session()->get('comedor_cliente_id') : null;
        if (!$clienteId) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Debes iniciar sesión.']);
        }

        $cliente = $this->clienteModel->find($clienteId);

        $rows = $this->headModel
            ->select('comedor_pedidos_head.*, comedor_pedidos_detalles.item_nombre,
                      comedor_pedidos_detalles.cantidad, comedor_pedidos_detalles.servicio')
            ->join('comedor_pedidos_detalles', 'comedor_pedidos_detalles.pedido_id = comedor_pedidos_head.id', 'left')
            ->where('comedor_pedidos_head.cliente_id', $clienteId)
            ->orderBy('comedor_pedidos_head.id', 'DESC')
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
                    'texto'    => $row['cantidad'] . '× ' . $row['item_nombre'],
                    'servicio' => $row['servicio'],
                ];
            }
        }

        return $this->response->setJSON([
            'ok'      => true,
            'cliente' => [
                'nombre'           => $cliente['nombre'],
                'saldo_pendiente'  => $cliente['saldo_pendiente'],
                'vuelto_pendiente' => $cliente['vuelto_pendiente'],
            ],
            'pedidos' => array_values($agrupado),
        ]);
    }
}
