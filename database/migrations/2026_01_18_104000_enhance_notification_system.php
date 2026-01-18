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
        // Notification types
        Schema::create('notification_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Application Status, New Opportunity, Payment, System, Reminder
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Notification channels
        Schema::create('notification_channels', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Email, SMS, In-App, Push
            $table->boolean('is_active')->default(true);
            $table->json('configuration')->nullable(); // Channel-specific config
            $table->timestamps();
        });

        // Notification templates
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('notification_channel_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('subject_template')->nullable();
            $table->text('body_template');
            $table->json('variables')->nullable(); // Available template variables
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Update notifications table to use normalized structure
        Schema::table('notifications', function (Blueprint $table) {
            $table->foreignId('notification_type_id')->nullable()->after('user_id')->constrained()->onDelete('set null');
            $table->foreignId('notification_channel_id')->nullable()->after('notification_type_id')->constrained()->onDelete('set null');
            $table->foreignId('template_id')->nullable()->after('notification_channel_id')->constrained('notification_templates')->onDelete('set null');
            // metadata, delivered_at, retry_count, failure_reason already exist in the original table
        });

        // Payment gateways (normalize payment system)
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Stripe, PayPal, Mobile Money
            $table->boolean('is_active')->default(true);
            $table->json('configuration')->nullable(); // Gateway-specific settings
            $table->timestamps();
        });

        // Payment types
        Schema::create('payment_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Scholarship Support, Material Donation, General Donation, Application Fee
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Update payments table to use normalized structure
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('payment_gateway_id')->nullable()->after('user_id')->constrained()->onDelete('set null');
            $table->foreignId('payment_type_id')->nullable()->after('payment_gateway_id')->constrained()->onDelete('set null');
            // gateway_transaction_id, currency_code (as currency), recipient_id, receipt_url already exist
        });

        // Sponsorship relationships
        Schema::create('sponsorship_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sponsor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('student_profile_id')->constrained()->onDelete('cascade');
            $table->enum('relationship_type', ['Direct Support', 'General Fund', 'Material Support']);
            $table->decimal('amount_committed', 10, 2)->default(0);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->integer('duration_months')->nullable();
            $table->enum('status', ['Active', 'Completed', 'Cancelled'])->default('Active');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sponsorship_relationships');
        
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['payment_gateway_id']);
            $table->dropForeign(['payment_type_id']);
            $table->dropColumn(['payment_gateway_id', 'payment_type_id']);
        });
        
        Schema::dropIfExists('payment_types');
        Schema::dropIfExists('payment_gateways');
        
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['notification_type_id']);
            $table->dropForeign(['notification_channel_id']);
            $table->dropForeign(['template_id']);
            $table->dropColumn(['notification_type_id', 'notification_channel_id', 'template_id']);
        });
        
        Schema::dropIfExists('notification_templates');
        Schema::dropIfExists('notification_channels');
        Schema::dropIfExists('notification_types');
    }
};