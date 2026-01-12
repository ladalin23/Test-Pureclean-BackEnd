<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Models\status;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Controllers\Api\v1\BaseAPI;
use App\Models\LoyaltyCard;
use Carbon\Carbon;
class LoyaltyController extends BaseAPI
{

    /**
     * Display the specified resource.
     */
    public function show()
    {
        $user = auth('api-user')->user();
        $userId = $user->id;
        $loyaltyCards = LoyaltyCard::query()
            ->where('active', 1)
            ->where('user_id', $userId)
            ->where('expires_at', '>', Carbon::now())
            ->where(function ($query) {
                $query->whereNull('first_reward_id')
                      ->orWhereNull('second_reward_id');
            })
            ->with([
                'purchase1', 'purchase2', 'purchase3', 'purchase4', 'purchase5',
                'purchase6', 'purchase7', 'purchase8', 'purchase9', 'purchase10', 'purchase11'
            ])
            ->get();
        return $this->successResponse($loyaltyCards, 'Loyalty cards for user retrieved successfully.');
    }
}
