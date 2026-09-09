<?php
namespace App\Models;
use CodeIgniter\Model;
class ProductoModel extends Model
{
    protected $table = 'producto';
    protected $primaryKey = 'id_producto';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = ['nombre','precio','id_categoria','disponible','imagen'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $validationRules = [
        'nombre' => 'required|min_length[2]|max_length[100]',
        'precio' => 'required|decimal',
        'id_categoria' => 'required|is_natural_no_zero',
        'disponible' => 'required|in_list[0,1]',
    ];
}
