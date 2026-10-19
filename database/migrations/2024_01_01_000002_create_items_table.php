<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->nullable()->constrained('submissions')->onDelete('set null');
            $table->string('title');
            $table->string('slug')->unique(); // Clean URL slug
            $table->string('category')->default('Umum');
            $table->text('description');
            $table->string('contact');
            $table->string('image_url')->nullable();
            $table->unsignedBigInteger('views')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
