<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class TelegramService
{
    protected $botToken;

    public function __construct()
    {
        // config/services.php uses 'token' key for telegram — use that to avoid null token
        $this->botToken = config('services.telegram.token') ?? config('services.telegram.bot_token');
    }
    
    public function sendMessage($chatId, $message, $keyboard = null)
    {
        $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";

        // Prepare data for the request
        $data = [
            'chat_id'    => $chatId,
            'text'       => $message,
            'parse_mode' => 'HTML',  // You can switch this to 'Markdown' if you prefer
        ];

        // Add the inline keyboard if provided
        if ($keyboard) {
            $data['reply_markup'] = json_encode($keyboard);
        }

        try {
            // Send POST request to Telegram API
            $response = Http::post($url, $data);

            // Return response JSON if successful
            return $response->json();
        } catch (Exception $e) {
            // Handle or log the error as needed
            return ['error' => $e->getMessage()];
        }
    }

    public function sendPhoto($chatId, $photoUrl, $caption = null)
    {
        $url = "https://api.telegram.org/bot{$this->botToken}/sendPhoto";

        try {
            $response = Http::post($url, [
                'chat_id'    => $chatId,
                'photo'      => $photoUrl,
                'caption'    => $caption,
                'parse_mode' => 'HTML',
            ]);

            return $response->json();
        } catch (Exception $e) {
            // Handle or log the error as needed
            return ['error' => $e->getMessage()];
        }
    }

    public function sendDocument($chatId, $documentUrl, $caption = null)
    {
        $url = "https://api.telegram.org/bot{$this->botToken}/sendDocument";

        try {
            $response = Http::post($url, [
                'chat_id'    => $chatId,
                'document'   => $documentUrl,
                'caption'    => $caption,
                'parse_mode' => 'HTML',
            ]);

            return $response->json();
        } catch (Exception $e) {
            // Handle or log the error as needed
            return ['error' => $e->getMessage()];
        }
    }

    public function deleteMessage($chatId, $messageId)
    {
        $url = "https://api.telegram.org/bot{$this->botToken}/deleteMessage";

        try {
            $response = Http::post($url, [
                'chat_id'    => $chatId,
                'message_id' => $messageId,
            ]);

            return $response->json();
        } catch (Exception $e) {
            // Handle or log the error as needed
            return ['error' => $e->getMessage()];
        }
    }
}
