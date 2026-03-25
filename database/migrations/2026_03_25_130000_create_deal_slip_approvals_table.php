<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deal_slip_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->unique()->constrained()->cascadeOnDelete();
            $table->enum('status', ['prepare', 'check', 'approved'])->default('prepare');
            $table->foreignId('prepared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('prepared_name')->nullable();
            $table->text('prepared_signature')->nullable();
            $table->timestamp('prepared_at')->nullable();
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('checked_name')->nullable();
            $table->text('checked_signature')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('approved_name')->nullable();
            $table->text('approved_signature')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deal_slip_approvals');
    }
};
