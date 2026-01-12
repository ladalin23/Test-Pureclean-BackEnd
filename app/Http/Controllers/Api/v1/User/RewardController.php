<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Models\status;
use App\Http\Controllers\Api\v1\BaseAPI;
use App\Models\Reward;
use App\Services\BaseService;
use App\Services\RewardSV;
class RewardController extends BaseAPI
{

    private BaseService $service;
    protected $rewardSV;
    public function __construct()
    {
        $this->rewardSV = new RewardSV();
        // Same idea as ProductController: just tell BaseService how to query Reward.
        $this->service = new class extends BaseService {
            protected function getQuery()
            {
                return Reward::query()
                    ->with([
                        'user:id,global_id,username,u_id',
                        'branch:id,global_id,name',
                        'admin:id,global_id,username',
                        'product:id,global_id,name,image_url',
                        'service:id,global_id,name',
                        // load the loyalty card that points to this reward via second_reward_id
                        'loyaltyCard:id,global_id,second_reward_id,purchase11_id',
                        // and from that loyalty card, load purchase11 with the status
                        'loyaltyCard.purchase11:id,status',
                    ]);
            }
        };
    }
    public function index()
    {
        $user = auth()->user();
        // require product_id to be not null
        // do query here instead of relying on BaseService
        $rewards = \App\Models\Reward::query()
            ->with([
            'user:id,global_id,username,u_id',
            'product:id,global_id,name,image_url'
            ])
            ->where('user_id', $user->id)
            ->whereNotNull('product_id')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse($rewards, "Rewards retrieved successfully.");

        $rewards = $this->service->getAll($params);
        return $this->successResponse($rewards, "Rewards retrieved successfully.");
    }

}
