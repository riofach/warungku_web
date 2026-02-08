<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\HousingBlock;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================
        // 1. ADMIN USER
        // ==========================================
        User::firstOrCreate(
            ['email' => 'admin@warungluthfan.com'],
            [
                'name' => 'Admin Warung',
                'password' => bcrypt('batamganteng1'),
                'email_verified_at' => now(),
            ]
        );
        $this->command->info('✅ Admin User seeded');

        // ==========================================
        // 2. SETTINGS (Buka 24 Jam)
        // ==========================================
        $settings = [
            ['key' => Setting::KEY_OPERATING_HOURS_OPEN, 'value' => '00:00'],
            ['key' => Setting::KEY_OPERATING_HOURS_CLOSE, 'value' => '23:59'],
            ['key' => Setting::KEY_WHATSAPP_NUMBER, 'value' => '6281234567890'],
            ['key' => Setting::KEY_DELIVERY_ENABLED, 'value' => 'true'],
            ['key' => Setting::KEY_WARUNG_NAME, 'value' => 'WarungKu Digital Test'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], ['value' => $setting['value']]);
        }
        $this->command->info('✅ Settings seeded');

        // ==========================================
        // 3. MASTER DATA
        // ==========================================
        
        // Housing Blocks
        $blocks = ['Blok A - Depan Taman', 'Blok B - Dekat Masjid', 'Blok C - Jalan Utama'];
        $blockModels = [];
        foreach ($blocks as $name) {
            $blockModels[] = HousingBlock::firstOrCreate(['name' => $name]);
        }

        // Categories
        $categories = [
            'Makanan Berat' => 'makanan',
            'Minuman Dingin' => 'minuman',
            'Sembako' => 'sembako',
            'Snack & Jajanan' => 'snack',
        ];
        $catIds = [];
        foreach ($categories as $name => $slug) {
            $cat = Category::firstOrCreate(['name' => $name]);
            $catIds[$slug] = $cat->id;
        }

        // Items
        $itemsData = [
            ['name' => 'Nasi Goreng Spesial', 'cat' => 'makanan', 'buy' => 12000, 'sell' => 18000, 'img' => 'https://placehold.co/400x300/orange/white?text=Nasi+Goreng'],
            ['name' => 'Es Teh Manis', 'cat' => 'minuman', 'buy' => 1000, 'sell' => 4000, 'img' => 'https://placehold.co/400x300/brown/white?text=Es+Teh'],
            ['name' => 'Beras Premium 5kg', 'cat' => 'sembako', 'buy' => 65000, 'sell' => 72000, 'img' => 'https://placehold.co/400x300/white/black?text=Beras'],
            ['name' => 'Chiki Balls Keju', 'cat' => 'snack', 'buy' => 4000, 'sell' => 5500, 'img' => 'https://placehold.co/400x300/yellow/red?text=Chiki'],
        ];

        $createdItems = [];
        foreach ($itemsData as $d) {
            $item = Item::updateOrCreate(
                ['name' => $d['name']],
                [
                    'category_id' => $catIds[$d['cat']],
                    'buy_price' => $d['buy'],
                    'sell_price' => $d['sell'],
                    'stock' => 100, // Stok Banyak
                    'is_active' => true,
                    'image_url' => $d['img']
                ]
            );
            $createdItems[] = $item;
        }
        $this->command->info('✅ Items seeded');

        // ==========================================
        // 4. DUMMY ORDERS (Web History)
        // ==========================================
        // Cek order dummy agar tidak duplikat
        $dummyCode = 'WRG-' . date('Ymd') . '-DUMMY';
        if (!Order::where('code', $dummyCode)->exists()) {
            $dummyOrder = Order::create([
                'code' => $dummyCode,
                'housing_block_id' => $blockModels[0]->id,
                'customer_name' => 'Customer Dummy',
                'payment_method' => 'tunai',
                'delivery_type' => 'pickup',
                'status' => 'pending',
                'total' => 22000
            ]);
            
            OrderItem::create([
                'order_id' => $dummyOrder->id,
                'item_id' => $createdItems[0]->id, // Nasi Goreng
                'quantity' => 1,
                'price' => 18000,
                'subtotal' => 18000
            ]);
            OrderItem::create([
                'order_id' => $dummyOrder->id,
                'item_id' => $createdItems[1]->id, // Es Teh
                'quantity' => 1,
                'price' => 4000,
                'subtotal' => 4000
            ]);
            $this->command->info('✅ Dummy Web Order seeded');
        }

        // ==========================================
        // 5. DUMMY TRANSACTIONS (POS History)
        // ==========================================
        // Menggunakan DB::table karena Model Transaction mungkin belum ada di Laravel
        if (\Illuminate\Support\Facades\Schema::hasTable('transactions')) {
            $trxCode = 'TRX-' . date('Ymd') . '-001';
            
            if (!DB::table('transactions')->where('code', $trxCode)->exists()) {
                $trxId = Str::uuid();
                
                DB::table('transactions')->insert([
                    'id' => $trxId,
                    'code' => $trxCode,
                    'payment_method' => 'cash',
                    'cash_received' => 50000,
                    'change' => 28000,
                    'total' => 22000,
                    'created_at' => now()->subHour(),
                    'updated_at' => now()->subHour(),
                ]);

                DB::table('transaction_items')->insert([
                    'id' => Str::uuid(),
                    'transaction_id' => $trxId,
                    'item_id' => $createdItems[0]->id,
                    'quantity' => 1,
                    'price' => 18000,
                    'subtotal' => 18000
                ]);
                
                $this->command->info('✅ Dummy POS Transaction seeded');
            }
        }
    }
}
