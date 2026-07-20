<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code', 30)->unique();
            $table->string('invoice_number', 30)->nullable()->unique();
            $table->foreignId('event_id')->constrained();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('bank_account_id')->nullable()->constrained();
            $table->enum('order_status', [
                'pending_payment','waiting_verification','paid',
                'payment_rejected','expired','cancelled','refunded'
            ])->default('pending_payment');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('admin_fee', 12, 2)->default(0);
            $table->integer('unique_code')->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->string('currency', 3)->default('IDR');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('cancelled_at')->nullable();
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('terms_accepted_at')->nullable();
            $table->timestamp('privacy_accepted_at')->nullable();
            $table->string('created_ip', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('order_status');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
