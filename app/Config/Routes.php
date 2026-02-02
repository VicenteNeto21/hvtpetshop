<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Auth::index');
$routes->get('/login', 'Auth::index');
$routes->post('/login/auth', 'Auth::login');
$routes->get('/logout', 'Auth::logout');
$routes->get('/dashboard', 'Dashboard::index'); 

// Pets
$routes->get('/pets', 'Pets::index');
$routes->get('/pets/search', 'Pets::search');
$routes->get('/pets/ver/(:num)', 'Pets::ver/$1');
