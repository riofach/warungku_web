<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Category;
use App\Models\HousingBlock;
use App\Models\Item;
use App\Models\Setting;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Sync Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@warungluthfan.com'],
            [
                'name' => 'Admin Warung',
                'password' => bcrypt('batamganteng1'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Settings (Epic 8)
        $settings = [
            'operating_hours' => '08:00-21:00',
            'whatsapp_number' => '6281234567890',
            'delivery_enabled' => 'true',
            'warung_name' => 'Warung Luthfan',
            'address' => 'Perumahan Pesona Indah Blok A1 No. 5',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        // 3. Housing Blocks (Epic 3 - FR12)
        $blocks = ['Blok A', 'Blok B', 'Blok C', 'Blok D', 'Blok E'];
        foreach ($blocks as $blockName) {
            HousingBlock::firstOrCreate([
                'name' => $blockName
            ]);
        }

        // 4. Categories (Epic 3 - FR10)
        $categories = [
            'Sembako' => ['Beras 5kg', 'Minyak Goreng 2L', 'Gula Pasir 1kg', 'Telur Ayam 1kg'],
            'Minuman' => ['Aqua Galon', 'Teh Pucuk Harum', 'Kopi Kapal Api', 'Susu UHT Ultra'],
            'Jajanan' => ['Chitato', 'Beng-Beng', 'Oreo', 'Roti Tawar'],
            'Kebersihan' => ['Rinso Anti Noda', 'Sunlight Jeruk Nipis', 'Sabun Lifebuoy'],
            'Bumbu Dapur' => ['Kecap Bango', 'Saus ABC', 'Garam Refina', 'Masako Ayam']
        ];

        foreach ($categories as $categoryName => $items) {
            $category = Category::firstOrCreate([
                'name' => $categoryName
            ]);

            // 5. Items (Epic 3 - FR6)
            foreach ($items as $itemName) {
                // Determine price range based on category/item guess
                $basePrice = str_contains($itemName, 'Beras') ? 60000 : 
                             (str_contains($itemName, 'Minyak') ? 35000 : 
                             (str_contains($itemName, 'Telur') ? 28000 : rand(3000, 15000)));
                
                $sellPrice = $basePrice;
                $buyPrice = $basePrice * 0.85; // 15% margin

                Item::firstOrCreate(
                    ['name' => $itemName],
                    [
                        'category_id' => $category->id,
                        'buy_price' => (int) $buyPrice,
                        'sell_price' => (int) $sellPrice,
                        'stock' => rand(0, 50),
                        'stock_threshold' => 5,
                        'is_active' => true,
                        // Placeholder image from ui-avatars (text based) or placeholder service
                        // Using a simple service that generates images with text
                        'image_url' => 'https://placehold.co/400x400/png?text=' . urlencode($itemName),
                    ]
                );
            }
        }

        $this->command->info('Database seeded successfully with Admin, Settings, Master Data, and Dummy Items.');
    }
}
