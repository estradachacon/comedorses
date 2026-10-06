<?php

namespace App\Controllers;

use App\Models\ComedorClienteModel;
use App\Models\ComedorPedidoHeadModel;
use App\Models\ComedorPagoModel;

class ComedorDeudoresController extends BaseController
{
    protected ComedorClienteModel    $clienteModel;
    protected ComedorPedidoHeadModel $pedidoModel;
    protected ComedorPagoModel       $pagoModel;

    public function __construct()
    {
        $this->clienteModel = new ComedorClienteModel();
        $this->pedidoModel  = new ComedorPedidoHeadModel();
        $this->pagoModel    = new ComedorPagoModel();
    }

    public function index()
    {
        if (!tienePermiso('ver_deudores_comedor')) {
            return redirect()->back()->with('permiso_error', 'Sin permiso.');
        }
        $data['comensales'] = $this->clienteModel->todos();
        $data['title']       = 'Deudores del Comedor';
        return view('comedor/deudores/index', $data);
    }

    public function pendientesCliente(int $clienteId)
    {
        if (!tienePermiso('ver_deudores_comedor')) {
            return $this->response->setStatusCode(403);
        }
        $pedidos = $this->pedidoModel
            ->where('cliente_id', $clienteId)
            ->where('estado', 'pendiente')
            ->where('anulado', 0)
            ->orderBy('fecha', 'ASC')
            ->findAll();
        return $this->response->setJSON($pedidos);
    }

    public function registrarPago()
    {
        if (!tienePermiso('registrar_pago_deudor_comedor')) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Sin permiso.']);
        }

        $clienteId = (int) $this->request->getPost('cliente_id');
        $monto     = (float) $this->request->getPost('monto');
        $notas     = $this->request->getPost('notas') ?: 'Abono';

        if ($monto <= 0) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'El monto debe ser mayor a cero.']);
        }

        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            $pendientes = $this->pedidoModel
                ->where('cliente_id', $clienteId)
                ->where('estado', 'pendiente')
                ->where('anulado', 0)
                ->orderBy('fecha', 'ASC')
                ->findAll();

            $restante = $monto;
            foreach ($pendientes as $p) {
                if ($restante <= 0) break;

                $aplicar          = min($restante, (float) $p['saldo']);
                $nuevoSaldo       = round((float) $p['saldo'] - $aplicar, 2);
                $nuevoMontoPagado = round((float) $p['monto_pagado'] + $aplicar, 2);
                $nuevoEstado      = ($nuevoSaldo <= 0) ? 'pagado' : 'pendiente';

                $this->pedidoModel->update($p['id'], [
                    'saldo'        => $nuevoSaldo,
                    'monto_pagado' => $nuevoMontoPagado,
                    'estado'       => $nuevoEstado,
                ]);

                $this->pagoModel->insert([
                    'pedido_id'  => $p['id'],
                    'cliente_id' => $clienteId,
                    'monto'      => $aplicar,
                    'notas'      => $notas,
                    'created_by' => session()->get('id'),
                ]);

                $restante -= $aplicar;
            }

            // Recalcular saldo total del cliente desde los pedidos
            $suma = $db->table('comedor_pedidos_head')
                ->selectSum('saldo')
                ->where('cliente_id', $clienteId)
                ->where('estado', 'pendiente')
                ->where('anulado', 0)
                ->get()->getRow();

            $db->table('comedor_clientes')
                ->where('id', $clienteId)
                ->update(['saldo_pendiente' => $suma->saldo ?? 0]);

            $db->transCommit();
            return $this->response->setJSON(['ok' => true]);
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->response->setJSON(['ok' => false, 'msg' => $e->getMessage()]);
        }
    }

    // Liquida (total o parcialmente) el vuelto que el comedor le quedó debiendo a un cliente.
    public function entregarVuelto()
    {
        if (!tienePermiso('registrar_pago_deudor_comedor')) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Sin permiso.']);
        }

        $clienteId = (int) $this->request->getPost('cliente_id');
        $monto     = (float) $this->request->getPost('monto');

        $cliente = $this->clienteModel->find($clienteId);
        if (!$cliente) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Comensal no encontrado.']);
        }
        if ($monto <= 0) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'El monto debe ser mayor a cero.']);
        }
        if ($monto > (float) $cliente['vuelto_pendiente']) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'El monto no puede ser mayor al vuelto pendiente.']);
        }

        $db = \Config\Database::connect();
        $db->table('comedor_clientes')
            ->where('id', $clienteId)
            ->set('vuelto_pendiente', "GREATEST(0, vuelto_pendiente - {$monto})", false)
            ->update();

        return $this->response->setJSON(['ok' => true]);
    }
}
