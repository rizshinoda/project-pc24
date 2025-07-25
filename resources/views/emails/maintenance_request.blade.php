<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>
        Request SPK Alokasi Team & Perangkat @if($getMaintenance->onlineBilling)
        / Client {{ $getMaintenance->onlineBilling->pelanggan->nama_pelanggan ?? '-' }} {{ $getMaintenance->onlineBilling->nama_site ?? '' }}
        @endif
    </title>
</head>

<body style="font-family: Arial, sans-serif; font-size: 14px; color: #333; line-height: 1.4;">

    <p style="margin-bottom: 10px;">Dear Team PC24,</p>

    <p style="margin-bottom: 12px;">
        Mohon dibantu dibuatkan SPK, Alokasikan Team & Perangkat untuk hari
        {{ \Carbon\Carbon::parse($getMaintenance->tanggal_maintenance)->translatedFormat('l, d F Y') }} dengan detail sebagai berikut:
    </p>

    <table cellpadding="4" cellspacing="0" border="0"
        style="width: 100%; max-width: 700px; border-collapse: collapse; margin-left: 40px;">
        <tbody>

            @if ($billing = $getMaintenance->onlineBilling)
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
                <td><strong>Bandwidth</strong></td>
                <td>:</td>
                <td>{{ $billing->bandwidth ?? '-' }} {{ $billing->satuan ?? '' }}</td>
            </tr>
            @endif

            {{-- Perangkat Stock --}}
            @if ($detailBarang->isNotEmpty())
            <tr>
                <td style="vertical-align: top;"><strong>Perangkat Stock</strong></td>
                <td style="vertical-align: top;">:</td>
                <td style="padding: 4px; margin: 0px;">
                    @foreach ($detailBarang as $barang)
                    - {{ $barang->merek }} - {{ $barang->tipe }} ({{ $barang->kualitas }}), Jumlah: {{ $barang->jumlah }} Unit<br>
                    @endforeach
                </td>
            </tr>
            @endif

            {{-- Perangkat Non-Stock --}}
            @if (!empty($getMaintenance->non_stock))
            @php
            $nonStockItems = preg_split('/\r\n|\r|\n/', $getMaintenance->non_stock);
            @endphp
            <tr>
                <td style="vertical-align: top;"><strong>Perangkat Non-Stock</strong></td>
                <td style="vertical-align: top;">:</td>
                <td style="padding: 4px; margin: 0px;">
                    @foreach ($nonStockItems as $item)
                    - {{ trim($item) }}<br>
                    @endforeach
                </td>
            </tr>
            @endif



        </tbody>
    </table>

    <br>

    @if ($getMaintenance->keterangan)
    <p style="margin: 6px 0;"><strong>Note:</strong> {{ $getMaintenance->keterangan }}</p>
    @endif

    <p style="margin: 6px 0;">📎 <a href="{{ url('/ga/maintenance/' . $getMaintenance->id) }}">Lihat Detail Permintaan</a></p>

    <br>
    <p style="margin-top: 10px;">Warm regards,<br>
        {{ $getMaintenance->admin->name ?? 'User Pengaju' }}
    </p>

</body>

</html>