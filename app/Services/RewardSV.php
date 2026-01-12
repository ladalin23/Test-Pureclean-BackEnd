<?php

namespace App\Services;

use Exception;
use App\Services\BaseService;
use Illuminate\Support\Facades\Hash;
use App\Models\Reward;
use App\Models\User;
use App\Models\Product;
use App\Models\Service;
use App\Models\LoyaltyCard;


class RewardSV extends BaseService
{
    protected ?string $modelLabel  = 'Reward';

    /** Central place to define which relations (and columns) to load */
    protected function relations(): array
    {
        return [
            'branch:id,global_id,name',
            'user:id,global_id,username,email',
            'admin:id,global_id,username,email',
            'product:id,global_id,name,image_url',
            'service:id,global_id,name',
        ];
    }

    /** Always start queries with the relations loaded */
    protected function getQuery()
    {
        return Reward::query()->with($this->relations());
    }

    // Get all rewards (optionally by active)
    public function getAllRewards($active = null)
    {
        $params = [
            'with' => $this->relations(),   // <-- make sure BaseService also loads them
        ];
        if ($active !== null) {
            $params['filter_by'] = ['active' => (int)($active ? 1 : 0)];
        }
        // $params = array_merge($params, request()->all());
        // dd($params);
        return $this->getAll($params);
    }


    public function createReward($data)
    {
        // check loyalty card, 
        $loyaltyCard = $this->getByGlobalId(LoyaltyCard::class, $data['loyalty_card_id']);
        if($loyaltyCard->first_reward_id != null && $loyaltyCard->second_reward_id != null) {
            throw new Exception('This loyalty card already claimed rewards.', 400);
        }
        
        // get user_id by global_id
        $data['user_id'] = $this->getIdByGlobalId(User::class, $data['user_id']);
        $admin = auth()->user();
        $data['admin_id'] = $admin->id;
        $data['branch_id'] = $admin->branch_id;
        if ($data['product_id']) {
            $data['product_id'] = $this->getIdByGlobalId(Product::class, $data['product_id']);
        }
        $reward = null;
    
        // make sure the reward_type is fit with the available loyalty card rewards
        if ($loyaltyCard->first_reward_id == null && $data['reward_type'] == 'first') {
            // Create the reward
            $reward = $this->create($data);
            $rewardId = $reward->refresh()->id;
            $loyaltyCard->first_reward_id = $rewardId;
            $loyaltyCard->save();
        } elseif ($loyaltyCard->second_reward_id == null && $data['reward_type'] == 'second') {
            // Create the reward
            $reward = $this->create($data);
            $rewardId = $reward->refresh()->id;
            $loyaltyCard->second_reward_id = $rewardId;
            $loyaltyCard->save();
        } else {
            abort(400, 'Reward already claimed.');
        }

        // // check the loyalty card if the first_reward and second_reward already claim make the active to 0;
        // if ($loyaltyCard->first_reward_id != null && $loyaltyCard->second_reward_id != null) {
        //     $loyaltyCard->active = 0;
        //     $loyaltyCard->save();
        // }

        return $reward;
    }

    /**
     * Display the specified resource.
     */

    public function getReward($global_id)
    {
        return $this->getByGlobalId(Reward::class, $global_id);
    }

    /**
     * Update the specified resource in storage.
     */

     public function updateReward($global_id, $data)
    {
        return $this->update($data, $global_id); // <-- uses BaseService::update(), returns model
    }
}
