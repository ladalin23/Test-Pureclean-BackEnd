<?php

namespace App\Services;

use Exception;
use App\Services\BaseService;
use Illuminate\Support\Facades\Hash;
use App\Models\Purchased;
use App\Models\User;
use App\Models\Service;
use App\Support\ServicesCache; // <-- plural, correct one
use App\Support\SettingsCache; // <-- plural, correct one


class PurchasedSV extends BaseService
{
    protected string $statusColumn = 'active';
    protected ?string $modelLabel  = 'Purchased';

    protected function getQuery()
    {
        // return Purchased::query();
        return Purchased::query()
            ->with([
                // Only these columns from children will be serialized
                'user:id,global_id,username,u_id',
                'branch:id,global_id,name',
                'admin:id,global_id,username',
                'service:id,global_id,name',
            ]);
    }

    // Get all purchased items by active status
    public function getAllPurchased($active = null)
    {
        $params = [];
        if ($active !== null) {
            $active = ($active == 1) ? 1 : 0; // Ensure only 0 or 1
            $params['filter_by'] = ['active' => $active];
            
        }
        $user = auth()->user();
        if ($user->role != 'super-admin') {
            $params['filter_by'] = ['is_gift' => 0];
        }
        // If $active is null, get all (active and inactive), except deleted (handled by default scopes)
        return $this->getAll($params);
    }

    public function createPurchased($data)
    {
        // get user id by global_id
        $user_id = $this->getIdByGlobalId(User::class, $data['user_id']);
        $data['user_id'] = $user_id;
        // get branch id by user
        $branch_id = auth()->user()->branch_id;
        $data['branch_id'] = $branch_id;
        $data['admin_id'] = auth()->user()->id; 

        $service = ServicesCache::findByGlobalId($data['service_id']);

        $data['service_id'] = $service->id;

        if ($data['status'] == "Cold"){
            $data['service_price'] = $service->price_cold; // default status
        }
        elseif ($data['status'] == "Warm") {
            $data['service_price'] = $service->price_warm;
        }
        elseif ($data['status'] == "Hot") {
            $data['service_price'] = $service->price_hot;
        }
        elseif ($data['status'] == "Dry") {
            $data['service_price'] = $service->price_dry;
        } else {
            // fallback to 0 if status is unknown
            $data['service_price'] = 0;
        }

        $data['det_price'] = (int) SettingsCache::get('det_price');
        $data['sft_price'] = (int) SettingsCache::get('sft_price');
        $data['acn_price'] = (int) SettingsCache::get('acn_price');

        $data['total_price'] = $data['service_price'] + ($data['det_price'] * $data['det'])  + ($data['sft_price'] * $data['sft']) + ($data['acn_price'] * $data['acn']);

        return $this->create($data);
    }

    public function getPurchased($global_id)
    {
        return $this->getByGlobalId(Purchased::class, $global_id);
    }

    public function updatePurchased($global_id, $data)
    {
        // get branch id by user
        $branch_id = auth()->user()->branch_id;
        $data['branch_id'] = $branch_id;
        $data['admin_id'] = auth()->user()->id; 

        $service = ServicesCache::findByGlobalId($data['service_id']);
        $purchasedItem = $this->getByGlobalId(Purchased::class, $global_id);
        $data['service_id'] = $service->id;
        if($purchasedItem->is_gift == 1){
            $data['service_price'] = 0;
        }
        else if ($data['status'] == "Cold"){
            $data['service_price'] = $service->price_cold; // default status
        }
        elseif ($data['status'] == "Warm") {
            $data['service_price'] = $service->price_warm;
        }
        elseif ($data['status'] == "Hot") {
            $data['service_price'] = $service->price_hot;
        }
        elseif ($data['status'] == "Dry") {
            $data['service_price'] = $service->price_dry;
        } else {
            // fallback to 0 if status is unknown
            $data['service_price'] = 0;
        }
        

        $data['det_price'] = (int) SettingsCache::get('det_price');
        $data['sft_price'] = (int) SettingsCache::get('sft_price');
        $data['acn_price'] = (int) SettingsCache::get('acn_price');

        $data['total_price'] = $data['service_price'] + ($data['det_price'] * $data['det'])  + ($data['sft_price'] * $data['sft']) + ($data['acn_price'] * $data['acn']);

        return $this->update($data, $global_id);
    }
    
}
