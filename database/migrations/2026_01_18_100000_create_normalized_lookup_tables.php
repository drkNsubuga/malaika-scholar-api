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
        // Countries
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 2)->unique(); // ISO 3166-1 alpha-2
            $table->string('currency_code', 3);
            $table->timestamps();
        });

        // States/Provinces
        Schema::create('states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('code', 10)->nullable();
            $table->timestamps();
        });

        // Cities
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('state_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->timestamps();
        });

        // Education levels
        Schema::create('education_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Primary, Secondary, Tertiary
            $table->text('description')->nullable();
            $table->string('grade_range')->nullable(); // e.g., "1-8", "9-12", "13-16"
            $table->timestamps();
        });

        // Support types
        Schema::create('support_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Full Scholarship, Partial Bursary, Material Support, Fee Support
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Opportunity categories
        Schema::create('opportunity_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Academic Excellence, Health Support, Special Needs, General Support
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color', 7)->nullable(); // Hex color code
            $table->timestamps();
        });

        // Schools (normalized)
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('city_id')->constrained()->onDelete('restrict');
            $table->text('address')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('website')->nullable();
            $table->enum('school_type', ['Public', 'Private', 'International'])->default('Public');
            $table->json('accreditation_info')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // School admin account
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
        });

        // User roles (normalized)
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Student/Parent, School, Sponsor, Donor, Admin
            $table->text('description')->nullable();
            $table->json('permissions')->nullable();
            $table->timestamps();
        });

        // User role assignments (many-to-many)
        Schema::create('user_role_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_role_id')->constrained()->onDelete('cascade');
            $table->timestamp('assigned_at')->useCurrent();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_role_assignments');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('schools');
        Schema::dropIfExists('opportunity_categories');
        Schema::dropIfExists('support_types');
        Schema::dropIfExists('education_levels');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('states');
        Schema::dropIfExists('countries');
    }
};