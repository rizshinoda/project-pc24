<!DOCTYPE html>
<html lang="en">

<head>
    @include('admin.partials.style')
</head>

<body>
    <div class="container-scroller">

        @include('admin.partials.navbar')
        <div class="container-fluid page-body-wrapper">
            <!-- partial:partials/_sidebar.html -->
            <nav class="sidebar sidebar-offcanvas" id="sidebar">
                <ul class="nav">
                    <li class="nav-item nav-profile">
                        <a href="#" class="nav-link">
                            <div class="nav-profile-image">
                                <img src="{{asset('/dist/assets/images/faces/2.png')}}" alt="profile" />
                                <span class="login-status online"></span>
                                <!--change to offline or busy as needed-->
                            </div>

                            <div class="nav-profile-text d-flex flex-column">
                                <span class="font-weight-bold mb-2">{{ Auth::user()->name }}</span>
                                <span class="text-secondary text-small">{{ $roleText }}</span>
                            </div>
                            <i class="mdi mdi-bookmark-check text-success nav-profile-badge"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.dashboard') }}">
                            <span class="menu-title">Dashboard</span>
                            <i class="mdi mdi-home menu-icon"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#ui-basic1" aria-expanded="false" aria-controls="ui-basic1">
                            <span class="menu-title">Tambah Data</span>
                            <i class="menu-arrow"></i>
                            <i class="mdi mdi-format-list-bulleted menu-icon"></i>
                        </a>
                        <div class="collapse" id="ui-basic1">
                            <ul class="nav flex-column sub-menu">
                                <li class="nav-item">
                                    <a class="nav-link" href="{{route('admin.pelanggan')}}">Data Pelanggan</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{route('admin.namavendor')}}">Data Vendor</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{route('admin.instansi')}}">Data Instansi</a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#ui-basic2" aria-expanded="false" aria-controls="ui-basic2">
                            <span class="menu-title">Work Order</span>
                            <i class="menu-arrow"></i>
                            <i class="mdi mdi-format-list-bulleted menu-icon"></i>
                        </a>
                        <div class="collapse" id="ui-basic2">
                            <ul class="nav flex-column sub-menu">
                                <li class="nav-item">
                                    <a class="nav-link" href="{{route('admin.survey')}}">Survey</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{route('admin.instalasi')}}">Instalasi</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{route('admin.upgrade')}}">Upgrade</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{route('admin.downgrade')}}">Downgrade</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{route('admin.dismantle')}}">Dismantle</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{route('admin.relokasi')}}">Relokasi</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{route('admin.gantivendor')}}">Ganti Vendor</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{route('admin.request_barang')}}">Request Barang</a>
                                </li>

                            </ul>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{route('admin.OB')}}">
                            <span class="menu-title">Online Billing</span>
                            <i class="mdi mdi-database-outline menu-icon"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.sitedismantle') }}">
                            <span class="menu-title">Site Dismantle</span>
                            <i class="mdi mdi-delete-circle menu-icon"></i>
                        </a>
                    </li>

            </nav>
            <!-- partial -->

            <!-- Main Panel -->
            <div class="main-panel">
                <div class="content-wrapper">
                    <div class="page-header">
                        <h3 class="page-title">
                            <span class="page-title-icon bg-gradient-danger text-white me-2">
                                <i class="mdi mdi-home"></i>
                            </span> Form
                        </h3>


                    </div>

                    <div class="col-lg-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="mb-5 text-center">Edit Instalasi</h4>
                                <!-- {{-- Menampilkan pesan error jika ada --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif -->

                                {{-- Form untuk membuat work order --}}
                                <form action="{{ route('admin.wo_instalasi_update', ['id' => $getInstall->id]) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT') {{-- Karena ini adalah form update, gunakan method PUT --}}

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label for="nama_site" class="col-sm-4 col-form-label">No SPK</label>
                                                <div class="col-sm-8">
                                                    <!-- Nomor SPK akan di-generate otomatis di server -->
                                                    <input type="text" name="no_spk" id="no_spk" class="form-control" value="{{ old('no_spk', $getInstall->no_spk) }}" readonly>
                                                </div>
                                            </div>
                                            <!-- Kolom pertama -->
                                            <div class="form-group row">
                                                <label for="pelanggan_id" class="col-sm-4 col-form-label">Pelanggan</label>
                                                <div class="col-sm-8">
                                                    <select class="form-control" id="pelanggan_id" name="pelanggan_id" required>
                                                        <option value="">Pilih Pelanggan</option>
                                                        @foreach ($pelanggans as $pelanggan)
                                                        <option value="{{ $pelanggan->id }}"
                                                            data-nama-gedung="{{ $pelanggan->nama_gedung }}"
                                                            data-alamat="{{ $pelanggan->alamat }}"
                                                            data-no-pelanggan="{{ $pelanggan->no_pelanggan }}"
                                                            data-foto="{{ asset('storage/pelanggan/' . $pelanggan->foto) }}"

                                                            {{ $getInstall->pelanggan_id == $pelanggan->id ? 'selected' : '' }}>
                                                            {{ $pelanggan->nama_pelanggan }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <!-- Alamat -->
                                            <div class="form-group row">
                                                <label for="alamat" class="col-sm-4 col-form-label">Alamat</label>
                                                <div class="col-sm-8">
                                                    <textarea class="form-control" id="alamat" name="alamat" rows="4" readonly>{{ $getInstall->pelanggan->alamat }}</textarea>
                                                </div>
                                            </div>

                                            <!-- No Pelanggan -->
                                            <div class="form-group row">
                                                <label for="no_pelanggan" class="col-sm-4 col-form-label">No Pelanggan</label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control" id="no_pelanggan" name="no_pelanggan" value="{{ $getInstall->pelanggan->no_pelanggan }}" readonly>
                                                </div>
                                            </div>
                                            <!-- Nama Gedung -->
                                            <div class="form-group row">
                                                <label for="nama_gedung" class="col-sm-4 col-form-label">Nama Gedung</label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control" id="nama_gedung" name="nama_gedung" value="{{ $getInstall->pelanggan->nama_gedung }}" readonly>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="alamat_pelanggan" class="col-sm-4 col-form-label">Layanan</label>
                                                <div class="col-sm-8">
                                                    <select class="form-control" id="layanan" name="layanan" required>
                                                        <option value="">Pilih Layanan</option>
                                                        <option value="-" {{ old('layanan', $getInstall->layanan) == '-' ? 'selected' : '' }}>-</option>
                                                        <option value="INTERNET" {{ old('layanan', $getInstall->layanan) == 'INTERNET' ? 'selected' : '' }}>INTERNET</option>
                                                        <option value="METRO" {{ old('layanan', $getInstall->layanan) == 'METRO' ? 'selected' : '' }}>METRO</option>
                                                        <option value="METRO-E" {{ old('layanan', $getInstall->layanan) == 'METRO-E' ? 'selected' : '' }}>METRO-E</option>
                                                        <option value="VPN" {{ old('layanan', $getInstall->layanan) == 'VPN' ? 'selected' : '' }}>VPN</option>
                                                        <option value="LOCALLOOP" {{ old('layanan', $getInstall->layanan) == 'LOCALLOOP' ? 'selected' : '' }}>LOCALLOOP</option>
                                                        <option value="INTERCONECTION" {{ old('layanan', $getInstall->layanan) == 'INTERCONECTION' ? 'selected' : '' }}>INTERCONECTION</option>
                                                        <option value="CROSSCONNECT" {{ old('layanan', $getInstall->layanan) == 'CROSSCONNECT' ? 'selected' : '' }}>CROSSCONNECT</option>
                                                        <option value="COLOCATION" {{ old('layanan', $getInstall->layanan) == 'COLOCATION' ? 'selected' : '' }}>COLOCATION</option>
                                                        <option value="INTERNET BROADBAND" {{ old('layanan', $getInstall->layanan) == 'INTERNET BROADBAND' ? 'selected' : '' }}>INTERNET BROADBAND</option>
                                                        <option value="INTERNET DEDICATED" {{ old('layanan', $getInstall->layanan) == 'INTERNET DEDICATED' ? 'selected' : '' }}>INTERNET DEDICATED</option>
                                                        <option value="INTERNET BROADBAND+IP" {{ old('layanan', $getInstall->layanan) == 'INTERNET BROADBAND+IP' ? 'selected' : '' }}>INTERNET BROADBAND+IP</option>

                                                        <option value="METRO - DARK FIBER" {{ old('layanan', $getInstall->layanan) == 'METRO - DARK FIBER' ? 'selected' : '' }}>METRO - DARK FIBER</option>
                                                        <option value="IP TRANSIT" {{ old('layanan', $getInstall->layanan) == 'IP TRANSIT' ? 'selected' : '' }}>IP TRANSIT</option>
                                                        <option value="METRO P2MP" {{ old('layanan', $getInstall->layanan) == 'METRO P2MP' ? 'selected' : '' }}>METRO P2MP</option>
                                                        <option value="DARK FIBER" {{ old('layanan', $getInstall->layanan) == 'DARK FIBER' ? 'selected' : '' }}>DARK FIBER</option>
                                                        <option value="Internet Kuota" {{ old('layanan', $getInstall->layanan) == 'Internet Kuota' ? 'selected' : '' }}>Internet Kuota</option>
                                                        <option value="CCTV" {{ old('layanan', $getInstall->layanan) == 'CCTV' ? 'selected' : '' }}>CCTV</option>

                                                    </select>
                                                </div>
                                            </div>
                                            <!-- Edit Bandwidth dan Satuan -->
                                            <div class="form-group row">
                                                <label for="bandwidth" class="col-sm-4 col-form-label">Volume</label>

                                                <!-- Input Bandwidth -->
                                                <div class="col-sm-4">
                                                    <input type="number"
                                                        name="bandwidth"
                                                        id="bandwidth"
                                                        class="form-control"
                                                        min="1"
                                                        value="{{ old('bandwidth', $getInstall->bandwidth) }}"
                                                        required>
                                                </div>

                                                <!-- Input Satuan -->
                                                <div class="col-sm-4">
                                                    <select class="form-control" id="satuan" name="satuan" required>
                                                        <option value="" disabled>Pilih Satuan</option>
                                                        <option value="Gbps" {{ old('satuan', $getInstall->satuan) == 'Gbps' ? 'selected' : '' }}>Gbps</option>
                                                        <option value="Mbps" {{ old('satuan', $getInstall->satuan) == 'Mbps' ? 'selected' : '' }}>Mbps</option>
                                                        <option value="Kbps" {{ old('satuan', $getInstall->satuan) == 'Kbps' ? 'selected' : '' }}>Kbps</option>
                                                        <option value="RU(RACK UNIT)" {{ old('satuan', $getInstall->satuan) == 'RU(RACK UNIT)' ? 'selected' : '' }}>RU(RACK UNIT)</option>
                                                        <option value="CORE" {{ old('satuan', $getInstall->satuan) == 'CORE' ? 'selected' : '' }}>CORE</option>
                                                        <option value="PAIR" {{ old('satuan', $getInstall->satuan) == 'PAIR' ? 'selected' : '' }}>PAIR</option>

                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label for="alamat_pelanggan" class="col-sm-4 col-form-label">NNI</label>
                                                <div class="col-sm-8">
                                                    <select class="form-control" id="nni" name="nni">
                                                        <option value="">Pilih NNI</option>
                                                        <option value="-" {{ old('nni', $getInstall->nni) == '-' ? 'selected' : '' }}>-</option>
                                                        <option value="SURABAYA" {{ old('nni', $getInstall->nni) == 'SURABAYA' ? 'selected' : '' }}>SURABAYA</option>
                                                        <option value="YOGYAKARTA" {{ old('nni', $getInstall->nni) == 'YOGYAKARTA' ? 'selected' : '' }}>YOGYAKARTA</option>
                                                        <option value="TBS" {{ old('nni', $getInstall->nni) == 'TBS' ? 'selected' : '' }}>TBS</option>
                                                        <option value="NONE" {{ old('nni', $getInstall->nni) == 'NONE' ? 'selected' : '' }}>NONE</option>
                                                        <option value="IDC3" {{ old('nni', $getInstall->nni) == 'IDC3' ? 'selected' : '' }}>IDC3</option>
                                                        <option value="MEDAN" {{ old('nni', $getInstall->nni) == 'MEDAN' ? 'selected' : '' }}>MEDAN</option>
                                                        <option value="BALI" {{ old('nni', $getInstall->nni) == 'BALI' ? 'selected' : '' }}>BALI</option>
                                                        <option value="MAKASSAR" {{ old('nni', $getInstall->nni) == 'MAKASSAR' ? 'selected' : '' }}>MAKASSAR</option>
                                                        <option value="IDC BARU" {{ old('nni', $getInstall->nni) == 'IDC BARU' ? 'selected' : '' }}>IDC BARU</option>
                                                        <option value="INDOSAT" {{ old('nni', $getInstall->nni) == 'INDOSAT' ? 'selected' : '' }}>INDOSAT</option>
                                                        <option value="SANATEL" {{ old('nni', $getInstall->nni) == 'SANATEL' ? 'selected' : '' }}>SANATEL</option>
                                                        <option value="CYBER 1" {{ old('nni', $getInstall->nni) == 'CYBER 1' ? 'selected' : '' }}>CYBER 1</option>
                                                        <option value="WATSON" {{ old('nni', $getInstall->nni) == 'WATSON' ? 'selected' : '' }}>WATSON</option>
                                                        <option value="DISTRIBUSI 1" {{ old('nni', $getInstall->nni) == 'DISTRIBUSI 1' ? 'selected' : '' }}>DISTRIBUSI 1</option>
                                                        <option value="BALIKPAPAN" {{ old('nni', $getInstall->nni) == 'BALIKPAPAN' ? 'selected' : '' }}>BALIKPAPAN</option>
                                                        <option value="BATAM" {{ old('nni', $getInstall->nni) == 'BATAM' ? 'selected' : '' }}>BATAM</option>
                                                        <option value="PONTIANAK" {{ old('nni', $getInstall->nni) == 'PONTIANAK' ? 'selected' : '' }}>PONTIANAK</option>
                                                        <option value="BANDUNG" {{ old('nni', $getInstall->nni) == 'BANDUNG' ? 'selected' : '' }}>BANDUNG</option>
                                                        <option value="MUP" {{ old('nni', $getInstall->nni) == 'MUP' ? 'selected' : '' }}>MUP</option>
                                                        <option value="MPPA" {{ old('nni', $getInstall->nni) == 'MPPA' ? 'selected' : '' }}>MPPA</option>
                                                        <option value="GASNET" {{ old('nni', $getInstall->nni) == 'GASNET' ? 'selected' : '' }}>GASNET</option>
                                                        <option value="DISTRIBUSI 2" {{ old('nni', $getInstall->nni) == 'DISTRIBUSI 2' ? 'selected' : '' }}DISTRIBUSI 2</option>
                                                        <option value="ICON-JKT" {{ old('nni', $getInstall->nni) == 'ICON-JKT' ? 'selected' : '' }}>ICON-JKT</option>
                                                        <option value="PALAPA" {{ old('nni', $getInstall->nni) == 'PALAPA' ? 'selected' : '' }}>PALAPA</option>
                                                        <option value="CHAROEN" {{ old('nni', $getInstall->nni) == 'CHAROEN' ? 'selected' : '' }}>CHAROEN</option>

                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="vendor_id" class="col-sm-4 col-form-label">Nama Vendor</label>
                                                <div class="col-sm-8">
                                                    <select class="form-control" id="vendor_id" name="vendor_id" required>
                                                        <option value="">Pilih Vendor</option>
                                                        @foreach ($vendors as $vendor)
                                                        <option value="{{ $vendor->id }}"
                                                            {{ $getInstall->vendor_id == $vendor->id ? 'selected' : '' }}>
                                                            {{ $vendor->nama_vendor }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Edit Durasi -->
                                            <div class="form-group row">
                                                <label for="durasi" class="col-sm-4 col-form-label">Durasi</label>
                                                <div class="col-sm-4">
                                                    <input type="number"
                                                        name="durasi"
                                                        id="durasi"
                                                        class="form-control"
                                                        min="1"
                                                        value="{{ old('durasi', $getInstall->durasi) }}"
                                                        required>
                                                </div>
                                                <div class="col-sm-4">
                                                    <select name="nama_durasi" id="nama_durasi" class="form-control" required>
                                                        <option value="" disabled>Pilih Satuan Durasi</option>
                                                        <option value="hari" {{ old('nama_durasi', $getInstall->nama_durasi) == 'hari' ? 'selected' : '' }}>Hari</option>
                                                        <option value="bulan" {{ old('nama_durasi', $getInstall->nama_durasi) == 'bulan' ? 'selected' : '' }}>Bulan</option>
                                                        <option value="tahun" {{ old('nama_durasi', $getInstall->nama_durasi) == 'tahun' ? 'selected' : '' }}>Tahun</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <!-- Edit Harga Sewa -->
                                            <div class="form-group row">
                                                <label for="harga_sewa" class="col-sm-4 col-form-label">Harga Sewa</label>
                                                <div class="col-sm-8">
                                                    <input type="text"
                                                        name="harga_sewa"
                                                        id="harga_sewa"
                                                        class="form-control"
                                                        value="{{ old('harga_sewa', number_format($getInstall->harga_sewa, 0, ',', '.')) }}"
                                                        onkeyup="formatRupiah(this)"
                                                        required>
                                                    <!-- Hidden input untuk angka murni -->
                                                    <input type="hidden" name="harga_sewa_hidden" id="harga_sewa_hidden" value="{{ old('harga_sewa_hidden', $getInstall->harga_sewa) }}">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="foto" class="col-sm-4 col-form-label">Foto</label>
                                                <div class="col-sm-8">

                                                    <!-- Cek apakah pelanggan terkait memiliki foto -->
                                                    @if($getInstall->pelanggan && $getInstall->pelanggan->foto)
                                                    <div class="mt-3">
                                                        <img src="{{ asset('storage/pelanggan/' . $getInstall->pelanggan->foto) }}" alt="Foto Pelanggan" class="img-fluid" width="200">
                                                    </div>
                                                    @else
                                                    <p class="mt-3">Belum ada foto pelanggan yang diupload.</p>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="jenis_id">Jenis Barang</label>
                                                <div class="col-sm-8">
                                                    <select id="jenis_id" name="jenis_id" class="form-control">
                                                        <option value="">Pilih Jenis Barang</option>
                                                        @foreach ($jenisList as $jenis)
                                                        <option value="{{ $jenis->id }}" {{ $jenis->id == $getInstall->jenis_id ? 'selected' : '' }}>{{ $jenis->nama_jenis }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label>Data Barang</label>
                                                <div class="col-sm-8">
                                                    <input type="text" id="search" class="form-control" />
                                                    <table class="table table-bordered mt-2" id="stock-table">
                                                        <thead>
                                                            <tr>
                                                                <th style="text-align: center;">Merek</th>
                                                                <th style="text-align: center;">Tipe</th>
                                                                <th style="text-align: center;">Jumlah Tersedia</th>
                                                                <th style="text-align: center;">Kualitas</th>
                                                                <th style="text-align: center;">Jumlah Request</th>
                                                                <th style="text-align: center;">Aksi</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="stock-table-body">
                                                            @foreach ($stockBarangs as $stock)
                                                            <tr data-jenis-id="{{ $stock->jenis_id }}">
                                                                <td style="text-align: center;">{{ $stock->merek->nama_merek }}</td>
                                                                <td style="text-align: center;">{{ $stock->tipe->nama_tipe }}</td>
                                                                <td style="text-align: center;">{{ $stock->total_jumlah }}</td>
                                                                <td style="text-align: center;">{{ $stock->kualitas }}</td>
                                                                <td style="text-align: center;">
                                                                    <input type="number" min="0" max="{{ $stock->total_jumlah }}" value="0" class="form-control quantity" />
                                                                </td>
                                                                <td style="text-align: center;">
                                                                    <button type="button" class="btn btn-success add-to-cart" data-id="{{ $stock->id }}" data-merek="{{ $stock->merek->nama_merek }}" data-tipe="{{ $stock->tipe->nama_tipe }}" data-jumlah="{{ $stock->total_jumlah }}" data-kualitas="{{ $stock->kualitas }}">
                                                                        <i class="fa fa-plus"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label>Keranjang</label>
                                                <div class="col-sm-8">
                                                    <table class="table table-bordered mt-2" id="cart-table">
                                                        <thead>
                                                            <tr>
                                                                <th style="text-align: center;">Merek</th>
                                                                <th style="text-align: center;">Tipe</th>
                                                                <th style="text-align: center;">Jumlah</th>
                                                                <th style="text-align: center;">Kualitas</th>
                                                                <th style="text-align: center;">Aksi</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="cart-table-body">

                                                            @foreach ($WorkOrderInstallDetail as $detail)
                                                            <tr data-unique-key="{{ $detail->id }}">
                                                                <td style="text-align: center;">{{ $detail->merek }}</td>
                                                                <td style="text-align: center;">{{ $detail->tipe }}</td>
                                                                <td style="text-align: center;">
                                                                    <input type="number"
                                                                        name="cart[{{ $detail->id }}][total_jumlah]"
                                                                        value="{{ $detail->jumlah }}"
                                                                        class="form-control cart-quantity"
                                                                        min="1"
                                                                        max="{{ $detail->stock_max }}"
                                                                        style="width: 80px; text-align: center;" />
                                                                </td>
                                                                <td style="text-align: center;">{{ $detail->kualitas }}</td>
                                                                <td style="text-align: center;">
                                                                    <button type="button" class="btn btn-danger remove-from-cart" data-id="{{ $detail->id }}">
                                                                        <i class="fa fa-close"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                            @endforeach

                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <!-- Kolom kedua -->
                                            <div class="form-group row">
                                                <label for="instansi_id" class="col-sm-4 col-form-label">Nama Instansi</label>
                                                <div class="col-sm-8">
                                                    <select class="form-control" id="instansi_id" name="instansi_id" required>
                                                        <option value="">Pilih Instansi</option>
                                                        @foreach ($instansis as $instansi)
                                                        <option value="{{ $instansi->id }}"
                                                            {{ $getInstall->instansi_id == $instansi->id ? 'selected' : '' }}>
                                                            {{ $instansi->nama_instansi }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label for="no_pelanggan" class="col-sm-4 col-form-label">Nama Site</label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control" id="nama_site" name="nama_site" value="{{ old('nama_site', $getInstall->nama_site) }}" required>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="no_pelanggan" class="col-sm-4 col-form-label">Alamat Pemasangan</label>
                                                <div class="col-sm-8">
                                                    <textarea class="form-control" id="alamat_pemasangan" name="alamat_pemasangan" rows="4" required>{{ $getInstall->alamat_pemasangan}}</textarea>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="provinsi" class="col-sm-4 col-form-label">Nama Provinsi</label>
                                                <div class="col-sm-8">
                                                    <select class="form-control" id="provinsi" name="provinsi" required>
                                                        <option value="">Pilih Provinsi</option>
                                                        <option value="Aceh" {{ $getInstall->provinsi == 'Aceh' ? 'selected' : '' }}>Aceh</option>
                                                        <option value="Sumatera Utara" {{ $getInstall->provinsi == 'Sumatera Utara' ? 'selected' : '' }}>Sumatera Utara</option>
                                                        <option value="Sumatera Barat" {{ $getInstall->provinsi == 'Sumatera Barat' ? 'selected' : '' }}>Sumatera Barat</option>
                                                        <option value="Riau" {{ $getInstall->provinsi == 'Riau' ? 'selected' : '' }}>Riau</option>
                                                        <option value="Kepulauan Riau" {{ $getInstall->provinsi == 'Kepulauan Riau' ? 'selected' : '' }}>Kepulauan Riau</option>
                                                        <option value="Jambi" {{ $getInstall->provinsi == 'Jambi' ? 'selected' : '' }}>Jambi</option>
                                                        <option value="Sumatera Selatan" {{ $getInstall->provinsi == 'Sumatera Selatan' ? 'selected' : '' }}>Sumatera Selatan</option>
                                                        <option value="Bangka Belitung" {{ $getInstall->provinsi == 'Bangka Belitung' ? 'selected' : '' }}>Bangka Belitung</option>
                                                        <option value="Bengkulu" {{ $getInstall->provinsi == 'Bengkulu' ? 'selected' : '' }}>Bengkulu</option>
                                                        <option value="Lampung" {{ $getInstall->provinsi == 'Lampung' ? 'selected' : '' }}>Lampung</option>
                                                        <option value="DKI Jakarta" {{ $getInstall->provinsi == 'DKI Jakarta' ? 'selected' : '' }}>DKI Jakarta</option>
                                                        <option value="Jawa Barat" {{ $getInstall->provinsi == 'Jawa Barat' ? 'selected' : '' }}>Jawa Barat</option>
                                                        <option value="Jawa Tengah" {{ $getInstall->provinsi == 'Jawa Tengah' ? 'selected' : '' }}>Jawa Tengah</option>
                                                        <option value="DI Yogyakarta" {{ $getInstall->provinsi == 'DI Yogyakarta' ? 'selected' : '' }}>DI Yogyakarta</option>
                                                        <option value="Jawa Timur" {{ $getInstall->provinsi == 'Jawa Timur' ? 'selected' : '' }}>Jawa Timur</option>
                                                        <option value="Bali" {{ $getInstall->provinsi == 'Bali' ? 'selected' : '' }}>Bali</option>
                                                        <option value="Nusa Tenggara Barat" {{ $getInstall->provinsi == 'Nusa Tenggara Barat' ? 'selected' : '' }}>Nusa Tenggara Barat</option>
                                                        <option value="Nusa Tenggara Timur" {{ $getInstall->provinsi == 'Nusa Tenggara Timur' ? 'selected' : '' }}>Nusa Tenggara Timur</option>
                                                        <option value="Kalimantan Barat" {{ $getInstall->provinsi == 'Kalimantan Barat' ? 'selected' : '' }}>Kalimantan Barat</option>
                                                        <option value="Kalimantan Tengah" {{ $getInstall->provinsi == 'Kalimantan Tengah' ? 'selected' : '' }}>Kalimantan Tengah</option>
                                                        <option value="Kalimantan Selatan" {{ $getInstall->provinsi == 'Kalimantan Selatan' ? 'selected' : '' }}>Kalimantan Selatan</option>
                                                        <option value="Kalimantan Timur" {{ $getInstall->provinsi == 'Kalimantan Timur' ? 'selected' : '' }}>Kalimantan Timur</option>
                                                        <option value="Kalimantan Utara" {{ $getInstall->provinsi == 'Kalimantan Utara' ? 'selected' : '' }}>Kalimantan Utara</option>
                                                        <option value="Sulawesi Utara" {{ $getInstall->provinsi == 'Sulawesi Utara' ? 'selected' : '' }}>Sulawesi Utara</option>
                                                        <option value="Gorontalo" {{ $getInstall->provinsi == 'Gorontalo' ? 'selected' : '' }}>Gorontalo</option>
                                                        <option value="Sulawesi Tengah" {{ $getInstall->provinsi == 'Sulawesi Tengah' ? 'selected' : '' }}>Sulawesi Tengah</option>
                                                        <option value="Sulawesi Barat" {{ $getInstall->provinsi == 'Sulawesi Barat' ? 'selected' : '' }}>Sulawesi Barat</option>
                                                        <option value="Sulawesi Selatan" {{ $getInstall->provinsi == 'Sulawesi Selatan' ? 'selected' : '' }}>Sulawesi Selatan</option>
                                                        <option value="Sulawesi Tenggara" {{ $getInstall->provinsi == 'Sulawesi Tenggara' ? 'selected' : '' }}>Sulawesi Tenggara</option>
                                                        <option value="Maluku" {{ $getInstall->provinsi == 'Maluku' ? 'selected' : '' }}>Maluku</option>
                                                        <option value="Maluku Utara" {{ $getInstall->provinsi == 'Maluku Utara' ? 'selected' : '' }}>Maluku Utara</option>
                                                        <option value="Papua" {{ $getInstall->provinsi == 'Papua' ? 'selected' : '' }}>Papua</option>
                                                        <option value="Papua Barat" {{ $getInstall->provinsi == 'Papua Barat' ? 'selected' : '' }}>Papua Barat</option>
                                                        <option value="Papua Tengah" {{ $getInstall->provinsi == 'Papua Tengah' ? 'selected' : '' }}>Papua Tengah</option>
                                                        <option value="Papua Pegunungan" {{ $getInstall->provinsi == 'Papua Pegunungan' ? 'selected' : '' }}>Papua Pegunungan</option>
                                                        <option value="Papua Selatan" {{ $getInstall->provinsi == 'Papua Selatan' ? 'selected' : '' }}>Papua Selatan</option>
                                                        <option value="Papua Barat Daya" {{ $getInstall->provinsi == 'Papua Barat Daya' ? 'selected' : '' }}>Papua Barat Daya</option>

                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="no_pelanggan" class="col-sm-4 col-form-label">Nama PIC</label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control" id="nama_pic" name="nama_pic" value="{{ old('nama_pic', $getInstall->nama_pic) }}">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="no_pelanggan" class="col-sm-4 col-form-label">Nomer PIC</label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control" id="no_pic" name="no_pic" value="{{ old('no_pic', $getInstall->no_pic) }}">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="alamat_pelanggan" class="col-sm-4 col-form-label">Media</label>
                                                <div class="col-sm-8">
                                                    <select class="form-control" id="media" name="media" required>
                                                        <option value="">Pilih Media</option>
                                                        <option value="FIBER OPTIC" {{ old('media', $getInstall->media) == 'FIBER OPTIC' ? 'selected' : '' }}>FIBER OPTIC</option>
                                                        <option value="WIRELESS" {{ old('media', $getInstall->media) == 'WIRELESS' ? 'selected' : '' }}>WIRELESS</option>
                                                        <option value="M2M" {{ old('media', $getInstall->media) == 'M2M' ? 'selected' : '' }}>M2M</option>
                                                        <option value="NONE" {{ old('media', $getInstall->media) == 'NONE' ? 'selected' : '' }}>NONE</option>
                                                        <option value="STARLINK" {{ old('media', $getInstall->media) == 'STARLINK' ? 'selected' : '' }}>STARLINK</option>

                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label for="no_pelanggan" class="col-sm-4 col-form-label">Vlan</label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control" id="vlan" name="vlan" value="{{ old('vlan', $getInstall->vlan) }}">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="no_pelanggan" class="col-sm-4 col-form-label">No Jaringan</label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control" id="no_jaringan" name="no_jaringan" value="{{ old('no_jaringan', $getInstall->no_jaringan) }}">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="no_pelanggan" class="col-sm-4 col-form-label">Tanggal RFS</label>
                                                <div class="col-sm-8">
                                                    <input type="date" class="form-control" id="tanggal_rfs" name="tanggal_rfs" value="{{ old('tanggal_rfs', $getInstall->tanggal_rfs) }}" required>
                                                </div>
                                            </div>
                                            <!-- Edit Harga Instalasi -->
                                            <div class="form-group row">
                                                <label for="harga_instalasi" class="col-sm-4 col-form-label">Harga Instalasi</label>
                                                <div class="col-sm-8">
                                                    <input type="text"
                                                        name="harga_instalasi"
                                                        id="harga_instalasi"
                                                        class="form-control"
                                                        value="{{ old('harga_instalasi', number_format($getInstall->harga_instalasi, 0, ',', '.')) }}"
                                                        onkeyup="formatRupiah(this)"
                                                        required>
                                                    <!-- Hidden input untuk angka murni -->
                                                    <input type="hidden" name="harga_instalasi_hidden" id="harga_instalasi_hidden" value="{{ old('harga_instalasi_hidden', $getInstall->harga_instalasi) }}">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="keterangan" class="col-sm-4 col-form-label">Keterangan</label>
                                                <div class="col-sm-8">
                                                    <textarea name="keterangan" id="keterangan" class="form-control" rows="4">{{ $getInstall->keterangan }}</textarea>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="non_stock" class="col-sm-4 col-form-label">Input Barang Non Stock</label>
                                                <div class="col-sm-8">
                                                    <textarea id="non_stock" name="non_stock" class="form-control" rows="4">{{ old('non_stock', $getInstall->non_stock) }}</textarea>
                                                </div>
                                            </div>
                                        </div>


                                    </div>
                                    <br>
                                    <div class="text-center">
                                        <!-- Tombol submit -->
                                        <button type="submit" class="btn btn-info">Edit</button>
                                        <a href="{{ route('admin.instalasi') }}" class="btn btn-light">Kembali</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <!-- main-panel ends -->
                    </div>
                </div>
                <footer class="footer">
                    <div class="d-sm-flex justify-content-center justify-content-sm-between">
                        <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Copyright © 2024</a>. All rights reserved.</span>
                        <span class="text-muted float-none float-sm-right d-block mt-1 mt-sm-0 text-center">Hand-crafted & made with Rizal<i class="mdi mdi-heart text-danger"></i></span>
                    </div>
                </footer>
                <!-- partial -->
            </div>
            <!-- main-panel ends -->
        </div>

    </div>
    @include('admin.partials.script')
</body>

</html>

</html>

</html>