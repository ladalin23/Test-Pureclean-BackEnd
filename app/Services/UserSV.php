<?php

namespace App\Services;

use Exception;
use App\Services\BaseService;
use Illuminate\Support\Facades\Hash;
use App\Models\User;


class UserSV extends BaseService
{
    protected string $statusColumn = 'active';
    protected ?string $modelLabel  = 'User';
    protected function getQuery()
    {
        return User::query();
    }

    public function getAllUsers($active = null)
    {
        $params = [];
        if ($active !== null) {
            $active = ($active == 1) ? 1 : 0; // Ensure only 0 or 1
            $params['filter_by'] = ['active' => $active];
        }
        return $this->getAll($params);
    }

    public function createUser($data)
    {
        $lastUser = User::orderBy('id', 'desc')->first();
        if ($lastUser) {
            $lastUId = (int) filter_var($lastUser->u_id, FILTER_SANITIZE_NUMBER_INT);
            $newUId = 'U' . str_pad($lastUId + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newUId = 'U00001'; // Starting ID if no users exist
        }
        $data['u_id'] = $newUId;

        return $this->create($data);
    }

    /**
     * Display the specified resource.
     */

    public function getUser($global_id)
    {
        if(!is_numeric($global_id)){
            return $this->getByGlobalId(User::class, $global_id);
        }
        $user = User::where('u_id', $global_id)->first();
        if (!$user) {
            abort(404, 'Data not found');
        }
        return $user;
    }

    /**
     * Update the specified resource in storage.
     */

     public function updateUser($global_id, $data)
    {
        return $this->update($data, $global_id); // <-- uses BaseService::update(), returns model
    }
    /**
     * Update the specified resource in storage.
     */

    public function userStatus(string $global_id, int|bool $status): array
    {
        // Uses BaseService::setStatus(), returns ['data' => model, 'message' => string]
        return $this->setStatus($global_id, (int) $status);
    }
    
}
