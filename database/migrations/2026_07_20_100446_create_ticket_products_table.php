<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ticket_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->date('valid_date')->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('promo_price', 12, 2)->nullable();
            $table->timestamp('promo_start_at')->nullable();
            $table->timestamp('promo_end_at')->nullable();
            $table->unsignedInteger('quota')->default(0);
            $table->unsignedInteger('reserved_quantity')->default(0);
            $table->unsignedInteger('sold_quantity')->default(0);
            $table->unsignedInteger('max_per_order')->default(10);
            $table->decimal('admin_fee', 12, 2)->default(0);
            $table->string('image')->nullable();
            $table->enum('status', ['draft','coming_soon','available','sold_out','closed'])->default('draft');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_products');
    }
};
