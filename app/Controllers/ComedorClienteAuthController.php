<?php

namespace App\Controllers;

use App\Models\ComedorClienteModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class ComedorClienteAuthController extends Controller
{
    protected ComedorClienteModel $clienteModel;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->clienteModel = new ComedorClienteModel();
    }

    public function registrar()
    {
        $nombre         = trim((string) $this->request->getPost('nombre'));
        $identificacion = trim((string) $this->request->getPost('identificacion'));
        $telefono       = trim((string) $this->request->getPost('telefono'));
        $password       = (string) $this->request->getPost('password');

        if (!$nombre || !$identificacion || !$password) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Nombre, DUI y contraseña son requeridos.']);
        }
        if (strlen($password) < 4) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'La contraseña debe tener al menos 4 caracteres.']);
        }

        $existente = $this->clienteModel->buscarPorIdentificacion($identificacion);

        if ($existente && !empty($existente['password'])) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Ya existe una cuenta con ese DUI. Inicia sesión.']);
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        if ($existente) {
            // Ficha creada antes por el cajero (deudor sin cuenta): se le asigna la cuenta.
            $clienteId = $existente['id'];
            $this->clienteModel->update($clienteId, [
                'nombre'   => $nombre,
                'telefono' => $telefono ?: $existente['telefono'],
                'password' => $hash,
            ]);
        } else {
            $clienteId = $this->clienteModel->insert([
                'nombre'          => $nombre,
                'identificacion'  => $identificacion,
                'telefono'        => $telefono ?: null,
                'password'        => $hash,
                'activo'          => 1,
                'saldo_pendiente' => 0,
            ]);
        }

        $this->iniciarSesion($clienteId, $nombre);

        return $this->response->setJSON(['ok' => true, 'nombre' => $nombre]);
    }

    public function login()
    {
        $identificacion = trim((string) $this->request->getPost('identificacion'));
        $password       = (string) $this->request->getPost('password');

        if (!$identificacion || !$password) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Ingresa tu DUI y contraseña.']);
        }

        $cliente = $this->clienteModel->buscarPorIdentificacion($identificacion);

        if (!$cliente || empty($cliente['password']) || !password_verify($password, $cliente['password'])) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'DUI o contraseña incorrectos.']);
        }

        if ((int) $cliente['activo'] !== 1) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Cuenta inactiva.']);
        }

        $this->iniciarSesion($cliente['id'], $cliente['nombre']);

        return $this->response->setJSON(['ok' => true, 'nombre' => $cliente['nombre']]);
    }

    public function logout()
    {
        session()->remove(['comedor_cliente_id', 'comedor_cliente_nombre', 'comedor_cliente_logged_in']);
        return $this->response->setJSON(['ok' => true]);
    }

    private function iniciarSesion(int $clienteId, string $nombre): void
    {
        session()->set([
            'comedor_cliente_id'        => $clienteId,
            'comedor_cliente_nombre'    => $nombre,
            'comedor_cliente_logged_in' => true,
        ]);
    }
}
