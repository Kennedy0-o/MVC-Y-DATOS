<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Services\PedidoService;
use App\Models\PedidoModel;

/**
 * Pruebas con dobles de prueba (Stub / Mock). No tocan la BD.
 * Trazabilidad: RN-03 -> CP-05 (Guía 6) -> TA-04
 */
class PedidoServiceMockTest extends CIUnitTestCase
{
    // TA-04a: STUB (solo devuelve datos controlados)
    public function testCambiarEstadoRechazaPedidoEntregado(): void
    {
        // Arrange
        $pedidoModel = $this->createStub(PedidoModel::class);
        $pedidoModel->method('find')
            ->willReturn(['id_pedido' => 10, 'estado' => 'entregado']);

        $service = new PedidoService($pedidoModel);

        // Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'No se puede cambiar el estado de un pedido que ya está entregado.'
        );

        // Act
        $service->cambiarEstado(10, 'listo');
    }

    // TA-04b: MOCK (verifica que update se llame una vez con los datos correctos)
    public function testCambiarEstadoActualizaUnaSolaVez(): void
    {
        // Arrange
        $pedidoModel = $this->createMock(PedidoModel::class);
        $pedidoModel->method('find')
            ->willReturn(['id_pedido' => 10, 'estado' => 'pendiente']);
        $pedidoModel->expects($this->once())
            ->method('update')
            ->with(10, ['estado' => 'listo']);

        $service = new PedidoService($pedidoModel);

        // Act
        $service->cambiarEstado(10, 'listo');

        // Assert: la expectativa del Mock actúa como verificación
    }

    // TA-04c: MOCK negativo (si la regla rechaza, update NUNCA se llama)
    public function testCambiarEstadoNoActualizaSiPedidoCancelado(): void
    {
        // Arrange
        $pedidoModel = $this->createMock(PedidoModel::class);
        $pedidoModel->method('find')
            ->willReturn(['id_pedido' => 11, 'estado' => 'cancelado']);
        $pedidoModel->expects($this->never())->method('update');

        $service = new PedidoService($pedidoModel);

        // Assert
        $this->expectException(\RuntimeException::class);

        // Act
        $service->cambiarEstado(11, 'listo');
    }
}