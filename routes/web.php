<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Api\CameraApiController;
use App\Http\Controllers\Api\CameraLiveStatusController;
use App\Http\Controllers\CameraController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\MaintenanceTaskTypeController;
use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/uptime', [ReportController::class, 'uptime'])->name('reports.uptime');
    Route::get('/reports/uptime/export', [ReportController::class, 'exportUptime'])->name('reports.uptime.export');
    Route::get('/reports/events', [ReportController::class, 'events'])->name('reports.events');
    Route::get('/reports/events/export', [ReportController::class, 'exportEvents'])->name('reports.events.export');
    Route::get('/reports/sites', [ReportController::class, 'sites'])->name('reports.sites');
    Route::get('/reports/sites/export', [ReportController::class, 'exportSites'])->name('reports.sites.export');
    Route::get('/reports/clients', [ReportController::class, 'clients'])->name('reports.clients');
    Route::get('/reports/clients/export', [ReportController::class, 'exportClients'])->name('reports.clients.export');
    Route::get('/maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');
    Route::get('/maintenance/create', [MaintenanceController::class, 'create'])->name('maintenance.create');
    Route::post('/maintenance', [MaintenanceController::class, 'store'])->name('maintenance.store');
    Route::get('/maintenance/{task}', [MaintenanceController::class, 'show'])->name('maintenance.show');
    Route::get('/maintenance/{task}/edit', [MaintenanceController::class, 'edit'])->name('maintenance.edit');
    Route::put('/maintenance/{task}', [MaintenanceController::class, 'update'])->name('maintenance.update');
    Route::post('/maintenance/{task}/start', [MaintenanceController::class, 'start'])->name('maintenance.start');
    Route::post('/maintenance/{task}/complete', [MaintenanceController::class, 'complete'])->name('maintenance.complete');
    Route::post('/maintenance/{task}/cancel', [MaintenanceController::class, 'cancel'])->name('maintenance.cancel');
    Route::post('/maintenance/{task}/attachments', [MaintenanceController::class, 'upload'])->name('maintenance.attachments.store');
    Route::get('/maintenance/{task}/service-report/pdf', [MaintenanceController::class, 'serviceReportPdf'])->name('maintenance.service-report.pdf');
    Route::prefix('api')->group(function (): void {
        Route::get('/cameras/live-status', [CameraLiveStatusController::class, 'index'])->name('api.cameras.live-status');
        Route::get('/cameras/{camera}/live-status', [CameraLiveStatusController::class, 'show'])->name('api.cameras.live-status.show');
        Route::get('/cameras', [CameraApiController::class, 'index'])->name('api.cameras.index');
        Route::get('/cameras/{camera}', [CameraApiController::class, 'show'])->name('api.cameras.show');
        Route::post('/maintenance/{task}/attachments', [MaintenanceController::class, 'upload'])->name('api.maintenance.attachments.store');
    });
    Route::get('/cameras', [CameraController::class, 'index'])->name('cameras.index');
    Route::get('/cameras/create', [CameraController::class, 'create'])->name('cameras.create');
    Route::get('/cameras/events', [CameraController::class, 'events'])->name('cameras.events');
    Route::get('/cameras/map', [CameraController::class, 'map'])->name('cameras.map');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::get('/settings/hikvision-setup', [SettingsController::class, 'hikvisionSetup'])->name('settings.hikvision-setup');
    Route::post('/cameras', [CameraController::class, 'store'])->name('cameras.store');
    Route::get('/cameras/{camera}', [CameraController::class, 'show'])->name('cameras.show');
    Route::get('/cameras/{camera}/edit', [CameraController::class, 'edit'])->name('cameras.edit');
    Route::put('/cameras/{camera}', [CameraController::class, 'update'])->name('cameras.update');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

Route::middleware(['auth', 'role:admin'])->group(function (): void {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::get('/organisations/create', [OrganisationController::class, 'create'])->name('organisations.create');
    Route::post('/organisations', [OrganisationController::class, 'store'])->name('organisations.store');
    Route::get('/organisations/{organisation}/edit', [OrganisationController::class, 'edit'])->name('organisations.edit');
    Route::put('/organisations/{organisation}', [OrganisationController::class, 'update'])->name('organisations.update');
    Route::get('/sites/create', [SiteController::class, 'create'])->name('sites.create');
    Route::post('/sites', [SiteController::class, 'store'])->name('sites.store');
    Route::get('/sites/{site}/edit', [SiteController::class, 'edit'])->name('sites.edit');
    Route::put('/sites/{site}', [SiteController::class, 'update'])->name('sites.update');
});

Route::middleware(['auth', 'role:admin,council_operator'])->group(function (): void {
    Route::get('/settings/maintenance-task-types', [MaintenanceTaskTypeController::class, 'index'])->name('settings.maintenance-task-types.index');
    Route::get('/settings/maintenance-task-types/create', [MaintenanceTaskTypeController::class, 'create'])->name('settings.maintenance-task-types.create');
    Route::post('/settings/maintenance-task-types', [MaintenanceTaskTypeController::class, 'store'])->name('settings.maintenance-task-types.store');
    Route::get('/settings/maintenance-task-types/{taskType}/edit', [MaintenanceTaskTypeController::class, 'edit'])->name('settings.maintenance-task-types.edit');
    Route::put('/settings/maintenance-task-types/{taskType}', [MaintenanceTaskTypeController::class, 'update'])->name('settings.maintenance-task-types.update');
});

Route::middleware(['auth', 'role:admin,council_operator,auditor'])->group(function (): void {
    Route::get('/organisations', [OrganisationController::class, 'index'])->name('organisations.index');
});

Route::middleware(['auth', 'role:admin,council_operator,client,auditor'])->group(function (): void {
    Route::get('/sites', [SiteController::class, 'index'])->name('sites.index');
    Route::get('/sites/{site}', [SiteController::class, 'show'])->name('sites.show');
});
