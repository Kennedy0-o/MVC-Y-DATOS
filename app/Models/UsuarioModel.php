<?php
namespace App\Models;
use CodeIgniter\Model;
class UsuarioModel extends Model
{
    protected $table = 'usuario';
    protected $primaryKey = 'id_usuario';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = ['nombre','email','password','rol','estado'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $validationRules = [
        'nombre' => 'required|min_length[3]|max_length[100]',
        'email' => 'required|valid_email|is_unique[usuario.email]',
        'password' => 'required|min_length[6]',
        'rol' => 'required|in_list[administrador,mozo,cocina,caja,gerente]',
        'estado' => 'required|in_list[activo,inactivo]',
    ];
}
