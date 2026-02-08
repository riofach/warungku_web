<?php

namespace Tests\Feature\Middleware;

use App\Http\Middleware\CheckOperatingHours;
use App\Services\SettingsService;
use Illuminate\Support\Facades\Route;
use Mockery\MockInterface;
use Tests\TestCase;

class CheckOperatingHoursTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware([CheckOperatingHours::class])->get('/test-checkout', function () {
            return 'Checkout Page';
        });

        Route::get('/closed', function () {
            return 'Warung Closed';
        })->name('closed');
    }

    public function test_allows_access_when_warung_is_open()
    {
        $this->mock(SettingsService::class, function (MockInterface $mock) {
            $mock->shouldReceive('isWarungOpen')->once()->andReturn(true);
        });

        $this->get('/test-checkout')
            ->assertStatus(200)
            ->assertSee('Checkout Page');
    }

    public function test_redirects_to_closed_page_when_warung_is_closed()
    {
        $this->mock(SettingsService::class, function (MockInterface $mock) {
            $mock->shouldReceive('isWarungOpen')->once()->andReturn(false);
        });

        $this->get('/test-checkout')
            ->assertRedirect(route('closed'));
    }
}
