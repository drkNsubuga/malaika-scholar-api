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
        // Promotional packages (what schools/sponsors can buy)
        Schema::create('promotional_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Featured Listing, Premium Spotlight, Homepage Banner, etc.
            $table->text('description');
            $table->enum('package_type', ['opportunity_boost', 'homepage_feature', 'search_priority', 'banner_ad']);
            $table->integer('duration_days');
            $table->decimal('price', 10, 2);
            $table->string('currency_code', 3)->default('USD');
            $table->json('features'); // What's included in the package
            $table->integer('max_opportunities')->nullable(); // How many opportunities can be promoted
            $table->integer('placement_priority')->default(1); // Higher number = better placement
            $table->boolean('includes_analytics')->default(false);
            $table->boolean('includes_logo_display')->default(false);
            $table->boolean('includes_homepage_feature')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Promotional purchases (schools/sponsors buying promotion)
        Schema::create('promotional_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchaser_id')->constrained('users')->onDelete('cascade'); // School or sponsor
            $table->foreignId('promotional_package_id')->constrained()->onDelete('restrict');
            $table->enum('status', ['Active', 'Expired', 'Cancelled', 'Pending Payment'])->default('Pending Payment');
            $table->timestamp('purchased_at')->useCurrent();
            $table->timestamp('starts_at');
            $table->timestamp('expires_at');
            $table->foreignId('payment_id')->nullable()->constrained('payments')->onDelete('set null');
            $table->decimal('total_amount', 10, 2);
            $table->string('currency_code', 3)->default('USD');
            $table->timestamps();
        });

        // Promoted opportunities (which opportunities are being promoted)
        Schema::create('promoted_opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotional_purchase_id')->constrained()->onDelete('cascade');
            $table->foreignId('opportunity_id')->constrained()->onDelete('cascade');
            $table->enum('promotion_type', ['Featured', 'Spotlight', 'Banner', 'Top Search']);
            $table->integer('placement_priority')->default(1);
            $table->timestamp('starts_at');
            $table->timestamp('expires_at');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Homepage promotional slots
        Schema::create('homepage_promotional_slots', function (Blueprint $table) {
            $table->id();
            $table->string('slot_name'); // Hero Banner, Featured Grid, Sidebar, Footer
            $table->enum('slot_type', ['Single Opportunity', 'Multiple Opportunities', 'Sponsor Banner']);
            $table->integer('max_items')->default(1);
            $table->decimal('price_per_day', 8, 2);
            $table->json('dimensions')->nullable(); // Width, height for banners
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Promotional slot bookings
        Schema::create('promotional_slot_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotional_purchase_id')->constrained()->onDelete('cascade');
            $table->foreignId('homepage_promotional_slot_id')->constrained()->onDelete('cascade');
            $table->foreignId('promoted_opportunity_id')->nullable()->constrained()->onDelete('cascade');
            $table->date('booking_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Promotional analytics (track performance of paid promotions)
        Schema::create('promotional_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promoted_opportunity_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->integer('impressions_count')->default(0);
            $table->integer('clicks_count')->default(0);
            $table->integer('applications_count')->default(0);
            $table->decimal('conversion_rate', 5, 2)->default(0); // Percentage
            $table->timestamps();
            
            $table->unique(['promoted_opportunity_id', 'date']);
        });

        // Click tracking for promoted content
        Schema::create('promotional_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promoted_opportunity_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Who clicked
            $table->enum('click_source', ['Homepage Banner', 'Search Results', 'Featured Section', 'Sidebar']);
            $table->timestamp('clicked_at')->useCurrent();
            $table->string('user_ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        // Application attribution (track which applications came from promotions)
        Schema::create('promotional_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promoted_opportunity_id')->constrained()->onDelete('cascade');
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->decimal('conversion_value', 10, 2)->nullable(); // Estimated value of the conversion
            $table->timestamp('attributed_at')->useCurrent();
            $table->timestamps();
        });

        // Add promotional fields to existing opportunities table
        Schema::table('opportunities', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->after('is_hot');
            $table->boolean('is_premium')->default(false)->after('is_featured');
            $table->boolean('is_sponsored')->default(false)->after('is_premium');
            $table->integer('promotion_priority')->default(0)->after('is_sponsored');
            $table->timestamp('promotion_expires_at')->nullable()->after('promotion_priority');
            $table->integer('total_impressions')->default(0)->after('promotion_expires_at');
            $table->integer('total_clicks')->default(0)->after('total_impressions');
            $table->integer('total_applications_from_promotion')->default(0)->after('total_clicks');
            $table->string('featured_image_url')->nullable()->after('total_applications_from_promotion');
            $table->text('promotional_text')->nullable()->after('featured_image_url');
            $table->string('sponsor_logo_url')->nullable()->after('promotional_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->dropColumn([
                'is_featured', 'is_premium', 'is_sponsored', 'promotion_priority',
                'promotion_expires_at', 'total_impressions', 'total_clicks',
                'total_applications_from_promotion', 'featured_image_url',
                'promotional_text', 'sponsor_logo_url'
            ]);
        });

        Schema::dropIfExists('promotional_conversions');
        Schema::dropIfExists('promotional_clicks');
        Schema::dropIfExists('promotional_analytics');
        Schema::dropIfExists('promotional_slot_bookings');
        Schema::dropIfExists('homepage_promotional_slots');
        Schema::dropIfExists('promoted_opportunities');
        Schema::dropIfExists('promotional_purchases');
        Schema::dropIfExists('promotional_packages');
    }
};