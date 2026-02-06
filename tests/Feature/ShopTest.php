<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Category;
use App\Models\Item;

class ShopTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_is_accessible()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('shop.index');
    }

    public function test_homepage_displays_categories_and_items()
    {
        // Arrange
        $category = Category::factory()->create(['name' => 'Sembako']);
        $item = Item::factory()->create([
            'category_id' => $category->id,
            'name' => 'Beras 5kg',
            'is_active' => true
        ]);

        // Act
        $response = $this->get('/');

        // Assert
        $response->assertStatus(200);
        $response->assertViewHas('categories');
        $response->assertViewHas('items');
        $response->assertSee('Sembako');
        $response->assertSee('Beras 5kg');
    }
}
