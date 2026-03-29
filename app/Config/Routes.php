<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Page d'accueil
$routes->get('/', 'Home::index');

// Routes d'authentification (publiques)
$routes->get('login', 'AuthController::login');
$routes->post('auth/attempt-login', 'AuthController::attemptLogin');
$routes->get('logout', 'AuthController::logout');
$routes->get('auth/forgot-password', 'AuthController::forgotPassword');
$routes->post('auth/send-reset-link', 'AuthController::sendResetLink');
$routes->get('auth/reset-password/(:segment)', 'AuthController::resetPassword/$1');
$routes->post('auth/update-password', 'AuthController::updatePassword');

// Routes kiosque (publiques mais filtrées par IP)
$routes->get('kiosque', 'KiosqueController::index', ['filter' => 'kiosque_ip']);
$routes->post('kiosque/pointage-arrivee', 'KiosqueController::pointageArrivee', ['filter' => 'kiosque_ip']);
$routes->post('kiosque/pointage-depart', 'KiosqueController::pointageDepart', ['filter' => 'kiosque_ip']);
$routes->get('kiosque/search', 'KiosqueController::searchEmployee', ['filter' => 'kiosque_ip']);

// Routes visiteurs (publiques)
$routes->get('visitor/index', 'VisitorController::index');
$routes->post('visitor/register', 'VisitorController::register');
$routes->post('visitor/checkout/(:num)', 'VisitorController::checkout/$1');
$routes->get('visitor/history', 'VisitorController::history');
$routes->get('visitor/statistics', 'VisitorController::statistics');
$routes->post('visitor/get-present', 'VisitorController::getPresentAjax');

// Routes API (publiques)
$routes->get('api/presences/today', 'API\RealtimeController::getPresencesToday', ['filter' => 'auth']);
$routes->get('api/presences/today/stats', 'API\RealtimeController::getPresencesStats', ['filter' => 'auth']);
$routes->get('api/presences/absents/today', 'API\RealtimeController::getAbsentsToday', ['filter' => 'auth']);
$routes->get('api/visiteurs/presents', 'API\RealtimeController::getVisitorsPresents', ['filter' => 'auth']);
$routes->get('api/visiteurs/today/stats', 'API\RealtimeController::getVisitorsStats', ['filter' => 'auth']);
$routes->get('api/visiteurs/analytics', 'API\RealtimeController::getVisitorsAnalytics', ['filter' => 'auth']);
$routes->get('api/realtime/dashboard', 'API\RealtimeController::getDashboardData', ['filter' => 'auth']);

// Routes protégées par AuthFilter
$routes->group('', ['filter' => 'auth'], function($routes) {
    // Global notifications (available to all authenticated users)
    $routes->get('notifications', 'NotificationsController::index');
    $routes->post('notifications/lire/(:num)', 'NotificationsController::markRead/$1');
    $routes->post('notifications/tout-lire', 'NotificationsController::markAllRead');
    $routes->get('shared/realtime', 'RealtimeController::index');
    $routes->post('ia/assistant-conge', 'AIController::assistantConge');
    $routes->post('ia/chatbot', 'AIController::chatbot');
    $routes->get('api/holidays', 'API\CalendarController::holidays');

    // Routes admin
    $routes->group('admin', ['filter' => 'role:admin'], function($routes) {
        $routes->get('dashboard', 'Admin\DashboardController::index');

        // Legacy aliases (route normalization compatibility)
        $routes->get('conges', static fn() => redirect()->to('/admin/leaves'));
        $routes->get('presences', static fn() => redirect()->to('/admin/presences/index'));
        $routes->get('visiteurs', static fn() => redirect()->to('/admin/visitors'));
        $routes->get('documents-rh', static fn() => redirect()->to('/admin/documents'));
        
        // Employee management
        $routes->get('employees', 'Admin\EmployeesController::index');
        $routes->get('employees/create', 'Admin\EmployeesController::create');
        $routes->post('employees/store', 'Admin\EmployeesController::store');
        $routes->get('employees/(:num)', 'Admin\EmployeesController::show/$1');
        $routes->get('employees/(:num)/edit', 'Admin\EmployeesController::edit/$1');
        $routes->post('employees/(:num)/update', 'Admin\EmployeesController::update/$1');
        $routes->post('employees/(:num)/deactivate', 'Admin\EmployeesController::deactivate/$1');
        
        // Presence management
        $routes->get('presences/index', 'Admin\PresencesController::index');
        $routes->get('presences/history', 'Admin\PresencesController::history');
        $routes->get('presences/correct/(:num)', 'Admin\PresencesController::correct/$1');
        $routes->post('presences/store-correction/(:num)', 'Admin\PresencesController::storeCorrection/$1');
        $routes->get('presences/statistics', 'Admin\PresencesController::statistics');
        
        // Leave management
        $routes->get('leaves', 'Admin\LeaveController::index');
        $routes->get('leaves/(:num)', 'Admin\LeaveController::show/$1');
        $routes->post('leaves/(:num)/approve', 'Admin\LeaveController::approve/$1');
        $routes->post('leaves/(:num)/reject', 'Admin\LeaveController::reject/$1');
        $routes->post('leaves/(:num)/cancel', 'Admin\LeaveController::cancel/$1');
        
        // Planning & Shifts
        $routes->get('planning', 'Admin\PlanningController::index');
        $routes->get('planning/shifts', 'Admin\PlanningController::manageShifts');
        $routes->post('planning/shifts', 'Admin\PlanningController::manageShifts');
        $routes->post('planning/assign', 'Admin\PlanningController::assignShift');
        
        // Visitor management
        $routes->get('visitors', 'Admin\VisitorController::index');
        $routes->get('visitors/(:num)', 'Admin\VisitorController::show/$1');
        $routes->get('visitors/(:num)/badge', 'Admin\VisitorController::badge/$1');
        $routes->post('visitors/(:num)/checkout', 'Admin\VisitorController::checkout/$1');
        $routes->get('visitors/statistics', 'Admin\VisitorController::statistics');
        $routes->get('visitors/export-csv', 'Admin\VisitorController::exportCsv');

        // Documents RH
        $routes->get('documents', 'Admin\DocumentsController::index');
        $routes->get('documents/create', 'Admin\DocumentsController::create');
        $routes->post('documents', 'Admin\DocumentsController::store');
        $routes->get('documents/(:num)/edit', 'Admin\DocumentsController::edit/$1');
        $routes->post('documents/(:num)/update', 'Admin\DocumentsController::update/$1');
        $routes->get('documents/(:num)/download', 'Admin\DocumentsController::download/$1');
        $routes->post('documents/(:num)/delete', 'Admin\DocumentsController::delete/$1');

        // Reporting (PDF/CSV)
        $routes->get('rapports', 'Admin\ReportsController::index');
        $routes->post('rapports/generer', 'Admin\ReportsController::generate');

        // Finance, audit and system configuration
        $routes->get('finance', 'Admin\FinanceController::index');
        $routes->get('finance/export-csv', 'Admin\FinanceController::exportCsv');
        $routes->get('audit', 'Admin\AuditController::index');
        $routes->get('audit/export-csv', 'Admin\AuditController::exportCsv');
        $routes->get('audit/(:num)', 'Admin\AuditController::detail/$1');
        $routes->get('configuration', 'Admin\ConfigController::index');
        $routes->post('configuration', 'Admin\ConfigController::update');
        $routes->post('configuration/jours-feries', 'Admin\ConfigController::addHoliday');
        $routes->post('configuration/jours-feries/(:num)', 'Admin\ConfigController::updateHoliday/$1');
        $routes->post('configuration/jours-feries/(:num)/delete', 'Admin\ConfigController::deleteHoliday/$1');
    });

    // Routes agent
    $routes->group('agent', ['filter' => 'role:agent'], function($routes) {
        $routes->get('dashboard', 'Agent\DashboardController::index');
        
        // Visitor management
        $routes->get('visitors/register', 'Agent\VisitorController::register');
        $routes->post('visitors/store', 'Agent\VisitorController::store');
        $routes->get('visitors/current', 'Agent\VisitorController::current');
        $routes->get('visitors/search', 'Agent\VisitorController::search');
        $routes->post('visitors/(:num)/checkout', 'Agent\VisitorController::checkout/$1');
        $routes->get('visitors/history', 'Agent\VisitorController::history');
        $routes->get('visitors/(:num)/print-badge', 'Agent\VisitorController::printBadge/$1');
        $routes->get('visitors/summary', 'Agent\VisitorController::todaysSummary');
    });

    // Routes employé
    $routes->group('employe', ['filter' => 'role:employe'], function($routes) {
        $routes->get('dashboard', 'Employe\DashboardController::index');

        // Legacy aliases (route normalization compatibility)
        $routes->get('conges', static fn() => redirect()->to('/employe/leaves'));
        $routes->get('planning-hebdo', static fn() => redirect()->to('/employe/planning'));
        
        // Leave management (employee side)
        $routes->get('leaves', 'Employe\LeaveController::index');
        $routes->get('leaves/create', 'Employe\LeaveController::create');
        $routes->post('leaves', 'Employe\LeaveController::store');
        $routes->get('leaves/(:num)', 'Employe\LeaveController::show/$1');
        $routes->get('leaves/calculate-working-days', 'Employe\LeaveController::calculateWorkingDays');
        $routes->post('leaves/calculate-working-days', 'Employe\LeaveController::calculateWorkingDays');
        $routes->post('leaves/(:num)/cancel', 'Employe\LeaveController::cancel/$1');
        
        // Planning (employee side)
        $routes->get('planning', 'Employe\PlanningController::index');
        $routes->get('planning/month', 'Employe\PlanningController::month');

        // Documents RH
        $routes->get('documents', 'Employe\DocumentsController::index');
        $routes->get('documents/(:num)/download', 'Employe\DocumentsController::download/$1');
    });

    // Routes partagées (tous rôles)
    $routes->get('profile', 'ProfileController::index');
    $routes->post('profile/update-password', 'ProfileController::updatePassword');
    $routes->post('profile/update-pin', 'ProfileController::updatePin');
});
