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
Route::get('/chat/csrf-token', fn () => response()->json(['token' => csrf_token()]))->name('chat.csrf-token');
Route::get('/chat/sessions', [ChatPageController::class, 'sessions'])->name('chat.sessions');
Route::get('/chat/sessions/{id}/messages', [ChatPageController::class, 'messages'])->name('chat.messages');
Route::post('/chat/upload', [ChatPageController::class, 'upload'])->name('chat.upload');

// All protected routes
Route::middleware('auth')->group(function () {
    // Admin Chat Dashboard
    Route::prefix('admin/chat')->name('admin.chat.')->group(function () {
        Route::get('/', [ChatManagementController::class, 'index'])->name('index');
        Route::get('/sessions', [ChatManagementController::class, 'sessions'])->name('sessions');
        Route::get('/sessions/{session}/messages', [ChatManagementController::class, 'messages'])->name('messages');
        Route::post('/sessions/{session}/send', [ChatManagementController::class, 'send'])->name('send');
        Route::post('/sessions/{session}/takeover', [ChatManagementController::class, 'takeover'])->name('takeover');
        Route::post('/sessions/{session}/handback', [ChatManagementController::class, 'handback'])->name('handback');
        Route::post('/sessions/{session}/resolve', [ChatManagementController::class, 'resolve'])->name('resolve');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/report', [ReportController::class, 'index'])->name('report.index');
    Route::get('/report/export-pdf', [ReportController::class, 'exportPdf'])->name('report.export-pdf');
    Route::post('/report/budget', [ReportController::class, 'saveBudget'])->name('report.save-budget');
    Route::delete('/report/budget/{budget}', [ReportController::class, 'deleteBudget'])->name('report.delete-budget');
    Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index');
    Route::get('/activity', [ActivityController::class, 'index'])->name('activity.index');

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

    Route::get('/upload-template', [PdfTemplateController::class, 'create'])->name('upload-template.create');
    Route::post('/upload-template', [PdfTemplateController::class, 'store'])->name('upload-template.store');

    Route::get('/templates', [TemplateMappingController::class, 'index'])->name('templates.index');
    Route::get('/templates/{template}/mappings', [TemplateMappingController::class, 'show'])->name('templates.mappings.show');
    Route::post('/templates/{template}/mappings', [TemplateMappingController::class, 'store'])->name('templates.mappings.store');
    Route::put('/templates/{template}/mappings/{mapping}', [TemplateMappingController::class, 'update'])->name('templates.mappings.update');
    Route::delete('/templates/{template}/mappings/{mapping}', [TemplateMappingController::class, 'destroy'])->name('templates.mappings.destroy');
    Route::delete('/templates/{template}', [TemplateMappingController::class, 'destroyTemplate'])->name('templates.destroy');

    // Buy/Sale Pipeline
    Route::prefix('buy-sale')->name('buy-sale.')->group(function () {
        Route::get('/', [SalePipelineController::class, 'index'])->name('index');
        Route::post('/store', [SalePipelineController::class, 'store'])->name('store');
        Route::get('/{sale}/form/{type}', [SalePipelineController::class, 'form'])->name('form');
        Route::post('/{sale}/advance', [SalePipelineController::class, 'advance'])->name('advance');
        Route::post('/{sale}/remarks', [SalePipelineController::class, 'updateRemark'])->name('remarks');
        Route::post('/{sale}/cancel', [SalePipelineController::class, 'cancel'])->name('cancel');
        Route::get('/{sale}/installments', [SalePipelineController::class, 'installments'])->name('installments');
        Route::post('/{sale}/installments/{installment}/proof', [SalePipelineController::class, 'uploadProof'])->name('installments.proof');
        Route::get('/{sale}/deal-slip', [SalePipelineController::class, 'dealSlip'])->name('deal-slip');
        Route::post('/{sale}/deal-slip/action', [SalePipelineController::class, 'dealSlipAction'])->name('deal-slip.action');
        Route::post('/{sale}/quotation-visitor', [SalePipelineController::class, 'saveQuotationVisitor'])->name('quotation-visitor');
        Route::get('/api/projects/{location}', [SalePipelineController::class, 'getProjects'])->name('api.projects');
        Route::get('/api/floors/{project}', [SalePipelineController::class, 'getFloors'])->name('api.floors');
        Route::get('/api/units/{project}/{floor}', [SalePipelineController::class, 'getUnits'])->name('api.units');
    });

    // Listing Setting
    Route::resource('locations', LocationController::class);
    Route::resource('projects', ProjectController::class);
    Route::post('/projects/{project}/floor-plan', [ProjectController::class, 'uploadFloorPlan'])->name('projects.floor-plan.upload');
    Route::delete('/projects/{project}/floor-plan', [ProjectController::class, 'removeFloorPlan'])->name('projects.floor-plan.remove');
    Route::post('/projects/{project}/room-layout', [ProjectController::class, 'uploadRoomLayout'])->name('projects.room-layout.upload');
    Route::delete('/projects/{project}/room-layout', [ProjectController::class, 'removeRoomLayout'])->name('projects.room-layout.remove');
    Route::resource('units', ListingController::class);
    Route::get('/units-import', [ListingController::class, 'importForm'])->name('units.import.form');
    Route::post('/units-import', [ListingController::class, 'importExcel'])->name('units.import');
    Route::get('/units-template', [ListingController::class, 'downloadTemplate'])->name('units.template');

    // Floor Plan
    Route::get('/floor-plan', [FloorPlanController::class, 'index'])->name('floor-plan.index');
    Route::get('/api/floorplan', [FloorPlanController::class, 'apiIndex'])->name('floor-plan.api.index');
    Route::get('/api/floorplan/unit/{unitCode}', [FloorPlanController::class, 'apiUnit'])->name('floor-plan.api.unit');

    // Employee Module
    Route::prefix('employee')->name('employee.')->group(function () {
        // Company Information
        Route::get('/company', [CompanyController::class, 'index'])->name('company.index');
        Route::put('/company', [CompanyController::class, 'update'])->name('company.update');

        // Profile Information
        Route::get('/profile-info', [ProfileFieldController::class, 'index'])->name('profile-info.index');
        Route::post('/profile-info', [ProfileFieldController::class, 'store'])->name('profile-info.store');
        Route::put('/profile-info/{profileField}', [ProfileFieldController::class, 'update'])->name('profile-info.update');
        Route::post('/profile-info/{profileField}/toggle/{field}', [ProfileFieldController::class, 'toggle'])->name('profile-info.toggle');

        // Employee List
        Route::get('/list', [EmployeeController::class, 'index'])->name('list.index');
        Route::get('/list/create', [EmployeeController::class, 'create'])->name('list.create');
        Route::post('/list', [EmployeeController::class, 'store'])->name('list.store');
        Route::get('/list/{employee}/edit', [EmployeeController::class, 'edit'])->name('list.edit');
        Route::put('/list/{employee}', [EmployeeController::class, 'update'])->name('list.update');
        Route::delete('/list/{employee}', [EmployeeController::class, 'destroy'])->name('list.destroy');

        // Positions
        Route::get('/positions', [PositionController::class, 'index'])->name('positions.index');
        Route::post('/positions', [PositionController::class, 'store'])->name('positions.store');
        Route::put('/positions/{position}', [PositionController::class, 'update'])->name('positions.update');
        Route::post('/positions/{position}/toggle', [PositionController::class, 'toggleActive'])->name('positions.toggle');
        Route::delete('/positions/{position}', [PositionController::class, 'destroy'])->name('positions.destroy');

        // Teams
        Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
        Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
        Route::put('/teams/{team}', [TeamController::class, 'update'])->name('teams.update');
        Route::post('/teams/{team}/toggle', [TeamController::class, 'toggleActive'])->name('teams.toggle');
        Route::get('/teams/{team}/members', [TeamController::class, 'members'])->name('teams.members');
        Route::delete('/teams/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');
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
