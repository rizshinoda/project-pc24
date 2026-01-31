<!DOCTYPE html>
<html>
@php
$basePath = match ((int) $targetRole) {
5 => '/psb/instalasi/',
2 => '/ga/instalasi/',

};
@endphp

<head>
    <meta charset="UTF-8">
    <title>
        Work Order Instalasi
        / Client {{ $getInstall->pelanggan->nama_pelanggan ?? '-' }} {{ $getInstall->nama_site ?? '' }}

    </title>
</head>

<body style="font-family: Arial, sans-serif; font-size: 14px; color: #333; line-height: 1.4;">

    <p style="margin-bottom: 10px;">Dear Team PSB & GA,</p>

    <p style="margin-bottom: 12px;">
        Terlampir Work Order Instalasi dengan detail site sebagai berikut:
    </p>

    <table cellpadding="4" cellspacing="0" border="0"
        style="width: 100%; max-width: 700px; border-collapse: collapse; margin-left: 40px;">
        <tbody>


            <tr>
                <td><strong>Pelanggan</strong></td>
                <td>:</td>
                <td>{{ $getInstall->pelanggan->nama_pelanggan ?? '-' }}</td>
            </tr>

            <tr>
                <td><strong>Alamat Pelanggan</strong></td>
                <td>:</td>
                <td>{{ $getInstall->pelanggan->alamat ?? '-' }}</td>
            </tr>
            <tr>
                <td style="width: 180px;"><strong>No. JAR</strong></td>
                <td style="width: 10px;">:</td>
                <td>{{ $getInstall->no_jar ?? '-' }}</td>
            </tr>

            <tr>
                <td><strong>VLAN</strong></td>
                <td>:</td>
                <td>{{ $getInstall->vlan ?? '-' }}</td>
            </tr>

            <tr>
                <td><strong>Layanan</strong></td>
                <td>:</td>
                <td>{{ $getInstall->layanan ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>Media</strong></td>
                <td>:</td>
                <td>{{ $getInstall->media ?? '-' }}</td>
            </tr>

            <tr>
                <td><strong>Nama Site</strong></td>
                <td>:</td>
                <td>{{ $getInstall->nama_site ?? '-' }}</td>
            </tr>

            <tr>
                <td><strong>Alamat Pemasangan</strong></td>
                <td>:</td>
                <td>{{ $getInstall->alamat_pemasangan ?? '-' }}</td>
            </tr>

            <tr>
                <td><strong>PIC</strong></td>
                <td>:</td>
                <td>
                    {{ $getInstall->nama_pic ?? '-' }}
                    @if(!empty($getInstall->no_pic))
                    ({{ $getInstall->no_pic }})
                    @endif
                </td>
            </tr>



            <tr>
                <td><strong>Volume</strong></td>
                <td>:</td>
                <td>
                    {{ $getInstall->bandwidth ?? '-' }}
                    {{ $getInstall->satuan ?? '' }}
                </td>
            </tr>



            {{-- Perangkat Stock --}}
            @if ($detailBarang->isNotEmpty())
            <tr>
                <td style="vertical-align: top;"><strong>Perangkat Stock</strong></td>
                <td style="vertical-align: top;">:</td>
                <td>
                    @foreach ($detailBarang as $barang)
                    - {{ $barang->merek }} - {{ $barang->tipe }}
                    ({{ $barang->kualitas }}),
                    Jumlah: {{ $barang->jumlah }} Unit<br>
                    @endforeach
                </td>
            </tr>
            @endif

            {{-- Perangkat Non-Stock --}}
            @if (!empty($getInstall->non_stock))
            @php
            $nonStockItems = preg_split('/\r\n|\r|\n/', $getInstall->non_stock);
            @endphp
            <tr>
                <td style="vertical-align: top;"><strong>Perangkat Non-Stock</strong></td>
                <td style="vertical-align: top;">:</td>
                <td>
                    @foreach ($nonStockItems as $item)
                    - {{ trim($item) }}<br>
                    @endforeach
                </td>
            </tr>
            @endif

        </tbody>
    </table>

    <p style="margin-bottom: 12px;">
        Mohon dapat diterima dengan baik dan mohon dibantu proses lebih lanjutnya.
        Terima kasih.
    </p>

    <br>

    <p style="margin: 6px 0;">
        📎
        <a href="{{ url($basePath . $getInstall->id) }}">
            Lihat Detail Permintaan
        </a>
    </p>

    <br>

    <p style="margin-top: 10px;">
        Warm regards,<br>
        {{ $getInstall->admin->name ?? 'User Pengaju' }}
    </p>

</body>

</html>