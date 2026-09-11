<?php

namespace Config;

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', 'PedidoController::index');

// ============================================================
// CRUD COMPLETO - PEDIDO (Entidad principal)
// ============================================================

// READ
$routes->get('pedidos', 'PedidoController::index');
$routes->get('pedidos/ver/(:num)', 'PedidoController::ver/$1');

// CREATE
$routes->get('pedidos/nuevo', 'PedidoController::nuevo');
$routes->post('pedidos/registrar', 'PedidoController::registrar');

// UPDATE
$routes->get('pedidos/editar/(:num)', 'PedidoController::editar/$1');
$routes->post('pedidos/actualizar/(:num)', 'PedidoController::actualizar/$1');

// DELETE (Desactivacion logica)
$routes->get('pedidos/cancelar/(:num)', 'PedidoController::cancelar/$1');

// ============================================================
// PRODUCTOS (Consulta)
// ============================================================
$routes->get('productos', 'ProductoController::index');

// Health check
$routes->get('health', function() {
    return \Config\Services::response()->setJSON(['status' => 'ok', 'system' => 'Sagras CI4 - Guia 4']);
});
$routes->get('productos/nuevo', 'ProductoController::nuevo');
$routes->post('productos/registrar', 'ProductoController::registrar');