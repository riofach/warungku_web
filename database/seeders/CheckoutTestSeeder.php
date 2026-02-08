<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\HousingBlock;
use App\Models\Item;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CheckoutTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Housing Blocks
        $blocks = ['Blok A', 'Blok B', 'Blok C', 'Blok D', 'Blok E'];
        foreach ($blocks as $name) {
            HousingBlock::firstOrCreate(['name' => $name]);
        }
        $this->command->info('Housing Blocks created.');

        // 2. Create Categories
        $categories = [
            'Sembako' => 'Makanan Pokok',
            'Minuman' => 'Minuman Segar',
            'Snack' => 'Makanan Ringan',
        ];
        
        $catIds = [];
        foreach ($categories as $name => $desc) {
            $cat = Category::firstOrCreate(['name' => $name]);
            $catIds[$name] = $cat->id;
        }
        $this->command->info('Categories created.');

        // 3. Create Items
        $items = [
            [
                'category_id' => $catIds['Sembako'],
                'name' => 'Beras Premium 5kg',
                'buy_price' => 60000,
                'sell_price' => 75000,
                'stock' => 50,
                'image_url' => 'https://assets.klikindomaret.com/share/20055486/20055486_1.jpg',
            ],
            [
                'category_id' => $catIds['Sembako'],
                'name' => 'Minyak Goreng 2L',
                'buy_price' => 30000,
                'sell_price' => 38000,
                'stock' => 100,
                'image_url' => 'https://assets.klikindomaret.com/share/20036643/20036643_1.jpg',
            ],
            [
                'category_id' => $catIds['Sembako'],
                'name' => 'Telur Ayam 1kg',
                'buy_price' => 24000,
                'sell_price' => 28000,
                'stock' => 30,
                'image_url' => 'https://assets.klikindomaret.com/share/20101675/20101675_1.jpg',
            ],
            [
                'category_id' => $catIds['Minuman'],
                'name' => 'Teh Pucuk Harum 350ml',
                'buy_price' => 2800,
                'sell_price' => 4000,
                'stock' => 100,
                'image_url' => 'https://assets.klikindomaret.com/share/20002824/20002824_1.jpg',
            ],
            [
                'category_id' => $catIds['Minuman'],
                'name' => 'Aqua Botol 600ml',
                'buy_price' => 2500,
                'sell_price' => 3500,
                'stock' => 100,
                'image_url' => 'https://assets.klikindomaret.com/share/10003558/10003558_1.jpg',
            ],
            [
                'category_id' => $catIds['Snack'],
                'name' => 'Chitato Sapi Panggang 68g',
                'buy_price' => 9000,
                'sell_price' => 11500,
                'stock' => 50,
                'image_url' => 'https://assets.klikindomaret.com/share/10000388/10000388_1.jpg',
            ],
        ];

        foreach ($items as $itemData) {
            Item::updateOrCreate(
                ['name' => $itemData['name']],
                $itemData + ['is_active' => true]
            );
        }
        $this->command->info('Items created.');

        // 4. Settings
        Setting::setValue(Setting::KEY_OPERATING_HOURS_OPEN, '08:00');
        Setting::setValue(Setting::KEY_OPERATING_HOURS_CLOSE, '21:00');
        Setting::setValue(Setting::KEY_DELIVERY_ENABLED, 'true');
        Setting::setValue(Setting::KEY_WARUNG_NAME, 'WarungKu Test');
        
        $this->command->info('Settings updated.');
    }
}
