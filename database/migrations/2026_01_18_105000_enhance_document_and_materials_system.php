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
        // Document types
        Schema::create('document_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // ID Document, Academic Transcript, Birth Certificate, etc.
            $table->text('description')->nullable();
            $table->json('allowed_formats'); // ['pdf', 'jpg', 'png', 'docx']
            $table->integer('max_file_size')->default(5242880); // 5MB in bytes
            $table->boolean('is_required_for_applications')->default(false);
            $table->timestamps();
        });

        // Update documents table to use normalized structure
        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('document_type_id')->nullable()->after('user_id')->constrained()->onDelete('set null');
            // status, verification_notes, verified_at, verified_by already exist
        });

        // Material categories
        Schema::create('material_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Books, Stationery, Uniforms, Technology, Sports Equipment, Other
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->timestamps();
        });

        // Material conditions
        Schema::create('material_conditions', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // New, Good, Fair, Poor
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Update scholastic_materials table to use normalized structure
        Schema::table('scholastic_materials', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('description')->constrained('material_categories')->onDelete('set null');
            $table->foreignId('condition_id')->nullable()->after('category_id')->constrained('material_conditions')->onDelete('set null');
            // quantity_reserved, low_stock_threshold, image_url, specifications already exist
        });

        // Material transactions
        Schema::create('material_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained('scholastic_materials')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('transaction_type', ['Purchase', 'Donation', 'Reservation', 'Distribution']);
            $table->integer('quantity');
            $table->decimal('amount', 10, 2)->nullable(); // Cost for purchases
            $table->enum('status', ['Pending', 'Completed', 'Cancelled'])->default('Pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // AI assistant messages (Requirement 14)
        Schema::create('ai_assistant_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('session_id'); // Group related messages
            $table->enum('message_type', ['user', 'assistant']);
            $table->text('content');
            $table->json('context_data')->nullable(); // Additional context for the AI
            $table->integer('response_time_ms')->nullable(); // How long AI took to respond
            $table->tinyInteger('feedback_rating')->nullable(); // 1-5 rating
            $table->text('feedback_comment')->nullable();
            $table->string('ai_model_version')->nullable();
            $table->timestamps();
        });

        // AI conversation sessions
        Schema::create('ai_conversation_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('session_id')->unique();
            $table->string('topic')->nullable(); // What the conversation was about
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->integer('message_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_conversation_sessions');
        Schema::dropIfExists('ai_assistant_messages');
        Schema::dropIfExists('material_transactions');
        
        Schema::table('scholastic_materials', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['condition_id']);
            $table->dropColumn(['category_id', 'condition_id']);
        });
        
        Schema::dropIfExists('material_conditions');
        Schema::dropIfExists('material_categories');
        
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['document_type_id']);
            $table->dropColumn(['document_type_id']);
        });
        
        Schema::dropIfExists('document_types');
    }
};