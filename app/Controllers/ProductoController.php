<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProductoModel;

class ProductoController extends BaseController
{
    protected $productoModel;

    public function __construct()
    {
        $this->productoModel = new ProductoModel();
    }

    public function index()
    {
        $data['productos'] = $this->productoModel
            ->select('producto.*, categoria.nombre as categoria_nombre')
            ->join('categoria', 'categoria.id_categoria = producto.id_categoria')
            ->findAll();
        $data['titulo'] = 'Catalogo de Productos - Sagras';
        return view('producto/consultar', $data);
    }
}
