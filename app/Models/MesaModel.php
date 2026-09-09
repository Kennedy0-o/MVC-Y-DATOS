<?php
namespace App\Models;
use CodeIgniter\Model;
class MesaModel extends Model
{
    protected $table = 'mesa';
    protected $primaryKey = 'id_mesa';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = ['numero','capacidad','estado','nivel'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
}
