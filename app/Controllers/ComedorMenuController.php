<?php

namespace App\Controllers;

use App\Models\ComedorMenuDiaModel;
use App\Models\ComedorItemModel;

class ComedorMenuController extends BaseController
{
    protected ComedorMenuDiaModel $menuModel;
    protected ComedorItemModel    $itemModel;

    public function __construct()
    {
        $this->menuModel = new ComedorMenuDiaModel();
        $this->itemModel = new ComedorItemModel();
    }

    public function index()
    {
        if (!tienePermiso('gestionar_menu_comedor')) {
            return redirect()->back()->with('permiso_error', 'Sin permiso.');
        }

        $fecha = $this->request->getGet('fecha') ?? date('Y-m-d');

        // Todos los items disponibles del catálogo
        $todosItems = $this->itemModel
            ->select('comedor_items.*, comedor_categorias.nombre AS categoria_nombre')
            ->join('comedor_categorias', 'comedor_categorias.id = comedor_items.categoria_id', 'left')
            ->where('comedor_items.disponible', 1)
            ->orderBy('comedor_categorias.nombre')
            ->orderBy('comedor_items.nombre')
            ->findAll();

        // IDs que ya están en el menú de ese día
        $enMenu = array_column(
            $this->menuModel->where('fecha', $fecha)->findAll(),
            'item_id'
        );

        $data['todos']   = $todosItems;
        $data['enMenu']  = $enMenu;
        $data['fecha']   = $fecha;
        $data['urlPublico'] = base_url('menu');
        $data['title']   = 'Menú del Día';
        return view('comedor/menu/index', $data);
    }

    public function toggle()
    {
        if (!tienePermiso('gestionar_menu_comedor')) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Sin permiso.']);
        }

        $itemId = (int) $this->request->getPost('item_id');
        $fecha  = $this->request->getPost('fecha') ?? date('Y-m-d');

        if ($this->menuModel->estaEnMenu($itemId, $fecha)) {
            $this->menuModel->quitarItem($itemId, $fecha);
            $enMenu = false;
        } else {
            $this->menuModel->agregarItem($itemId, $fecha);
            $enMenu = true;
        }

        return $this->response->setJSON(['ok' => true, 'en_menu' => $enMenu]);
    }

    public function agregarTodos()
    {
        if (!tienePermiso('gestionar_menu_comedor')) {
            return $this->response->setJSON(['ok' => false]);
        }

        $fecha = $this->request->getPost('fecha') ?? date('Y-m-d');
        $items = $this->itemModel->where('disponible', 1)->findAll();

        foreach ($items as $item) {
            $this->menuModel->agregarItem($item['id'], $fecha);
        }

        return $this->response->setJSON(['ok' => true, 'count' => count($items)]);
    }

    public function limpiar()
    {
        if (!tienePermiso('gestionar_menu_comedor')) {
            return $this->response->setJSON(['ok' => false]);
        }

        $fecha = $this->request->getPost('fecha') ?? date('Y-m-d');
        $this->menuModel->limpiarDia($fecha);
        return $this->response->setJSON(['ok' => true]);
    }
}
