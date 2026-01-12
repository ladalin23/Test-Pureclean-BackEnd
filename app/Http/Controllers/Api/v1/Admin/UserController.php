<?php

namespace App\Http\Controllers\Api\v1\Admin;

use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\UserSV;
use App\Http\Controllers\Api\v1\BaseAPI;


class UserController extends BaseAPI
{
    protected $userSV;
    public function __construct()
    {
        $this->userSV = new UserSV();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {   
        $users = $this->userSV->getAllUsers();
        return $this->successResponse($users, 'Users retrieved successfully');
    }

    public function getAllActiveUsers()
    {
        $users = $this->userSV->getAllUsers(1);
        return $this->successResponse($users, 'Active users retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $params = $request->validated();
        $user = $this->userSV->createUser($params);
        return $this->successResponse($user, 'User created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $global_id)
    {
        $user = $this->userSV->getUser($global_id);
        return $this->successResponse($user, 'User retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, string $global_id)
    {
        $params = $request->validated();
        $user = $this->userSV->updateUser($global_id, $params);
        return $this->successResponse($user, 'User updated successfully');
    }

    public function changeStatus(string $global_id, int $status)
    {
        $result = $this->userSV->userStatus($global_id, $status);
        return $this->successResponse($result['data'], $result['message']);
    }
}
