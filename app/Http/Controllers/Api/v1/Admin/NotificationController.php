<?php

// app/Http/Controllers/NotificationController.php
namespace App\Http\Controllers\Api\v1\Admin;

use App\Models\User;
use App\Models\DeviceToken;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Services\BaseService;
use App\Services\FCMService;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FcmNotification;
use App\Http\Controllers\Api\v1\BaseAPI;

class NotificationController extends BaseAPI
{
    private FCMService $fcmService;
        private BaseService $service;
    public function __construct(private Messaging $messaging)
    {
        // $this->fcmService = new FCMService();
        // Minimal glue: define getQuery() for Admin on the fly.
        $this->service = new class extends BaseService {
            protected function getQuery() { return Notification::query(); }
        };
    }

    // get all notifications
    public function index(Request $request)
    {
        $params = [];
        $notifications = $this->service->getAll($params);
        return $this->successResponse($notifications, 'Notifications retrieved successfully');
    }

    // get single notification
    public function show(string $global_id)
    {
        $notification = $this->service->getByGlobalId(Notification::class, $global_id);
        return $this->successResponse($notification, 'Notification retrieved successfully');
    }

    // send to users 
    public function sendToUsers(Request $request)
    {
        $payload = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'integer|exists:users,id',
            'title'   => 'required|string',
            'body'    => 'required|string',
            'data'    => 'nullable|json',
            'image_url'   => 'nullable|url',
        ]);

        $tokens = DeviceToken::whereIn('user_id', $payload['user_ids'])->pluck('token')->all();
        // if (empty($tokens)) return response()->json(['ok' => false, 'message' => 'No tokens'], 404);

        if (empty($tokens)) {
            return $this->errorResponse('No device tokens found for the specified users', 422);
        }

        $admin = auth('api')->user();
        $n_db = Notification::create([
            'title' => $payload['title'],
            'body' => $payload['body'],
            'image_url' => $payload['image_url'] ?? null,
            'admin_id' => $admin->id ?? null,
            'data' => $payload['data'] ?? null,
            'user_ids' => $payload['user_ids'],
        ]);
        if (!$n_db) {
            // return response()->json(['ok' => false, 'message' => 'Failed to create notification record'], 500);
            return $this->errorResponse('Failed to create notification record', 500);
        }
        $notification = FcmNotification::create(
            $payload['title'],
            $payload['body'],
            $payload['image_url'] ?? null,
            $payload['user_ids'] ?? null,
            $admin->id ?? null,
            $payload['data'] ?? null
        );

        $rawData = $payload['data'] ?? null;
        $parsedData = is_string($rawData) ? (json_decode($rawData, true) ?? []) : ($rawData ?? []);

        $message = CloudMessage::new()
            ->withNotification($notification)
            ->withData($parsedData);

        // Multicast to many tokens:
        $report = $this->messaging->sendMulticast($message, $tokens);
        return [
            'success' => $report->successes()->count(),
            'failure' => $report->failures()->count(),
            'failed_tokens' => array_map(fn($r) => $r->target()->value(), $report->failures()->getItems()),
        ];

        return response()->json(['ok' => true]);
    }

    public function toAll(Request $request)
    {
        $payload = $request->validate([
            'topic'   => 'required|string',
            'title'   => 'required|string',
            'body'    => 'required|string',
            'data'    => 'nullable|json',
            'image_url'   => 'nullable|url',
        ]);

        $admin = auth('api')->user();
        $n_db = Notification::create([
            'title' => $payload['title'],
            'body' => $payload['body'],
            'image_url' => $payload['image_url'] ?? null,
            'admin_id' => $admin->id ?? null,
            'data' => $payload['data'] ?? null,
            'topic' => $payload['topic'],
        ]);
        if (!$n_db) {
            return $this->errorResponse('Failed to create notification record', 500);
        }
        $notification = FcmNotification::create(
            $payload['title'],
            $payload['body'],
            $payload['image_url'] ?? null,
            null,
            $admin->id ?? null,
            $payload['data'] ?? null
        );

        $rawData = $payload['data'] ?? null;
        $parsedData = is_string($rawData) ? (json_decode($rawData, true) ?? []) : ($rawData ?? []);

        $message = CloudMessage::new()
            ->withNotification($notification)
            ->withData($parsedData)
            ->withChangedTarget('topic', $payload['topic']);

        $this->messaging->send($message);

        return response()->json(['ok' => true]);
    }

}
