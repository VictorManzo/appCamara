<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->setAutoRoute(false);

// Público
$routes->get('/', 'Auth::inicio');
$routes->get('catalogo', 'Catalogo::index');
$routes->get('entrar', 'Auth::entrar');
$routes->post('entrar', 'Auth::procesarEntrada');
$routes->get('salir', 'Auth::salir');
$routes->match(['GET', 'POST'], 'instalar', 'Auth::instalar');

// Panel (admin y vendedores)
$routes->get('panel', 'Panel::index', ['filter' => 'auth']);

// Importar desde la app de claude.ai (solo admin)
$routes->get('importar', 'Importar::index', ['filter' => 'auth:admin']);
$routes->post('importar', 'Importar::procesar', ['filter' => 'auth:admin']);

// API JSON
$routes->group('api', ['namespace' => 'App\Controllers\Api'], static function ($r) {
    $r->get('productos', 'Productos::index', ['filter' => 'auth']);
    $r->post('productos', 'Productos::crear', ['filter' => 'auth:admin']);
    $r->post('productos/(:num)', 'Productos::actualizar/$1', ['filter' => 'auth:admin']);
    $r->post('productos/(:num)/sumar', 'Productos::sumar/$1', ['filter' => 'auth:admin']);
    $r->post('productos/(:num)/eliminar', 'Productos::eliminar/$1', ['filter' => 'auth:admin']);

    $r->get('ventas', 'Ventas::index', ['filter' => 'auth']);
    $r->post('ventas', 'Ventas::registrar', ['filter' => 'auth']);
    $r->post('ventas/deshacer', 'Ventas::deshacer', ['filter' => 'auth']);

    $r->get('usuarios', 'Usuarios::index', ['filter' => 'auth:admin']);
    $r->post('usuarios', 'Usuarios::crear', ['filter' => 'auth:admin']);
    $r->post('usuarios/(:num)', 'Usuarios::actualizar/$1', ['filter' => 'auth:admin']);
    $r->post('usuarios/(:num)/eliminar', 'Usuarios::eliminar/$1', ['filter' => 'auth:admin']);
    $r->post('ajustes', 'Usuarios::ajustes', ['filter' => 'auth:admin']);
});
