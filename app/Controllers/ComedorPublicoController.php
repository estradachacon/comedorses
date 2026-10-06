<?php

namespace App\Controllers;

use App\Models\ComedorMenuDiaModel;
use App\Models\ComedorPedidoHeadModel;
use App\Models\ComedorPedidoDetalleModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class ComedorPublicoController extends Controller
{
    protected ComedorMenuDiaModel       $menuModel;
    protected ComedorPedidoHeadModel    $headModel;
    protected ComedorPedidoDetalleModel $detalleModel;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        date_default_timezone_set('America/El_Salvador');
        $this->menuModel    = new ComedorMenuDiaModel();
        $this->headModel    = new ComedorPedidoHeadModel();
        $this->detalleModel = new ComedorPedidoDetalleModel();
    }

    public function index()
    {
        $items = $this->menuModel->itemsDelDia(date('Y-m-d'));

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
        $clienteNombre  = trim($this->request->getPost('cliente_nombre'));
        $notas          = $this->request->getPost('notas');
        $tipoPago       = $this->request->getPost('tipo_pago') === 'fiado' ? 'fiado' : 'contado';
        $montoRecibido  = $this->request->getPost('monto_recibido');
        $montoRecibido  = ($montoRecibido !== null && $montoRecibido !== '') ? (float) $montoRecibido : null;

        $clienteSesionId     = session()->get('comedor_cliente_logged_in') ? session()->get('comedor_cliente_id') : null;
        $clienteSesionNombre = session()->get('comedor_cliente_nombre');

        if ($tipoPago === 'fiado' && !$clienteSesionId) {
            return $this->response->setJSON([
                'ok'              => false,
                'requiere_cuenta' => true,
                'msg'             => 'Debes iniciar sesión o crear una cuenta para pedir fiado.',
            ]);
        }

        if ($clienteSesionId) {
            $clienteNombre = $clienteSesionNombre;
        }

        if (empty($items) || !$clienteNombre) {
            return $this->response->setJSON([
                'ok'  => false,
                'msg' => 'Completa tu nombre y selecciona al menos un item.',
            ]);
        }

        if ($montoRecibido !== null && $montoRecibido < array_sum(array_column($items, 'subtotal'))) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'El monto con el que pagas no puede ser menor al total.']);
        }

        // Validar que los items pertenecen al menú de hoy (seguridad)
        $menuHoy = array_column($this->menuModel->itemsDelDia(date('Y-m-d')), null, 'item_id');
        foreach ($items as $item) {
            if (!isset($menuHoy[$item['item_id']])) {
                return $this->response->setJSON(['ok' => false, 'msg' => 'Item no disponible hoy.']);
            }
        }

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
                    'precio_unitario' => $item['precio'],
                    'cantidad'        => $item['cantidad'],
                    'subtotal'        => $item['subtotal'],
                ]);
            }

            $db->transCommit();
            return $this->response->setJSON([
                'ok'     => true,
                'numero' => $numero,
                'total'  => number_format($total, 2),
            ]);
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->response->setJSON(['ok' => false, 'msg' => 'Error al guardar. Intenta de nuevo.']);
        }
    }
}
