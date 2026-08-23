<?php

namespace App\Mail\Transport;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * Sends mail via Brevo's HTTPS API instead of SMTP. Some hosts (Railway
 * included) block outbound SMTP ports entirely on cheaper plans — this
 * goes over the same HTTPS port the app itself is already served on,
 * which is never blocked.
 */
class BrevoApiTransport extends AbstractTransport
{
    private const ENDPOINT = 'https://api.brevo.com/v3/smtp/email';

    public function __construct(private readonly string $apiKey)
    {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $email = $message->getOriginalMessage();

        if (! $email instanceof Email) {
            throw new TransportException('BrevoApiTransport only supports Symfony\Component\Mime\Email messages.');
        }

        $payload = array_filter([
            'sender' => $this->addressToArray($this->pickFrom($email)),
            'to' => $this->addressesToArray($email->getTo()),
            'cc' => $this->addressesToArray($email->getCc()) ?: null,
            'bcc' => $this->addressesToArray($email->getBcc()) ?: null,
            'replyTo' => ($replyTo = $email->getReplyTo()) ? $this->addressToArray($replyTo[0]) : null,
            'subject' => $email->getSubject(),
            'htmlContent' => $email->getHtmlBody(),
            'textContent' => $email->getTextBody(),
            'attachment' => $this->attachmentsToArray($email) ?: null,
        ], fn ($value) => $value !== null);

        try {
            $response = (new Client())->post(self::ENDPOINT, [
                'headers' => [
                    'api-key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $payload,
                'http_errors' => false,
            ]);
        } catch (GuzzleException $e) {
            throw new TransportException('Could not reach the Brevo API: '.$e->getMessage(), 0, $e);
        }

        $status = $response->getStatusCode();
        if ($status < 200 || $status >= 300) {
            throw new TransportException("Brevo API returned HTTP {$status}: ".$response->getBody()->getContents());
        }
    }

    private function pickFrom(Email $email): Address
    {
        $from = $email->getFrom();

        return $from[0] ?? new Address(config('mail.from.address'), config('mail.from.name') ?? '');
    }

    /**
     * @param  Address[]  $addresses
     * @return array<int, array{email: string, name?: string}>
     */
    private function addressesToArray(array $addresses): array
    {
        return array_map(fn (Address $a) => $this->addressToArray($a), $addresses);
    }

    /**
     * @return array{email: string, name?: string}
     */
    private function addressToArray(Address $address): array
    {
        return array_filter([
            'email' => $address->getAddress(),
            'name' => $address->getName() ?: null,
        ], fn ($value) => $value !== null);
    }

    /**
     * @return array<int, array{content: string, name: string}>
     */
    private function attachmentsToArray(Email $email): array
    {
        return array_map(function ($attachment) {
            $headers = $attachment->getPreparedHeaders();
            $name = $headers->getHeaderParameter('Content-Disposition', 'filename') ?? 'attachment';

            return [
                'content' => base64_encode($attachment->getBody()),
                'name' => $name,
            ];
        }, $email->getAttachments());
    }

    public function __toString(): string
    {
        return 'brevo+api://api.brevo.com';
    }
}
