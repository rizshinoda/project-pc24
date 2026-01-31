<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProgressInstalasiMail extends Mailable
{
    use Queueable, SerializesModels;


    public $getInstall;
    public $targetRole;
    public $detailBarang;


    public function __construct($getInstall,  $detailBarang, int $targetRole)
    {
        $this->getInstall = $getInstall;
        $this->targetRole = $targetRole;
        $this->detailBarang = $detailBarang;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $client = $this->getInstall;

        return new Envelope(
            from: config('mail.from.address'),
            subject: 'Work Order Instalasi U/ Client '
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
            view: 'emails.progressinstalasi',
            with: [
                'getInstall' => $this->getInstall,
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

        if (!empty($this->getInstall->attachments)) {
            foreach ($this->getInstall->attachments as $file) {
                $attachments[] = Attachment::fromStorageDisk(
                    'public',
                    $file
                )->as(basename($file));
            }
        }

        return $attachments;
    }
}
