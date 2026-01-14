<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Contracts\Queue\ShouldQueue;

class RelokasiMail extends Mailable
{
    use Queueable, SerializesModels;

    public $getRelokasi;
    public $targetRole;
    public $detailBarang;

    public function __construct($getRelokasi, $detailBarang, int $targetRole)
    {
        $this->getRelokasi = $getRelokasi;
        $this->targetRole = $targetRole;
        $this->detailBarang = $detailBarang;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $client = $this->getRelokasi->onlineBilling;

        return new Envelope(
            from: config('mail.from.address'),
            subject: 'Work Order Relokasi U/ Client '
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
            view: 'emails.relokasi',
            with: [
                'getRelokasi' => $this->getRelokasi,
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

        if (!empty($this->getRelokasi->attachments)) {
            foreach ($this->getRelokasi->attachments as $file) {
                $attachments[] = Attachment::fromStorageDisk(
                    'public',
                    $file
                )->as(basename($file));
            }
        }

        return $attachments;
    }
}
