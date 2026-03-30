<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SalePipelineController;
use App\Http\Controllers\PdfTemplateController;
use App\Http\Controllers\PurchaseContractController;
use App\Http\Controllers\QuotationContractController;
use App\Http\Controllers\ReservationAgreementContractController;
use App\Http\Controllers\ReservationContractController;
use App\Http\Controllers\ContractPreviewController;
use App\Http\Controllers\ContractDownloadController;
use App\Http\Controllers\ContractPreviewPageController;
use App\Http\Controllers\PurchaseAgreementContractController;
use App\Http\Controllers\InstallmentContractController;
use App\Http\Controllers\TemplateMappingController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\PublicListingController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ProfileFieldController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\FloorPlanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ReservationSignatureController;
use App\Http\Controllers\QuestionnaireController;
use App\Http\Controllers\SuperAdmin\SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\SuperAdminOrganizationController;
use App\Http\Controllers\SuperAdmin\SuperAdminPlanController;
use App\Http\Controllers\SuperAdmin\SuperAdminUserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Admin\ChatManagementController;
use App\Http\Controllers\ChatPageController;
use Illuminate\Support\Facades\Route;

// Auth
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Password Reset
Route::get('/forgot-password', [ForgotPasswordController::class, 'show'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'send'])->name('password.email');
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'show'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');

Route::redirect('/', '/dashboard');

// Public listing view (no auth required)
Route::get('/listing/{unit}', [PublicListingController::class, 'show'])->name('public.listing.show');

// Public questionnaire (no auth required)
Route::get('/questionnaire', [QuestionnaireController::class, 'create'])->name('questionnaire.create');
Route::post('/questionnaire', [QuestionnaireController::class, 'store'])->name('questionnaire.store');
Route::get('/questionnaire/thank-you', [QuestionnaireController::class, 'thankYou'])->name('questionnaire.thank-you');

// Chat (public — no auth required)
Route::get('/chat', [ChatPageController::class, 'index'])->name('chat.index');
Route::post('/chat/send', [ChatPageController::class, 'send'])->name('chat.send');
Route::get('/chat/sessions', [ChatPageController::class, 'sessions'])->name('chat.sessions');
Route::get('/chat/sessions/{id}/messages', [ChatPageController::class, 'messages'])->name('chat.messages');
Route::post('/chat/upload', [ChatPageController::class, 'upload'])->name('chat.upload');

// All protected routes
Route::middleware('auth')->group(function () {
    // Admin Chat Dashboard
    Route::prefix('admin/chat')->name('admin.chat.')->middleware('permission:admin_chat.view')->group(function () {
        Route::get('/', [ChatManagementController::class, 'index'])->name('index');
        Route::get('/sessions', [ChatManagementController::class, 'sessions'])->name('sessions');
        Route::get('/sessions/{session}/messages', [ChatManagementController::class, 'messages'])->name('messages');
        Route::post('/sessions/{session}/send', [ChatManagementController::class, 'send'])->name('send')->middleware('permission:admin_chat.manage');
        Route::post('/sessions/{session}/takeover', [ChatManagementController::class, 'takeover'])->name('takeover')->middleware('permission:admin_chat.manage');
        Route::post('/sessions/{session}/handback', [ChatManagementController::class, 'handback'])->name('handback')->middleware('permission:admin_chat.manage');
        Route::post('/sessions/{session}/resolve', [ChatManagementController::class, 'resolve'])->name('resolve')->middleware('permission:admin_chat.manage');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('permission:dashboard.view');

    // Report
    Route::get('/report', [ReportController::class, 'index'])->name('report.index')->middleware('permission:report.view');
    Route::get('/report/export-pdf', [ReportController::class, 'exportPdf'])->name('report.export-pdf')->middleware('permission:report.export');
    Route::post('/report/budget', [ReportController::class, 'saveBudget'])->name('report.save-budget')->middleware('permission:report.manage_budget');
    Route::delete('/report/budget/{budget}', [ReportController::class, 'deleteBudget'])->name('report.delete-budget')->middleware('permission:report.manage_budget');

    // Finance
    Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index')->middleware('permission:finance.view');
    Route::post('/finance/transfer/{sale}', [FinanceController::class, 'storeTransfer'])->name('finance.transfer.store')->middleware('permission:finance.transfer');

    Route::get('/activity', [ActivityController::class, 'index'])->name('activity.index')->middleware('permission:activity.view');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/signature', [ProfileController::class, 'updateSignature'])->name('profile.signature');

    Route::get('/contracts/reservation/create', [ReservationContractController::class, 'create'])->name('contracts.reservation.create');
    Route::post('/contracts/reservation/store', [ReservationContractController::class, 'store'])->name('contracts.reservation.store');

    Route::get('/contracts/purchase/create', [PurchaseContractController::class, 'create'])->name('contracts.purchase.create');
    Route::post('/contracts/purchase/store', [PurchaseContractController::class, 'store'])->name('contracts.purchase.store');

    Route::get('/contracts/purchase-agreement/{sale}/preview-page', [ContractPreviewPageController::class, 'purchaseAgreement'])
        ->name('contracts.purchase-agreement.preview-page');
    Route::get('/contracts/purchase-agreement/{sale}/preview', [PurchaseAgreementContractController::class, 'preview'])
        ->name('contracts.purchase-agreement.preview');
    Route::get('/contracts/purchase-agreement/{sale}/download', [PurchaseAgreementContractController::class, 'download'])
        ->name('contracts.purchase-agreement.download');

    Route::get('/contracts/quotation', [QuotationContractController::class, 'index'])->name('contracts.quotation.index');
    Route::get('/contracts/quotation/{listing}/{language}/preview', [QuotationContractController::class, 'previewListing'])
        ->name('contracts.quotation.preview-listing');
    Route::get('/contracts/quotation/{listing}/{language}/download', [QuotationContractController::class, 'downloadListing'])
        ->name('contracts.quotation.download-listing');

    Route::get('/contracts/{contract}/preview', [ContractPreviewController::class, 'show'])->name('contracts.preview');
    Route::get('/contracts/{contract}/download', [ContractDownloadController::class, 'download'])->name('contracts.download');
    Route::get('/contracts/reservation-agreement/{sale}/preview-page', [ContractPreviewPageController::class, 'reservation'])
        ->name('contracts.reservation-agreement.preview-page');
    Route::get('/contracts/reservation-agreement/{sale}/preview', [ReservationAgreementContractController::class, 'preview'])
        ->name('contracts.reservation-agreement.preview');
    Route::get('/contracts/reservation-agreement/{sale}/download', [ReservationAgreementContractController::class, 'download'])
        ->name('contracts.reservation-agreement.download');
    // Reservation Signatures
    Route::get('/contracts/reservation-agreement/{sale}/signature/{type}', [ReservationSignatureController::class, 'show'])
        ->where('type', 'buyer|witness1|witness2')
        ->name('contracts.reservation-agreement.signature');
    Route::post('/contracts/reservation-agreement/{sale}/signature/{type}', [ReservationSignatureController::class, 'save'])
        ->where('type', 'buyer|witness1|witness2')
        ->name('contracts.reservation-agreement.signature.save');

    Route::get('/contracts/addendum/{sale}/preview-page', [ContractPreviewPageController::class, 'addendum'])
        ->name('contracts.addendum.preview-page');
    Route::get('/contracts/addendum/{sale}/preview', [ReservationAgreementContractController::class, 'previewAddendum'])
        ->name('contracts.addendum.preview');
    Route::get('/contracts/addendum/{sale}/download', [ReservationAgreementContractController::class, 'downloadAddendum'])
        ->name('contracts.addendum.download');

    // Deal Slip
    Route::get('/contracts/deal-slip/{sale}/preview-page', [ContractPreviewPageController::class, 'dealSlip'])
        ->name('contracts.deal-slip.preview-page');
    Route::get('/contracts/deal-slip/{sale}/preview', [InstallmentContractController::class, 'previewDealSlip'])
        ->name('contracts.deal-slip.preview');
    Route::get('/contracts/deal-slip/{sale}/download', [InstallmentContractController::class, 'downloadDealSlip'])
        ->name('contracts.deal-slip.download');

    // Overdue Reminder 1st Notice
    Route::get('/contracts/overdue-reminder-1/{sale}/preview-page', [ContractPreviewPageController::class, 'overdueReminder1'])
        ->name('contracts.overdue-reminder-1.preview-page');
    Route::get('/contracts/overdue-reminder-1/{sale}/preview', [InstallmentContractController::class, 'previewOverdueReminder1'])
        ->name('contracts.overdue-reminder-1.preview');
    Route::get('/contracts/overdue-reminder-1/{sale}/download', [InstallmentContractController::class, 'downloadOverdueReminder1'])
        ->name('contracts.overdue-reminder-1.download');

    // Overdue Reminder 2nd Notice
    Route::get('/contracts/overdue-reminder-2/{sale}/preview-page', [ContractPreviewPageController::class, 'overdueReminder2'])
        ->name('contracts.overdue-reminder-2.preview-page');
    Route::get('/contracts/overdue-reminder-2/{sale}/preview', [InstallmentContractController::class, 'previewOverdueReminder2'])
        ->name('contracts.overdue-reminder-2.preview');
    Route::get('/contracts/overdue-reminder-2/{sale}/download', [InstallmentContractController::class, 'downloadOverdueReminder2'])
        ->name('contracts.overdue-reminder-2.download');

    // Templates
    Route::get('/upload-template', [PdfTemplateController::class, 'create'])->name('upload-template.create')->middleware('permission:templates.upload');
    Route::post('/upload-template', [PdfTemplateController::class, 'store'])->name('upload-template.store')->middleware('permission:templates.upload');
    Route::get('/templates', [TemplateMappingController::class, 'index'])->name('templates.index')->middleware('permission:templates.view');
    Route::get('/templates/{template}/mappings', [TemplateMappingController::class, 'show'])->name('templates.mappings.show')->middleware('permission:templates.view');
    Route::post('/templates/{template}/mappings', [TemplateMappingController::class, 'store'])->name('templates.mappings.store')->middleware('permission:templates.manage_mappings');
    Route::put('/templates/{template}/mappings/{mapping}', [TemplateMappingController::class, 'update'])->name('templates.mappings.update')->middleware('permission:templates.manage_mappings');
    Route::delete('/templates/{template}/mappings/{mapping}', [TemplateMappingController::class, 'destroy'])->name('templates.mappings.destroy')->middleware('permission:templates.manage_mappings');
    Route::delete('/templates/{template}', [TemplateMappingController::class, 'destroyTemplate'])->name('templates.destroy')->middleware('permission:templates.delete');

    // Buy/Sale Pipeline
    Route::prefix('buy-sale')->name('buy-sale.')->middleware('permission:buy_sale.view')->group(function () {
        Route::get('/', [SalePipelineController::class, 'index'])->name('index');
        Route::post('/store', [SalePipelineController::class, 'store'])->name('store')->middleware('permission:buy_sale.create');
        Route::get('/{sale}/form/{type}', [SalePipelineController::class, 'form'])->name('form');
        Route::post('/{sale}/advance', [SalePipelineController::class, 'advance'])->name('advance')->middleware('permission:buy_sale.advance');
        Route::post('/{sale}/remarks', [SalePipelineController::class, 'updateRemark'])->name('remarks')->middleware('permission:buy_sale.remarks');
        Route::post('/{sale}/cancel', [SalePipelineController::class, 'cancel'])->name('cancel')->middleware('permission:buy_sale.cancel');
        Route::get('/{sale}/installments', [SalePipelineController::class, 'installments'])->name('installments');
        Route::post('/{sale}/installments/{installment}/proof', [SalePipelineController::class, 'uploadProof'])->name('installments.proof')->middleware('permission:buy_sale.edit');
        Route::get('/{sale}/deal-slip', [SalePipelineController::class, 'dealSlip'])->name('deal-slip')->middleware('permission:buy_sale.deal_slip');
        Route::post('/{sale}/deal-slip/action', [SalePipelineController::class, 'dealSlipAction'])->name('deal-slip.action')->middleware('permission:buy_sale.deal_slip');
        Route::post('/{sale}/quotation-visitor', [SalePipelineController::class, 'saveQuotationVisitor'])->name('quotation-visitor')->middleware('permission:buy_sale.create');
        Route::get('/api/projects/{location}', [SalePipelineController::class, 'getProjects'])->name('api.projects');
        Route::get('/api/floors/{project}', [SalePipelineController::class, 'getFloors'])->name('api.floors');
        Route::get('/api/units/{project}/{floor}', [SalePipelineController::class, 'getUnits'])->name('api.units');
    });

    // Listing Setting — Locations
    Route::get('/locations', [LocationController::class, 'index'])->name('locations.index')->middleware('permission:listing_locations.view');
    Route::get('/locations/create', [LocationController::class, 'create'])->name('locations.create')->middleware('permission:listing_locations.create');
    Route::post('/locations', [LocationController::class, 'store'])->name('locations.store')->middleware('permission:listing_locations.create');
    Route::get('/locations/{location}', [LocationController::class, 'show'])->name('locations.show')->middleware('permission:listing_locations.view');
    Route::get('/locations/{location}/edit', [LocationController::class, 'edit'])->name('locations.edit')->middleware('permission:listing_locations.edit');
    Route::put('/locations/{location}', [LocationController::class, 'update'])->name('locations.update')->middleware('permission:listing_locations.edit');
    Route::delete('/locations/{location}', [LocationController::class, 'destroy'])->name('locations.destroy')->middleware('permission:listing_locations.delete');

    // Listing Setting — Projects
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index')->middleware('permission:listing_projects.view');
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create')->middleware('permission:listing_projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store')->middleware('permission:listing_projects.create');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show')->middleware('permission:listing_projects.view')->where('project', '[0-9]+');
    Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit')->middleware('permission:listing_projects.edit');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update')->middleware('permission:listing_projects.edit');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy')->middleware('permission:listing_projects.delete');
    Route::post('/projects/{project}/floor-plan', [ProjectController::class, 'uploadFloorPlan'])->name('projects.floor-plan.upload')->middleware('permission:listing_projects.edit');
    Route::delete('/projects/{project}/floor-plan', [ProjectController::class, 'removeFloorPlan'])->name('projects.floor-plan.remove')->middleware('permission:listing_projects.edit');
    Route::post('/projects/{project}/room-layout', [ProjectController::class, 'uploadRoomLayout'])->name('projects.room-layout.upload')->middleware('permission:listing_projects.edit');
    Route::delete('/projects/{project}/room-layout', [ProjectController::class, 'removeRoomLayout'])->name('projects.room-layout.remove')->middleware('permission:listing_projects.edit');

    // Listing Setting — Units
    Route::get('/units', [ListingController::class, 'index'])->name('units.index')->middleware('permission:listing_units.view');
    Route::get('/units/create', [ListingController::class, 'create'])->name('units.create')->middleware('permission:listing_units.create');
    Route::post('/units', [ListingController::class, 'store'])->name('units.store')->middleware('permission:listing_units.create');
    Route::get('/units/{unit}', [ListingController::class, 'show'])->name('units.show')->middleware('permission:listing_units.view')->where('unit', '[0-9]+');
    Route::get('/units/{unit}/edit', [ListingController::class, 'edit'])->name('units.edit')->middleware('permission:listing_units.edit');
    Route::put('/units/{unit}', [ListingController::class, 'update'])->name('units.update')->middleware('permission:listing_units.edit');
    Route::delete('/units/{unit}', [ListingController::class, 'destroy'])->name('units.destroy')->middleware('permission:listing_units.delete');
    Route::get('/units-import', [ListingController::class, 'importForm'])->name('units.import.form')->middleware('permission:listing_units.import');
    Route::post('/units-import', [ListingController::class, 'importExcel'])->name('units.import')->middleware('permission:listing_units.import');
    Route::get('/units-template', [ListingController::class, 'downloadTemplate'])->name('units.template')->middleware('permission:listing_units.import');

    // Floor Plan
    Route::middleware('permission:floor_plan.view')->group(function () {
        Route::get('/floor-plan', [FloorPlanController::class, 'index'])->name('floor-plan.index');
        Route::get('/api/floorplan', [FloorPlanController::class, 'apiIndex'])->name('floor-plan.api.index');
        Route::get('/api/floorplan/unit/{unitCode}', [FloorPlanController::class, 'apiUnit'])->name('floor-plan.api.unit');
    });

    // Employee Module
    Route::prefix('employee')->name('employee.')->group(function () {
        // Company Information
        Route::get('/company', [CompanyController::class, 'index'])->name('company.index')->middleware('permission:employee_company.view');
        Route::put('/company', [CompanyController::class, 'update'])->name('company.update')->middleware('permission:employee_company.edit');

        // Profile Information
        Route::get('/profile-info', [ProfileFieldController::class, 'index'])->name('profile-info.index')->middleware('permission:employee_profile_fields.view');
        Route::post('/profile-info', [ProfileFieldController::class, 'store'])->name('profile-info.store')->middleware('permission:employee_profile_fields.manage');
        Route::put('/profile-info/{profileField}', [ProfileFieldController::class, 'update'])->name('profile-info.update')->middleware('permission:employee_profile_fields.manage');
        Route::post('/profile-info/{profileField}/toggle/{field}', [ProfileFieldController::class, 'toggle'])->name('profile-info.toggle')->middleware('permission:employee_profile_fields.manage');

        // Employee List
        Route::get('/list', [EmployeeController::class, 'index'])->name('list.index')->middleware('permission:employee_list.view');
        Route::get('/list/create', [EmployeeController::class, 'create'])->name('list.create')->middleware('permission:employee_list.create');
        Route::post('/list', [EmployeeController::class, 'store'])->name('list.store')->middleware('permission:employee_list.create');
        Route::get('/list/{employee}/edit', [EmployeeController::class, 'edit'])->name('list.edit')->middleware('permission:employee_list.edit');
        Route::put('/list/{employee}', [EmployeeController::class, 'update'])->name('list.update')->middleware('permission:employee_list.edit');
        Route::delete('/list/{employee}', [EmployeeController::class, 'destroy'])->name('list.destroy')->middleware('permission:employee_list.delete');

        // Positions
        Route::get('/positions', [PositionController::class, 'index'])->name('positions.index')->middleware('permission:employee_positions.view');
        Route::post('/positions', [PositionController::class, 'store'])->name('positions.store')->middleware('permission:employee_positions.manage');
        Route::put('/positions/{position}', [PositionController::class, 'update'])->name('positions.update')->middleware('permission:employee_positions.manage');
        Route::post('/positions/{position}/toggle', [PositionController::class, 'toggleActive'])->name('positions.toggle')->middleware('permission:employee_positions.manage');
        Route::delete('/positions/{position}', [PositionController::class, 'destroy'])->name('positions.destroy')->middleware('permission:employee_positions.manage');

        // Teams
        Route::get('/teams', [TeamController::class, 'index'])->name('teams.index')->middleware('permission:employee_teams.view');
        Route::post('/teams', [TeamController::class, 'store'])->name('teams.store')->middleware('permission:employee_teams.manage');
        Route::put('/teams/{team}', [TeamController::class, 'update'])->name('teams.update')->middleware('permission:employee_teams.manage');
        Route::post('/teams/{team}/toggle', [TeamController::class, 'toggleActive'])->name('teams.toggle')->middleware('permission:employee_teams.manage');
        Route::get('/teams/{team}/members', [TeamController::class, 'members'])->name('teams.members')->middleware('permission:employee_teams.view');
        Route::delete('/teams/{team}', [TeamController::class, 'destroy'])->name('teams.destroy')->middleware('permission:employee_teams.manage');

        // Roles & Permissions
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index')->middleware('permission:roles.view');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store')->middleware('permission:roles.manage');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update')->middleware('permission:roles.manage');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy')->middleware('permission:roles.manage');
    });
});

// ─── Super Admin Panel ────────────────────────────────────────────────────
Route::middleware(['auth', 'superadmin'])
    ->prefix('super-admin')
    ->name('super-admin.')
    ->group(function () {

    // Dashboard
    Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])
        ->name('dashboard');

    // Plan management
    Route::get('/plans', [SuperAdminPlanController::class, 'index'])
        ->name('plans.index');
    Route::get('/plans/create', [SuperAdminPlanController::class, 'create'])
        ->name('plans.create');
    Route::post('/plans', [SuperAdminPlanController::class, 'store'])
        ->name('plans.store');
    Route::get('/plans/{plan}/edit', [SuperAdminPlanController::class, 'edit'])
        ->name('plans.edit');
    Route::put('/plans/{plan}', [SuperAdminPlanController::class, 'update'])
        ->name('plans.update');
    Route::post('/plans/{plan}/toggle-active', [SuperAdminPlanController::class, 'toggleActive'])
        ->name('plans.toggle-active');

    // Organization management
    Route::get('/organizations', [SuperAdminOrganizationController::class, 'index'])
        ->name('organizations.index');
    Route::get('/organizations/create', [SuperAdminOrganizationController::class, 'create'])
        ->name('organizations.create');
    Route::post('/organizations', [SuperAdminOrganizationController::class, 'store'])
        ->name('organizations.store');
    Route::get('/organizations/{organization}/edit', [SuperAdminOrganizationController::class, 'edit'])
        ->name('organizations.edit');
    Route::put('/organizations/{organization}', [SuperAdminOrganizationController::class, 'update'])
        ->name('organizations.update');
    Route::post('/organizations/{organization}/toggle-active', [SuperAdminOrganizationController::class, 'toggleActive'])
        ->name('organizations.toggle-active');
    Route::get('/organizations/{organization}/users', [SuperAdminOrganizationController::class, 'users'])
        ->name('organizations.users');

    // Impersonation
    Route::post('/impersonate/{user}', [SuperAdminOrganizationController::class, 'impersonate'])
        ->name('impersonate');
    Route::post('/leave-impersonate', [SuperAdminOrganizationController::class, 'leaveImpersonate'])
        ->name('leave-impersonate');

    // User overview
    Route::get('/users', [SuperAdminUserController::class, 'index'])
        ->name('users.index');
    Route::post('/users/{user}/toggle-active', [SuperAdminUserController::class, 'toggleActive'])
        ->name('users.toggle-active');
});
