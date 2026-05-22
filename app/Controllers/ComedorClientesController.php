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
            ->like('nombre', $q)
            ->where('activo', 1)
            ->orderBy('nombre')
            ->findAll(15);
        return $this->response->setJSON($clientes);
    }
}
