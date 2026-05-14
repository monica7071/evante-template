<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\ChatbotController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\SupportController;
use App\Http\Controllers\Api\V2\AdminChatController;
use App\Http\Controllers\Api\V2\WebhookController;
use Illuminate\Support\Facades\Route;

// v2 — Channel webhooks (no api.key middleware; each channel verifies its own signature)
Route::prefix('v2')->group(function () {
    // POST /api/v2/webhook/{channel}  — inbound messages (line|whatsapp|telegram|instagram|tiktok)
    // GET  /api/v2/webhook/{channel}  — verification handshake (WhatsApp/Instagram/TikTok)
    Route::match(['GET', 'POST'], '/webhook/{channel}', [WebhookController::class, 'handle'])
        ->where('channel', 'line|whatsapp|telegram|instagram|tiktok')
        ->middleware('throttle:300,1');
});

// v2 Admin API — protected by X-API-Key header (used by evante-aura admin panel)
Route::prefix('v2')->middleware(['api.key', 'throttle:300,1'])->group(function () {
    // Chat messages
    Route::post('/chat/messages', [AdminChatController::class, 'sendMessage']);
    Route::get('/chat/messages', [AdminChatController::class, 'allMessages']);
    Route::get('/chat/messages/{messageId}/exists', [AdminChatController::class, 'messageExists']);

    // Chat sessions
    Route::get('/chat/sessions', [AdminChatController::class, 'sessions']);
    Route::get('/chat/sessions/token/{token}/messages', [AdminChatController::class, 'sessionMessagesByToken']);
    Route::get('/chat/sessions/{id}/messages', [AdminChatController::class, 'sessionMessages'])
        ->where('id', '[0-9]+');
    Route::get('/chat/sessions/token/{token}/next-sequence', [AdminChatController::class, 'nextSequenceByToken']);
    Route::get('/chat/sessions/{id}/next-sequence', [AdminChatController::class, 'nextSequence'])
        ->where('id', '[0-9]+');
    Route::patch('/chat/sessions/token/{token}/viewed', [AdminChatController::class, 'markViewedByToken']);
    Route::patch('/chat/sessions/{id}/viewed', [AdminChatController::class, 'markViewed'])
        ->where('id', '[0-9]+');

    // Contacts
    Route::get('/contacts', [AdminChatController::class, 'contacts']);
    Route::get('/contacts/labels', [AdminChatController::class, 'contactLabels']);

    // AI Prompts
    Route::get('/prompts', [AdminChatController::class, 'prompts']);
    Route::put('/prompts/{id}', [AdminChatController::class, 'updatePrompt'])
        ->where('id', '[0-9]+');

    // Monitoring / KPIs
    Route::get('/monitoring/kpis', [AdminChatController::class, 'kpis']);
});

Route::prefix('v1')->middleware(['api.key', 'throttle:60,1'])->group(function () {
    // ── ข้อมูลโครงการ/ห้อง ──
    Route::get('/projects', [ChatbotController::class, 'projects']);
    Route::get('/rooms', [ChatbotController::class, 'availableRooms']);
    Route::get('/rooms/{unit_code}', [ChatbotController::class, 'roomDetail']);
    Route::get('/units/{unit_code}/price', [ChatbotController::class, 'unitPrice']);
    Route::get('/promotions', [ChatbotController::class, 'promotions']);

    // ── นัดหมาย ──
    Route::post('/appointments', [ChatbotController::class, 'bookAppointment'])->middleware('throttle:10,1');
    Route::post('/appointments/{unit_code}/cancel', [ChatbotController::class, 'cancelAppointment'])->middleware('throttle:10,1');
    Route::get('/appointments/slots', [AppointmentController::class, 'slots']);

    // ── จอง & ชำระเงิน ──
    Route::post('/bookings', [PaymentController::class, 'createBooking'])->middleware('throttle:10,1');
    Route::get('/payments/schedule', [PaymentController::class, 'schedule']);
    Route::post('/payments/qr', [PaymentController::class, 'generateQr']);
    Route::post('/payments/omise', [PaymentController::class, 'omiseCharge']);
    Route::get('/payments/status', [PaymentController::class, 'status']);
    Route::get('/payments/overdue', [PaymentController::class, 'overdue']);

    // ── แจ้งเตือน ──
    Route::post('/notify/reminder', [AppointmentController::class, 'sendReminder']);

    // ── Support APIs ──
    Route::get('/knowledge/search', [SupportController::class, 'knowledgeSearch']);
    Route::get('/customers/{id}', [SupportController::class, 'customer'])->where('id', '[0-9]+');
    Route::post('/chat/handoff', [SupportController::class, 'chatHandoff']);
    Route::post('/documents/upload', [SupportController::class, 'uploadDocument']);
    Route::get('/loan-calculator', [SupportController::class, 'loanCalculator']);
    Route::post('/verify-slip', [SupportController::class, 'verifySlip']);
});
