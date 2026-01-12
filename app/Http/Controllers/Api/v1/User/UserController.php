<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Models\status;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Controllers\Api\v1\BaseAPI;

class UserController extends BaseAPI
{
    public function show()
    {
        $user = auth('api-user')->user();
        return $this->successResponse($user, 'User retrieved successfully');

    }

    public function update(UpdateUserRequest $request)
    {
        $user = auth('api-user')->user();
        $data = $request->validated();

        // Update only provided fields
        foreach ($data as $key => $value) {
            if ($value !== null) {
                $user->$key = $value;
            }
        }
        $user->save();

        return $this->successResponse($user, 'User profile updated successfully');
    }
}
