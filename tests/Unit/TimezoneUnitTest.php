<?php

namespace Tests\Unit;

use Tests\TestCase;

class TimezoneUnitTest extends TestCase
{
    public function test_config_timezone(): void
    {
        $this->assertEquals('Asia/Jakarta', config('app.timezone'));
    }
}
