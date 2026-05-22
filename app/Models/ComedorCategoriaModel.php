<?php

namespace App\Models;

use CodeIgniter\Model;

class ComedorCategoriaModel extends Model
{
    protected $table         = 'comedor_categorias';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['nombre', 'activa'];
    protected $useTimestamps = true;
}
