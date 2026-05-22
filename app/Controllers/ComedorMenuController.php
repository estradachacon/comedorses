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

        $data['todos']      = $todosItems;
        $data['enMenu']     = $enMenu;
        $data['servicios']  = $this->menuModel->getServiciosDia($fecha);
        $data['fecha']      = $fecha;
        $data['urlPublico'] = base_url('menu');
        $data['ultimoMenu'] = empty($enMenu) ? $this->menuModel->ultimoDiaConMenu($fecha) : null;
        $data['title']      = 'Menú del Día';
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

    public function setServicio()
    {
        if (!tienePermiso('gestionar_menu_comedor')) {
            return $this->response->setJSON(['ok' => false]);
        }

        $itemId   = (int) $this->request->getPost('item_id');
        $fecha    = $this->request->getPost('fecha') ?? date('Y-m-d');
        $servicio = $this->request->getPost('servicio');
        $valor    = (int) $this->request->getPost('valor');

        if (!in_array($servicio, ['desayuno', 'refrigerio', 'almuerzo'])) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Servicio inválido.']);
        }

        $this->menuModel->setServicio($itemId, $fecha, $servicio, $valor);
        return $this->response->setJSON(['ok' => true]);
    }

    public function whatsapp()
    {
        if (!tienePermiso('gestionar_menu_comedor')) {
            return $this->response->setJSON(['ok' => false]);
        }

        $fecha = $this->request->getGet('fecha') ?? date('Y-m-d');
        $items = $this->menuModel->itemsDelDia($fecha);

        $complementos = [];
        $grupos       = ['desayuno' => [], 'refrigerio' => [], 'almuerzo' => []];
        $sinServicio  = [];

        foreach ($items as $item) {
            $activos = (int)$item['desayuno'] + (int)$item['refrigerio'] + (int)$item['almuerzo'];

            if ($activos === 3) {
                $complementos[] = $item;
            } elseif ($activos === 0) {
                $sinServicio[] = $item;
            } else {
                foreach (['desayuno', 'refrigerio', 'almuerzo'] as $s) {
                    if (!empty($item[$s])) {
                        $grupos[$s][] = $item;
                    }
                }
            }
        }

        $emojis    = ['desayuno' => '☀️', 'refrigerio' => '🥪', 'almuerzo' => '🍽️'];
        $etiquetas = ['desayuno' => 'Desayuno', 'refrigerio' => 'Refrigerio', 'almuerzo' => 'Almuerzo'];

        $lineas = ['Buen día!! ☀️☀️'];
        foreach ($grupos as $key => $grupo) {
            if (empty($grupo)) continue;
            $lineas[] = '';
            $lineas[] = $emojis[$key] . ' ' . $etiquetas[$key];
            foreach ($grupo as $item) {
                $precio = '$' . number_format($item['precio'], 2);
                $lineas[] = ($key === 'almuerzo' ? '✔️ ' : '') . $item['nombre'] . ' ' . $precio;
            }
        }

        if (!empty($complementos)) {
            $lineas[] = '';
            $lineas[] = '🌟 Complementos (todo el día)';
            foreach ($complementos as $item) {
                $lineas[] = $item['nombre'] . ' $' . number_format($item['precio'], 2);
            }
        }

        if (!empty($sinServicio)) {
            $lineas[] = '';
            $lineas[] = '📋 Sin servicio asignado';
            foreach ($sinServicio as $item) {
                $lineas[] = $item['nombre'] . ' $' . number_format($item['precio'], 2);
            }
        }

        return $this->response->setJSON([
            'ok'    => true,
            'texto' => implode("\n", $lineas),
        ]);
    }

    public function copiarUltimo()
    {
        if (!tienePermiso('gestionar_menu_comedor')) {
            return $this->response->setJSON(['ok' => false]);
        }

        $fecha  = $this->request->getPost('fecha') ?? date('Y-m-d');
        $ultimo = $this->menuModel->ultimoDiaConMenu($fecha);

        if (!$ultimo) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'No hay menú anterior registrado.']);
        }

        $count = $this->menuModel->copiarDeFecha($ultimo['fecha'], $fecha);
        return $this->response->setJSON(['ok' => true, 'count' => $count, 'desde' => $ultimo['fecha']]);
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
