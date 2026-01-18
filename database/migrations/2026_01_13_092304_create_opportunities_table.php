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
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('school_name');
            $table->string('location');
            $table->enum('education_level', ['Primary', 'Secondary', 'Tertiary']);
            $table->enum('support_type', ['Full Scholarship', 'Partial Bursary', 'Material Support', 'Fee Support']);
            $table->text('eligibility');
            $table->enum('status', ['Open', 'Closed', 'Draft'])->default('Draft');
            $table->text('description');
            $table->datetime('deadline');
            $table->enum('category', ['Academic Excellence', 'Health Support', 'Special Needs', 'General Support']);
            $table->decimal('coverage_percentage', 5, 2)->nullable();
            $table->string('duration')->nullable();
            $table->integer('available_slots')->default(1);
            $table->json('detailed_eligibility')->nullable();
            $table->json('application_requirements')->nullable();
            $table->json('selection_criteria')->nullable();
            $table->json('contact_info')->nullable();
            $table->foreignId('sponsor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_hot')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};
