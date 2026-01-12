<?php
// app/Services/FcmService.php
namespace App\Services;

use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FcmService
{
    public function __construct(private Messaging $messaging) {}

    public function sendToTokens(array $tokens, string $title, string $body, array $data = [], ?string $image = null, array $user_ids = []): array
    {
        $admin = auth('admin')->user();
        $notification = Notification::create($title, $body, $image, $user_ids, $admin->id ?? null);
        $message = CloudMessage::new()
            ->withNotification($notification)
            ->withData($data);

        // Multicast to many tokens:
        $report = $this->messaging->sendMulticast($message, $tokens);
        return [
            'success' => $report->successes()->count(),
            'failure' => $report->failures()->count(),
            'failed_tokens' => array_map(fn($r) => $r->target()->value(), $report->failures()->getItems()),
        ];
    }

    public function sendToTopic(string $topic, string $title, string $body, array $data = [], ?string $image = null): string
    {
        $notification = Notification::create($title, $body, $image, null, auth('admin')->id(), $topic);
        $message = CloudMessage::new()
            ->withNotification($notification)
            ->withData($data)
            ->withChangedTarget('topic', $topic);

        return $this->messaging->send($message);
    }
}