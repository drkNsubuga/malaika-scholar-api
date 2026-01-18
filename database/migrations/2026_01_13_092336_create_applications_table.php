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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('opportunity_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['Draft', 'Pending', 'Under Review', 'Approved', 'Rejected', 'Withdrawn'])->default('Draft');
            $table->json('student_data')->nullable();
            $table->json('guardian_data')->nullable();
            $table->text('additional_info')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
