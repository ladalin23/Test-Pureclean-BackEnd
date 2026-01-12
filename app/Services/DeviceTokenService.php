<?php

namespace App\Services;

use Exception;
use App\Models\DeviceToken;
use App\Services\BaseService;

class DeviceTokenService extends BaseService
{
    protected ?string $modelLabel  = 'Device Token';

    protected function getQuery()
    {
        return DeviceToken::query();
    }

    public function createOrUpdateToken(array $data)
    {
        // Ensure one token per user per platform
        $userId = $data['user_id'] ?? null;
        $platform = $data['platform'] ?? null;
        $token = $data['token'] ?? null;

        // Find existing record for this user + platform
        $deviceToken = DeviceToken::where('user_id', $userId)
            ->where('platform', $platform)
            ->first();

        if ($deviceToken) {
            // Update existing record for the user's platform
            $deviceToken->update($data);
        } else {
            // If the same token exists elsewhere, remove it so the token remains unique
            if ($token) {
            DeviceToken::where('token', $token)
                ->where(function ($q) use ($userId, $platform) {
                $q->where('user_id', '!=', $userId)
                  ->orWhere('platform', '!=', $platform);
                })
                ->delete();
            }

            // Create new record for this user + platform
            $deviceToken = DeviceToken::create($data);
        }

        return $deviceToken;
    }
}
