<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AfricasTalkingDriver
{
    private const BASE_URL        = 'https://api.africastalking.com/version1';
    private const SANDBOX_URL     = 'https://api.sandbox.africastalking.com/version1';
    private const SEND_ENDPOINT   = '/messaging';
    private const STATUS_ENDPOINT = '/messaging/fetch';

    // WhatsApp Chat API — same endpoint for sandbox and production (auth controls env)
    private const WA_URL = 'https://chat.africastalking.com/whatsapp/v1/messages';

    private string $username;
    private string $apiKey;
    private string $senderId;
    private string $waProductName; // WhatsApp product name from AT dashboard
    private bool   $sandbox;
    private string $baseUrl;

    public function __construct()
    {
        $this->username      = config('gracimor.at_username',      env('AT_USERNAME',      'sandbox'));
        $this->apiKey        = config('gracimor.at_api_key',       env('AT_API_KEY',       ''));
        $this->senderId      = config('gracimor.sms_sender_id',    env('AT_SENDER_ID',     'GRACIMOR'));
        $this->waProductName = config('gracimor.at_wa_product',    env('AT_WA_PRODUCT',    ''));
        $this->sandbox       = (bool) env('AT_SANDBOX', false);
        $this->baseUrl       = $this->sandbox ? self::SANDBOX_URL : self::BASE_URL;
    }

    // ── Send a single SMS ─────────────────────────────────────────────────────

    /**
     * Send a single SMS message.
     *
     * @param  string $to     Recipient phone in E.164 format (+260977000001)
     * @param  string $body   Message text (max 918 chars / 6 pages)
     * @return array {
     *   status:       'sent' | 'failed',
     *   provider_ref: string|null,   Africa's Talking messageId
     *   phone:        string,
     *   cost:         string|null,
     *   error:        string|null,
     * }
     */
    public function send(string $to, string $body): array
    {
        if (empty($this->apiKey)) {
            Log::warning('[AfricasTalkingDriver] API key not configured — SMS not sent.', ['to' => $to]);
            return $this->failResult($to, 'API key not configured.');
        }

        // Truncate to 6 SMS pages (918 chars) to avoid billing surprises
        $body = mb_substr($body, 0, 918);

        try {
            $response = Http::withHeaders([
                'apiKey'       => $this->apiKey,
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept'       => 'application/json',
            ])
            ->timeout(15)
            ->asForm()
            ->post($this->baseUrl . self::SEND_ENDPOINT, [
                'username' => $this->username,
                'to'       => $to,
                'message'  => $body,
                'from'     => $this->senderId,
            ]);

            if (!$response->successful()) {
                Log::error('[AfricasTalkingDriver] HTTP error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                    'to'     => $to,
                ]);
                return $this->failResult($to, 'HTTP ' . $response->status());
            }

            $data        = $response->json();
            $smsList     = $data['SMSMessageData']['Recipients'] ?? [];
            $firstResult = $smsList[0] ?? [];

            $atStatus = strtolower($firstResult['status'] ?? 'failed');

            // AT statuses: 'Success', 'UserInBlacklist', 'InvalidSenderId', etc.
            $isSuccess = str_starts_with($atStatus, 'success');

            if (!$isSuccess) {
                Log::warning('[AfricasTalkingDriver] Non-success status', [
                    'at_status' => $atStatus,
                    'to'        => $to,
                ]);
            }

            return [
                'status'       => $isSuccess ? 'sent' : 'failed',
                'provider_ref' => $firstResult['messageId'] ?? null,
                'phone'        => $to,
                'cost'         => $firstResult['cost'] ?? null,
                'error'        => $isSuccess ? null : $atStatus,
            ];

        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('[AfricasTalkingDriver] Request exception', [
                'message' => $e->getMessage(),
                'to'      => $to,
            ]);
            return $this->failResult($to, $e->getMessage());

        } catch (\Throwable $e) {
            Log::error('[AfricasTalkingDriver] Unexpected error', [
                'message' => $e->getMessage(),
                'to'      => $to,
            ]);
            return $this->failResult($to, $e->getMessage());
        }
    }

    // ── Send WhatsApp message via AT Chat API ─────────────────────────────────

    /**
     * Send a WhatsApp message via Africa's Talking Chat API.
     *
     * Requires a WhatsApp product to be set up in the AT dashboard
     * (AT_WA_PRODUCT env var = the product name you configured there).
     *
     * @param  string $to    Recipient in E.164 format (+260977000001)
     * @param  string $body  Message text
     * @return array  Same shape as send(): {status, provider_ref, phone, cost, error}
     */
    public function sendWhatsApp(string $to, string $body): array
    {
        if (empty($this->apiKey)) {
            Log::warning('[AfricasTalkingDriver] API key not configured — WhatsApp not sent.', ['to' => $to]);
            return $this->failResult($to, 'API key not configured.');
        }

        if (empty($this->waProductName)) {
            Log::warning('[AfricasTalkingDriver] AT_WA_PRODUCT not configured — WhatsApp not sent.', ['to' => $to]);
            return $this->failResult($to, 'AT_WA_PRODUCT not configured.');
        }

        try {
            $response = Http::withHeaders([
                'apiKey'       => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ])
            ->timeout(15)
            ->post(self::WA_URL, [
                'username'    => $this->username,
                'phoneNumber' => $to,
                'message'     => $body,
                'productName' => $this->waProductName,
            ]);

            $data = $response->json();

            if (!$response->successful()) {
                $errMsg = $data['description'] ?? $data['error'] ?? ('HTTP ' . $response->status());
                Log::error('[AfricasTalkingDriver] WhatsApp HTTP error', ['to' => $to, 'error' => $errMsg]);
                return $this->failResult($to, $errMsg);
            }

            $atStatus  = strtolower($data['status'] ?? 'error');
            $isSuccess = $atStatus === 'success';

            if (!$isSuccess) {
                Log::warning('[AfricasTalkingDriver] WhatsApp non-success', ['to' => $to, 'status' => $atStatus, 'desc' => $data['description'] ?? '']);
            }

            return [
                'status'       => $isSuccess ? 'sent' : 'failed',
                'provider_ref' => $data['messageId'] ?? $data['id'] ?? null,
                'phone'        => $to,
                'cost'         => null, // AT doesn't return cost in WA response
                'error'        => $isSuccess ? null : ($data['description'] ?? $atStatus),
            ];

        } catch (\Throwable $e) {
            Log::error('[AfricasTalkingDriver] WhatsApp exception', ['to' => $to, 'message' => $e->getMessage()]);
            return $this->failResult($to, $e->getMessage());
        }
    }

    // ── Send bulk SMS (up to 1,000 recipients per request) ───────────────────

    /**
     * Send the same message body to multiple recipients in one API call.
     *
     * @param  array  $recipients  Array of E.164 phone strings
     * @param  string $body        Message text
     * @return array               Keyed by phone number, each value = send result
     */
    public function sendBulk(array $recipients, string $body): array
    {
        if (empty($recipients)) {
            return [];
        }

        // Africa's Talking bulk limit per request
        $chunks  = array_chunk($recipients, 1000);
        $results = [];

        foreach ($chunks as $chunk) {
            $to       = implode(',', $chunk);
            $body     = mb_substr($body, 0, 918);

            try {
                $response = Http::withHeaders([
                    'apiKey'       => $this->apiKey,
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Accept'       => 'application/json',
                ])
                ->timeout(30)
                ->asForm()
                ->post($this->baseUrl . self::SEND_ENDPOINT, [
                    'username' => $this->username,
                    'to'       => $to,
                    'message'  => $body,
                    'from'     => $this->senderId,
                ]);

                $atRecipients = $response->json()['SMSMessageData']['Recipients'] ?? [];

                foreach ($atRecipients as $r) {
                    $phone = $r['number'] ?? '';
                    $atStatus = strtolower($r['status'] ?? 'failed');
                    $results[$phone] = [
                        'status'       => str_starts_with($atStatus, 'success') ? 'sent' : 'failed',
                        'provider_ref' => $r['messageId'] ?? null,
                        'phone'        => $phone,
                        'cost'         => $r['cost'] ?? null,
                        'error'        => str_starts_with($atStatus, 'success') ? null : $atStatus,
                    ];
                }

            } catch (\Throwable $e) {
                Log::error('[AfricasTalkingDriver] Bulk send failed', [
                    'chunk_size' => count($chunk),
                    'error'      => $e->getMessage(),
                ]);
                // Mark all in chunk as failed
                foreach ($chunk as $phone) {
                    $results[$phone] = $this->failResult($phone, $e->getMessage());
                }
            }
        }

        return $results;
    }

    // ── Check delivery status of a sent message ───────────────────────────────

    /**
     * Fetch the delivery status of a previously sent message.
     *
     * @param  string $messageId  The provider_ref returned from send()
     * @return string             'delivered' | 'failed' | 'buffered' | 'rejected' | 'unknown'
     */
    public function checkStatus(string $messageId): string
    {
        try {
            $response = Http::withHeaders([
                'apiKey' => $this->apiKey,
                'Accept' => 'application/json',
            ])
            ->timeout(10)
            ->get($this->baseUrl . self::STATUS_ENDPOINT, [
                'username'  => $this->username,
                'messageId' => $messageId,
            ]);

            $messages = $response->json()['SMSMessageData']['Messages'] ?? [];
            $status   = strtolower($messages[0]['status'] ?? 'unknown');

            return match ($status) {
                'delivrd', 'delivered'  => 'delivered',
                'undelivrd', 'failed'   => 'failed',
                'buffered', 'sent'      => 'buffered',
                'rejected'              => 'rejected',
                default                 => 'unknown',
            };

        } catch (\Throwable $e) {
            Log::warning('[AfricasTalkingDriver] Status check failed', ['messageId' => $messageId]);
            return 'unknown';
        }
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function failResult(string $phone, string $error): array
    {
        return [
            'status'       => 'failed',
            'provider_ref' => null,
            'phone'        => $phone,
            'cost'         => null,
            'error'        => $error,
        ];
    }

    public function isSandbox(): bool
    {
        return $this->sandbox;
    }
}
