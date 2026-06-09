<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Auth::index');
$routes->get('/login', 'Auth::index');
$routes->post('/login/auth', 'Auth::login');
$routes->get('/logout', 'Auth::logout');
$routes->get('/cadastro', 'Auth::cadastro');
$routes->post('/auth/processar-cadastro', 'Auth::processarCadastro');
// Rotas protegidas (Apenas logados)
$routes->group('', ['filter' => 'auth'], function($routes) {
    $routes->get('/inicio', 'Inicio::index'); 
    $routes->get('/sobre', 'Inicio::sobre'); 
    $routes->get('/perfil', 'Perfil::index'); 
    $routes->get('/inicio/agenda-data', 'Inicio::getAgendaData');

    // Utils
    $routes->post('/utils/marcar-aviso-visto', 'Utils::marcarAvisoVisto');

    // Pets
    $routes->get('/pets', 'Pets::index');
    $routes->get('/pets/search', 'Pets::search');
    $routes->get('/pets/novo', 'Pets::novo');
    $routes->post('/pets/salvar', 'Pets::salvar');
    $routes->get('/pets/editar/(:num)', 'Pets::editar/$1');
    $routes->get('/pets/excluir/(:num)', 'Pets::excluir/$1');
    $routes->get('/pets/ver/(:num)', 'Pets::ver/$1');

    // Agenda
    $routes->get('/agenda', 'Agenda::index');
    $routes->get('/agenda/novo', 'Agenda::novo');
    $routes->get('/agenda/editar/(:num)', 'Agenda::editar/$1');
    $routes->get('/agenda/cadastro-rapido', 'Agenda::cadastroRapido');
    $routes->post('/agenda/salvar-cadastro-rapido', 'Agenda::salvarCadastroRapido');
    $routes->post('/agenda/salvar', 'Agenda::salvar');
    $routes->get('/agenda/horarios', 'Agenda::horariosDisponiveis');
    $routes->get('/agenda/concluir/(:num)', 'Agenda::concluir/$1');
    $routes->get('/agenda/ficha/(:num)', 'Agenda::ficha/$1');
    $routes->get('/agenda/cancelar/(:num)', 'Agenda::cancelar/$1');
    $routes->get('/agenda/excluir/(:num)', 'Agenda::excluir/$1');
    $routes->post('/agenda/salvarFicha', 'Agenda::salvarFicha');

    // Tutores
    $routes->get('/tutores', 'Tutores::index');
    $routes->get('/tutores/novo', 'Tutores::novo');
    $routes->post('/tutores/salvar', 'Tutores::salvar');
    $routes->get('/tutores/editar/(:num)', 'Tutores::editar/$1');
    $routes->get('/tutores/excluir/(:num)', 'Tutores::excluir/$1');
    $routes->get('/tutores/ver/(:num)', 'Tutores::ver/$1');

    // Vacinas
    $routes->post('vacinas/salvar', 'Vacinas::salvar');
    $routes->get('vacinas/aplicar/(:num)', 'Vacinas::aplicar/$1');
    $routes->get('vacinas/editar/(:num)', 'Vacinas::editar/$1');
    $routes->post('vacinas/atualizar/(:num)', 'Vacinas::atualizar/$1');
    $routes->get('vacinas/excluir/(:num)', 'Vacinas::excluir/$1');
    $routes->get('vacinas/imprimir/(:num)', 'Vacinas::imprimir/$1');

    // Servicos
    $routes->get('/servicos', 'Servicos::index');
    $routes->get('/servicos/novo', 'Servicos::novo');
    $routes->post('/servicos/salvar', 'Servicos::salvar');
    $routes->get('/servicos/editar/(:num)', 'Servicos::editar/$1');
    $routes->get('/servicos/excluir/(:num)', 'Servicos::excluir/$1');
});

// Rotas Administrativas (Admin)
$routes->group('', ['filter' => 'admin'], function($routes) {
    $routes->get('/admin', 'Admin::index'); 
    $routes->get('/admin/relatorio', 'Admin::relatorio');
    
    $routes->get('/usuarios', 'Usuarios::index');
    $routes->post('/usuarios/aprovar/(:num)', 'Usuarios::aprovar/$1');
    $routes->post('/usuarios/rejeitar/(:num)', 'Usuarios::rejeitar/$1');
    $routes->post('/usuarios/excluir/(:num)', 'Usuarios::excluir/$1');
    $routes->post('/usuarios/alternar-tipo/(:num)', 'Usuarios::alternarTipo/$1'); 
});
