<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NormalizedLookupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Countries
        $countries = [
            ['name' => 'Kenya', 'code' => 'KE', 'currency_code' => 'KES'],
            ['name' => 'Uganda', 'code' => 'UG', 'currency_code' => 'UGX'],
            ['name' => 'Tanzania', 'code' => 'TZ', 'currency_code' => 'TZS'],
            ['name' => 'Rwanda', 'code' => 'RW', 'currency_code' => 'RWF'],
            ['name' => 'United States', 'code' => 'US', 'currency_code' => 'USD'],
        ];
        DB::table('countries')->insert($countries);

        // States (Kenya focus)
        $states = [
            ['country_id' => 1, 'name' => 'Nairobi', 'code' => 'NRB'],
            ['country_id' => 1, 'name' => 'Central', 'code' => 'CEN'],
            ['country_id' => 1, 'name' => 'Coast', 'code' => 'CST'],
            ['country_id' => 1, 'name' => 'Eastern', 'code' => 'EST'],
            ['country_id' => 1, 'name' => 'North Eastern', 'code' => 'NET'],
            ['country_id' => 1, 'name' => 'Nyanza', 'code' => 'NYZ'],
            ['country_id' => 1, 'name' => 'Rift Valley', 'code' => 'RVY'],
            ['country_id' => 1, 'name' => 'Western', 'code' => 'WST'],
        ];
        DB::table('states')->insert($states);

        // Cities (Major Kenyan cities)
        $cities = [
            ['state_id' => 1, 'name' => 'Nairobi'],
            ['state_id' => 2, 'name' => 'Nyeri'],
            ['state_id' => 2, 'name' => 'Thika'],
            ['state_id' => 3, 'name' => 'Mombasa'],
            ['state_id' => 3, 'name' => 'Malindi'],
            ['state_id' => 4, 'name' => 'Machakos'],
            ['state_id' => 4, 'name' => 'Kitui'],
            ['state_id' => 6, 'name' => 'Kisumu'],
            ['state_id' => 6, 'name' => 'Homa Bay'],
            ['state_id' => 7, 'name' => 'Nakuru'],
            ['state_id' => 7, 'name' => 'Eldoret'],
            ['state_id' => 8, 'name' => 'Kakamega'],
            ['state_id' => 8, 'name' => 'Bungoma'],
        ];
        DB::table('cities')->insert($cities);

        // Education levels
        $educationLevels = [
            ['name' => 'Primary', 'description' => 'Primary school education', 'grade_range' => '1-8'],
            ['name' => 'Secondary', 'description' => 'Secondary school education', 'grade_range' => '9-12'],
            ['name' => 'Tertiary', 'description' => 'University and college education', 'grade_range' => '13-16'],
        ];
        DB::table('education_levels')->insert($educationLevels);

        // Support types
        $supportTypes = [
            ['name' => 'Full Scholarship', 'description' => 'Complete coverage of all educational expenses'],
            ['name' => 'Partial Bursary', 'description' => 'Partial coverage of educational expenses'],
            ['name' => 'Material Support', 'description' => 'Provision of educational materials and supplies'],
            ['name' => 'Fee Support', 'description' => 'Coverage of specific fees (tuition, exam, etc.)'],
        ];
        DB::table('support_types')->insert($supportTypes);

        // Opportunity categories
        $categories = [
            ['name' => 'Academic Excellence', 'description' => 'For students with outstanding academic performance', 'icon' => 'academic-cap', 'color' => '#3B82F6'],
            ['name' => 'Health Support', 'description' => 'For students with health-related challenges', 'icon' => 'heart', 'color' => '#EF4444'],
            ['name' => 'Special Needs', 'description' => 'For students with special educational needs', 'icon' => 'hand-heart', 'color' => '#8B5CF6'],
            ['name' => 'General Support', 'description' => 'General financial assistance for education', 'icon' => 'currency-dollar', 'color' => '#10B981'],
        ];
        DB::table('opportunity_categories')->insert($categories);

        // User roles
        $userRoles = [
            ['name' => 'Student/Parent', 'description' => 'Students and their parents/guardians', 'permissions' => json_encode(['apply', 'view_opportunities', 'manage_profile'])],
            ['name' => 'School', 'description' => 'Educational institutions', 'permissions' => json_encode(['create_opportunities', 'review_applications', 'manage_school_profile'])],
            ['name' => 'Sponsor', 'description' => 'Individual or corporate sponsors', 'permissions' => json_encode(['fund_opportunities', 'view_impact', 'manage_sponsorships'])],
            ['name' => 'Donor', 'description' => 'General donors and contributors', 'permissions' => json_encode(['donate', 'view_impact', 'purchase_materials'])],
            ['name' => 'Admin', 'description' => 'System administrators', 'permissions' => json_encode(['manage_all', 'system_settings', 'user_management'])],
        ];
        DB::table('user_roles')->insert($userRoles);

        // Notification types
        $notificationTypes = [
            ['name' => 'Application Status', 'description' => 'Updates about application status changes'],
            ['name' => 'New Opportunity', 'description' => 'Notifications about new scholarship opportunities'],
            ['name' => 'Payment', 'description' => 'Payment confirmations and receipts'],
            ['name' => 'System', 'description' => 'System announcements and updates'],
            ['name' => 'Reminder', 'description' => 'Deadline and task reminders'],
        ];
        DB::table('notification_types')->insert($notificationTypes);

        // Notification channels
        $notificationChannels = [
            ['name' => 'Email', 'is_active' => true, 'configuration' => json_encode(['smtp_enabled' => true])],
            ['name' => 'SMS', 'is_active' => true, 'configuration' => json_encode(['provider' => 'twilio'])],
            ['name' => 'In-App', 'is_active' => true, 'configuration' => json_encode(['real_time' => true])],
            ['name' => 'Push', 'is_active' => false, 'configuration' => json_encode(['fcm_enabled' => false])],
        ];
        DB::table('notification_channels')->insert($notificationChannels);

        // Payment gateways
        $paymentGateways = [
            ['name' => 'Stripe', 'is_active' => true, 'configuration' => json_encode(['supports_cards' => true, 'supports_bank' => true])],
            ['name' => 'PayPal', 'is_active' => true, 'configuration' => json_encode(['supports_paypal_account' => true])],
            ['name' => 'M-Pesa', 'is_active' => true, 'configuration' => json_encode(['provider' => 'safaricom', 'country' => 'KE'])],
            ['name' => 'Bank Transfer', 'is_active' => true, 'configuration' => json_encode(['manual_verification' => true])],
        ];
        DB::table('payment_gateways')->insert($paymentGateways);

        // Payment types
        $paymentTypes = [
            ['name' => 'Scholarship Support', 'description' => 'Direct financial support for student scholarships'],
            ['name' => 'Material Donation', 'description' => 'Purchase of educational materials for donation'],
            ['name' => 'General Donation', 'description' => 'General fund contributions'],
            ['name' => 'Application Fee', 'description' => 'Fees for application processing'],
            ['name' => 'Promotion Fee', 'description' => 'Payment for opportunity promotion and advertising'],
        ];
        DB::table('payment_types')->insert($paymentTypes);

        // Document types
        $documentTypes = [
            ['name' => 'ID Document', 'description' => 'National ID or passport', 'allowed_formats' => json_encode(['pdf', 'jpg', 'png']), 'is_required_for_applications' => true],
            ['name' => 'Academic Transcript', 'description' => 'Official academic records', 'allowed_formats' => json_encode(['pdf']), 'is_required_for_applications' => true],
            ['name' => 'Birth Certificate', 'description' => 'Official birth certificate', 'allowed_formats' => json_encode(['pdf', 'jpg', 'png']), 'is_required_for_applications' => true],
            ['name' => 'Financial Statement', 'description' => 'Family financial information', 'allowed_formats' => json_encode(['pdf', 'docx']), 'is_required_for_applications' => false],
            ['name' => 'Recommendation Letter', 'description' => 'Letter of recommendation', 'allowed_formats' => json_encode(['pdf', 'docx']), 'is_required_for_applications' => false],
            ['name' => 'Medical Certificate', 'description' => 'Medical documentation if applicable', 'allowed_formats' => json_encode(['pdf', 'jpg', 'png']), 'is_required_for_applications' => false],
        ];
        DB::table('document_types')->insert($documentTypes);

        // Material categories
        $materialCategories = [
            ['name' => 'Books', 'description' => 'Textbooks and educational books', 'icon' => 'book-open'],
            ['name' => 'Stationery', 'description' => 'Pens, pencils, notebooks, etc.', 'icon' => 'pencil'],
            ['name' => 'Uniforms', 'description' => 'School uniforms and clothing', 'icon' => 'user'],
            ['name' => 'Technology', 'description' => 'Computers, tablets, calculators', 'icon' => 'computer-desktop'],
            ['name' => 'Sports Equipment', 'description' => 'Sports and physical education equipment', 'icon' => 'trophy'],
            ['name' => 'Other', 'description' => 'Other educational materials', 'icon' => 'cube'],
        ];
        DB::table('material_categories')->insert($materialCategories);

        // Material conditions
        $materialConditions = [
            ['name' => 'New', 'description' => 'Brand new, unused items'],
            ['name' => 'Good', 'description' => 'Used but in good condition'],
            ['name' => 'Fair', 'description' => 'Used with some wear but functional'],
            ['name' => 'Poor', 'description' => 'Heavily used but still usable'],
        ];
        DB::table('material_conditions')->insert($materialConditions);

        // Promotional packages
        $promotionalPackages = [
            [
                'name' => 'Basic Featured',
                'description' => 'Highlight your opportunity in search results',
                'package_type' => 'opportunity_boost',
                'duration_days' => 30,
                'price' => 49.00,
                'currency_code' => 'USD',
                'features' => json_encode(['Featured badge', 'Higher search ranking', 'Basic analytics']),
                'max_opportunities' => 3,
                'placement_priority' => 2,
                'includes_analytics' => true,
                'includes_logo_display' => false,
                'includes_homepage_feature' => false,
            ],
            [
                'name' => 'Premium Spotlight',
                'description' => 'Premium placement with enhanced visibility',
                'package_type' => 'search_priority',
                'duration_days' => 30,
                'price' => 149.00,
                'currency_code' => 'USD',
                'features' => json_encode(['Top search results', 'Premium badge', 'Logo display', 'Advanced analytics']),
                'max_opportunities' => 5,
                'placement_priority' => 5,
                'includes_analytics' => true,
                'includes_logo_display' => true,
                'includes_homepage_feature' => true,
            ],
            [
                'name' => 'Hero Banner',
                'description' => 'Homepage hero section with maximum visibility',
                'package_type' => 'homepage_feature',
                'duration_days' => 30,
                'price' => 299.00,
                'currency_code' => 'USD',
                'features' => json_encode(['Homepage hero banner', 'Custom branding', 'Click tracking', 'Conversion analytics']),
                'max_opportunities' => 1,
                'placement_priority' => 10,
                'includes_analytics' => true,
                'includes_logo_display' => true,
                'includes_homepage_feature' => true,
            ],
        ];
        DB::table('promotional_packages')->insert($promotionalPackages);

        // Homepage promotional slots
        $promotionalSlots = [
            ['slot_name' => 'Hero Banner', 'slot_type' => 'Single Opportunity', 'max_items' => 1, 'price_per_day' => 15.00, 'dimensions' => json_encode(['width' => 1200, 'height' => 400])],
            ['slot_name' => 'Featured Grid', 'slot_type' => 'Multiple Opportunities', 'max_items' => 6, 'price_per_day' => 5.00, 'dimensions' => json_encode(['width' => 300, 'height' => 200])],
            ['slot_name' => 'Sidebar', 'slot_type' => 'Sponsor Banner', 'max_items' => 3, 'price_per_day' => 8.00, 'dimensions' => json_encode(['width' => 250, 'height' => 300])],
            ['slot_name' => 'Footer', 'slot_type' => 'Sponsor Banner', 'max_items' => 5, 'price_per_day' => 3.00, 'dimensions' => json_encode(['width' => 200, 'height' => 100])],
        ];
        DB::table('homepage_promotional_slots')->insert($promotionalSlots);
    }
}