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
        Schema::create('scholastic_materials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->enum('category', ['Books', 'Stationery', 'Uniforms', 'Technology', 'Sports Equipment', 'Other']);
            $table->decimal('price', 8, 2)->nullable();
            $table->integer('quantity_available')->default(0);
            $table->integer('quantity_reserved')->default(0);
            $table->integer('low_stock_threshold')->default(10);
            $table->enum('condition', ['New', 'Good', 'Fair', 'Poor'])->default('New');
            $table->string('provider_name')->nullable();
            $table->text('provider_contact')->nullable();
            $table->string('image_url')->nullable();
            $table->enum('availability_status', ['Available', 'Out of Stock', 'Discontinued'])->default('Available');
            $table->json('specifications')->nullable(); // Size, color, brand, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scholastic_materials');
    }
};
