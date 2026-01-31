<?php
session_start();

require __DIR__ . '/../app/core/db.php';
require __DIR__ . '/../app/core/router.php';
require __DIR__ . '/../app/core/auth.php';
require __DIR__ . '/../app/core/csrf.php';
require __DIR__ . '/../app/core/helpers.php';

require __DIR__ . '/../app/controllers/DashboardController.php';
require __DIR__ . '/../app/controllers/CompaniesController.php';
require __DIR__ . '/../app/controllers/OffersController.php';
require __DIR__ . '/../app/controllers/JobsController.php';
require __DIR__ . '/../app/controllers/AdminController.php';
require __DIR__ . '/../app/controllers/UsersController.php';
require __DIR__ . '/../app/controllers/AgendaController.php';

route('GET','/', function() {
  require_login();
  DashboardController::index();
});

route('GET','/login', fn() => AuthController::loginForm());
route('POST','/login', fn() => AuthController::login());
route('POST','/logout', fn() => AuthController::logout());

// Dashboard / Bacheca notifiche
route('POST','/dashboard/read', fn() => (require_login()) ?: DashboardController::markRead());
route('POST','/dashboard/read-all', fn() => (require_login()) ?: DashboardController::markAllRead());

// Assegnazione commessa
route('POST','/jobs/assign', fn() => (require_login()) ?: JobsController::assign());


// Companies
route('GET','/companies', fn() => (require_login()) ?: CompaniesController::list());
route('GET','/companies/new', fn() => (require_login()) ?: CompaniesController::form());
route('POST','/companies/save', fn() => (require_login()) ?: CompaniesController::save());
route('GET','/companies/view', fn() => (require_login()) ?: CompaniesController::view());
route('POST','/companies/delete', fn() => (require_login()) ?: CompaniesController::delete());

// Offers
route('GET','/offers', fn() => (require_login()) ?: OffersController::list());
route('GET','/offers/new', fn() => (require_login()) ?: OffersController::form());
route('POST','/offers/save', fn() => (require_login()) ?: OffersController::save());
route('GET','/offers/view', fn() => (require_login()) ?: OffersController::view());
route('POST','/offers/delete', fn() => (require_login()) ?: OffersController::delete());
route('GET','/offers/history', fn() => (require_login()) ?: OffersController::history());
route('POST','/offers/status', fn() => (require_login()) ?: OffersController::changeStatus());

// Jobs
route('GET','/jobs', fn() => (require_login()) ?: JobsController::list());
route('GET','/jobs/view', fn() => (require_login()) ?: JobsController::view());
route('POST','/jobs/assign', fn() => (require_login()) ?: JobsController::assign());
route('GET','/jobs/planning', fn() => (require_login()) ?: JobsController::planning());
route('GET','/jobs/actual', fn() => (require_login()) ?: JobsController::actual());

// Admin
route('GET','/admin/production', fn() => (require_login()) ?: AdminController::production());
route('GET','/admin/invoices', fn() => (require_login()) ?: AdminController::invoices());
route('GET','/admin/payments', fn() => (require_login()) ?: AdminController::payments());

// Users
route('GET','/users', fn() => (require_login()) ?: UsersController::list());

// Agenda
route('GET','/agenda', fn() => (require_login()) ?: AgendaController::index());

dispatch();
