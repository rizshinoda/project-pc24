<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MaintenanceRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $getMaintenance;
    public $detailBarang;

    public function __construct($getMaintenance, $detailBarang)
    {
        $this->getMaintenance = $getMaintenance;
        $this->detailBarang = $detailBarang;
    }

    public function build()
    {
        $client = $this->getMaintenance->onlineBilling;

        $namaPelanggan = $client?->pelanggan->nama_pelanggan ?? '-';
        $namaSite = $client?->nama_site ?? '-';

        $subject = 'Request SPK Alokasi Team & Perangkat U/ Client ' . $namaPelanggan . ' - ' . $namaSite;

        return $this->from(config('mail.from.address'), $this->getMaintenance->admin->name)
            ->subject($subject)
            ->view('emails.maintenance_request');
    }
}
