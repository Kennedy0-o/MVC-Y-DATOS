<?php

namespace App\Services;

use App\Models\PedidoModel;
use App\Models\DetallePedidoModel;
use App\Models\MesaModel;
use App\Models\ProductoModel;
use App\Models\PreparacionModel;
use App\Models\PagoModel;

/**
 * PedidoService - Capa de lógica de negocio
 * Separa la persistencia (Model) de las reglas del negocio
 */
class PedidoService
{
    protected $pedidoModel;
    protected $detalleModel;
    protected $mesaModel;
    protected $productoModel;
    protected $preparacionModel;
    protected $pagoModel;

    public function __construct()
    {
        $this->pedidoModel      = new PedidoModel();
        $this->detalleModel     = new DetallePedidoModel();
        $this->mesaModel        = new MesaModel();
        $this->productoModel    = new ProductoModel();
        $this->preparacionModel = new PreparacionModel();
        $this->pagoModel        = new PagoModel();
    }

    // ================================================================
    // REGLAS DE NEGOCIO
    // ================================================================

    /**
     * RN-01: Una mesa ocupada no puede recibir un nuevo pedido.
     */
    private function validarMesaDisponible(int $idMesa, ?int $excluirPedido = null): void
    {
        $mesa = $this->mesaModel->find($idMesa);
        if (!$mesa) {
            throw new \InvalidArgumentException('La mesa seleccionada no existe.');
        }
        if ($mesa['estado'] === 'ocupada') {
            // Si es edición, permitir mantener la misma mesa
            if ($excluirPedido) {
                $pedidoActual = $this->pedidoModel->find($excluirPedido);
                if ($pedidoActual && $pedidoActual['id_mesa'] == $idMesa) {
                    return;
                }
            }
            throw new \RuntimeException('La mesa está ocupada. Seleccione otra mesa.');
        }
    }

    /**
     * RN-02: Solo productos disponibles pueden agregarse al pedido.
     */
    private function validarProductosDisponibles(array $productos): void
    {
        foreach ($productos as $idProducto) {
            $prod = $this->productoModel->find($idProducto);
            if (!$prod) {
                throw new \InvalidArgumentException("El producto ID {$idProducto} no existe.");
            }
            if (!$prod['disponible']) {
                throw new \RuntimeException("El producto '{$prod['nombre']}' no está disponible.");
            }
        }
    }

    /**
     * RN-03: No se puede modificar un pedido entregado o cancelado.
     */
    private function validarPedidoEditable(int $idPedido): void
    {
        $pedido = $this->pedidoModel->find($idPedido);
        if (!$pedido) {
            throw new \InvalidArgumentException('El pedido no existe.');
        }
        if (in_array($pedido['estado'], ['entregado', 'cancelado'])) {
            throw new \RuntimeException('No se puede modificar un pedido que ya está ' . $pedido['estado'] . '.');
        }
    }

    /**
     * RN-04: No se puede cancelar un pedido que ya fue pagado.
     */
    private function validarPedidoCancelable(int $idPedido): void
    {
        $pago = $this->pagoModel->where('id_pedido', $idPedido)->first();
        if ($pago) {
            throw new \RuntimeException('No se puede cancelar un pedido que ya tiene un pago registrado.');
        }
    }

    /**
     * RN-05: No se puede eliminar físicamente; solo desactivar (cancelar).
     */
    private function validarEliminacionLogica(int $idPedido): void
    {
        $this->validarPedidoCancelable($idPedido);
    }

    // ================================================================
    // VALIDACIONES SINTÁCTICAS
    // ================================================================
    private function validarDatosEntrada(array $datos, bool $esEdicion = false): void
    {
        if (empty($datos['id_mesa']) || !is_numeric($datos['id_mesa'])) {
            throw new \InvalidArgumentException('El campo mesa es obligatorio y debe ser numérico.');
        }
        if (empty($datos['id_usuario']) || !is_numeric($datos['id_usuario'])) {
            throw new \InvalidArgumentException('El campo mozo es obligatorio y debe ser numérico.');
        }
        if (empty($datos['productos']) || !is_array($datos['productos'])) {
            throw new \InvalidArgumentException('Debe seleccionar al menos un producto.');
        }
        if (count($datos['productos']) > 20) {
            throw new \InvalidArgumentException('No puede seleccionar más de 20 productos por pedido.');
        }
    }

    // ================================================================
    // CRUD - CREATE
    // ================================================================
    public function registrar(array $datos): int
    {
        // 1. Validaciones sintácticas
        $this->validarDatosEntrada($datos);

        $idMesa      = (int) $datos['id_mesa'];
        $idUsuario   = (int) $datos['id_usuario'];
        $productos   = $datos['productos'];
        $cantidades  = $datos['cantidades'] ?? [];

        // 2. Validaciones semánticas / Reglas de negocio
        $this->validarMesaDisponible($idMesa);
        $this->validarProductosDisponibles($productos);

        // 3. Transacción de persistencia
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Calcular total
            $total = 0;
            foreach ($productos as $index => $idProducto) {
                $prod = $this->productoModel->find($idProducto);
                $cant = isset($cantidades[$index]) ? (int)$cantidades[$index] : 1;
                if ($cant < 1 || $cant > 50) {
                    throw new \InvalidArgumentException("Cantidad inválida para '{$prod['nombre']}'.");
                }
                $total += $prod['precio'] * $cant;
            }

            // Insertar pedido
            $pedidoData = [
                'id_mesa'    => $idMesa,
                'id_usuario' => $idUsuario,
                'fecha'      => date('Y-m-d H:i:s'),
                'estado'     => 'pendiente',
                'total'      => $total,
            ];
            $this->pedidoModel->insert($pedidoData);
            $idPedido = $this->pedidoModel->getInsertID();

            // Insertar detalles
            foreach ($productos as $index => $idProducto) {
                $prod = $this->productoModel->find($idProducto);
                $cant = isset($cantidades[$index]) ? (int)$cantidades[$index] : 1;
                $this->detalleModel->insert([
                    'id_pedido'       => $idPedido,
                    'id_producto'     => $idProducto,
                    'cantidad'        => $cant,
                    'precio_unitario' => $prod['precio'],
                    'subtotal'        => $prod['precio'] * $cant,
                ]);
            }

            // Actualizar mesa
            $this->mesaModel->update($idMesa, ['estado' => 'ocupada']);

            // Crear preparación
            $this->preparacionModel->insert([
                'id_pedido' => $idPedido,
                'estado'    => 'pendiente',
            ]);

            $db->transComplete();
            return $idPedido;

        } catch (\Exception $e) {
            $db->transRollback();
            throw $e;
        }
    }

    // ================================================================
    // CRUD - READ (ya existía, se mantiene)
    // ================================================================
    public function listarPedidos(): array
    {
        return $this->pedidoModel->getPedidosConDetalle();
    }

    public function obtenerPedido(int $id): ?array
    {
        return $this->pedidoModel->getPedidoPorId($id);
    }

    public function obtenerDetallePorPedido(int $id): array
    {
        return $this->detalleModel->getDetallePorPedido($id);
    }

    // ================================================================
    // CRUD - UPDATE
    // ================================================================
    public function actualizar(int $idPedido, array $datos): void
    {
        // 1. Validaciones sintácticas
        $this->validarDatosEntrada($datos, true);

        $idMesa     = (int) $datos['id_mesa'];
        $productos  = $datos['productos'];
        $cantidades = $datos['cantidades'] ?? [];

        // 2. Reglas de negocio
        $this->validarPedidoEditable($idPedido);
        $this->validarMesaDisponible($idMesa, $idPedido);
        $this->validarProductosDisponibles($productos);

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $pedidoActual = $this->pedidoModel->find($idPedido);
            $mesaAnterior = (int) $pedidoActual['id_mesa'];

            // Calcular nuevo total
            $total = 0;
            foreach ($productos as $index => $idProducto) {
                $prod = $this->productoModel->find($idProducto);
                $cant = isset($cantidades[$index]) ? (int)$cantidades[$index] : 1;
                $total += $prod['precio'] * $cant;
            }

            // Actualizar pedido
            $this->pedidoModel->update($idPedido, [
                'id_mesa' => $idMesa,
                'total'   => $total,
            ]);

            // Eliminar detalles antiguos e insertar nuevos
            $this->detalleModel->where('id_pedido', $idPedido)->delete();
            foreach ($productos as $index => $idProducto) {
                $prod = $this->productoModel->find($idProducto);
                $cant = isset($cantidades[$index]) ? (int)$cantidades[$index] : 1;
                $this->detalleModel->insert([
                    'id_pedido'       => $idPedido,
                    'id_producto'     => $idProducto,
                    'cantidad'        => $cant,
                    'precio_unitario' => $prod['precio'],
                    'subtotal'        => $prod['precio'] * $cant,
                ]);
            }

            // Si cambió la mesa, liberar la anterior y ocupar la nueva
            if ($mesaAnterior !== $idMesa) {
                $this->mesaModel->update($mesaAnterior, ['estado' => 'libre']);
                $this->mesaModel->update($idMesa, ['estado' => 'ocupada']);
            }

            $db->transComplete();

        } catch (\Exception $e) {
            $db->transRollback();
            throw $e;
        }
    }

    // ================================================================
    // CRUD - DELETE (Desactivación lógica = Cancelar)
    // ================================================================
    public function cancelar(int $idPedido): void
    {
        // 1. Validar existencia
        $pedido = $this->pedidoModel->find($idPedido);
        if (!$pedido) {
            throw new \InvalidArgumentException('El pedido no existe.');
        }

        // 2. Reglas de negocio
        $this->validarPedidoCancelable($idPedido);

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Cambiar estado a cancelado (eliminación lógica)
            $this->pedidoModel->update($idPedido, ['estado' => 'cancelado']);

            // Liberar la mesa
            $this->mesaModel->update($pedido['id_mesa'], ['estado' => 'libre']);

            // Actualizar preparación si existe
            $prep = $this->preparacionModel->where('id_pedido', $idPedido)->first();
            if ($prep) {
                $this->preparacionModel->update($prep['id_preparacion'], ['estado' => 'cancelado']);
            }

            $db->transComplete();

        } catch (\Exception $e) {
            $db->transRollback();
            throw $e;
        }
    }
}
