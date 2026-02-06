<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Category;
use App\Models\Item;

class ShopTest extends TestCase
{
    use DatabaseTransactions;

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

    public function test_homepage_filters_items_by_category_name()
    {
        $category1 = Category::factory()->create(['name' => 'Sembako']);
        $category2 = Category::factory()->create(['name' => 'Minuman']);
        
        $item1 = Item::factory()->create(['category_id' => $category1->id, 'name' => 'Beras', 'is_active' => true]);
        $item2 = Item::factory()->create(['category_id' => $category2->id, 'name' => 'Cola', 'is_active' => true]);

        // Filter by Name
        $response = $this->get('/?category=Sembako');

        $response->assertStatus(200);
        $response->assertSee('Beras');
        $response->assertDontSee('Cola');
    }

    public function test_homepage_filters_items_by_category_id()
    {
        $category1 = Category::factory()->create(['name' => 'Category 1']);
        $category2 = Category::factory()->create(['name' => 'Category 2']);
        
        $item1 = Item::factory()->create(['category_id' => $category1->id, 'name' => 'Item 1', 'is_active' => true]);
        $item2 = Item::factory()->create(['category_id' => $category2->id, 'name' => 'Item 2', 'is_active' => true]);

        // Changed parameter from 'category' to 'category_id' per Story 9.2 requirements
        $response = $this->get('/?category_id=' . $category1->id);

        $response->assertStatus(200);
        $response->assertSee('Item 1');
        $response->assertDontSee('Item 2');
    }

    public function test_shows_empty_state_when_category_has_no_items()
    {
        $category1 = Category::factory()->create(['name' => 'Empty Category']);
        $category2 = Category::factory()->create(['name' => 'Other Category']);
        
        $item = Item::factory()->create(['category_id' => $category2->id, 'name' => 'Other Item', 'is_active' => true]);

        $response = $this->get('/?category_id=' . $category1->id);

        $response->assertStatus(200);
        $response->assertSee('Belum ada produk di kategori ini');
        $response->assertDontSee('Other Item');
    }

    public function test_shows_all_items_when_filter_cleared()
    {
        $category1 = Category::factory()->create();
        $item1 = Item::factory()->create(['category_id' => $category1->id, 'name' => 'Item 1', 'is_active' => true]);
        
        // No query param = cleared filter
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Item 1');
    }

    public function test_homepage_search_items()
    {
        $category = Category::factory()->create();
        Item::factory()->create(['name' => 'Apple', 'category_id' => $category->id, 'is_active' => true]);
        Item::factory()->create(['name' => 'Banana', 'category_id' => $category->id, 'is_active' => true]);

        $response = $this->get('/?q=Apple');

        $response->assertStatus(200);
        $response->assertSee('Apple');
        $response->assertDontSee('Banana');
    }

    public function test_homepage_shows_out_of_stock_badge()
    {
        $category = Category::factory()->create();
        Item::factory()->create([
            'name' => 'Out of Stock Item', 
            'stock' => 0, 
            'category_id' => $category->id, 
            'is_active' => true
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Out of Stock Item');
        $response->assertSee('HABIS');
    }

    public function test_homepage_pagination()
    {
        $category = Category::factory()->create();
        Item::factory()->count(13)->create([
            'category_id' => $category->id, 
            'is_active' => true
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        // Assert that we have 12 items on the first page
        $response->assertViewHas('items', function ($items) {
            return $items->count() === 12;
        });
    }

    public function test_categories_with_only_inactive_items_are_not_shown()
    {
        // Category with active items
        $activeCat = Category::factory()->create(['name' => 'Active Cat']);
        Item::factory()->create(['category_id' => $activeCat->id, 'is_active' => true]);

        // Category with inactive items only
        $inactiveCat = Category::factory()->create(['name' => 'Inactive Cat']);
        Item::factory()->create(['category_id' => $inactiveCat->id, 'is_active' => false]);

        // Category with no items
        Category::factory()->create(['name' => 'Empty Cat']);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Active Cat');
        $response->assertDontSee('Inactive Cat');
        $response->assertDontSee('Empty Cat');
    }

    public function test_filter_handles_invalid_category_id_gracefully()
    {
        // Should cast 'abc' to 0 or similar and return empty result or ignore, but definitely not 500 error
        $response = $this->get('/?category_id=abc');

        $response->assertStatus(200);
        // Should just show empty state or all items depending on how casting works (int)'abc' is 0
        // Since no category has ID 0, it should probably show empty state "Belum ada produk..."
        $response->assertSee('Belum ada produk');
    }
}
