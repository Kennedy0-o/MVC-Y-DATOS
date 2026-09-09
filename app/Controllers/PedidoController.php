<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\PedidoService;
use App\Models\MesaModel;
use App\Models\ProductoModel;

/**
 * PedidoController - Recibe solicitudes y prepara respuestas
 * Delega la lógica de negocio a PedidoService
 */
class PedidoController extends BaseController
{
    protected $pedidoService;
    protected $mesaModel;
    protected $productoModel;

    public function __construct()
    {
        $this->pedidoService  = new PedidoService();
        $this->mesaModel      = new MesaModel();
        $this->productoModel  = new ProductoModel();
    }

    // ============================================================
    // READ - Listar todos los pedidos
    // ============================================================
    public function index()
    {
        $data['pedidos'] = $this->pedidoService->listarPedidos();
        $data['titulo']  = 'Listado de Pedidos - Sagras';
        return view('pedido/consultar', $data);
    }

    // ============================================================
    // READ - Ver detalle de un pedido
    // ============================================================
    public function ver($id = null)
    {
        $data['pedido']  = $this->pedidoService->obtenerPedido((int)$id);
        if (!$data['pedido']) {
            return redirect()->to('/pedidos')
                             ->with('error', 'El pedido solicitado no existe.');
        }
        $data['detalles'] = $this->pedidoService->obtenerDetallePorPedido((int)$id);
        $data['titulo']   = 'Detalle del Pedido #' . $id;
        return view('pedido/ver', $data);
    }

    // ============================================================
    // CREATE - Mostrar formulario de registro
    // ============================================================
    public function nuevo()
    {
        $data['mesas']     = $this->mesaModel->where('estado', 'libre')->findAll();
        $data['productos'] = $this->productoModel->where('disponible', 1)->findAll();
        $data['titulo']    = 'Registrar Pedido - Sagras';
        $data['accion']    = 'registrar';
        return view('pedido/formulario', $data);
    }

    // ============================================================
    // CREATE - Procesar registro
    // ============================================================
    public function registrar()
    {
        try {
            $datos = [
                'id_mesa'    => $this->request->getPost('id_mesa'),
                'id_usuario' => $this->request->getPost('id_usuario'),
                'productos'  => $this->request->getPost('productos') ?? [],
                'cantidades' => $this->request->getPost('cantidades') ?? [],
            ];

            $idPedido = $this->pedidoService->registrar($datos);

            return redirect()->to('/pedidos/ver/' . $idPedido)
                             ->with('success', 'Pedido registrado y enviado a cocina correctamente.');

        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                             ->withInput()
                             ->with('error', $e->getMessage());
        } catch (\RuntimeException $e) {
            return redirect()->back()
                             ->withInput()
                             ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            log_message('error', '[PedidoController::registrar] ' . $e->getMessage());
            return redirect()->back()
                             ->withInput()
                             ->with('error', 'Ocurrió un error inesperado. Intente nuevamente.');
        }
    }

    // ============================================================
    // UPDATE - Mostrar formulario de edición
    // ============================================================
    public function editar($id = null)
    {
        $pedido = $this->pedidoService->obtenerPedido((int)$id);
        if (!$pedido) {
            return redirect()->to('/pedidos')
                             ->with('error', 'El pedido no existe.');
        }
        if (in_array($pedido['estado'], ['entregado', 'cancelado'])) {
            return redirect()->to('/pedidos')
                             ->with('error', 'No se puede editar un pedido ' . $pedido['estado'] . '.');
        }

        $data['pedido']    = $pedido;
        $data['detalles']  = $this->pedidoService->obtenerDetallePorPedido((int)$id);
        $data['mesas']     = $this->mesaModel->where('estado', 'libre')->orWhere('id_mesa', $pedido['id_mesa'])->findAll();
        $data['productos'] = $this->productoModel->where('disponible', 1)->findAll();
        $data['titulo']    = 'Editar Pedido #' . $id;
        $data['accion']    = 'actualizar/' . $id;
        return view('pedido/formulario', $data);
    }

    // ============================================================
    // UPDATE - Procesar actualización
    // ============================================================
    public function actualizar($id = null)
    {
        try {
            $datos = [
                'id_mesa'    => $this->request->getPost('id_mesa'),
                'id_usuario' => $this->request->getPost('id_usuario'),
                'productos'  => $this->request->getPost('productos') ?? [],
                'cantidades' => $this->request->getPost('cantidades') ?? [],
            ];

            $this->pedidoService->actualizar((int)$id, $datos);

            return redirect()->to('/pedidos/ver/' . $id)
                             ->with('success', 'Pedido actualizado correctamente.');

        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                             ->withInput()
                             ->with('error', $e->getMessage());
        } catch (\RuntimeException $e) {
            return redirect()->back()
                             ->withInput()
                             ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            log_message('error', '[PedidoController::actualizar] ' . $e->getMessage());
            return redirect()->back()
                             ->withInput()
                             ->with('error', 'Ocurrió un error inesperado al actualizar.');
        }
    }

    // ============================================================
    // DELETE - Cancelar pedido (eliminación lógica)
    // ============================================================
    public function cancelar($id = null)
{
        try {
            $this->pedidoService->cancelar((int)$id);
            return redirect()->to(base_url('pedidos'))
                            ->with('success', 'Pedido cancelado correctamente. La mesa ha sido liberada.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->to(base_url('pedidos'))
                            ->with('error', $e->getMessage());
        } catch (\RuntimeException $e) {
            return redirect()->to(base_url('pedidos'))
                            ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            log_message('error', '[PedidoController::cancelar] ' . $e->getMessage());
            return redirect()->to(base_url('pedidos'))
                            ->with('error', 'Ocurrió un error al cancelar el pedido.');
        }
    }
}
