<!DOCTYPE html>
<html>
@php
$basePath = match ((int) $targetRole) {
5 => '/psb/survey/show/',
};
@endphp

<head>
    <meta charset="UTF-8">
    <title>
        Work Order Survey
        / Client {{ $survey->pelanggan->nama_pelanggan ?? '-' }} {{ $survey->nama_site ?? '' }}

    </title>
</head>

<body style="font-family: Arial, sans-serif; font-size: 14px; color: #333; line-height: 1.4;">

    <p style="margin-bottom: 10px;">Dear Team PSB,</p>

    <p style="margin-bottom: 12px;">Terlampir Work Order Survey dengan detail site sebagai berikut:</p>

    <table cellpadding="4" cellspacing="0" border="0"
        style="width: 100%; max-width: 700px; border-collapse: collapse; margin-left: 40px;">
        <tbody>


            <tr>
                <td><strong>Pelanggan</strong></td>
                <td>:</td>
                <td>{{ $survey->pelanggan->nama_pelanggan ?? '-' }}</td>
            </tr>

            <tr>
                <td><strong>Alamat Pelanggan</strong></td>
                <td>:</td>
                <td>{{ $survey->pelanggan->alamat ?? '-' }}</td>
            </tr>
            <tr>
                <td style="width: 180px;"><strong>No. JAR</strong></td>
                <td style="width: 10px;">:</td>
                <td>{{ $survey->no_jar ?? '-' }}</td>
            </tr>

            <tr>
                <td><strong>VLAN</strong></td>
                <td>:</td>
                <td>{{ $survey->vlan ?? '-' }}</td>
            </tr>

            <tr>
                <td><strong>Layanan</strong></td>
                <td>:</td>
                <td>{{ $survey->layanan ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>Media</strong></td>
                <td>:</td>
                <td>{{ $survey->media ?? '-' }}</td>
            </tr>

            <tr>
                <td><strong>Nama Site</strong></td>
                <td>:</td>
                <td>{{ $survey->nama_site ?? '-' }}</td>
            </tr>

            <tr>
                <td><strong>Alamat Pemasangan</strong></td>
                <td>:</td>
                <td>{{ $survey->alamat_pemasangan ?? '-' }}</td>
            </tr>

            <tr>
                <td><strong>PIC</strong></td>
                <td>:</td>
                <td>
                    {{ $survey->nama_pic ?? '-' }}
                    @if(!empty($survey->no_pic))
                    ({{ $survey->no_pic }})
                    @endif
                </td>
            </tr>



            <tr>
                <td><strong>Volume</strong></td>
                <td>:</td>
                <td>
                    {{ $survey->bandwidth ?? '-' }}
                    {{ $survey->satuan ?? '' }}
                </td>
            </tr>




        </tbody>
    </table>
    <p style="margin-bottom: 12px;">Mohon dapat diterima dengan baik dan mohon dibantu proses lebih lanjutnya. Terima Kasih</p>

    <br>



    <p style="margin: 6px 0;">
        📎 <a href="{{ url($basePath . $survey->id) }}">
            Lihat Detail Permintaan
        </a>
    </p>
    <br>
    <p style="margin-top: 10px;">Warm regards,<br>
        {{ $survey->admin->name ?? 'User Pengaju' }}
    </p>

</body>

</html>