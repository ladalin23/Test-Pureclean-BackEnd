<?php

namespace App\Http\Controllers\Api\v1\Admin;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\v1\BaseAPI;
use App\Http\Requests\StoreRewardRequest;
use App\Http\Requests\UpdateRewardRequest;
use App\Models\Reward;
use App\Models\LoyaltyCard;
use App\Models\User;
use App\Models\Product;
use App\Services\BaseService;
use App\Services\RewardSV;
use Illuminate\Support\Arr;

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
        $params = [];
        $params['filter_by'] = ['active' => 1];
        $rewards = $this->service->getAll($params);
        return $this->successResponse($rewards, "Rewards retrieved successfully.");
    }

    public function store(StoreRewardRequest $request)
    {
        $params = $request->validated();

        $reward = $this->rewardSV->createReward($params);

        return $this->successResponse($reward, 'Reward created successfully.');
    }

    public function show(string $global_id)
    {
        $reward = $this->service->getByGlobalId(Reward::class, $global_id);
        return $this->successResponse($reward, "Reward retrieved successfully.");
    }
}
