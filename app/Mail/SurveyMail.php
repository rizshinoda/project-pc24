<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Contracts\Queue\ShouldQueue;

class SurveyMail extends Mailable
{
    use Queueable, SerializesModels;
    public $survey;
    public $targetRole;
    /**
     * Create a new message instance.
     */
    public function __construct($survey, int $targetRole)
    {
        $this->survey = $survey;
        $this->targetRole = $targetRole;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $client = $this->survey;

        return new Envelope(
            from: config('mail.from.address'),
            subject: 'Work Order Survey U/ Client '
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
            view: 'emails.survey',
            with: [
                'survey' => $this->survey,
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

        if (!empty($this->survey->attachments)) {
            foreach ($this->survey->attachments as $file) {
                $attachments[] = Attachment::fromStorageDisk(
                    'public',
                    $file
                )->as(basename($file));
            }
        }

        return $attachments;
    }
}
