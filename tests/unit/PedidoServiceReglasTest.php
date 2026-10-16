<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Services\PedidoService;

/**
 * Pruebas UNITARIAS puras (sin consultas a BD).
 * Trazabilidad: RN-03 -> CP-05 (Guía 6) -> TA-01 / TA-02 / TA-03
 */
class PedidoServiceReglasTest extends CIUnitTestCase
{
    // TA-01: regla válida (pedido pendiente sí puede cambiar de estado)
    public function testPuedeCambiarEstadoConPedidoPendiente(): void
    {
        // Arrange
        $service = new PedidoService();

        // Act
        $resultado = $service->puedeCambiarEstado('pendiente');

        // Assert
        $this->assertTrue($resultado);
    }

    // TA-02: regla inválida (pedido entregado NO puede cambiar de estado)
    public function testNoPuedeCambiarEstadoConPedidoEntregado(): void
    {
        // Arrange
        $service = new PedidoService();

        // Act
        $resultado = $service->puedeCambiarEstado('entregado');

        // Assert
        $this->assertFalse($resultado);
    }

    // TA-03: excepción esperada (más de 20 productos por pedido)
    // Trazabilidad: CP-07 (frontera 21 productos) -> TA-03
    public function testRegistrarLanzaExcepcionConMasDe20Productos(): void
    {
        // Arrange
        $service = new PedidoService();
        $datos = [
            'id_mesa'    => 1,
            'id_usuario' => 1,
            'productos'  => range(1, 21),
            'cantidades' => array_fill(0, 21, 1),
        ];

        // Assert (se declara ANTES del Act)
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No puede seleccionar más de 20 productos por pedido.');

        // Act
        $service->registrar($datos);
    }

    // TA-03b: excepción por estado inválido en el flujo de cocina
    public function testCambiarEstadoLanzaExcepcionConEstadoInvalido(): void
    {
        // Arrange
        $service = new PedidoService();

        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Estado inválido.');

        // Act
        $service->cambiarEstado(1, 'inventado');
    }
}