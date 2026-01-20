<!DOCTYPE html>
<html>

<head>
    <title>Form Serah Terima Barang</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }

        .header {
            text-align: center;
            position: relative;
        }

        .logo {
            position: absolute;
            left: 0;
            top: 0;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
        }

        .line {
            border-top: 3px solid #000;
            margin: 10px 0 20px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
        }

        .no-border td {
            border: none;
            padding: 4px;
            text-align: left;
        }

        .section-title {
            font-weight: bold;
            margin: 15px 0 5px;
        }

        .ttd td {
            height: 80px;
            vertical-align: bottom;
            font-weight: bold;
        }
    </style>
</head>

<body onload="window.print()">

    {{-- HEADER --}}
    <div class="header">
        <img src="{{ asset('dist/assets/images/images.jpeg') }}" class="logo" width="200">
        <div class="company-name">PC24 TELEKOMUNIKASI</div>
        <div>Kebayoran Center, Telp 08232230302</div>
        <div>pc24@gmail.com</div>
    </div>

    <div class="line"></div>

    {{-- FORM REQUEST --}}
    <div class="section-title">FORM REQUEST</div>

    <table class="no-border">
        <tr>
            <td width="60%">
                No Jaringan : {{ $getMaintenance->onlineBilling->no_jaringan ?? '-' }}<br>
                Nama Site : {{ $getMaintenance->onlineBilling->nama_site ?? '-' }}<br>
                Alamat : {{ $getMaintenance->alamat_penerima }}<br>
                VLAN : -
            </td>
            <td width="40%" style="text-align:right;">
                Tanggal :
                {{ now()->translatedFormat('l, d F Y, H:i') }} WIB
            </td>
        </tr>
    </table>

    {{-- DAFTAR BARANG --}}
    <div class="section-title">DAFTAR BARANG</div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Nama dan Tipe Perangkat</th>
                <th>Serial Number</th>
                <th>Jumlah</th>

                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($getMaintenance->barangKeluar as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                    {{ $item->stockBarang->jenis->nama_jenis }}
                    {{ $item->stockBarang->merek->nama_merek }}
                    {{ $item->stockBarang->tipe->nama_tipe }}
                </td>
                <td>{{ $item->serial_number }}</td>
                <td>{{ $item->jumlah }}</td>
                <td>Baru</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- TANDA TANGAN --}}
    <table class="ttd" style="margin-top:30px;">
        <tr>
            <th>Dibuat Oleh</th>
            <th>Penerima Barang</th>
            <th>Gudang</th>
            <th>K. Gudang</th>
            <th>Approval</th>
        </tr>
        <tr>
            <td>{{ $getMaintenance->admin->name }}</td>
            <td>.....................</td>
            <td>KUSMADI / MANDONG</td>
            <td>SAIFUL ANWAR</td>
            <td>NOVAL ISMAIL</td>
        </tr>
    </table>

</body>

</html>