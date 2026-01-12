<?php

// app/Http/Controllers/FCMTokenController.php
namespace App\Http\Controllers\Api\v1\User;

use App\Models\DeviceToken;
use Illuminate\Http\Request;

class FCMTokenController extends BaseAPI
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'token' => 'required|string',
            'platform' => 'required|string|in:ios,android,web',
        ]);

        $user = $request->user();

        // Remove this token if it exists for other users
        DeviceToken::where('token', $data['token'])
            ->where('user_id', '!=', $user->id)
            ->delete();

        // Ensure one token per platform for this user: update existing platform record or create it
        DeviceToken::updateOrCreate(
            ['user_id' => $user->id, 'platform' => $data['platform']],
            ['token' => $data['token']]
        );

        return response()->json(['ok' => true]);
    }

    public function unregister(Request $request)
    {
        $request->validate(['token' => 'required|string']);
        $request->user()->deviceTokens()->where('token', $request->token)->delete();
        return response()->json(['ok' => true]);
    }
}
