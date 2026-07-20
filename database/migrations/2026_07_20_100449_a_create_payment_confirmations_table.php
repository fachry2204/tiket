<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_confirmations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('sender_name');
            $table->string('sender_bank');
            $table->date('transfer_date');
            $table->decimal('transferred_amount', 12, 2);
            $table->string('proof_file_path');
            $table->string('proof_original_name');
            $table->string('proof_mime_type', 100);
            $table->unsignedBigInteger('proof_size');
            $table->enum('status', ['pending','verified','rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->text('customer_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_confirmations');
    }
};
