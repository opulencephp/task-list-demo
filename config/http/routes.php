<?php
use Opulence\Routing\Router;

/**
 * ----------------------------------------------------------
 * Create all of the routes for the HTTP kernel
 * ----------------------------------------------------------
 *
 * @var Router $router
 */
$router->group(['controllerNamespace' => 'Project\\Application\\Http\\Controllers'], function (Router $router) {
    $router->get('/', 'Tasks@showHome', ['name' => 'home']);
    $router->post('/', 'Tasks@addTask', ['name' => 'addTask']);
    $router->delete('/:taskId', 'Tasks@deleteTask', ['name' => 'deleteTask']);
});
