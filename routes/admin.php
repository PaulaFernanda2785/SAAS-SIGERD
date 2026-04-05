<?php

declare(strict_types=1);

use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\InstitutionController;
use App\Controllers\Admin\CommercialController;
use App\Controllers\Admin\EnterpriseController;

$router->get('/admin', [DashboardController::class, 'index'], ['authenticate', 'area.admin']);

$router->get('/admin/institucional', [InstitutionController::class, 'index'], ['authenticate', 'area.admin']);
$router->post('/admin/institucional/contas', [InstitutionController::class, 'storeAccount'], ['authenticate', 'area.admin', 'csrf']);
$router->post('/admin/institucional/orgaos', [InstitutionController::class, 'storeOrgao'], ['authenticate', 'area.admin', 'csrf']);
$router->post('/admin/institucional/unidades', [InstitutionController::class, 'storeUnidade'], ['authenticate', 'area.admin', 'csrf']);
$router->post('/admin/institucional/usuarios', [InstitutionController::class, 'storeUsuario'], ['authenticate', 'area.admin', 'csrf']);
$router->post('/admin/institucional/perfis', [InstitutionController::class, 'storePerfil'], ['authenticate', 'area.admin', 'csrf']);
$router->post('/admin/institucional/vinculos', [InstitutionController::class, 'attachPerfil'], ['authenticate', 'area.admin', 'csrf']);
$router->post('/admin/institucional/acoes', [InstitutionController::class, 'entityAction'], ['authenticate', 'area.admin', 'csrf']);

$router->get('/admin/comercial', [CommercialController::class, 'index'], ['authenticate', 'area.admin']);
$router->post('/admin/comercial/planos', [CommercialController::class, 'storePlan'], ['authenticate', 'area.admin', 'csrf']);
$router->post('/admin/comercial/assinaturas', [CommercialController::class, 'storeAssinatura'], ['authenticate', 'area.admin', 'csrf']);
$router->post('/admin/comercial/modulos', [CommercialController::class, 'upsertModulo'], ['authenticate', 'area.admin', 'csrf']);

$enterpriseMiddleware = ['authenticate', 'area.admin', 'enterprise.access'];

$router->get('/admin/enterprise', [EnterpriseController::class, 'index'], $enterpriseMiddleware);
$router->post('/admin/enterprise/features', [EnterpriseController::class, 'storeFeature'], ['authenticate', 'area.admin', 'enterprise.access', 'csrf']);
$router->post('/admin/enterprise/api-apps', [EnterpriseController::class, 'storeApiApp'], ['authenticate', 'area.admin', 'enterprise.access', 'csrf']);
$router->post('/admin/enterprise/integracoes', [EnterpriseController::class, 'storeIntegracao'], ['authenticate', 'area.admin', 'enterprise.access', 'csrf']);
$router->post('/admin/enterprise/automacoes', [EnterpriseController::class, 'storeAutomacao'], ['authenticate', 'area.admin', 'enterprise.access', 'csrf']);
$router->post('/admin/enterprise/sla', [EnterpriseController::class, 'storeSla'], ['authenticate', 'area.admin', 'enterprise.access', 'csrf']);
$router->post('/admin/enterprise/tickets', [EnterpriseController::class, 'storeTicket'], ['authenticate', 'area.admin', 'enterprise.access', 'csrf']);
$router->post('/admin/enterprise/assinaturas-digitais', [EnterpriseController::class, 'storeDigitalSignature'], ['authenticate', 'area.admin', 'enterprise.access', 'csrf']);
$router->post('/admin/enterprise/relatorios-executivos', [EnterpriseController::class, 'storeExecutiveReport'], ['authenticate', 'area.admin', 'enterprise.access', 'csrf']);
