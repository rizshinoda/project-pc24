<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Contracts\Queue\ShouldQueue;

class DismantleMail extends Mailable
{
    use Queueable, SerializesModels;

    public $workOrder;
    public $targetRole;

    public function __construct($workOrder, int $targetRole)
    {
        $this->workOrder = $workOrder;
        $this->targetRole = $targetRole;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $client = $this->workOrder->onlineBilling;

        return new Envelope(
            from: config('mail.from.address'),
            subject: 'Work Order Dismantle U/ Client '
                . ($client?->pelanggan->nama_pelanggan ?? '-')
                . ' - '
                . ($client?->nama_site ?? '-')
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.dismantle',
            with: [
                'getDismantle' => $this->workOrder,
                'targetRole' => $this->targetRole,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        if (!empty($this->workOrder->attachments)) {
            foreach ($this->workOrder->attachments as $file) {
                $attachments[] = Attachment::fromStorageDisk(
                    'public',
                    $file
                )->as(basename($file));
            }
        }

        return $attachments;
    }
}
