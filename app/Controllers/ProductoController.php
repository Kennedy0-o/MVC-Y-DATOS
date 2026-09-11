<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProductoModel;
use App\Models\CategoriaModel;

class ProductoController extends BaseController
{
    protected $productoModel;
    protected $categoriaModel;

    public function __construct()
    {
        $this->productoModel  = new ProductoModel();
        $this->categoriaModel = new CategoriaModel();
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

    public function nuevo()
    {
        $data['categorias'] = $this->categoriaModel->findAll();
        $data['titulo']     = 'Registrar Producto - Sagras';
        return view('producto/formulario', $data);
    }

    public function registrar()
    {
        try {
            $datos = [
                'nombre'       => $this->request->getPost('nombre'),
                'precio'       => $this->request->getPost('precio'),
                'id_categoria' => $this->request->getPost('id_categoria'),
                'disponible'   => $this->request->getPost('disponible') ?? 0,
            ];

            if (!$this->productoModel->insert($datos)) {
                $errores = $this->productoModel->errors();
                throw new \InvalidArgumentException(reset($errores) ?: 'Datos de producto inválidos.');
            }

            return redirect()->to('/productos')
                             ->with('success', 'Producto registrado correctamente.');

        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            log_message('error', '[ProductoController::registrar] ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Ocurrió un error inesperado al registrar el producto.');
        }
    }
}