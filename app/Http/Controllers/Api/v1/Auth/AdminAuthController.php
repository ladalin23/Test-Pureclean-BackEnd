<?php

namespace App\Http\Controllers\Api\v1\Auth;

use App\Http\Controllers\Api\v1\BaseAPI;
use Illuminate\Http\Request;
use App\Http\Requests\AdminLoginRequest;
use App\Services\AuthSV;
class AdminAuthController extends BaseAPI
{
    protected $AuthSV;
    public function __construct()
    {
        $this->AuthSV = new AuthSV();
    }
    // Login Admin
    public function loginAdmin(AdminLoginRequest $request)
    {
        try {
            $params = $request->validated();

            $loginData = $this->AuthSV->loginAdmin($params['email'], $params['password']);

            // Return response with token and user
            return $this->successResponse($loginData, 'Admin logged in successfully');
    
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }
    

    public function refreshToken()
    {
        try {
            $role = 'admin';
            $token = $this->AuthSV->refreshToken($role);
            // dd($token);
            return $this->successResponse($token, 'Token refreshed successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function logoutAdmin(Request $request)
    {
        try {
            // $request->user()->currentAccessToken()?->delete();
            $this->AuthSV->logout('admin');
            return $this->successResponse([], 'Admin logged out successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }
}
