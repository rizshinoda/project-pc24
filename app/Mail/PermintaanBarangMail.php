<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Queue\SerializesModels;

class PermintaanBarangMail extends Mailable
{
    use Queueable, SerializesModels;

    public $requestBarang;
    public $detailBarang;
    public $pdfPath;

    public function __construct($requestBarang, $detailBarang, $pdfPath = null)
    {
        $this->requestBarang = $requestBarang;
        $this->detailBarang = $detailBarang;
        $this->pdfPath = $pdfPath;
    }

    public function build()
    {
        $client = $this->requestBarang->onlineBilling;
        $subjectManual = $this->requestBarang->subject_manual;

        $subject = $subjectManual
            ? 'Request Kirim Perangkat U/ ' . $subjectManual
            : 'Request Kirim Perangkat U/ Client ' .
            ($client?->pelanggan->nama_pelanggan ?? '-') . ' ' .
            ($client?->nama_site ?? '-');

        $mail = $this->from(config('mail.from.address'), $this->requestBarang->user->name)
            ->subject($subject)
            ->view('emails.permintaan_barang');

        // Hanya attach PDF jika ada
        if ($this->pdfPath) {
            $mail->attach($this->pdfPath, [
                'as' => 'surat_pengajuan.pdf',
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
    }
}
