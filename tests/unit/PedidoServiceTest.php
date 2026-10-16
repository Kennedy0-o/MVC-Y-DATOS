<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Services\PedidoService;
use App\Models\MesaModel;
use App\Models\ProductoModel;
use App\Models\PedidoModel;
use App\Models\DetallePedidoModel;
use App\Models\PagoModel;
use App\Models\UsuarioModel;

/**
 * Pruebas del módulo de Pedidos (PedidoService).
 * Corresponde al Plan de Pruebas PT-01 (Guía 6).
 *
 * $refresh = true  -> cada test corre dentro de una transacción que se
 *                      revierte automáticamente al terminar (no ensucia tu BD real).
 * $migrate = false -> no usamos migraciones de CI4, las tablas ya existen en "sagras".
 */
class PedidoServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    // OJO: $refresh en false a propósito. PedidoService abre sus propias
    // transacciones ($db->transStart()/transComplete()) dentro de registrar(),
    // actualizar() y cancelar(). Cuando eso corre DENTRO de la transacción que
    // el framework de pruebas abre para revertir todo solo, MySQL no soporta
    // transacciones anidadas de verdad: la transacción interna del service
    // termina haciendo COMMIT antes de tiempo y el rollback automático ya no
    // tiene nada que revertir (por eso aparecía basura en la BD real).
    // En vez de eso, limpiamos a mano en tearDown().
    protected $refresh = false;
    protected $migrate = false;

    protected PedidoService $service;
    protected MesaModel $mesaModel;
    protected ProductoModel $productoModel;
    protected PedidoModel $pedidoModel;
    protected DetallePedidoModel $detalleModel;
    protected PagoModel $pagoModel;
    protected UsuarioModel $usuarioModel;
    protected \App\Models\PreparacionModel $preparacionModel;

    // IDs creados durante el test, para borrarlos todos en tearDown()
    protected array $pedidosCreados   = [];
    protected array $mesasCreadas     = [];
    protected array $productosCreados = [];
    protected array $usuariosCreados  = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->service          = new PedidoService();
        $this->mesaModel        = new MesaModel();
        $this->productoModel    = new ProductoModel();
        $this->pedidoModel      = new PedidoModel();
        $this->detalleModel     = new DetallePedidoModel();
        $this->pagoModel        = new PagoModel();
        $this->usuarioModel     = new UsuarioModel();
        $this->preparacionModel = new \App\Models\PreparacionModel();
    }

    /**
     * Borra, en orden (hijos antes que padres), todo lo que crearon los
     * helpers y las llamadas al service durante el test.
     */
    protected function tearDown(): void
    {
        foreach ($this->pedidosCreados as $idPedido) {
            $this->detalleModel->where('id_pedido', $idPedido)->delete();
            $this->preparacionModel->where('id_pedido', $idPedido)->delete();
            $this->pagoModel->where('id_pedido', $idPedido)->delete();
            $this->pedidoModel->delete($idPedido, true);
        }
        foreach ($this->productosCreados as $idProducto) {
            $this->productoModel->delete($idProducto, true);
        }
        foreach ($this->mesasCreadas as $idMesa) {
            $this->mesaModel->delete($idMesa, true);
        }
        foreach ($this->usuariosCreados as $idUsuario) {
            $this->usuarioModel->delete($idUsuario, true);
        }

        parent::tearDown();
    }

    // ============================================================
    // HELPERS: crean datos de prueba mínimos y devuelven su ID
    // ============================================================

    private function crearUsuario(): int
    {
        $id = $this->usuarioModel->insert([
            'nombre'   => 'Mozo de Prueba',
            'email'    => 'mozo' . uniqid() . '@test.com',
            'password' => password_hash('123456', PASSWORD_DEFAULT),
            'rol'      => 'mozo',
            'estado'   => 'activo',
        ]);
        $this->usuariosCreados[] = $id;
        return $id;
    }

    private function crearMesa(string $estado = 'libre'): int
    {
        $id = $this->mesaModel->insert([
            'numero'    => rand(100, 9999),
            'capacidad' => 4,
            'estado'    => $estado,
            'nivel'     => 1,
        ]);
        $this->mesasCreadas[] = $id;
        return $id;
    }

    private function crearProducto(bool $disponible = true, float $precio = 10.0): int
    {
        $id = $this->productoModel->insert([
            'nombre'       => 'Producto Test ' . uniqid(),
            'precio'       => $precio,
            'id_categoria' => 1,
            'disponible'   => $disponible ? 1 : 0,
        ]);
        $this->productosCreados[] = $id;
        return $id;
    }

    /**
     * Crea varios productos de golpe y devuelve el arreglo de IDs (CP-07).
     */
    private function crearProductos(int $cantidad, bool $disponible = true, float $precio = 5.0): array
    {
        $ids = [];
        for ($i = 0; $i < $cantidad; $i++) {
            $ids[] = $this->crearProducto($disponible, $precio);
        }
        return $ids;
    }

    /**
     * Crea un pedido "ya existente" directamente en el modelo (sin pasar por el
     * service), para usarlo como precondición de casos que EDITAN o CANCELAN
     * un pedido (CP-02, CP-05, CP-06).
     */
    private function crearPedido(int $idMesa, int $idUsuario, string $estado = 'pendiente', float $total = 10.0): int
    {
        $id = $this->pedidoModel->insert([
            'id_mesa'    => $idMesa,
            'id_usuario' => $idUsuario,
            'fecha'      => date('Y-m-d H:i:s'),
            'estado'     => $estado,
            'total'      => $total,
        ]);
        $this->pedidosCreados[] = $id;
        return $id;
    }

    /**
     * Inserta una línea de detalle para un pedido ya creado con crearPedido().
     */
    private function crearDetalle(int $idPedido, int $idProducto, int $cantidad, float $precio): int
    {
        return $this->detalleModel->insert([
            'id_pedido'       => $idPedido,
            'id_producto'     => $idProducto,
            'cantidad'        => $cantidad,
            'precio_unitario' => $precio,
            'subtotal'        => $precio * $cantidad,
        ]);
    }

    /**
     * Inserta un pago para un pedido, simulando que ya fue pagado (CP-06).
     * Uso el query builder directo (no PagoModel) porque el modelo tiene
     * $useTimestamps activado e intenta escribir 'updated_at', columna que
     * tu tabla 'pago' no tiene.
     */
    private function crearPago(int $idPedido, float $monto = 10.0): int
    {
        $db = \Config\Database::connect();
        $db->table('pago')->insert([
            'id_pedido'  => $idPedido,
            'monto'      => $monto,
            'metodo'     => 'efectivo',
            'fecha_pago' => date('Y-m-d H:i:s'),
        ]);
        return (int) $db->insertID();
    }

    // ============================================================
    // CP-01: Registrar pedido válido (positivo)
    // ============================================================
    public function testCP01_RegistrarPedidoValido(): void
    {
        // Precondición
        $idMesa    = $this->crearMesa('libre');
        $idUsuario = $this->crearUsuario();
        $idProd1   = $this->crearProducto(true, 10.0); // subtotal 10 * 1 = 10
        $idProd2   = $this->crearProducto(true, 5.0);  // subtotal 5 * 2 = 10

        // Acción
        $idPedido = $this->service->registrar([
            'id_mesa'    => $idMesa,
            'id_usuario' => $idUsuario,
            'productos'  => [$idProd1, $idProd2],
            'cantidades' => [1, 2],
        ]);

        // Resultado esperado
        $this->assertIsInt($idPedido);
        $this->pedidosCreados[] = $idPedido; // para limpieza en tearDown()

        $pedido = $this->pedidoModel->find($idPedido);
        $this->assertSame('pendiente', $pedido['estado']);
        $this->assertEquals(20.0, (float) $pedido['total']);

        $mesa = $this->mesaModel->find($idMesa);
        $this->assertSame('ocupada', $mesa['estado']);

        $this->seeInDatabase('detalle_pedido', [
            'id_pedido'   => $idPedido,
            'id_producto' => $idProd1,
            'cantidad'    => 1,
        ]);
        $this->seeInDatabase('detalle_pedido', [
            'id_pedido'   => $idPedido,
            'id_producto' => $idProd2,
            'cantidad'    => 2,
        ]);
        $this->seeInDatabase('preparacion', [
            'id_pedido' => $idPedido,
            'estado'    => 'pendiente',
        ]);
    }

    // ============================================================
    // CP-02: Actualizar pedido cambiando de mesa y de productos (positivo)
    // ============================================================
    public function testCP02_ActualizarPedidoValido(): void
    {
        // Precondición: pedido 40 "equivalente" -> pendiente, en mesa ocupada (5)
        $idUsuario = $this->crearUsuario();
        $idMesa5   = $this->crearMesa('ocupada'); // mesa actual del pedido
        $idMesa7   = $this->crearMesa('libre');   // mesa nueva, destino
        $idProd12  = $this->crearProducto(true, 10.0);
        $idProd15  = $this->crearProducto(true, 8.0);

        $idPedido = $this->crearPedido($idMesa5, $idUsuario, 'pendiente', 10.0);
        $this->crearDetalle($idPedido, $idProd12, 1, 10.0); // detalle original: solo prod 12 x1

        // Acción: mover el pedido a la mesa 7 y reemplazar el detalle por [12, 15]
        // OJO: actualizar() recibe el id del pedido como primer parámetro,
        // aparte del array de datos, y validarDatosEntrada() exige 'id_usuario'
        // aunque sea una edición.
        $this->service->actualizar($idPedido, [
            'id_mesa'    => $idMesa7,
            'id_usuario' => $idUsuario,
            'productos'  => [$idProd12, $idProd15],
            'cantidades' => [1, 1],
        ]);

        // Resultado esperado
        $pedido = $this->pedidoModel->find($idPedido);
        $this->assertEquals(18.0, (float) $pedido['total']); // 10*1 + 8*1
        $this->assertEquals($idMesa7, $pedido['id_mesa']);

        $mesa5 = $this->mesaModel->find($idMesa5);
        $mesa7 = $this->mesaModel->find($idMesa7);
        $this->assertSame('libre', $mesa5['estado']);
        $this->assertSame('ocupada', $mesa7['estado']);

        // El detalle debe quedar exactamente con los 2 productos nuevos
        $detalles = $this->detalleModel->where('id_pedido', $idPedido)->findAll();
        $this->assertCount(2, $detalles);

        $this->seeInDatabase('detalle_pedido', [
            'id_pedido'   => $idPedido,
            'id_producto' => $idProd12,
            'cantidad'    => 1,
        ]);
        $this->seeInDatabase('detalle_pedido', [
            'id_pedido'   => $idPedido,
            'id_producto' => $idProd15,
            'cantidad'    => 1,
        ]);
    }

    // ============================================================
    // CP-03: Mesa ocupada (RN-01, negativo)
    // ============================================================
    public function testCP03_MesaOcupada(): void
    {
        // Precondición: mesa ya ocupada por otro pedido
        $idMesa    = $this->crearMesa('ocupada');
        $idUsuario = $this->crearUsuario();
        $idProd    = $this->crearProducto(true, 10.0);

        // Resultado esperado: debe lanzar RuntimeException con este mensaje exacto
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('La mesa está ocupada. Seleccione otra mesa.');

        // Acción
        $this->service->registrar([
            'id_mesa'    => $idMesa,
            'id_usuario' => $idUsuario,
            'productos'  => [$idProd],
            'cantidades' => [1],
        ]);
    }

    // ============================================================
    // CP-04: Producto no disponible (RN-02, negativo)
    // ============================================================
    public function testCP04_ProductoNoDisponible(): void
    {
        // Precondición: producto marcado como NO disponible
        $idMesa    = $this->crearMesa('libre');
        $idUsuario = $this->crearUsuario();
        $idProd    = $this->crearProducto(false, 10.0);

        // El mensaje de error incluye el nombre real del producto, así que
        // lo leemos de la BD para armar el mensaje esperado exacto.
        $producto = $this->productoModel->find($idProd);

        // Resultado esperado
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("El producto '{$producto['nombre']}' no está disponible.");

        // Acción
        $this->service->registrar([
            'id_mesa'    => $idMesa,
            'id_usuario' => $idUsuario,
            'productos'  => [$idProd],
            'cantidades' => [1],
        ]);
    }

    // ============================================================
    // CP-05: Editar pedido entregado (RN-03, negativo)
    // ============================================================
    public function testCP05_EditarPedidoEntregado(): void
    {
        // Precondición: pedido que ya fue entregado
        $idUsuario       = $this->crearUsuario();
        $idMesaActual    = $this->crearMesa('ocupada'); // mesa donde "estaba" el pedido
        $idMesaDestino   = $this->crearMesa('libre');
        $idProd          = $this->crearProducto(true, 10.0);

        $idPedido = $this->crearPedido($idMesaActual, $idUsuario, 'entregado', 10.0);
        $this->crearDetalle($idPedido, $idProd, 1, 10.0);

        // Resultado esperado
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No se puede modificar un pedido que ya está entregado.');

        // Acción: intenta editar un pedido que ya está entregado
        $this->service->actualizar($idPedido, [
            'id_mesa'    => $idMesaDestino,
            'id_usuario' => $idUsuario,
            'productos'  => [$idProd],
            'cantidades' => [1],
        ]);
    }

    // ============================================================
    // CP-06: Cancelar pedido pagado (RN-04, negativo)
    // ============================================================
    public function testCP06_CancelarPedidoPagado(): void
    {
        // Precondición: pedido pendiente, con un pago ya registrado
        $idUsuario = $this->crearUsuario();
        $idMesa    = $this->crearMesa('ocupada');
        $idProd    = $this->crearProducto(true, 10.0);

        $idPedido = $this->crearPedido($idMesa, $idUsuario, 'pendiente', 10.0);
        $this->crearDetalle($idPedido, $idProd, 1, 10.0);
        $this->crearPago($idPedido, 10.0);

        // Resultado esperado
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No se puede cancelar un pedido que ya tiene un pago registrado.');

        // Acción
        $this->service->cancelar($idPedido);
    }

    // ============================================================
    // CP-07: Frontera de cantidad de productos por pedido (20 / 21)
    // ============================================================

    /** Sub-caso A: 20 productos -> debe permitirse (límite exacto) */
    public function testCP07_Frontera20ProductosValido(): void
    {
        // Precondición
        $idMesa    = $this->crearMesa('libre');
        $idUsuario = $this->crearUsuario();
        $productos = $this->crearProductos(20, true, 5.0);

        // Acción
        $idPedido = $this->service->registrar([
            'id_mesa'    => $idMesa,
            'id_usuario' => $idUsuario,
            'productos'  => $productos,
            'cantidades' => array_fill(0, 20, 1),
        ]);
        $this->pedidosCreados[] = $idPedido; // para limpieza en tearDown()

        // Resultado esperado: se registra sin problema, con las 20 líneas
        $this->assertIsInt($idPedido);
        $detalles = $this->detalleModel->where('id_pedido', $idPedido)->findAll();
        $this->assertCount(20, $detalles);
    }

    /** Sub-caso B: 21 productos -> debe rechazarse (1 más allá del límite) */
    public function testCP07_Frontera21ProductosInvalido(): void
    {
        // Precondición
        $idMesa    = $this->crearMesa('libre');
        $idUsuario = $this->crearUsuario();
        $productos = $this->crearProductos(21, true, 5.0);

        // Resultado esperado
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No puede seleccionar más de 20 productos por pedido.');

        // Acción
        $this->service->registrar([
            'id_mesa'    => $idMesa,
            'id_usuario' => $idUsuario,
            'productos'  => $productos,
            'cantidades' => array_fill(0, 21, 1),
        ]);
    }

    // ============================================================
    // CP-08: Frontera de cantidad por producto (0 / 1 / 20 / 21)
    // ============================================================

    /** Sub-caso A: cantidad 0 -> inválida */
    public function testCP08_Cantidad0Invalida(): void
    {
        $idMesa    = $this->crearMesa('libre');
        $idUsuario = $this->crearUsuario();
        $idProd    = $this->crearProducto(true, 10.0);
        $producto  = $this->productoModel->find($idProd);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Cantidad inválida para '{$producto['nombre']}'.");

        $this->service->registrar([
            'id_mesa'    => $idMesa,
            'id_usuario' => $idUsuario,
            'productos'  => [$idProd],
            'cantidades' => [0],
        ]);
    }

    /** Sub-caso B: cantidad 1 -> válida (límite inferior) */
    public function testCP08_Cantidad1Valida(): void
    {
        $idMesa    = $this->crearMesa('libre');
        $idUsuario = $this->crearUsuario();
        $idProd    = $this->crearProducto(true, 10.0);

        $idPedido = $this->service->registrar([
            'id_mesa'    => $idMesa,
            'id_usuario' => $idUsuario,
            'productos'  => [$idProd],
            'cantidades' => [1],
        ]);
        $this->pedidosCreados[] = $idPedido;

        $pedido = $this->pedidoModel->find($idPedido);
        $this->assertEquals(10.0, (float) $pedido['total']);
    }

    /** Sub-caso C: cantidad 20 -> válida (límite superior) */
    public function testCP08_Cantidad20Valida(): void
    {
        $idMesa    = $this->crearMesa('libre');
        $idUsuario = $this->crearUsuario();
        $idProd    = $this->crearProducto(true, 10.0);

        $idPedido = $this->service->registrar([
            'id_mesa'    => $idMesa,
            'id_usuario' => $idUsuario,
            'productos'  => [$idProd],
            'cantidades' => [20],
        ]);
        $this->pedidosCreados[] = $idPedido;

        $pedido = $this->pedidoModel->find($idPedido);
        $this->assertEquals(200.0, (float) $pedido['total']);
        $this->seeInDatabase('detalle_pedido', [
            'id_pedido'   => $idPedido,
            'id_producto' => $idProd,
            'cantidad'    => 20,
        ]);
    }

    /** Sub-caso D: cantidad 21 -> inválida (1 más allá del límite) */
    public function testCP08_Cantidad21Invalida(): void
    {
        $idMesa    = $this->crearMesa('libre');
        $idUsuario = $this->crearUsuario();
        $idProd    = $this->crearProducto(true, 10.0);
        $producto  = $this->productoModel->find($idProd);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Cantidad inválida para '{$producto['nombre']}'.");

        $this->service->registrar([
            'id_mesa'    => $idMesa,
            'id_usuario' => $idUsuario,
            'productos'  => [$idProd],
            'cantidades' => [21],
        ]);
    }
}