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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->morphs('documentable'); // Polymorphic relationship (application, opportunity, etc.)
            $table->string('original_name');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type');
            $table->bigInteger('file_size'); // in bytes
            $table->enum('document_type', ['ID Document', 'Academic Transcript', 'Birth Certificate', 'Passport Photo', 'Medical Report', 'Financial Statement', 'Other']);
            $table->enum('status', ['Pending', 'Verified', 'Rejected'])->default('Pending');
            $table->boolean('is_public')->default(false);
            $table->string('verification_notes')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
