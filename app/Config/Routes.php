<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ---------------------------------------------------------------
// Auth Routes (Shield)
// ---------------------------------------------------------------
service('auth')->routes($routes);

// ---------------------------------------------------------------
// Public Routes
// ---------------------------------------------------------------
$routes->get('/', 'AuthController::login');
$routes->get('maintenance', static function () {
    return view('errors/maintenance');
});

// ---------------------------------------------------------------
// Protected Routes (require login)
// ---------------------------------------------------------------
$routes->group('', ['filter' => 'session'], static function ($routes) {

    // Dashboard
    $routes->get('dashboard', 'DashboardController::index');

    // Switch Active Group
    $routes->post('switch-group', 'GroupSwitchController::switch');

    // Profile
    $routes->get('profile', 'ProfileController::index');
    $routes->post('profile/update', 'ProfileController::update');

    // ---------------------------------------------------------------
    // Admin Routes (require admin.access permission)
    // ---------------------------------------------------------------
    $routes->group('admin', ['filter' => 'permission:admin.access'], static function ($routes) {

        // User Management
        $routes->group('users', static function ($routes) {
            $routes->get('/', 'UserController::index', ['filter' => 'permission:users.list']);
            $routes->get('create', 'UserController::create', ['filter' => 'permission:users.create']);
            $routes->post('store', 'UserController::store', ['filter' => 'permission:users.create']);
            $routes->get('edit/(:num)', 'UserController::edit/$1', ['filter' => 'permission:users.edit']);
            $routes->post('update/(:num)', 'UserController::update/$1', ['filter' => 'permission:users.edit']);
            $routes->post('delete/(:num)', 'UserController::delete/$1', ['filter' => 'permission:users.delete']);
            $routes->post('assign-role/(:num)', 'UserController::assignRole/$1', ['filter' => 'permission:users.manage-roles']);
        });

        // Role Management (superadmin only)
        $routes->group('roles', ['filter' => 'role:superadmin'], static function ($routes) {
            $routes->get('/', 'RoleController::index');
            $routes->get('permissions', 'RoleController::permissions');
        });

        // Settings
        $routes->group('settings', ['filter' => 'permission:admin.settings'], static function ($routes) {
            $routes->get('/', 'SettingController::index');
            $routes->post('update/general', 'SettingController::updateGeneral');
            $routes->post('update/branding', 'SettingController::updateBranding');
            $routes->post('update/appearance', 'SettingController::updateAppearance');
            $routes->post('update/auth', 'SettingController::updateAuth');
            $routes->post('update/mail', 'SettingController::updateMail');
            $routes->post('reset', 'SettingController::resetDefaults');
        });
    });
});
