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
            ->where('tipo_pago', 'fiado')
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
            $this->aplicarPagoFifo($db, $clienteId, $monto, $notas);
            $db->transCommit();
            return $this->response->setJSON(['ok' => true]);
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->response->setJSON(['ok' => false, 'msg' => $e->getMessage()]);
        }
    }

    // Abona $monto a los pedidos fiado pendientes de un cliente (los más antiguos primero) y
    // recalcula su saldo_pendiente desde los pedidos (nunca incremental), para que tanto el
    // abono manual como la compensación con vuelto queden siempre consistentes.
    private function aplicarPagoFifo($db, int $clienteId, float $monto, string $notas): void
    {
        $pendientes = $this->pedidoModel
            ->where('cliente_id', $clienteId)
            ->where('tipo_pago', 'fiado')
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

        // Recalcular saldo total del cliente desde los pedidos (solo deuda fiado real)
        $suma = $db->table('comedor_pedidos_head')
            ->selectSum('saldo')
            ->where('cliente_id', $clienteId)
            ->where('tipo_pago', 'fiado')
            ->where('estado', 'pendiente')
            ->where('anulado', 0)
            ->get()->getRow();

        $db->table('comedor_clientes')
            ->where('id', $clienteId)
            ->update(['saldo_pendiente' => $suma->saldo ?? 0]);
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

    // Cuando un mismo cliente debe (saldo_pendiente) y a la vez se le debe (vuelto_pendiente),
    // en vez de cobrarle y luego darle cambio por separado, se netean entre sí: se abona el
    // menor de los dos montos a su deuda y se le resta lo mismo al vuelto que se le debe.
    public function compensar()
    {
        if (!tienePermiso('registrar_pago_deudor_comedor')) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Sin permiso.']);
        }

        $clienteId = (int) $this->request->getPost('cliente_id');
        $cliente   = $this->clienteModel->find($clienteId);
        if (!$cliente) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Comensal no encontrado.']);
        }

        $monto = round(min((float) $cliente['saldo_pendiente'], (float) $cliente['vuelto_pendiente']), 2);
        if ($monto <= 0) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Este comensal no tiene deuda y vuelto pendientes a la vez.']);
        }

        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            $this->aplicarPagoFifo($db, $clienteId, $monto, 'Compensación con vuelto pendiente');

            $db->table('comedor_clientes')
                ->where('id', $clienteId)
                ->set('vuelto_pendiente', "GREATEST(0, vuelto_pendiente - {$monto})", false)
                ->update();

            $db->transCommit();
            return $this->response->setJSON(['ok' => true, 'monto' => $monto]);
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->response->setJSON(['ok' => false, 'msg' => $e->getMessage()]);
        }
    }
}
