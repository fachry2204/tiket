<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('event_date');
            $table->string('location');
            $table->string('city')->nullable();
            $table->string('maps_url')->nullable();
            $table->string('banner_desktop')->nullable();
            $table->string('banner_mobile')->nullable();
            $table->string('logo')->nullable();
            $table->string('contact_whatsapp')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_instagram')->nullable();
            $table->string('contact_address')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->text('privacy_policy')->nullable();
            $table->text('participant_requirements')->nullable();
            $table->enum('status', ['draft', 'published', 'ended'])->default('draft');
            $table->string('timezone')->default('Asia/Jakarta');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
