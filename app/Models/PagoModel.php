<?php
namespace App\Models;
use CodeIgniter\Model;
class PagoModel extends Model
{
    protected $table = 'pago';
    protected $primaryKey = 'id_pago';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = ['id_pedido','metodo','monto','fecha_pago'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
}
