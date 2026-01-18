<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('homepage_content', function (Blueprint $table) {
            $table->id();
            $table->string('section_key')->unique(); // hero, about, features, testimonials, etc.
            $table->string('title')->nullable();
            $table->text('subtitle')->nullable();
            $table->longText('content')->nullable();
            $table->string('primary_button_text')->nullable();
            $table->string('primary_button_url')->nullable();
            $table->string('secondary_button_text')->nullable();
            $table->string('secondary_button_url')->nullable();
            $table->string('background_image_url')->nullable();
            $table->string('featured_image_url')->nullable();
            $table->json('spotlight_data')->nullable(); // For hero section spotlights (schools, sponsors, students)
            $table->json('additional_data')->nullable(); // For any extra section-specific data
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homepage_content');
    }
};
