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
        Schema::create('hero_spotlights', function (Blueprint $table) {
            $table->id();
            $table->enum('spotlight_type', ['School', 'Sponsor', 'Student']);
            $table->string('title');
            $table->text('description');
            $table->string('featured_image_url')->nullable();
            $table->string('profile_image_url')->nullable();
            $table->morphs('spotlightable'); // Polymorphic relationship to users, opportunities, etc.
            $table->json('statistics')->nullable(); // Success metrics, numbers, achievements
            $table->string('call_to_action_text')->nullable();
            $table->string('call_to_action_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hero_spotlights');
    }
};
