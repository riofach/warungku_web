<?php

namespace Tests\Feature;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TimezoneTest extends TestCase
{
    use DatabaseTransactions;

    public function test_application_timezone_is_asia_jakarta(): void
    {
        $this->assertEquals('Asia/Jakarta', config('app.timezone'));
        $this->assertEquals('Asia/Jakarta', Carbon::now()->timezoneName);
    }

    public function test_is_warung_open_respects_timezone(): void
    {
        // Set operating hours 08:00 - 21:00
        Setting::setValue(Setting::KEY_OPERATING_HOURS_OPEN, '08:00');
        Setting::setValue(Setting::KEY_OPERATING_HOURS_CLOSE, '21:00');

        // Mock time to 07:59 Jakarta time
        Carbon::setTestNow(Carbon::createFromTime(7, 59, 0, 'Asia/Jakarta'));
        $this->assertFalse(Setting::isWarungOpen(), 'Should be closed at 07:59');

        // Mock time to 08:00 Jakarta time
        Carbon::setTestNow(Carbon::createFromTime(8, 0, 0, 'Asia/Jakarta'));
        $this->assertTrue(Setting::isWarungOpen(), 'Should be open at 08:00');

        // Mock time to 21:00 Jakarta time
        Carbon::setTestNow(Carbon::createFromTime(21, 0, 0, 'Asia/Jakarta'));
        $this->assertTrue(Setting::isWarungOpen(), 'Should be open at 21:00');

        // Mock time to 21:01 Jakarta time
        Carbon::setTestNow(Carbon::createFromTime(21, 1, 0, 'Asia/Jakarta'));
        $this->assertFalse(Setting::isWarungOpen(), 'Should be closed at 21:01');
    }
}
