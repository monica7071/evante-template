<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');               // 1-12
            $table->decimal('reservation_total', 14, 2)->default(0);
            $table->decimal('contract_payment_total', 14, 2)->default(0);
            $table->decimal('transfer_amount_total', 14, 2)->default(0);
            $table->decimal('fees_total', 14, 2)->default(0);
            $table->decimal('transferred_value', 14, 2)->default(0); // sum price_per_room of transferred
            $table->unsignedInteger('transferred_count')->default(0);
            $table->unsignedInteger('active_deals')->default(0);     // non-available, non-transferred
            $table->json('per_person')->nullable();                  // [{user_id, name, deals, value}]
            $table->json('per_status')->nullable();                  // {reserved: {cnt, val}, ...}
            $table->timestamp('snapshot_at')->nullable();            // when the snapshot was taken
            $table->timestamps();

            $table->unique(['year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_snapshots');
    }
};
