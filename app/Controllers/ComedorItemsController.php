<?php

namespace App\Controllers;

use App\Models\ComedorItemModel;
use App\Models\ComedorCategoriaModel;

class ComedorItemsController extends BaseController
{
    protected ComedorItemModel      $itemModel;
    protected ComedorCategoriaModel $catModel;

    public function __construct()
    {
        $this->itemModel = new ComedorItemModel();
        $this->catModel  = new ComedorCategoriaModel();
    }

    public function index()
    {
        if (!tienePermiso('ver_items_comedor')) {
            return redirect()->back()->with('permiso_error', 'No tiene permiso para ver items.');
        }
        $data['items'] = $this->itemModel->conCategoria();
        $data['title'] = 'Catálogo de Items';
        return view('comedor/items/index', $data);
    }

    public function nuevo()
    {
        if (!tienePermiso('gestionar_items_comedor')) {
            return redirect()->back()->with('permiso_error', 'No tiene permiso para crear items.');
        }
        $data['categorias'] = $this->catModel->where('activa', 1)->orderBy('nombre')->findAll();
        $data['item']       = null;
        $data['title']      = 'Nuevo Item';
        return view('comedor/items/form', $data);
    }

    public function crear()
    {
        if (!tienePermiso('gestionar_items_comedor')) {
            return $this->response->setStatusCode(403);
        }
        $this->itemModel->insert([
            'categoria_id' => $this->request->getPost('categoria_id') ?: null,
            'nombre'       => $this->request->getPost('nombre'),
            'descripcion'  => $this->request->getPost('descripcion'),
            'precio'       => $this->request->getPost('precio'),
            'disponible'   => $this->request->getPost('disponible') ? 1 : 0,
        ]);
        return redirect()->to('/comedor/items')->with('success', 'Item creado correctamente.');
    }

    public function editar(int $id)
    {
        if (!tienePermiso('gestionar_items_comedor')) {
            return redirect()->back()->with('permiso_error', 'No tiene permiso para editar items.');
        }
        $data['item']       = $this->itemModel->findOrFail($id);
        $data['categorias'] = $this->catModel->where('activa', 1)->orderBy('nombre')->findAll();
        $data['title']      = 'Editar Item';
        return view('comedor/items/form', $data);
    }

    public function actualizar(int $id)
    {
        if (!tienePermiso('gestionar_items_comedor')) {
            return $this->response->setStatusCode(403);
        }
        $this->itemModel->update($id, [
            'categoria_id' => $this->request->getPost('categoria_id') ?: null,
            'nombre'       => $this->request->getPost('nombre'),
            'descripcion'  => $this->request->getPost('descripcion'),
            'precio'       => $this->request->getPost('precio'),
            'disponible'   => $this->request->getPost('disponible') ? 1 : 0,
        ]);
        return redirect()->to('/comedor/items')->with('success', 'Item actualizado.');
    }

    public function toggleDisponible(int $id)
    {
        if (!tienePermiso('gestionar_items_comedor')) {
            return $this->response->setJSON(['ok' => false]);
        }
        $item = $this->itemModel->find($id);
        if (!$item) return $this->response->setJSON(['ok' => false]);
        $nuevoEstado = $item['disponible'] ? 0 : 1;
        $this->itemModel->update($id, ['disponible' => $nuevoEstado]);
        return $this->response->setJSON(['ok' => true, 'disponible' => $nuevoEstado]);
    }

    public function categorias()
    {
        if (!tienePermiso('gestionar_items_comedor')) {
            return redirect()->back()->with('permiso_error', 'Sin permiso.');
        }
        $data['categorias'] = $this->catModel->orderBy('nombre')->findAll();
        $data['title']      = 'Categorías del Comedor';
        return view('comedor/items/categorias', $data);
    }

    public function crearCategoria()
    {
        if (!tienePermiso('gestionar_items_comedor')) {
            return $this->response->setStatusCode(403);
        }
        $nombre = trim($this->request->getPost('nombre'));
        if (!$nombre) return redirect()->back()->with('error', 'El nombre es requerido.');
        $this->catModel->insert(['nombre' => $nombre, 'activa' => 1]);
        return redirect()->to('/comedor/categorias')->with('success', 'Categoría creada.');
    }

    public function toggleCategoria(int $id)
    {
        if (!tienePermiso('gestionar_items_comedor')) {
            return $this->response->setJSON(['ok' => false]);
        }
        $cat = $this->catModel->find($id);
        if (!$cat) return $this->response->setJSON(['ok' => false]);
        $this->catModel->update($id, ['activa' => $cat['activa'] ? 0 : 1]);
        return $this->response->setJSON(['ok' => true]);
    }
}
