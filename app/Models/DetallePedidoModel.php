<?php
namespace App\Models;
use CodeIgniter\Model;
class DetallePedidoModel extends Model
{
    protected $table = 'detalle_pedido';
    protected $primaryKey = 'id_detalle';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = ['id_pedido','id_producto','cantidad','precio_unitario','subtotal'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = '';   // 👈 agrega esta línea

    public function getDetallePorPedido($idPedido)
    {
        return $this->select('detalle_pedido.*, producto.nombre as producto_nombre')
                    ->join('producto', 'producto.id_producto = detalle_pedido.id_producto')
                    ->where('id_pedido', $idPedido)
                    ->findAll();
    }
}