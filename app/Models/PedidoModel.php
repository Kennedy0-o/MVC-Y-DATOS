<?php
namespace App\Models;
use CodeIgniter\Model;
class PedidoModel extends Model
{
    protected $table = 'pedido';
    protected $primaryKey = 'id_pedido';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = ['id_mesa','id_usuario','fecha','estado','total'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $validationRules = [
        'id_mesa' => 'required|is_natural_no_zero',
        'id_usuario' => 'required|is_natural_no_zero',
        'estado' => 'required|in_list[pendiente,en_preparacion,listo,entregado,cancelado]',
        'total' => 'required|decimal',
    ];
    public function getPedidosConDetalle()
    {
        return $this->select('pedido.*, mesa.numero as mesa_numero, usuario.nombre as mozo')
                    ->join('mesa', 'mesa.id_mesa = pedido.id_mesa')
                    ->join('usuario', 'usuario.id_usuario = pedido.id_usuario')
                    ->orderBy('pedido.id_pedido', 'DESC')
                    ->findAll();
    }
    public function getPedidoPorId($id)
    {
        return $this->select('pedido.*, mesa.numero as mesa_numero, usuario.nombre as mozo')
                    ->join('mesa', 'mesa.id_mesa = pedido.id_mesa')
                    ->join('usuario', 'usuario.id_usuario = pedido.id_usuario')
                    ->where('pedido.id_pedido', $id)
                    ->first();
    }
}
