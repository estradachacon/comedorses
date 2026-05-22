<?php

namespace App\Models;

use CodeIgniter\Model;

class ComedorMenuDiaModel extends Model
{
    protected $table         = 'comedor_menu_dia';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['fecha', 'item_id', 'desayuno', 'refrigerio', 'almuerzo'];
    protected $useTimestamps = false;
    protected $createdField  = 'created_at';

    public function itemsDelDia(?string $fecha = null): array
    {
        $fecha = $fecha ?? date('Y-m-d');
        return $this->select('comedor_menu_dia.item_id, comedor_menu_dia.desayuno,
                              comedor_menu_dia.refrigerio, comedor_menu_dia.almuerzo,
                              comedor_items.nombre, comedor_items.descripcion,
                              comedor_items.precio, comedor_categorias.nombre AS categoria_nombre')
            ->join('comedor_items',      'comedor_items.id = comedor_menu_dia.item_id')
            ->join('comedor_categorias', 'comedor_categorias.id = comedor_items.categoria_id', 'left')
            ->where('comedor_menu_dia.fecha', $fecha)
            ->orderBy('comedor_categorias.nombre')
            ->orderBy('comedor_items.nombre')
            ->findAll();
    }

    public function estaEnMenu(int $itemId, string $fecha): bool
    {
        return $this->where('fecha', $fecha)->where('item_id', $itemId)->countAllResults() > 0;
    }

    public function agregarItem(int $itemId, string $fecha): void
    {
        if (!$this->estaEnMenu($itemId, $fecha)) {
            $this->insert(['fecha' => $fecha, 'item_id' => $itemId]);
        }
    }

    public function quitarItem(int $itemId, string $fecha): void
    {
        $this->where('fecha', $fecha)->where('item_id', $itemId)->delete();
    }

    public function limpiarDia(string $fecha): void
    {
        $this->where('fecha', $fecha)->delete();
    }

    public function getServiciosDia(string $fecha): array
    {
        $rows = $this->select('item_id, desayuno, refrigerio, almuerzo')
                     ->where('fecha', $fecha)->findAll();
        $map = [];
        foreach ($rows as $r) {
            $map[$r['item_id']] = $r;
        }
        return $map;
    }

    public function setServicio(int $itemId, string $fecha, string $servicio, int $valor): void
    {
        $this->where('fecha', $fecha)->where('item_id', $itemId)
             ->set($servicio, $valor)->update();
    }

    public function ultimoDiaConMenu(string $fechaExcluida): ?array
    {
        return $this->db->table('comedor_menu_dia')
            ->select('fecha, COUNT(*) AS total')
            ->where('fecha <', $fechaExcluida)
            ->groupBy('fecha')
            ->orderBy('fecha', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray() ?: null;
    }

    public function copiarDeFecha(string $fechaOrigen, string $fechaDestino): int
    {
        $items = $this->where('fecha', $fechaOrigen)->findAll();
        $count = 0;
        foreach ($items as $item) {
            if (!$this->estaEnMenu($item['item_id'], $fechaDestino)) {
                $this->insert([
                    'fecha'      => $fechaDestino,
                    'item_id'    => $item['item_id'],
                    'desayuno'   => $item['desayuno'],
                    'refrigerio' => $item['refrigerio'],
                    'almuerzo'   => $item['almuerzo'],
                ]);
                $count++;
            }
        }
        return $count;
    }
}
