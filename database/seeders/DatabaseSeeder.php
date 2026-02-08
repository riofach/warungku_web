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
        // Panggil DummyDataSeeder yang baru kita buat
        $this->call(DummyDataSeeder::class);

        // Code lama bisa dikomentari atau dihapus jika DummyDataSeeder sudah mencakup semuanya
        // Tapi untuk amannya kita biarkan DummyDataSeeder menghandle logic "UpdateOrCreate"
        // sehingga tidak konflik dengan data existing.
    }
}
