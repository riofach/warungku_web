<?php

namespace App\Services;

use App\Models\Setting;

class SettingsService
{
    public function isWarungOpen(): bool
    {
        return Setting::isWarungOpen();
    }
}
