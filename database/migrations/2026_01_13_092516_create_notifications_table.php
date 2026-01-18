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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('message');
            $table->enum('type', ['Application Status', 'New Opportunity', 'Payment', 'System', 'Reminder']);
            $table->enum('channel', ['Email', 'SMS', 'In-App', 'Push']);
            $table->enum('status', ['Pending', 'Sent', 'Delivered', 'Failed', 'Read'])->default('Pending');
            $table->json('metadata')->nullable(); // Additional data like application_id, opportunity_id, etc.
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->integer('retry_count')->default(0);
            $table->text('failure_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
