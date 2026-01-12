<?php
// app/Jobs/SendTelegramMessage.php
namespace App\Jobs;

use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;

class SendTelegramMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private array|string $targets,
        private string $message,
        private ?array $keyboard = null
    ) {}

    public int $tries = 3;
    public array $backoff = [5, 30];

    public function handle(TelegramService $telegram): void
    {
        $targets = is_array($this->targets) ? $this->targets : [$this->targets];
        foreach ($targets as $chatId) {
            try {
                $response = $telegram->sendMessage($chatId, $this->message, $this->keyboard);

                // Telegram API responds with an 'ok' boolean; log if not ok or unexpected
                if (is_array($response)) {
                    if (isset($response['ok']) && !$response['ok']) {
                        \Illuminate\Support\Facades\Log::error('Telegram API error', ['chatId' => $chatId, 'response' => $response]);
                    } elseif (isset($response['error'])) {
                        \Illuminate\Support\Facades\Log::error('TelegramService exception', ['chatId' => $chatId, 'error' => $response['error']]);
                    } else {
                        // Successful-ish: optionally log message_id for tracing
                        if (isset($response['result']['message_id'])) {
                            \Illuminate\Support\Facades\Log::info('Telegram message sent', ['chatId' => $chatId, 'message_id' => $response['result']['message_id']]);
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Log and continue; job will retry on exception if necessary.
                \Illuminate\Support\Facades\Log::error('SendTelegramMessage failed', ['chatId' => $chatId, 'error' => $e->getMessage()]);
            }
        }
    }
}
