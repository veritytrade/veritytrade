<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerShipmentLogisticsUpdate extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array{address: string, name: string}  $fromMailbox
     */
    public function __construct(
        public string $kind,
        public string $recipientName,
        public ?string $newStageName,
        public ?string $previousStageName,
        public ?string $latestLine,
        public ?string $latestAt,
        public string $trackingUrl,
        public string $appName,
        public array $fromMailbox,
        public ?string $orderSummary = null,
    ) {}

    public function envelope(): Envelope
    {
        $from = $this->fromMailbox;
        $subject = $this->kind === 'stage'
            ? 'Shipment update — '.(string) $this->newStageName
            : 'Logistics update for your order';

        return new Envelope(
            from: new Address($from['address'], $from['name']),
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.customer.shipment-logistics-update',
        );
    }
}
