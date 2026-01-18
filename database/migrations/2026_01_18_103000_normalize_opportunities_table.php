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
        // Create normalized opportunity-related tables first
        
        // Detailed eligibility criteria (normalized)
        Schema::create('opportunity_eligibility', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opportunity_id')->constrained()->onDelete('cascade');
            $table->enum('criteria_type', ['Academic', 'Financial', 'Geographic', 'Other']);
            $table->text('criteria_description');
            $table->boolean('is_required')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Application requirements (normalized)
        Schema::create('opportunity_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opportunity_id')->constrained()->onDelete('cascade');
            $table->enum('requirement_type', ['Document', 'Essay', 'Interview', 'Test']);
            $table->text('requirement_description');
            $table->boolean('is_required')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Selection criteria (normalized)
        Schema::create('opportunity_selection_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opportunity_id')->constrained()->onDelete('cascade');
            $table->string('criteria_name');
            $table->text('criteria_description');
            $table->decimal('weight_percentage', 5, 2)->default(0); // Percentage weight in selection
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Now update the opportunities table to use normalized relationships
        Schema::table('opportunities', function (Blueprint $table) {
            // Add foreign key relationships
            $table->foreignId('school_id')->nullable()->after('id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('education_level_id')->nullable()->after('school_id')->constrained('education_levels')->onDelete('restrict');
            $table->foreignId('support_type_id')->nullable()->after('education_level_id')->constrained('support_types')->onDelete('restrict');
            $table->foreignId('category_id')->nullable()->after('support_type_id')->constrained('opportunity_categories')->onDelete('restrict');
            $table->foreignId('created_by')->nullable()->after('sponsor_id')->constrained('users')->onDelete('set null');
            
            // Add new normalized fields
            $table->string('title')->after('category_id');
            $table->text('eligibility_summary')->nullable()->after('title'); // Brief summary, details in opportunity_eligibility
            $table->integer('duration_months')->nullable()->after('duration'); // Normalize duration to months
        });

        // Update applications table to support student profiles
        Schema::table('applications', function (Blueprint $table) {
            $table->foreignId('guardian_id')->nullable()->after('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('student_profile_id')->nullable()->after('guardian_id')->constrained('student_profiles')->onDelete('cascade');
            $table->foreignId('reviewed_by')->nullable()->after('status')->constrained('users')->onDelete('set null');
            $table->text('review_notes')->nullable()->after('reviewed_by');
            $table->decimal('score', 5, 2)->nullable()->after('review_notes');
            $table->timestamp('reviewed_at')->nullable()->after('score');
        });

        // Application data (normalized) - for flexible form data storage
        Schema::create('application_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->string('field_name');
            $table->json('field_value'); // Flexible JSON storage
            $table->enum('field_type', ['personal', 'academic', 'financial', 'essay', 'other'])->default('other');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_data');
        
        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign(['guardian_id']);
            $table->dropForeign(['student_profile_id']);
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn(['guardian_id', 'student_profile_id', 'reviewed_by', 'review_notes', 'score', 'reviewed_at']);
        });
        
        Schema::table('opportunities', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropForeign(['education_level_id']);
            $table->dropForeign(['support_type_id']);
            $table->dropForeign(['category_id']);
            $table->dropForeign(['created_by']);
            $table->dropColumn(['school_id', 'education_level_id', 'support_type_id', 'category_id', 'created_by', 'title', 'eligibility_summary', 'duration_months']);
        });
        
        Schema::dropIfExists('opportunity_selection_criteria');
        Schema::dropIfExists('opportunity_requirements');
        Schema::dropIfExists('opportunity_eligibility');
    }
};