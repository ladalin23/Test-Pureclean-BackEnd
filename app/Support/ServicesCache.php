<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use App\Models\Service;

class ServicesCache
{
    private const TTL = 86400; // 24 hours

    public static function all()
    {
        return Cache::remember('services.all', self::TTL, function () {
            // include columns you read in PurchasedSV (id, global_id, price_*)
            return Service::orderBy('name')->get([
                'id', 'global_id', 'name', 'active',
                'price_cold', 'price_warm', 'price_hot', 'price_dry'
            ]);
        });
    }

    public static function findByGlobalId(string $globalId): ?Service
    {
        return self::all()->where('global_id', $globalId)->where('active', 1)->first();
    }


    public static function forget()
    {
        Cache::forget('services.all');
    }
}
