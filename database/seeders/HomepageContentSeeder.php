<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HomepageContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $homepageContent = [
            [
                'section_key' => 'hero',
                'title' => 'Trending Deal',
                'subtitle' => 'Premium Education Opportunities',
                'content' => 'Auto-rotating showcase of hot scholarship opportunities with trending deals and premium education offers.',
                'primary_button_text' => 'Apply Now',
                'primary_button_url' => '/apply',
                'secondary_button_text' => 'Learn More',
                'secondary_button_url' => '/scholarship',
                'spotlight_data' => json_encode([
                    'auto_rotate' => true,
                    'rotation_interval' => 5000,
                    'show_indicators' => true,
                    'show_trending_badge' => true
                ]),
                'is_active' => true,
                'display_order' => 1,
            ],
            [
                'section_key' => 'student_spotlight',
                'title' => 'Support a Student',
                'subtitle' => 'Student Excellence Spotlight',
                'content' => 'Discover high-achieving students sorted by academic performance who need your support to continue their educational journey.',
                'primary_button_text' => 'Support Student',
                'primary_button_url' => '/wallet',
                'secondary_button_text' => 'View More',
                'secondary_button_url' => '/students',
                'additional_data' => json_encode([
                    'sort_by' => 'academic_performance',
                    'show_student_of_month' => true,
                    'auto_rotate' => true,
                    'rotation_interval' => 4000,
                    'display_fields' => ['name', 'school', 'grade', 'dream', 'fees_needed']
                ]),
                'is_active' => true,
                'display_order' => 2,
            ],
            [
                'section_key' => 'opportunities',
                'title' => 'Premium Education Opportunities',
                'subtitle' => 'Discover life-changing scholarships',
                'content' => 'Browse through carefully curated scholarship opportunities from top educational institutions.',
                'primary_button_text' => 'Apply',
                'primary_button_url' => '/apply',
                'secondary_button_text' => 'View',
                'secondary_button_url' => '/scholarship',
                'additional_data' => json_encode([
                    'filter_hot_only' => true,
                    'max_items' => 4,
                    'show_premium_badge' => true,
                    'grid_layout' => 'responsive'
                ]),
                'is_active' => true,
                'display_order' => 3,
            ],
            [
                'section_key' => 'scholastic_shop',
                'title' => 'Scholastic Shop',
                'subtitle' => 'Grab Your Scholastic Materials Today',
                'content' => 'Essential educational materials available for purchase or donation to support students in need.',
                'primary_button_text' => 'Buy item',
                'primary_button_url' => '/buy',
                'secondary_button_text' => 'Donate mine',
                'secondary_button_url' => '/donate',
                'additional_data' => json_encode([
                    'show_stock_count' => true,
                    'enable_buy_donate' => true,
                    'responsive_layout' => true,
                    'hover_effects' => true
                ]),
                'is_active' => true,
                'display_order' => 4,
            ],
            [
                'section_key' => 'sponsors',
                'title' => 'Sponsors',
                'subtitle' => 'Families, Companies & Individuals Investing in Your Future',
                'content' => 'Meet the generous sponsors who are making education accessible through their support and contributions.',
                'primary_button_text' => 'View Sponsor',
                'primary_button_url' => '/sponsor',
                'secondary_button_text' => 'View More',
                'secondary_button_url' => '/sponsors',
                'additional_data' => json_encode([
                    'show_sponsor_types' => true,
                    'show_active_offers' => true,
                    'grid_layout' => 'responsive',
                    'max_items' => 4
                ]),
                'is_active' => true,
                'display_order' => 5,
            ],
            [
                'section_key' => 'partners',
                'title' => 'Trusted Partners & Certifications',
                'subtitle' => 'Verified and accredited organizations',
                'content' => 'Verified and accredited by leading educational and humanitarian organizations',
                'additional_data' => json_encode([
                    'auto_rotate' => true,
                    'rotation_interval' => 2000,
                    'show_indicators' => true,
                    'responsive_layout' => true,
                    'grayscale_effect' => true
                ]),
                'is_active' => true,
                'display_order' => 6,
            ]
        ];

        foreach ($homepageContent as $content) {
            \DB::table('homepage_content')->updateOrInsert(
                ['section_key' => $content['section_key']],
                array_merge($content, [
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
        }

        // First, create a default admin user if it doesn't exist
        $adminUser = \DB::table('users')->where('email', 'admin@malaika.com')->first();
        if (!$adminUser) {
            $adminUserId = \DB::table('users')->insertGetId([
                'name' => 'Admin User',
                'email' => 'admin@malaika.com',
                'password' => bcrypt('password'),
                'role' => 'Admin',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } else {
            $adminUserId = $adminUser->id;
        }

        // Create sample hero spotlights based on frontend data (without foreign key dependencies)
        $heroSpotlights = [
            [
                'spotlight_type' => 'School',
                'title' => "St. Mary's Secondary School",
                'description' => 'Academic Excellence — Partial Bursary (30–50%)',
                'featured_image_url' => 'https://images.unsplash.com/photo-1546410531-bb4caa6b424d?auto=format&fit=crop&q=80&w=400',
                'spotlightable_type' => 'App\\Models\\User',
                'spotlightable_id' => $adminUserId,
                'statistics' => json_encode([
                    'coverage' => '30-50%',
                    'deadline' => 'Dec 15, 2025',
                    'available_slots' => 25
                ]),
                'call_to_action_text' => 'Apply Now',
                'call_to_action_url' => '/apply/1',
                'is_active' => true,
                'display_order' => 1,
                'created_by' => $adminUserId
            ],
            [
                'spotlight_type' => 'School',
                'title' => 'Metropolitan International University',
                'description' => 'Health Support — Full Tuition Support',
                'featured_image_url' => 'https://images.unsplash.com/photo-1606761568499-6d2451b23c66?auto=format&fit=crop&q=80&w=400',
                'spotlightable_type' => 'App\\Models\\User',
                'spotlightable_id' => $adminUserId,
                'statistics' => json_encode([
                    'coverage' => '100%',
                    'deadline' => 'Jan 10, 2026',
                    'available_slots' => 15
                ]),
                'call_to_action_text' => 'Apply Now',
                'call_to_action_url' => '/apply/2',
                'is_active' => true,
                'display_order' => 2,
                'created_by' => $adminUserId
            ],
            [
                'spotlight_type' => 'School',
                'title' => 'Victoria Vocational Institute',
                'description' => 'Vocational Skills — Material & Tuition Support',
                'featured_image_url' => 'https://images.unsplash.com/photo-1581092921461-eab62e97a780?auto=format&fit=crop&q=80&w=400',
                'spotlightable_type' => 'App\\Models\\User',
                'spotlightable_id' => $adminUserId,
                'statistics' => json_encode([
                    'coverage' => '100%',
                    'deadline' => 'Feb 05, 2026',
                    'available_slots' => 40
                ]),
                'call_to_action_text' => 'Apply Now',
                'call_to_action_url' => '/apply/3',
                'is_active' => true,
                'display_order' => 3,
                'created_by' => $adminUserId
            ],
            [
                'spotlight_type' => 'School',
                'title' => 'Inclusive Primary School',
                'description' => 'Inclusive Education — Specialized Care & Tuition',
                'featured_image_url' => 'https://images.unsplash.com/photo-1516627145497-ae6968895b74?auto=format&fit=crop&q=80&w=400',
                'spotlightable_type' => 'App\\Models\\User',
                'spotlightable_id' => $adminUserId,
                'statistics' => json_encode([
                    'coverage' => '100%',
                    'deadline' => 'Nov 30, 2025',
                    'special_needs' => true
                ]),
                'call_to_action_text' => 'Apply Now',
                'call_to_action_url' => '/apply/4',
                'is_active' => true,
                'display_order' => 4,
                'created_by' => $adminUserId
            ],
            [
                'spotlight_type' => 'School',
                'title' => 'Health-First Academy',
                'description' => 'Health Support — Medical-Inclusive Scholarship',
                'featured_image_url' => 'https://images.unsplash.com/photo-1518152006812-edab29b069ac?auto=format&fit=crop&q=80&w=400',
                'spotlightable_type' => 'App\\Models\\User',
                'spotlightable_id' => $adminUserId,
                'statistics' => json_encode([
                    'coverage' => '100%',
                    'deadline' => 'Dec 20, 2025',
                    'medical_support' => true
                ]),
                'call_to_action_text' => 'Apply Now',
                'call_to_action_url' => '/apply/5',
                'is_active' => true,
                'display_order' => 5,
                'created_by' => $adminUserId
            ]
        ];

        foreach ($heroSpotlights as $spotlight) {
            \DB::table('hero_spotlights')->updateOrInsert(
                ['title' => $spotlight['title']],
                array_merge($spotlight, [
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
        }
    }
}
