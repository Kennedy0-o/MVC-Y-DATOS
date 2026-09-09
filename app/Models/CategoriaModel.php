<?php
namespace App\Models;
use CodeIgniter\Model;
class CategoriaModel extends Model
{
    protected $table = 'categoria';
    protected $primaryKey = 'id_categoria';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = ['nombre'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
}
