<?php

// app/Http/Controllers/Api/v1/User/TelegramAuthController.php
namespace App\Http\Controllers\Api\v1\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\v1\BaseAPI;
use Illuminate\Support\Str;
use App\Models\TelegramAuthSession;
use Carbon\Carbon;
use App\Models\User;
use App\Services\DeviceTokenService;
use Illuminate\Support\Facades\Auth;

class TelegramAuthController extends BaseAPI
{
     public function verify(Request $request)
    {
        // The app will POST these from the Telegram widget payload
        // id, first_name, last_name, username, photo_url, auth_date, hash
        $data = $request->validate([
            'id'         => 'required|numeric',
            'first_name' => 'nullable|string',
            'last_name'  => 'nullable|string',
            'username'   => 'nullable|string',
            'photo_url'  => 'nullable|url',
            'auth_date'  => 'required|numeric',
            'hash'       => 'required|string',
            'token'      => 'required|string', // device token for push notifications
            'platform'   => 'required|string', // android|ios
        ]);

        // 1) Freshness (avoid replay attacks)
        if (abs(time() - (int)$data['auth_date']) > 300) { // 5 minutes
            return response()->json(['message' => 'Auth expired'], 401);
        }
        $platform = $data['platform'];
        $token = $data['token'];
        unset($data['platform'], $data['token']);


        // 2) Build data_check_string (all fields except hash, sorted by key)
        $receivedHash = $data['hash'];
        $check = $data; unset($check['hash']);
        ksort($check);
        $dataCheckString = collect($check)
            ->map(fn($v, $k) => $k.'='.$v)
            ->implode("\n");

        // 3) Compute HMAC-SHA256(data_check_string, secret_key)
        // secret_key = SHA256(bot_token)
        $botToken = config('services.telegram.token') ?? env('TELEGRAM_BOT_TOKEN');
        // dd($botToken);
        if (!$botToken) {
            Log::error('Missing TELEGRAM_BOT_TOKEN');
            return response()->json(['message' => 'Server misconfigured'], 500);
        }
        $secretKey = hash('sha256', $botToken, true); // raw binary
        $hmac = hash_hmac('sha256', $dataCheckString, $secretKey);

        if (!hash_equals($hmac, $receivedHash)) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        // 4) Create or update local user
        $user = User::where('telegram_id', $data['id'])->first();
        if (!$user) {
            $user = new User();
            $user->telegram_id = $data['id'];
            // random password (not used for Telegram login)
            $user->password = bcrypt(str()->random(32));
        }

        $user->username= trim(($data['first_name'] ?? '').' '.($data['last_name'] ?? '')) ?: ($user->name ?? 'Telegram User');
        $user->telegram_username  = $data['username'] ?? $user->telegram_username;
        $user->profile_picture    = $data['photo_url'] ?? $user->profile_picture;
        $user->save();

        $deviceTokenService = new DeviceTokenService();
        // dd($request->header('X-Device-Token'), $request->header('X-Device-Platform'));
        $deviceTokenService->createOrUpdateToken([
            'user_id' => $user->id,
            'token'   => $token,
            'platform'=> $platform,
        ]);

        // 5) Mint JWT
        // $token = JWTAuth::fromUser($user);
        $token = Auth::guard('api-user')->setTTL(60 * 24 * 90)->login($user); // TTL set to 90 days (in minutes)
        // 6) Return token + user object (Flutter saves both)
        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => [
                'id'              => $user->id,
                'telegram_id'     => $user->telegram_id,
                'global_id'       => $user->global_id,
                'u_id'            => $user->u_id,
                'dob'             => $user->dob,
                'gender'          => $user->gender,
                'username'        => $user->telegram_username,
                'profile_picture' => $user->profile_picture,
                'phone'           => $user->phone,
            ],
        ]);
    }
}
