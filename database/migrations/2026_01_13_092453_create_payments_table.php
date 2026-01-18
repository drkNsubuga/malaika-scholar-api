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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('transaction_id')->unique();
            $table->enum('payment_gateway', ['Stripe', 'PayPal', 'Mobile Money']);
            $table->string('gateway_transaction_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('status', ['Pending', 'Completed', 'Failed', 'Refunded', 'Cancelled'])->default('Pending');
            $table->enum('payment_type', ['Scholarship Support', 'Material Donation', 'General Donation', 'Application Fee']);
            $table->foreignId('recipient_id')->nullable()->constrained('users')->onDelete('set null');
            $table->morphs('payable'); // For polymorphic relationship (application, student, etc.)
            $table->json('gateway_response')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->string('receipt_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
