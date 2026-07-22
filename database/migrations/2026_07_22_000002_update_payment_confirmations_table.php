<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payment_confirmations', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->nullable()->change();
            $table->string('deleted_order_code')->nullable()->after('order_id');
            $table->string('status')->default('pending')->change();
        });

        try {
            Schema::table('payment_confirmations', function (Blueprint $table) {
                $table->dropForeign(['order_id']);
                $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
            });
        } catch (\Throwable $e) {}
    }

    public function down(): void
    {
        Schema::table('payment_confirmations', function (Blueprint $table) {
            $table->dropColumn('deleted_order_code');
        });
    }
};
