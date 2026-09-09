<?php
namespace App\Models;
use CodeIgniter\Model;
class PreparacionModel extends Model
{
    protected $table = 'preparacion';
    protected $primaryKey = 'id_preparacion';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = ['id_pedido','estado','hora_inicio','hora_fin'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
