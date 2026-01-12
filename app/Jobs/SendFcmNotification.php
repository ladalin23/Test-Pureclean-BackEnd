<?php
// app/Jobs/SendFcmNotification.php
namespace App\Jobs;

use App\Services\FCMService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendFcmNotification implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private string $audienceType,    // 'token' | 'tokens' | 'topic'
        private array|string $target,    // token | tokens[] | topic string
        private array $data = [],
        private ?array $notification = null
    ) {}

    public $tries = 3;           // retry on transient errors
    public $backoff = [10, 60];  // exponential backoff

    public function handle(FCMService $fcm): void
    {
        switch ($this->audienceType) {
            case 'token':
                $fcm->sendToToken($this->target, $this->data, $this->notification);
                break;
            case 'tokens':
                $report = $fcm->sendToTokens($this->target, $this->data, $this->notification);
                // clean up invalid tokens
                foreach ($report->invalidTokens() as $invalid) {
                    \App\Models\DeviceToken::where('token', $invalid)->delete();
                }
                break;
            case 'topic':
                $fcm->sendToTopic($this->target, $this->data, $this->notification);
                break;
        }
    }
}
