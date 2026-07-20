<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->string('ticket_code', 30)->unique();
            $table->string('qr_token', 100)->unique();
            $table->string('qr_token_hash', 100)->unique();
            $table->unsignedInteger('sequence_number');
            $table->string('holder_name');
            $table->enum('status', ['active','used','cancelled','refunded'])->default('active');
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->foreignId('checked_in_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index('ticket_code');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
