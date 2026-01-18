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
        // Student profiles (for family account management)
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guardian_id')->constrained('users')->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth');
            $table->string('grade_level')->nullable();
            $table->string('school_name')->nullable();
            $table->string('student_id_number')->nullable();
            $table->json('academic_performance')->nullable(); // Grades, GPA, etc.
            $table->json('achievements')->nullable(); // Awards, certificates, etc.
            $table->json('support_needs')->nullable(); // Financial, academic, material needs
            $table->boolean('is_primary_account')->default(false); // For 18+ students who can manage independently
            $table->timestamps();
        });

        // User preferences (normalized)
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('preference_key'); // notification_email, notification_sms, language, etc.
            $table->json('preference_value'); // Flexible JSON storage for any preference type
            $table->timestamps();
            
            $table->unique(['user_id', 'preference_key']);
        });

        // Add emergency access fields to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('backup_guardian_email')->nullable()->after('preferences');
            $table->string('emergency_contact_email')->nullable()->after('backup_guardian_email');
            $table->timestamp('emergency_activated_at')->nullable()->after('emergency_contact_email');
        });

        // Emergency access log
        Schema::create('emergency_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('initiated_by_email');
            $table->enum('status', ['Pending', 'Approved', 'Rejected', 'Expired'])->default('Pending');
            $table->text('reason')->nullable();
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emergency_access_logs');
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['backup_guardian_email', 'emergency_contact_email', 'emergency_activated_at']);
        });
        
        Schema::dropIfExists('user_preferences');
        Schema::dropIfExists('student_profiles');
    }
};