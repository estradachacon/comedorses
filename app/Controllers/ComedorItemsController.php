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

        $data = [
            'categoria_id' => $this->request->getPost('categoria_id') ?: null,
            'nombre'       => $this->request->getPost('nombre'),
            'descripcion'  => $this->request->getPost('descripcion'),
            'precio'       => $this->request->getPost('precio'),
            'disponible'   => $this->request->getPost('disponible') ? 1 : 0,
        ];

        $fotoError = $this->procesarFotoItem($data);
        if ($fotoError) {
            return redirect()->back()->with('error', $fotoError)->withInput();
        }

        $this->itemModel->insert($data);
        return redirect()->to('/comedor/items')->with('success', 'Item creado correctamente.');
    }

    // Valida y mueve la foto subida (cámara o galería), agregándola a $data['foto'] si todo
    // sale bien. Devuelve un mensaje de error si la foto es inválida, o null si no hay problema
    // (incluye el caso de no haber subido ninguna foto, ya que es opcional).
    private function procesarFotoItem(array &$data): ?string
    {
        $foto = $this->request->getFile('foto');
        if (!$foto || !$foto->isValid() || $foto->hasMoved()) {
            return null;
        }
        if (!in_array(strtolower($foto->getClientExtension()), ['jpg', 'jpeg', 'png', 'webp'])) {
            return 'La foto debe ser JPG, PNG o WEBP.';
        }
        if ($foto->getSize() > 3 * 1024 * 1024) {
            return 'La foto no debe superar 3MB.';
        }
        $nombreFoto = $foto->getRandomName();
        $foto->move('upload/comedor_items', $nombreFoto);
        $data['foto'] = $nombreFoto;
        return null;
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

        $data = [
            'categoria_id' => $this->request->getPost('categoria_id') ?: null,
            'nombre'       => $this->request->getPost('nombre'),
            'descripcion'  => $this->request->getPost('descripcion'),
            'precio'       => $this->request->getPost('precio'),
            'disponible'   => $this->request->getPost('disponible') ? 1 : 0,
        ];

        $fotoError = $this->procesarFotoItem($data);
        if ($fotoError) {
            return redirect()->back()->with('error', $fotoError)->withInput();
        }

        $this->itemModel->update($id, $data);
        return redirect()->to('/comedor/items')->with('success', 'Item actualizado.');
    }

    // Edición rápida (modal) usada desde /comedor/menu: nombre, precio, descripción, categoría, disponible y foto opcional.
    public function actualizarRapido(int $id)
    {
        if (!tienePermiso('gestionar_items_comedor')) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Sin permiso.']);
        }

        $item = $this->itemModel->find($id);
        if (!$item) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Item no encontrado.']);
        }

        $nombre = trim((string) $this->request->getPost('nombre'));
        $precio = $this->request->getPost('precio');

        if (!$nombre || !is_numeric($precio)) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Nombre y precio válidos son requeridos.']);
        }

        // No incluye 'disponible': ese campo se gestiona desde el catálogo (/comedor/items),
        // no desde este modal rápido en la vista del menú diario.
        $data = [
            'categoria_id' => $this->request->getPost('categoria_id') ?: null,
            'nombre'       => $nombre,
            'descripcion'  => $this->request->getPost('descripcion'),
            'precio'       => $precio,
        ];

        $fotoError = $this->procesarFotoItem($data);
        if ($fotoError) {
            return $this->response->setJSON(['ok' => false, 'msg' => $fotoError]);
        }

        $this->itemModel->update($id, $data);
        $actualizado = $this->itemModel->find($id);

        return $this->response->setJSON([
            'ok'   => true,
            'item' => [
                'id'          => $actualizado['id'],
                'nombre'      => $actualizado['nombre'],
                'precio'      => $actualizado['precio'],
                'descripcion' => $actualizado['descripcion'],
                'categoria_id'=> $actualizado['categoria_id'],
                'disponible'  => (int) $actualizado['disponible'],
                'foto_url'    => $actualizado['foto'] ? base_url('upload/comedor_items/' . $actualizado['foto']) : null,
            ],
        ]);
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

    public function actualizarCategoria(int $id)
    {
        if (!tienePermiso('gestionar_items_comedor')) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Sin permiso.']);
        }
        $cat = $this->catModel->find($id);
        if (!$cat) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Categoría no encontrada.']);
        }
        $nombre = trim((string) $this->request->getPost('nombre'));
        if (!$nombre) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'El nombre es requerido.']);
        }
        $this->catModel->update($id, ['nombre' => $nombre]);
        return $this->response->setJSON(['ok' => true, 'nombre' => $nombre]);
    }
}
