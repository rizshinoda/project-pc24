<!DOCTYPE html>
<html>
@php
$basePath = match ((int) $targetRole) {
1 => '/admin/gantivendor/',
};
@endphp

<head>
    <meta charset="UTF-8">
    <title>
        Work Order Gantivendor @if($getGantivendor->onlineBilling)
        / Client {{ $getGantivendor->onlineBilling->pelanggan->nama_pelanggan ?? '-' }} {{ $getGantivendor->onlineBilling->nama_site ?? '' }}
        @endif
    </title>
</head>

<body style="font-family: Arial, sans-serif; font-size: 14px; color: #333; line-height: 1.4;">

    <p style="margin-bottom: 10px;">Dear Rekan Procurement/Admin,</p>

    <p style="margin-bottom: 12px;">Mohon dibantu pergantian akses guna peningkatan mutu layanan pada site berikut:</p>

    <table cellpadding="4" cellspacing="0" border="0"
        style="width: 100%; max-width: 700px; border-collapse: collapse; margin-left: 40px;">
        <tbody>

            @if ($billing = $getGantivendor->onlineBilling)
            <tr>
                <td style="width: 180px;"><strong>No. JAR</strong></td>
                <td style="width: 10px;">:</td>
                <td>{{ $billing->no_jar ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>VLAN</strong></td>
                <td>:</td>
                <td>{{ $billing->vlan ?? '-' }}</td>
            </tr>

            <tr>
                <td><strong>Pelanggan</strong></td>
                <td>:</td>
                <td>{{ $billing->nama_site ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>Alamat</strong></td>
                <td>:</td>
                <td>{{ $billing->alamat_pemasangan ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>PIC</strong></td>
                <td>:</td>
                <td>{{ $billing->nama_pic ?? '-' }} @if ($billing->no_pic) ({{ $billing->no_pic }}) @endif</td>
            </tr>
            <tr>
                <td><strong>Layanan</strong></td>
                <td>:</td>
                <td>{{ $billing->layanan ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>Volume</strong></td>
                <td>:</td>
                <td>{{ $billing->bandwidth ?? '-' }} {{ $billing->satuan ?? '' }}</td>
            </tr>


            @endif



        </tbody>
    </table>

    <br>
    @if ($getGantivendor->keterangan)
    <p style="margin: 6px 0;"><strong>Note:</strong> {{ $getGantivendor->keterangan }}</p>
    @endif
    <p style="margin-bottom: 12px;">Mohon dapat diterima dengan baik dan mohon dibantu proses lebih lanjutnya. Terima Kasih</p>

    <br>



    <p style="margin: 6px 0;">
        📎 <a href="{{ url($basePath . $getGantivendor->id) }}">
            Lihat Detail Permintaan
        </a>
    </p>
    <br>
    <p style="margin-top: 10px;">Warm regards,<br>
        {{ $getGantivendor->admin->name ?? 'User Pengaju' }}
    </p>

</body>

</html>