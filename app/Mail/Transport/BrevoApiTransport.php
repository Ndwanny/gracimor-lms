<?php

namespace App\Mail\Transport;

use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class BrevoApiTransport extends AbstractTransport
{
    public function __construct(private readonly string $apiKey)
    {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $fromAddresses = $email->getFrom();
        $sender = count($fromAddresses) > 0 ? [
            'name'  => $fromAddresses[0]->getName() ?: $fromAddresses[0]->getAddress(),
            'email' => $fromAddresses[0]->getAddress(),
        ] : ['email' => 'admin@gracimorloans.com', 'name' => 'Gracimor Microfinance'];

        $to = array_values(array_map(fn ($a) => array_filter([
            'email' => $a->getAddress(),
            'name'  => $a->getName() ?: null,
        ]), $email->getTo()));

        $payload = array_filter([
            'sender'      => $sender,
            'to'          => $to,
            'subject'     => $email->getSubject(),
            'htmlContent' => $email->getHtmlBody(),
            'textContent' => $email->getTextBody(),
        ]);

        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'api-key: ' . $this->apiKey,
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_TIMEOUT        => 15,
        ]);

        $response   = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError  = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new \RuntimeException("Brevo API cURL error: {$curlError}");
        }

        if ($statusCode >= 400) {
            throw new \RuntimeException("Brevo API error ({$statusCode}): {$response}");
        }
    }

    public function __toString(): string
    {
        return 'brevo+api://default';
    }
}
