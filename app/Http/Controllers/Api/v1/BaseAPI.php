<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Support\Facades\Log;
use App\Services\TelegramService;

class BaseAPI {

    protected ?TelegramService $telegram = null;

    protected function tg(): TelegramService
    {
        return $this->telegram ??= app(TelegramService::class);
    }

    protected function successResponse($data = null, $message = null, $code = 200)
    {
        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    protected function errorResponse($message = null, $code = 400)
    {
        $code = (is_int($code) && $code >= 100 && $code < 600) ? $code : 500;

        Log::error($message);

        return response()->json([
            'status'  => 'error',
            'message' => $message,
            'data'    => null,
        ], $code);
    }
}
