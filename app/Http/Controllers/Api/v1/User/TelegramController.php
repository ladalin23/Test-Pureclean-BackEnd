<?php

namespace App\Http\Controllers\Api\v1\User;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\v1\BaseAPI;
use App\Models\User;
use App\Services\BaseService;

class TelegramController extends BaseAPI
{   
    private BaseService $service;
    public function __construct()
    {
        // Minimal glue: define getQuery() for Admin on the fly.
        $this->service = new class extends BaseService {
            protected function getQuery() { return User::query(); }
        };
    }

    public function store(TelegramRequest $request, string $global_id)
    {
        // $params = $request->validated();
        // $user = $this->service->getByGlobalId(User::class, $global_id);
        // if (!$user) {
        //     return $this->errorResponse('User not found', 404);
        // }
        // // Check if telegram_id is already linked to another user
        // $existingUser = User::where('telegram_id', $params['telegram_id'])
        //                     ->where('global_id', '!=', $global_id)
        //                     ->first();
        // if ($existingUser) {
        //     return $this->errorResponse('Telegram ID is already linked to another account', 409);
        // }

        // // Update user with telegram details
        // $user->telegram_id = $params['telegram_id'];
        // $user->telegram_username = $params['telegram_username'] ?? null;
        // $user->is_verify_telegram = true;
        // // Clear the telegram_code after successful linking
        // $user->telegram_code = null;
        // $user->save();

        // return $this->successResponse($user, 'Telegram account linked successfully');
    }

     /**
     * Create a telegram code for the user to link their account.
     */

    public function createCode(string $global_id)
    {
        $user = $this->service->getByGlobalId(User::class, $global_id);
        $code = rand(100000, 999999);
        $user->telegram_code = $code;
        $user->save();
        return $this->successResponse(['code' => $code], 'Telegram code created successfully');
    }

}
