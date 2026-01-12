<?php

// app/Http/Controllers/TelegramMessageController.php
namespace App\Http\Controllers\Api\v1\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\BaseService;
use App\Http\Controllers\Api\v1\BaseAPI;

class TelegramMessageController extends BaseAPI
{
    // Two endpoints implemented below:
    // - sendToUsers: send a rich text message to a list of users (by internal user id)
    // - toAll: send a message to ALL users with a telegram_id; uses queued jobs in chunks so it doesn't block

    public function sendToUsers(Request $request)
    {
        $payload = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'integer|exists:users,id',
            'message' => 'required|string',
            'keyboard' => 'nullable|array',
        ]);

        // Collect telegram chat ids from users
        $chatIds = User::whereIn('id', $payload['user_ids'])
            ->whereNotNull('telegram_id')
            ->pluck('telegram_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($chatIds)) {
            return $this->errorResponse('No Telegram accounts linked for the specified users', 422);
        }

        // Simple flow: chunk chat ids and dispatch the same message to each chunk.
        $chunkSize = 100;
        $dispatched = 0;
        foreach (array_chunk($chatIds, $chunkSize) as $chunk) {
            \App\Jobs\SendTelegramMessage::dispatch($chunk, $payload['message'], $payload['keyboard'] ?? null);
            $dispatched++;
        }

        return $this->successResponse(['dispatched_chunks' => $dispatched], 'Messages queued');
    }

    public function toAll(Request $request)
    {
        $payload = $request->validate([
            'message' => 'required|string',
            'keyboard' => 'nullable|array',
        ]);

        // Stream users with telegram_id in chunks and dispatch jobs for each batch
        // Use chunkById to avoid skipping/duplicating rows on large tables and pass 'id' explicitly
        $chunkSize = 200; // numbers per job
        $query = User::whereNotNull('telegram_id')->where('telegram_id', '<>', '');
        $total = 0;
        $query->select('id', 'telegram_id')->chunkById($chunkSize, function ($rows) use ($payload, &$total, $chunkSize) {
            $chatIds = [];
            foreach ($rows as $r) {
                if ($r->telegram_id) {
                    $chatIds[] = $r->telegram_id;
                }
            }

            foreach (array_chunk($chatIds, $chunkSize) as $chunk) {
                \App\Jobs\SendTelegramMessage::dispatch($chunk, $payload['message'], $payload['keyboard'] ?? null);
                $total += count($chunk);
            }
        }, 'id');

        return $this->successResponse(['queued' => $total], 'Messages queued to all Telegram users');
    }


}
