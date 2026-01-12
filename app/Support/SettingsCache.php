<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use App\Models\Setting;

class SettingsCache
{
    private const TTL = 86400; // 24 hours

    public static function all()
    {
        return Cache::remember('settings.all', self::TTL, function () {
            $data = Setting::query()
                ->where('active', 1)
                ->pluck('value', 'slug')   // 👈 key by slug
                ->toArray();
            return $data;
        });

    }

    public static function get(string $slug)
    {
        $all = self::all();
        return $all[$slug];
    }

    public static function forget()
    {
        Cache::forget('settings.all');
    }
}
