<?php

namespace App\Models;

use CodeIgniter\Model;

class ComedorItemModel extends Model
{
    protected $table         = 'comedor_items';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['categoria_id', 'nombre', 'descripcion', 'precio', 'disponible', 'foto'];
    protected $useTimestamps = true;

    public function disponibles(): array
    {
        return $this->select('comedor_items.*, comedor_categorias.nombre as categoria_nombre')
            ->join('comedor_categorias', 'comedor_categorias.id = comedor_items.categoria_id', 'left')
            ->where('comedor_items.disponible', 1)
            ->orderBy('comedor_categorias.nombre')
            ->orderBy('comedor_items.nombre')
            ->findAll();
    }

    public function conCategoria(): array
    {
        return $this->select('comedor_items.*, comedor_categorias.nombre as categoria_nombre')
            ->join('comedor_categorias', 'comedor_categorias.id = comedor_items.categoria_id', 'left')
            ->orderBy('comedor_categorias.nombre')
            ->orderBy('comedor_items.nombre')
            ->findAll();
    }
}
