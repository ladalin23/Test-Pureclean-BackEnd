<?php

namespace App\Http\Controllers\Api\v1\Admin;


use App\Http\Requests\StoreLoyaltyCardRequest;
use App\Http\Requests\UpdateLoyaltyCardRequest;
use App\Http\Controllers\Api\v1\BaseAPI;
use Illuminate\Support\Facades\DB;
use App\Models\LoyaltyCard;
use App\Models\User;
use App\Services\LoyaltyCardSV;
use Carbon\Carbon;

class LoyaltyCardController extends BaseAPI
{
    protected $loyaltyCardSV;

    public function __construct()
    {
        $this->loyaltyCardSV = new LoyaltyCardSV();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $loyaltyCards = $this->loyaltyCardSV->getAllLoyaltyCards();
        return $this->successResponse($loyaltyCards, 'Loyalty cards retrieved successfully.');
    }

    public function getAllActiveLoyaltyCards()
    {   
        $loyaltyCards = $this->loyaltyCardSV->getAllLoyaltyCards(1);
        return $this->successResponse($loyaltyCards, 'Loyalty cards retrieved successfully.');
    }

    public function show(string $global_id)
    {
        $card = $this->loyaltyCardSV->getLoyaltyCard($global_id);
        return $this->successResponse($card, 'Loyalty card retrieved successfully.');
    }

    public function getLoyaltyCardsUser(string $user_global_id)
    {
        $userId = $this->loyaltyCardSV->getIdByGlobalId(User::class, $user_global_id);
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