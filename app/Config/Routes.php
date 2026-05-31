<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ---------------------------------------------------------------
// Auth Routes (Shield)
// ---------------------------------------------------------------
// Exclude register routes so we can override with our custom controller
service('auth')->routes($routes, ['except' => ['register']]);

// Custom register routes with email domain guard
$routes->get('register', 'RegisterController::registerView', ['as' => 'register']);
$routes->post('register', 'RegisterController::registerAction');
$routes->get('register/cancel', 'RegisterController::cancelActivation', ['as' => 'register-cancel']);

// ---------------------------------------------------------------
// Public Routes
// ---------------------------------------------------------------
$routes->get('/', 'Home::index');
$routes->get('laboratorium', 'Home::laboratorium');
$routes->get('labs/scan/(:segment)', 'LabController::scan/$1');
$routes->post('labs/scan/(:segment)', 'LabController::scan/$1');
$routes->get('maintenance', static function () {
    return view('errors/maintenance');
});

// ---------------------------------------------------------------
// Protected Routes (require login)
// ---------------------------------------------------------------
$routes->group('', ['filter' => 'session'], static function ($routes) {

    // Dashboard
    $routes->get('dashboard', 'DashboardController::index');

    // Menu Search (navbar)
    $routes->get('menu-search', 'MenuSearchController::search');

    // Visitor Log
    $routes->get('admin/visits', 'LabVisitController::index', ['filter' => 'permission:visits.list']);
    $routes->get('admin/visits/datatable', 'LabVisitController::datatable', ['filter' => 'permission:visits.list']);
    $routes->get('admin/loans/labs/(:num)/visits', 'LabVisitController::labVisits/$1', ['filter' => 'permission:visits.list']);

    // Loan Module
    $routes->group('loans', ['filter' => 'permission:lending.access'], static function ($routes) {
        $routes->get('/', 'LoanProposalController::index', ['filter' => 'permission:lending.request.track']);
        $routes->get('create', 'LoanProposalController::create', ['filter' => 'permission:lending.request.create']);
        $routes->post('store', 'LoanProposalController::store', ['filter' => 'permission:lending.request.create']);
        $routes->get('analytics', 'LoanProposalController::analytics', ['filter' => 'permission:lending.analytics.view']);

        $routes->get('(:num)', 'LoanProposalController::show/$1', ['filter' => 'permission:lending.request.track']);
        $routes->get('(:num)/items', 'LoanProposalController::selectItems/$1', ['filter' => 'permission:lending.request.create']);
        $routes->post('(:num)/items/equipment', 'LoanProposalController::addEquipmentItem/$1', ['filter' => 'permission:lending.request.create']);
        $routes->post('(:num)/items/lab', 'LoanProposalController::addLabItem/$1', ['filter' => 'permission:lending.request.create']);
        $routes->post('(:num)/items/(:num)/delete', 'LoanProposalController::removeItem/$1/$2', ['filter' => 'permission:lending.request.create']);
        $routes->post('(:num)/submit', 'LoanProposalController::submit/$1', ['filter' => 'permission:lending.request.submit']);
        $routes->post('(:num)/cancel', 'LoanProposalController::cancel/$1', ['filter' => 'permission:lending.request.cancel']);

        $routes->post('(:num)/approve-l1', 'LoanProposalController::approveL1/$1', ['filter' => 'permission:lending.approval.l1']);
        $routes->post('(:num)/reject-l1', 'LoanProposalController::rejectL1/$1', ['filter' => 'permission:lending.approval.l1']);
        $routes->post('(:num)/approve-l2', 'LoanProposalController::approveL2/$1', ['filter' => 'permission:lending.approval.l2']);
        $routes->post('(:num)/reject-l2', 'LoanProposalController::rejectL2/$1', ['filter' => 'permission:lending.approval.l2']);
    });

    // Consumable (BHP) Module
    $routes->group('consumables', ['filter' => 'permission:bhp.access'], static function ($routes) {
        $routes->get('/', 'ConsumableController::index', ['filter' => 'permission:bhp.catalog.view']);
        $routes->get('datatable', 'ConsumableController::datatableItems');
        $routes->get('beranda', 'ConsumableController::beranda');
        $routes->get('api/items-by-lab', 'ConsumableController::itemsByLab');
        $routes->get('requests', 'ConsumableController::requests', ['filter' => 'permission:bhp.request.track']);
        $routes->get('requests/create', 'ConsumableController::create', ['filter' => 'permission:bhp.request.create']);
        $routes->post('requests', 'ConsumableController::store', ['filter' => 'permission:bhp.request.create']);
        $routes->get('requests/(:num)', 'ConsumableController::show/$1', ['filter' => 'permission:bhp.request.track']);
        $routes->post('requests/(:num)/submit', 'ConsumableController::submit/$1', ['filter' => 'permission:bhp.request.submit']);
        $routes->post('requests/(:num)/approve', 'ConsumableController::approve/$1', ['filter' => 'permission:bhp.approval']);
        $routes->post('requests/(:num)/reject', 'ConsumableController::reject/$1', ['filter' => 'permission:bhp.approval']);
        $routes->post('requests/(:num)/disburse', 'ConsumableController::disburse/$1', ['filter' => 'permission:bhp.disburse']);
        $routes->get('requests/(:num)/realize', 'ConsumableController::realize/$1', ['filter' => 'permission:bhp.realize']);
        $routes->post('requests/(:num)/realize', 'ConsumableController::storeRealization/$1', ['filter' => 'permission:bhp.realize']);
        $routes->post('requests/(:num)/cancel', 'ConsumableController::cancel/$1', ['filter' => 'permission:bhp.request.cancel']);
        $routes->get('analytics', 'ConsumableController::analytics', ['filter' => 'permission:bhp.analytics.view']);
        $routes->get('adjustments', 'ConsumableAdjustmentController::history', ['filter' => 'permission:bhp.stock.adjust']);
        $routes->get('adjustments/datatable', 'ConsumableAdjustmentController::datatableHistory', ['filter' => 'permission:bhp.stock.adjust']);
        $routes->get('adjustments/export', 'ConsumableAdjustmentController::exportHistory', ['filter' => 'permission:bhp.stock.adjust']);
        $routes->get('adjustments/(:num)/create', 'ConsumableAdjustmentController::create/$1', ['filter' => 'permission:bhp.stock.adjust']);
        $routes->post('adjustments/(:num)', 'ConsumableAdjustmentController::store/$1', ['filter' => 'permission:bhp.stock.adjust']);
    });

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
            $routes->get('datatable', 'UserController::datatable', ['filter' => 'permission:users.list']);
            $routes->get('create', 'UserController::create', ['filter' => 'permission:users.create']);
            $routes->post('store', 'UserController::store', ['filter' => 'permission:users.create']);
            $routes->get('edit/(:num)', 'UserController::edit/$1', ['filter' => 'permission:users.edit']);
            $routes->post('update/(:num)', 'UserController::update/$1', ['filter' => 'permission:users.edit']);
            $routes->post('delete/(:num)', 'UserController::delete/$1', ['filter' => 'permission:users.delete']);
            $routes->post('toggle-status/(:num)', 'UserController::toggleStatus/$1', ['filter' => 'permission:users.toggle-status']);
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
            $routes->post('test-email', 'SettingController::testEmail');
            $routes->post('reset', 'SettingController::resetDefaults');
        });

        // Loan Assets Master Data
        $routes->group('loans/assets', ['filter' => 'permission:lending.master.manage'], static function ($routes) {
            $routes->get('/', 'LoanAssetController::index');
            $routes->get('create', 'LoanAssetController::create');
            $routes->get('edit/(:num)', 'LoanAssetController::edit/$1');
            $routes->post('store', 'LoanAssetController::store');
            $routes->post('update/(:num)', 'LoanAssetController::update/$1');
            $routes->post('delete/(:num)', 'LoanAssetController::delete/$1');
            // Download
            $routes->get('download', 'LoanAssetController::download');
            // Import (page-based flow)
            $routes->get('import', 'LoanAssetController::importForm');
            $routes->get('import/template', 'LoanAssetController::downloadImportTemplate');
            $routes->get('import/sample', 'LoanAssetController::downloadSampleImport');
            $routes->post('import/preview', 'LoanAssetController::importPreview');
            $routes->post('import/process', 'LoanAssetController::importProcess');
            $routes->post('import', 'LoanAssetController::import');
            // QR Code routes
            $routes->get('qr', 'LoanAssetController::qrIndex');
            $routes->get('qr/bulk', 'LoanAssetController::qrBulkPrint');
            $routes->get('(:num)/qr', 'LoanAssetController::qr/$1');
            $routes->get('(:num)/qr/image', 'LoanAssetController::qrImage/$1');
        });

        // Loan Asset Categories Master Data
        $routes->group('loans/asset-categories', ['filter' => 'permission:lending.master.manage'], static function ($routes) {
            $routes->get('/', 'AssetCategoryController::index');
            $routes->get('create', 'AssetCategoryController::create');
            $routes->get('edit/(:num)', 'AssetCategoryController::edit/$1');
            $routes->post('store', 'AssetCategoryController::store');
            $routes->post('update/(:num)', 'AssetCategoryController::update/$1');
            $routes->post('delete/(:num)', 'AssetCategoryController::delete/$1');
        });

        // Loan Units Master Data
        $routes->group('loans/units', ['filter' => 'permission:lending.master.units.manage'], static function ($routes) {
            $routes->get('/', 'UnitController::index');
            $routes->get('create', 'UnitController::create');
            $routes->get('edit/(:num)', 'UnitController::edit/$1');
            $routes->post('store', 'UnitController::store');
            $routes->post('update/(:num)', 'UnitController::update/$1');
            $routes->post('delete/(:num)', 'UnitController::delete/$1');
        });

        // Asset Movements (Inventory tracking)
        $routes->group('loans/movements', ['filter' => 'permission:lending.master.movements.manage'], static function ($routes) {
            $routes->get('/', 'AssetMovementController::index');
            $routes->get('create', 'AssetMovementController::create');
            $routes->post('store', 'AssetMovementController::store');
            $routes->post('delete/(:num)', 'AssetMovementController::delete/$1');
        });

        // Asset Maintenances
        $routes->group('loans/maintenances', ['filter' => 'permission:lending.master.maintenances.manage'], static function ($routes) {
            $routes->get('/', 'AssetMaintenanceController::index');
            $routes->get('create', 'AssetMaintenanceController::create');
            $routes->get('edit/(:num)', 'AssetMaintenanceController::edit/$1');
            $routes->post('store', 'AssetMaintenanceController::store');
            $routes->post('update/(:num)', 'AssetMaintenanceController::update/$1');
            $routes->post('delete/(:num)', 'AssetMaintenanceController::delete/$1');
        });

        // Asset Documents
        $routes->group('loans/documents', ['filter' => 'permission:lending.master.documents.manage'], static function ($routes) {
            $routes->get('/', 'AssetDocumentController::index');
            $routes->post('upload', 'AssetDocumentController::upload');
            $routes->get('download/(:num)', 'AssetDocumentController::download/$1');
            $routes->post('delete/(:num)', 'AssetDocumentController::delete/$1');
        });

        // Loan Labs Master Data
        $routes->group('loans/labs', ['filter' => 'permission:lending.master.labs.manage'], static function ($routes) {
            $routes->get('/', 'LabController::index');
            $routes->get('create', 'LabController::create');
            $routes->get('edit/(:num)', 'LabController::edit/$1');
            $routes->post('store', 'LabController::store');
            $routes->post('update/(:num)', 'LabController::update/$1');
            $routes->post('delete/(:num)', 'LabController::delete/$1');
            $routes->get('archive', 'LabController::archive');
            $routes->post('restore/(:num)', 'LabController::restore/$1');
            $routes->post('force-delete/(:num)', 'LabController::forceDelete/$1');
            $routes->get('(:num)/photos', 'LabController::photos/$1');
            $routes->post('(:num)/photos/upload', 'LabController::uploadPhoto/$1');
            $routes->post('(:num)/photos/(:num)/delete', 'LabController::deletePhoto/$1/$2');
            $routes->post('(:num)/photos/(:num)/primary', 'LabController::setPrimaryPhoto/$1/$2');
            $routes->get('qr', 'LabController::qrIndex');
            $routes->get('(:num)/qr', 'LabController::qr/$1');
            $routes->get('(:num)/qr/image', 'LabController::qrImage/$1');
            $routes->get('datatable', 'LabController::datatable');
            $routes->get('condition-history', 'LabController::conditionHistoryAll');
            $routes->get('(:num)/condition-history', 'LabController::conditionHistory/$1');
        });

        // Consumable (BHP) Admin Master Data
        $routes->group('consumables', ['filter' => 'permission:bhp.master.manage'], static function ($routes) {
            // Categories
            $routes->get('categories', 'ConsumableCategoryController::index');
            $routes->get('categories/create', 'ConsumableCategoryController::create');
            $routes->post('categories', 'ConsumableCategoryController::store');
            $routes->get('categories/(:num)/edit', 'ConsumableCategoryController::edit/$1');
            $routes->post('categories/(:num)/update', 'ConsumableCategoryController::update/$1');
            $routes->post('categories/(:num)/delete', 'ConsumableCategoryController::delete/$1');
            // Items
            $routes->get('items', 'ConsumableItemController::index');
            $routes->get('items/create', 'ConsumableItemController::create');
            $routes->post('items', 'ConsumableItemController::store');
            $routes->get('items/(:num)/edit', 'ConsumableItemController::edit/$1');
            $routes->post('items/(:num)/update', 'ConsumableItemController::update/$1');
            $routes->post('items/(:num)/toggle', 'ConsumableItemController::toggleStatus/$1');
            $routes->post('items/(:num)/delete', 'ConsumableItemController::delete/$1');
        });

        // Loan Faculties Master Data
        $routes->group('loans/faculties', ['filter' => 'permission:lending.master.faculties.manage'], static function ($routes) {
            $routes->get('/', 'FacultyController::index');
            $routes->get('create', 'FacultyController::create');
            $routes->get('edit/(:num)', 'FacultyController::edit/$1');
            $routes->post('store', 'FacultyController::store');
            $routes->post('update/(:num)', 'FacultyController::update/$1');
            $routes->post('delete-logo/(:num)', 'FacultyController::deleteLogo/$1');
            $routes->post('delete/(:num)', 'FacultyController::delete/$1');
        });

        // Loan Study Programs Master Data
        $routes->group('loans/study-programs', ['filter' => 'permission:lending.master.study_programs.manage'], static function ($routes) {
            $routes->get('/', 'StudyProgramController::index');
            $routes->get('create', 'StudyProgramController::create');
            $routes->get('edit/(:num)', 'StudyProgramController::edit/$1');
            $routes->post('store', 'StudyProgramController::store');
            $routes->post('update/(:num)', 'StudyProgramController::update/$1');
            $routes->post('delete/(:num)', 'StudyProgramController::delete/$1');
        });
    });
});
