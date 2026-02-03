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
$routes->get('/admin', 'Admin::index'); 
$routes->get('/admin/relatorio', 'Admin::relatorio');
$routes->get('/dashboard/agenda-data', 'Dashboard::getAgendaData');

// Usuários (Admin)
$routes->get('/usuarios', 'Usuarios::index');
$routes->post('/usuarios/aprovar/(:num)', 'Usuarios::aprovar/$1');
$routes->post('/usuarios/rejeitar/(:num)', 'Usuarios::rejeitar/$1');
$routes->post('/usuarios/excluir/(:num)', 'Usuarios::excluir/$1');
$routes->post('/usuarios/alternar-tipo/(:num)', 'Usuarios::alternarTipo/$1'); 

// Utils
$routes->post('/utils/marcar-aviso-visto', 'Utils::marcarAvisoVisto');

// Pets
$routes->get('/pets', 'Pets::index');
$routes->get('/pets/search', 'Pets::search');
$routes->get('/pets/ver/(:num)', 'Pets::ver/$1');

// Agenda
$routes->get('/agenda', 'Agenda::index');
$routes->get('/agenda/novo', 'Agenda::novo');
$routes->get('/agenda/cadastro-rapido', 'Agenda::cadastroRapido');
$routes->post('/agenda/salvar-cadastro-rapido', 'Agenda::salvarCadastroRapido');
$routes->post('/agenda/salvar', 'Agenda::salvar');
$routes->get('/agenda/horarios', 'Agenda::horariosDisponiveis');
$routes->get('/agenda/concluir/(:num)', 'Agenda::ficha/$1'); // Redireciona para fichas ao concluir
$routes->get('/agenda/cancelar/(:num)', 'Agenda::cancelar/$1');
$routes->post('/agenda/salvarFicha', 'Agenda::salvarFicha');

// Tutores Routes
$routes->get('/tutores', 'Tutores::index');
$routes->get('/tutores/novo', 'Tutores::novo');
$routes->post('/tutores/salvar', 'Tutores::salvar');
$routes->get('/tutores/editar/(:num)', 'Tutores::editar/$1');
$routes->get('/tutores/excluir/(:num)', 'Tutores::excluir/$1');
$routes->get('/tutores/ver/(:num)', 'Tutores::ver/$1');

// Pets Routes
$routes->get('/pets/novo', 'Pets::novo'); // Antes de /pets/ver
$routes->post('/pets/salvar', 'Pets::salvar');
$routes->get('/pets/editar/(:num)', 'Pets::editar/$1');
$routes->get('/pets/excluir/(:num)', 'Pets::excluir/$1');

// Services Routes
$routes->get('/servicos', 'Servicos::index');
$routes->get('/servicos/novo', 'Servicos::novo');
$routes->post('/servicos/salvar', 'Servicos::salvar');
$routes->get('/servicos/editar/(:num)', 'Servicos::editar/$1');
$routes->get('/servicos/excluir/(:num)', 'Servicos::excluir/$1');
