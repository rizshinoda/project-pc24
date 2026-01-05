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
                                <h4 class="mb-5 text-center">Form Tambah Instalasi</h4>
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
                                <form action="{{route('admin.survey.storeprogresinstall',$getSurvey->id)}}" method="POST" enctype="multipart/form-data">
                                    @csrf

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label for="nama_site" class="col-sm-4 col-form-label">No SPK</label>
                                                <div class="col-sm-8">
                                                    <!-- Nomor SPK akan di-generate otomatis di server -->
                                                    <input type="text" class="form-control" id="no_spk" name="no_spk" value="{{ $no_spk }}" readonly>

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

                                                            {{ $getSurvey->pelanggan_id == $pelanggan->id ? 'selected' : '' }}>
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
                                                    <textarea class="form-control" id="alamat" name="alamat" rows="4" readonly>{{ $getSurvey->pelanggan->alamat }}</textarea>
                                                </div>
                                            </div>

                                            <!-- No Pelanggan -->
                                            <div class="form-group row">
                                                <label for="no_pelanggan" class="col-sm-4 col-form-label">No Pelanggan</label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control" id="no_pelanggan" name="no_pelanggan" value="{{ $getSurvey->pelanggan->no_pelanggan }}" readonly>
                                                </div>
                                            </div>
                                            <!-- Nama Gedung -->
                                            <div class="form-group row">
                                                <label for="nama_gedung" class="col-sm-4 col-form-label">Nama Gedung</label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control" id="nama_gedung" name="nama_gedung" value="{{ $getSurvey->pelanggan->nama_gedung }}" readonly>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="alamat_pelanggan" class="col-sm-4 col-form-label">Layanan</label>
                                                <div class="col-sm-8">
                                                    <select class="form-control" id="layanan" name="layanan" required>
                                                        <option value="">Pilih Layanan</option>
                                                        <option value="-" {{ old('layanan', $getSurvey->layanan) == '-' ? 'selected' : '' }}>-</option>
                                                        <option value="INTERNET" {{ old('layanan', $getSurvey->layanan) == 'INTERNET' ? 'selected' : '' }}>INTERNET</option>
                                                        <option value="METRO" {{ old('layanan', $getSurvey->layanan) == 'METRO' ? 'selected' : '' }}>METRO</option>
                                                        <option value="METRO-E" {{ old('layanan', $getSurvey->layanan) == 'METRO-E' ? 'selected' : '' }}>METRO-E</option>
                                                        <option value="VPN" {{ old('layanan', $getSurvey->layanan) == 'VPN' ? 'selected' : '' }}>VPN</option>
                                                        <option value="LOCAL LOOP" {{ old('layanan', $getSurvey->layanan) == 'LOCAL LOOP' ? 'selected' : '' }}>LOCAL LOOP</option>
                                                        <option value="INTERCONECTION" {{ old('layanan', $getSurvey->layanan) == 'INTERCONECTION' ? 'selected' : '' }}>INTERCONECTION</option>
                                                        <option value="CROSSCONNECT" {{ old('layanan', $getSurvey->layanan) == 'CROSSCONNECT' ? 'selected' : '' }}>CROSSCONNECT</option>
                                                        <option value="COLOCATION" {{ old('layanan', $getSurvey->layanan) == 'COLOCATION' ? 'selected' : '' }}>COLOCATION</option>
                                                        <option value="INTERNET BROADBAND" {{ old('layanan', $getSurvey->layanan) == 'INTERNET BROADBAND' ? 'selected' : '' }}>INTERNET BROADBAND</option>
                                                        <option value="DEDICATED" {{ old('layanan', $getSurvey->layanan) == 'DEDICATED' ? 'selected' : '' }}>DEDICATED</option>
                                                        <option value="METRO - DARK FIBER" {{ old('layanan', $getSurvey->layanan) == 'METRO - DARK FIBER' ? 'selected' : '' }}>METRO - DARK FIBER</option>
                                                        <option value="IP TRANSIT" {{ old('layanan', $getSurvey->layanan) == 'IP TRANSIT' ? 'selected' : '' }}>IP TRANSIT</option>
                                                        <option value="INTERNET DEDICATED" {{ old('layanan', $getSurvey->layanan) == 'INTERNET DEDICATED' ? 'selected' : '' }}>INTERNET DEDICATED</option>
                                                        <option value="METRO P2MP" {{ old('layanan', $getSurvey->layanan) == 'METRO P2MP' ? 'selected' : '' }}>METRO P2MP</option>
                                                        <option value="DARK FIBER" {{ old('layanan', $getSurvey->layanan) == 'DARK FIBER' ? 'selected' : '' }}>DARK FIBER</option>
                                                        <option value="Internet Kuota" {{ old('layanan', $getSurvey->layanan) == 'Internet Kuota' ? 'selected' : '' }}>Internet Kuota</option>
                                                        <option value="CCTV" {{ old('layanan', $getSurvey->layanan) == 'CCTV' ? 'selected' : '' }}>CCTV</option>

                                                    </select>
                                                </div>
                                            </div>
                                            <!-- Edit Bandwidth dan Satuan -->
                                            <div class="form-group row">
                                                <label for="bandwidth" class="col-sm-4 col-form-label">Bandwidth</label>

                                                <!-- Input Bandwidth -->
                                                <div class="col-sm-4">
                                                    <input type="number"
                                                        name="bandwidth"
                                                        id="bandwidth"
                                                        class="form-control"
                                                        min="1"
                                                        value="{{ old('bandwidth', $getSurvey->bandwidth) }}"
                                                        required>
                                                </div>

                                                <!-- Input Satuan -->
                                                <div class="col-sm-4">
                                                    <select class="form-control" id="satuan" name="satuan" required>
                                                        <option value="" disabled>Pilih Satuan</option>
                                                        <option value="Gbps" {{ old('satuan', $getSurvey->satuan) == 'Gbps' ? 'selected' : '' }}>Gbps</option>
                                                        <option value="Mbps" {{ old('satuan', $getSurvey->satuan) == 'Mbps' ? 'selected' : '' }}>Mbps</option>
                                                        <option value="Kbps" {{ old('satuan', $getSurvey->satuan) == 'Kbps' ? 'selected' : '' }}>Kbps</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label for="alamat_pelanggan" class="col-sm-4 col-form-label">NNI</label>
                                                <div class="col-sm-8">
                                                    <select class="form-control" id="nni" name="nni">
                                                        <option value="">Pilih NNI</option>
                                                        <option value="-" {{ old('nni', $getSurvey->nni) == '-' ? 'selected' : '' }}>-</option>
                                                        <option value="SURABAYA" {{ old('nni', $getSurvey->nni) == 'SURABAYA' ? 'selected' : '' }}>SURABAYA</option>
                                                        <option value="YOGYAKARTA" {{ old('nni', $getSurvey->nni) == 'YOGYAKARTA' ? 'selected' : '' }}>YOGYAKARTA</option>
                                                        <option value="TBS" {{ old('nni', $getSurvey->nni) == 'TBS' ? 'selected' : '' }}>TBS</option>
                                                        <option value="NONE" {{ old('nni', $getSurvey->nni) == 'NONE' ? 'selected' : '' }}>NONE</option>
                                                        <option value="IDC3" {{ old('nni', $getSurvey->nni) == 'IDC3' ? 'selected' : '' }}>IDC3</option>
                                                        <option value="MEDAN" {{ old('nni', $getSurvey->nni) == 'MEDAN' ? 'selected' : '' }}>MEDAN</option>
                                                        <option value="BALI" {{ old('nni', $getSurvey->nni) == 'BALI' ? 'selected' : '' }}>BALI</option>
                                                        <option value="MAKASSAR" {{ old('nni', $getSurvey->nni) == 'MAKASSAR' ? 'selected' : '' }}>MAKASSAR</option>
                                                        <option value="IDC BARU" {{ old('nni', $getSurvey->nni) == 'IDC BARU' ? 'selected' : '' }}>IDC BARU</option>
                                                        <option value="INDOSAT" {{ old('nni', $getSurvey->nni) == 'INDOSAT' ? 'selected' : '' }}>INDOSAT</option>
                                                        <option value="SANATEL" {{ old('nni', $getSurvey->nni) == 'SANATEL' ? 'selected' : '' }}>SANATEL</option>
                                                        <option value="CYBER 1" {{ old('nni', $getSurvey->nni) == 'CYBER 1' ? 'selected' : '' }}>CYBER 1</option>
                                                        <option value="WATSON" {{ old('nni', $getSurvey->nni) == 'WATSON' ? 'selected' : '' }}>WATSON</option>
                                                        <option value="DISTRIBUSI 1" {{ old('nni', $getSurvey->nni) == 'DISTRIBUSI 1' ? 'selected' : '' }}>DISTRIBUSI 1</option>
                                                        <option value="BALIKPAPAN" {{ old('nni', $getSurvey->nni) == 'BALIKPAPAN' ? 'selected' : '' }}>BALIKPAPAN</option>
                                                        <option value="BATAM" {{ old('nni', $getSurvey->nni) == 'BATAM' ? 'selected' : '' }}>BATAM</option>
                                                        <option value="PONTIANAK" {{ old('nni', $getSurvey->nni) == 'PONTIANAK' ? 'selected' : '' }}>PONTIANAK</option>
                                                        <option value="BANDUNG" {{ old('nni', $getSurvey->nni) == 'BANDUNG' ? 'selected' : '' }}>BANDUNG</option>
                                                        <option value="MUP" {{ old('nni', $getSurvey->nni) == 'MUP' ? 'selected' : '' }}>MUP</option>
                                                        <option value="MPPA" {{ old('nni', $getSurvey->nni) == 'MPPA' ? 'selected' : '' }}>MPPA</option>
                                                        <option value="GASNET" {{ old('nni', $getSurvey->nni) == 'GASNET' ? 'selected' : '' }}>GASNET</option>
                                                        <option value="DISTRIBUSI 2" {{ old('nni', $getSurvey->nni) == 'DISTRIBUSI 2' ? 'selected' : '' }}DISTRIBUSI 2</option>
                                                        <option value="ICON-JKT" {{ old('nni', $getSurvey->nni) == 'ICON-JKT' ? 'selected' : '' }}>ICON-JKT</option>
                                                        <option value="PALAPA" {{ old('nni', $getSurvey->nni) == 'PALAPA' ? 'selected' : '' }}>PALAPA</option>
                                                        <option value="CHAROEN" {{ old('nni', $getSurvey->nni) == 'CHAROEN' ? 'selected' : '' }}>CHAROEN</option>

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
                                                            {{ $getSurvey->vendor_id == $vendor->id ? 'selected' : '' }}>
                                                            {{ $vendor->nama_vendor }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <!-- Input Durasi dan Nama Durasi -->
                                            <div class="form-group row">
                                                <label for="durasi" class="col-sm-4 col-form-label">Durasi</label>
                                                <div class="col-sm-4">
                                                    <input type="number" name="durasi" id="durasi" class="form-control" min="1" required>
                                                </div>
                                                <div class="col-sm-4">
                                                    <select name="nama_durasi" id="nama_durasi" class="form-control " required>
                                                        <option value="" disabled selected>Pilih Satuan Durasi</option>
                                                        <option value="hari">Hari</option>
                                                        <option value="bulan">Bulan</option>
                                                        <option value="tahun">Tahun</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Input Harga Sewa -->
                                            <div class="form-group row">
                                                <label for="harga_sewa" class="col-sm-4 col-form-label">Harga Sewa</label>
                                                <div class="col-sm-8">
                                                    <input type="text" name="harga_sewa" id="harga_sewa" class="form-control" onkeyup="formatRupiah(this)" required>
                                                    <!-- Hidden input untuk angka murni -->
                                                    <input type="hidden" name="harga_sewa_hidden" id="harga_sewa_hidden">
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label for="foto_pelanggan" class="col-sm-4 col-form-label">Foto Pelanggan</label>
                                                <div class="col-sm-8">
                                                    <img id="foto" src="" alt="Foto Pelanggan" style="width: 200px; display: none;">
                                                </div>
                                            </div>

                                            <!-- Dropdown Jenis Barang -->
                                            <div class="form-group row">
                                                <label for="jenis_id">Jenis Barang</label>
                                                <div class="col-sm-8">
                                                    <select id="jenis_id" name="jenis_id" class="form-control">
                                                        <option value="">Pilih Jenis Barang</option>
                                                        @foreach ($jenisList as $jenis)
                                                        <option value="{{ $jenis->id }}">{{ $jenis->nama_jenis }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Tabel Stok Barang -->

                                            <!-- Tabel Stok Barang -->
                                            <div class="form-group row">
                                                <label>Data Barang</label>
                                                <div class="col-sm-8">
                                                    <input type="text" id="search" class="form-control w-50 h-46" />
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

                                            <!-- Tabel Keranjang -->
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
                                                            <!-- Items added to the cart will be displayed here -->
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
                                                            {{ $getSurvey->instansi_id == $instansi->id ? 'selected' : '' }}>
                                                            {{ $instansi->nama_instansi }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label for="no_pelanggan" class="col-sm-4 col-form-label">Nama Site</label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control" id="nama_site" name="nama_site" value="{{ old('nama_site', $getSurvey->nama_site) }}" required>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="no_pelanggan" class="col-sm-4 col-form-label">Alamat Pemasangan</label>
                                                <div class="col-sm-8">
                                                    <textarea class="form-control" id="alamat_pemasangan" name="alamat_pemasangan" rows="4" required>{{ $getSurvey->alamat_pemasangan }}</textarea>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="provinsi" class="col-sm-4 col-form-label">Nama Provinsi</label>
                                                <div class="col-sm-8">
                                                    <select class="form-control" id="provinsi" name="provinsi" required>
                                                        <option value="">Pilih Provinsi</option>
                                                        <option value="Aceh" {{ $getSurvey->provinsi == 'Aceh' ? 'selected' : '' }}>Aceh</option>
                                                        <option value="Sumatera Utara" {{ $getSurvey->provinsi == 'Sumatera Utara' ? 'selected' : '' }}>Sumatera Utara</option>
                                                        <option value="Sumatera Barat" {{ $getSurvey->provinsi == 'Sumatera Barat' ? 'selected' : '' }}>Sumatera Barat</option>
                                                        <option value="Riau" {{ $getSurvey->provinsi == 'Riau' ? 'selected' : '' }}>Riau</option>
                                                        <option value="Kepulauan Riau" {{ $getSurvey->provinsi == 'Kepulauan Riau' ? 'selected' : '' }}>Kepulauan Riau</option>
                                                        <option value="Jambi" {{ $getSurvey->provinsi == 'Jambi' ? 'selected' : '' }}>Jambi</option>
                                                        <option value="Sumatera Selatan" {{ $getSurvey->provinsi == 'Sumatera Selatan' ? 'selected' : '' }}>Sumatera Selatan</option>
                                                        <option value="Bangka Belitung" {{ $getSurvey->provinsi == 'Bangka Belitung' ? 'selected' : '' }}>Bangka Belitung</option>
                                                        <option value="Bengkulu" {{ $getSurvey->provinsi == 'Bengkulu' ? 'selected' : '' }}>Bengkulu</option>
                                                        <option value="Lampung" {{ $getSurvey->provinsi == 'Lampung' ? 'selected' : '' }}>Lampung</option>
                                                        <option value="DKI Jakarta" {{ $getSurvey->provinsi == 'DKI Jakarta' ? 'selected' : '' }}>DKI Jakarta</option>
                                                        <option value="Jawa Barat" {{ $getSurvey->provinsi == 'Jawa Barat' ? 'selected' : '' }}>Jawa Barat</option>
                                                        <option value="Jawa Tengah" {{ $getSurvey->provinsi == 'Jawa Tengah' ? 'selected' : '' }}>Jawa Tengah</option>
                                                        <option value="DI Yogyakarta" {{ $getSurvey->provinsi == 'DI Yogyakarta' ? 'selected' : '' }}>DI Yogyakarta</option>
                                                        <option value="Jawa Timur" {{ $getSurvey->provinsi == 'Jawa Timur' ? 'selected' : '' }}>Jawa Timur</option>
                                                        <option value="Bali" {{ $getSurvey->provinsi == 'Bali' ? 'selected' : '' }}>Bali</option>
                                                        <option value="Nusa Tenggara Barat" {{ $getSurvey->provinsi == 'Nusa Tenggara Barat' ? 'selected' : '' }}>Nusa Tenggara Barat</option>
                                                        <option value="Nusa Tenggara Timur" {{ $getSurvey->provinsi == 'Nusa Tenggara Timur' ? 'selected' : '' }}>Nusa Tenggara Timur</option>
                                                        <option value="Kalimantan Barat" {{ $getSurvey->provinsi == 'Kalimantan Barat' ? 'selected' : '' }}>Kalimantan Barat</option>
                                                        <option value="Kalimantan Tengah" {{ $getSurvey->provinsi == 'Kalimantan Tengah' ? 'selected' : '' }}>Kalimantan Tengah</option>
                                                        <option value="Kalimantan Selatan" {{ $getSurvey->provinsi == 'Kalimantan Selatan' ? 'selected' : '' }}>Kalimantan Selatan</option>
                                                        <option value="Kalimantan Timur" {{ $getSurvey->provinsi == 'Kalimantan Timur' ? 'selected' : '' }}>Kalimantan Timur</option>
                                                        <option value="Kalimantan Utara" {{ $getSurvey->provinsi == 'Kalimantan Utara' ? 'selected' : '' }}>Kalimantan Utara</option>
                                                        <option value="Sulawesi Utara" {{ $getSurvey->provinsi == 'Sulawesi Utara' ? 'selected' : '' }}>Sulawesi Utara</option>
                                                        <option value="Gorontalo" {{ $getSurvey->provinsi == 'Gorontalo' ? 'selected' : '' }}>Gorontalo</option>
                                                        <option value="Sulawesi Tengah" {{ $getSurvey->provinsi == 'Sulawesi Tengah' ? 'selected' : '' }}>Sulawesi Tengah</option>
                                                        <option value="Sulawesi Barat" {{ $getSurvey->provinsi == 'Sulawesi Barat' ? 'selected' : '' }}>Sulawesi Barat</option>
                                                        <option value="Sulawesi Selatan" {{ $getSurvey->provinsi == 'Sulawesi Selatan' ? 'selected' : '' }}>Sulawesi Selatan</option>
                                                        <option value="Sulawesi Tenggara" {{ $getSurvey->provinsi == 'Sulawesi Tenggara' ? 'selected' : '' }}>Sulawesi Tenggara</option>
                                                        <option value="Maluku" {{ $getSurvey->provinsi == 'Maluku' ? 'selected' : '' }}>Maluku</option>
                                                        <option value="Maluku Utara" {{ $getSurvey->provinsi == 'Maluku Utara' ? 'selected' : '' }}>Maluku Utara</option>
                                                        <option value="Papua" {{ $getSurvey->provinsi == 'Papua' ? 'selected' : '' }}>Papua</option>
                                                        <option value="Papua Barat" {{ $getSurvey->provinsi == 'Papua Barat' ? 'selected' : '' }}>Papua Barat</option>
                                                        <option value="Papua Tengah" {{ $getSurvey->provinsi == 'Papua Tengah' ? 'selected' : '' }}>Papua Tengah</option>
                                                        <option value="Papua Pegunungan" {{ $getSurvey->provinsi == 'Papua Pegunungan' ? 'selected' : '' }}>Papua Pegunungan</option>
                                                        <option value="Papua Selatan" {{ $getSurvey->provinsi == 'Papua Selatan' ? 'selected' : '' }}>Papua Selatan</option>
                                                        <option value="Papua Barat Daya" {{ $getSurvey->provinsi == 'Papua Barat Daya' ? 'selected' : '' }}>Papua Barat Daya</option>

                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="no_pelanggan" class="col-sm-4 col-form-label">Nama PIC</label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control" id="nama_pic" name="nama_pic" value="{{ old('nama_pic', $getSurvey->nama_pic) }}">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="no_pelanggan" class="col-sm-4 col-form-label">Nomer PIC</label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control" id="no_pic" name="no_pic" value="{{ old('no_pic', $getSurvey->no_pic) }}">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="alamat_pelanggan" class="col-sm-4 col-form-label">Media</label>
                                                <div class="col-sm-8">
                                                    <select class="form-control" id="media" name="media" required>
                                                        <option value="">Pilih Media</option>
                                                        <option value="FIBER OPTIC" {{ old('media', $getSurvey->media) == 'FIBER OPTIC' ? 'selected' : '' }}>FIBER OPTIC</option>
                                                        <option value="WIRELESS" {{ old('media', $getSurvey->media) == 'WIRELESS' ? 'selected' : '' }}>WIRELESS</option>
                                                        <option value="M2M" {{ old('media', $getSurvey->media) == 'M2M' ? 'selected' : '' }}>M2M</option>
                                                        <option value="NONE" {{ old('media', $getSurvey->media) == 'NONE' ? 'selected' : '' }}>NONE</option>

                                                    </select>

                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label for="no_pelanggan" class="col-sm-4 col-form-label">Vlan</label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control" id="vlan" name="vlan" value="{{ old('vlan', $getSurvey->vlan) }}">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="no_pelanggan" class="col-sm-4 col-form-label">No Jaringan</label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control" id="no_jaringan" name="no_jaringan" value="{{ old('no_jaringan', $getSurvey->no_jaringan) }}">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="no_pelanggan" class="col-sm-4 col-form-label">Tanggal RFS</label>
                                                <div class="col-sm-8">
                                                    <input type="date" class="form-control" id="tanggal_rfs" name="tanggal_rfs" value="{{ old('tanggal_rfs', $getSurvey->tanggal_rfs) }}" required>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="harga_instalasi" class="col-sm-4 col-form-label">Harga Instalasi</label>
                                                <div class="col-sm-8">
                                                    <input type="text" name="harga_instalasi" id="harga_instalasi" class="form-control" onkeyup="formatRupiah(this)" required>
                                                    <!-- Hidden input untuk angka murni -->
                                                    <input type="hidden" name="harga_instalasi_hidden" id="harga_instalasi_hidden">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="no_pelanggan" class="col-sm-4 col-form-label">Keterangan</label>
                                                <div class="col-sm-8">
                                                    <textarea class="form-control" id="keterangan" name="keterangan" rows="4"></textarea>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="no_penerima" class="col-sm-4 col-form-label">Input Barang Non Stock</label>
                                                <div class="col-sm-8">
                                                    <textarea id="non_stock" name="non_stock" class="form-control" rows="4"></textarea>
                                                </div>
                                            </div>
                                        </div>




                                    </div>
                                    <br>
                                    <div class="text-center">
                                        <!-- Tombol submit -->
                                        <button type="submit" class="btn btn-info">Submit</button>
                                        <a href="{{ route('admin.wo_survey_show', $getSurvey->id) }}" class="btn btn-light">Kembali</a>
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