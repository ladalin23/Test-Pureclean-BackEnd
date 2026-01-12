<?php

namespace App\Services;

use Brevo\Client\Configuration;
use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\Model\SendSmtpEmail;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Log;

class BrevoService
{
    protected TransactionalEmailsApi $api;

    public function __construct()
    {
        $key = config('services.brevo.key');

        // Provide a helpful log when the key looks like an SMTP key (common misconfiguration)
        if (!empty($key) && (str_starts_with($key, 'xsmtp') || str_starts_with($key, 'xsmtpsib'))) {
            \Illuminate\Support\Facades\Log::warning('BREVO_API_KEY appears to be an SMTP key (xsmtp...). The Brevo PHP SDK requires a v3 HTTP API key (xkeysib-...). Please replace BREVO_API_KEY with the API key from the Brevo dashboard.');
        }

        $config = Configuration::getDefaultConfiguration()
            // 👇 this name **must** be exactly 'api-key'
            ->setApiKey('api-key', $key);

        $this->api = new TransactionalEmailsApi(
            new GuzzleClient(),
            $config
        );
    }


    /**
     * Send a simple transactional email via Brevo API
     */
    public function sendSimpleEmail(
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlContent
    ) {
        $email = new SendSmtpEmail([
            'sender' => [
                'email' => config('services.brevo.sender_email'),
                'name'  => config('services.brevo.sender_name'),
            ],
            'to' => [[
                'email' => $toEmail,
                'name'  => $toName,
            ]],
            'subject'     => $subject,
            'htmlContent' => $htmlContent,
        ]);

        try {
            return $this->api->sendTransacEmail($email); // <- real API call :contentReference[oaicite:2]{index=2}
        } catch (\Throwable $e) {
            Log::error('Brevo API email failed: ' . $e->getMessage());
            throw $e; // or return false if you prefer
        }
    }
}
