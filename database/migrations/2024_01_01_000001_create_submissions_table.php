<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('user_name')->nullable();
            $table->string('user_email');
            $table->string('request_id')->nullable()->index();
            $table->string('title');
            $table->string('artist_band')->nullable();
            $table->string('merch_type')->nullable();
            $table->string('released_year')->nullable();
            $table->string('merch_edition')->nullable();
            $table->string('category')->default('Umum');
            $table->text('description');
            $table->string('contact')->nullable();
            $table->string('image_url')->nullable();
            $table->string('file_name')->nullable();
            $table->json('additional_data')->nullable();
            $table->string('status')->default('In Progress'); // In Progress, approved, rejected, pending
            $table->text('admin_notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
