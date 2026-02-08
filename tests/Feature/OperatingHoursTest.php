<?php

namespace Tests\Feature;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OperatingHoursTest extends TestCase
{
    use DatabaseTransactions;

    public function test_checkout_page_is_accessible_when_open(): void
    {
        Setting::setValue(Setting::KEY_OPERATING_HOURS_OPEN, '08:00');
        Setting::setValue(Setting::KEY_OPERATING_HOURS_CLOSE, '21:00');
        Carbon::setTestNow(Carbon::createFromTime(10, 0, 0, 'Asia/Jakarta'));

        $response = $this->get(route('checkout.index'));
        // It might redirect if cart is empty, but it shouldn't be 403 or redirect to closed
        // If empty cart, it redirects to cart.index
        // If not empty, it returns 200 view.
        // We just want to ensure it DOES NOT redirect to 'closed'.
        
        if ($response->status() === 302) {
             $this->assertNotEquals(route('closed'), $response->headers->get('Location'));
        } else {
             $response->assertStatus(200);
        }
    }

    public function test_checkout_page_is_blocked_when_closed(): void
    {
        Setting::setValue(Setting::KEY_OPERATING_HOURS_OPEN, '08:00');
        Setting::setValue(Setting::KEY_OPERATING_HOURS_CLOSE, '21:00');
        Carbon::setTestNow(Carbon::createFromTime(22, 0, 0, 'Asia/Jakarta'));

        $response = $this->get(route('checkout.index'));
        $response->assertRedirect(route('closed'));
    }

    public function test_checkout_submission_is_blocked_when_closed(): void
    {
        Setting::setValue(Setting::KEY_OPERATING_HOURS_OPEN, '08:00');
        Setting::setValue(Setting::KEY_OPERATING_HOURS_CLOSE, '21:00');
        Carbon::setTestNow(Carbon::createFromTime(22, 0, 0, 'Asia/Jakarta'));

        $response = $this->post(route('checkout.store'), []);
        $response->assertRedirect(route('closed'));
    }
}
