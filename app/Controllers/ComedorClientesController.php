<?php

namespace App\Controllers;

use App\Models\ComedorClienteModel;

class ComedorClientesController extends BaseController
{
    protected ComedorClienteModel $model;

    public function __construct()
    {
        $this->model = new ComedorClienteModel();
    }

    public function index()
    {
        if (!tienePermiso('ver_clientes_comedor')) {
            return redirect()->back()->with('permiso_error', 'No tiene permiso para ver comensales.');
        }
        $data['clientes'] = $this->model->where('activo', 1)->orderBy('nombre')->findAll();
        $data['title']    = 'Comensales';
        return view('comedor/clientes/index', $data);
    }

    public function nuevo()
    {
        if (!tienePermiso('gestionar_clientes_comedor')) {
            return redirect()->back()->with('permiso_error', 'Sin permiso.');
        }
        $data['cliente'] = null;
        $data['title']   = 'Nuevo Comensal';
        return view('comedor/clientes/form', $data);
    }

    public function crear()
    {
        if (!tienePermiso('gestionar_clientes_comedor')) {
            return $this->response->setStatusCode(403);
        }
        $this->model->insert([
            'nombre'         => $this->request->getPost('nombre'),
            'identificacion' => $this->request->getPost('identificacion'),
            'telefono'       => $this->request->getPost('telefono'),
            'notas'          => $this->request->getPost('notas'),
            'activo'         => 1,
        ]);
        return redirect()->to('/comedor/clientes')->with('success', 'Comensal registrado correctamente.');
    }

    public function editar(int $id)
    {
        if (!tienePermiso('gestionar_clientes_comedor')) {
            return redirect()->back()->with('permiso_error', 'Sin permiso.');
        }
        $data['cliente'] = $this->model->findOrFail($id);
        $data['title']   = 'Editar Comensal';
        return view('comedor/clientes/form', $data);
    }

    public function actualizar(int $id)
    {
        if (!tienePermiso('gestionar_clientes_comedor')) {
            return $this->response->setStatusCode(403);
        }
        $this->model->update($id, [
            'nombre'         => $this->request->getPost('nombre'),
            'identificacion' => $this->request->getPost('identificacion'),
            'telefono'       => $this->request->getPost('telefono'),
            'notas'          => $this->request->getPost('notas'),
        ]);
        return redirect()->to('/comedor/clientes')->with('success', 'Comensal actualizado.');
    }

    public function buscar()
    {
        $q = $this->request->getGet('q') ?? '';
        $clientes = $this->model
            ->groupStart()
                ->like('nombre', $q)
                ->orLike('identificacion', $q)
            ->groupEnd()
            ->where('activo', 1)
            ->orderBy('nombre')
            ->findAll(15);
        return $this->response->setJSON($clientes);
    }

    // Alta rápida de comensal desde la pantalla de "Nuevo Pedido", sin necesidad de que el
    // comensal se loguee ni de salir a /comedor/clientes. Si el DUI ya existe, lo reutiliza.
    public function crearRapido()
    {
        if (!tienePermiso('tomar_pedido_comedor')) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Sin permiso.']);
        }

        $nombre         = trim((string) $this->request->getPost('nombre'));
        $identificacion = trim((string) $this->request->getPost('identificacion'));
        $telefono       = trim((string) $this->request->getPost('telefono'));

        if (!$nombre) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'El nombre es requerido.']);
        }

        if ($identificacion) {
            $existente = $this->model->buscarPorIdentificacion($identificacion);
            if ($existente) {
                return $this->response->setJSON([
                    'ok'         => true,
                    'id'         => $existente['id'],
                    'nombre'     => $existente['nombre'],
                    'ya_existia' => true,
                ]);
            }
        }

        $id = $this->model->insert([
            'nombre'         => $nombre,
            'identificacion' => $identificacion ?: null,
            'telefono'       => $telefono ?: null,
            'activo'         => 1,
        ]);

        return $this->response->setJSON(['ok' => true, 'id' => $id, 'nombre' => $nombre, 'ya_existia' => false]);
    }
}
