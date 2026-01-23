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
                            </span> Survey
                        </h3>


                    </div>

                    <div class="col-lg-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="mb-5 text-center">Form Tambah Survey</h4>
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
                                <form action="{{ route('admin.form_store') }}" method="POST" enctype="multipart/form-data">
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
                                                            data-foto="{{ asset('storage/pelanggan/' . $pelanggan->foto) }}">

                                                            {{ $pelanggan->nama_pelanggan }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                    <!-- @if ($errors->has('no_spk'))
                                                    <span class="text-danger">{{ $errors->first('no_spk') }}</span>
                                                    @endif -->
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="alamat_pelanggan" class="col-sm-4 col-form-label">Alamat Pelanggan</label>
                                                <div class="col-sm-8">
                                                    <textarea class="form-control" id="alamat" name="alamat" rows="4"
                                                        readonly></textarea>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="no_pelanggan" class="col-sm-4 col-form-label">No Pelanggan</label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control" id="no_pelanggan" name="no_pelanggan" readonly>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="no_pelanggan" class="col-sm-4 col-form-label">Nama Gedung</label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control" id="nama_gedung" name="nama_gedung" readonly>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="alamat_pelanggan" class="col-sm-4 col-form-label">Layanan</label>
                                                <div class="col-sm-8">
                                                    <select class="form-control" id="layanan" name="layanan" required>
                                                        <option value="">Pilih Layanan</option>
                                                        <option value="-">-</option>
                                                        <option value="INTERNET">INTERNET</option>
                                                        <option value="METRO">METRO</option>
                                                        <option value="METRO-E">METRO-E</option>
                                                        <option value="VPN">VPN</option>
                                                        <option value="LOCALLOOP">LOCALLOOP</option>
                                                        <option value="INTERCONECTION">INTERCONECTION</option>
                                                        <option value="CROSSCONNECT">CROSSCONNECT</option>
                                                        <option value="COLOCATION">COLOCATION</option>
                                                        <option value="INTERNET BROADBAND">INTERNET BROADBAND</option>
                                                        <option value="INTERNET BROADBAND 1:4">INTERNET BROADBAND 1:4</option>
                                                        <option value="INTERNET BROADBAND 1:10">INTERNET BROADBAND 1:10</option>

                                                        <option value="INTERNET BROADBAND+IP">INTERNET BROADBAND+IP</option>
                                                        <option value="INTERNET DEDICATED">INTERNET DEDICATED</option>
                                                        <option value="METRO - DARK FIBER">METRO - DARK FIBER</option>
                                                        <option value="IP TRANSIT">IP TRANSIT</option>
                                                        <option value="METRO P2MP">METRO P2MP</option>
                                                        <option value="DARK FIBER">DARK FIBER</option>
                                                        <option value="Internet Kuota">Internet Kuota</option>
                                                        <option value="CCTV">CCTV</option>
                                                    </select>

                                                </div>
                                            </div>
                                            <!-- Input Durasi dan Nama Durasi -->
                                            <div class="form-group row">
                                                <label for="bandwidth" class="col-sm-4 col-form-label">Volume</label>
                                                <div class="col-sm-4">
                                                    <input type="number" name="bandwidth" id="bandwidth" class="form-control" min="1" required>
                                                </div>

                                                <div class="col-sm-4">
                                                    <select class="form-control" id="satuan" name="satuan" required>
                                                        <option value="" disabled selected>Pilih Satuan</option>
                                                        <option value="Gbps">Gbps</option>
                                                        <option value="Mbps">Mbps</option>
                                                        <option value="Kbps">Kbps</option>
                                                        <option value="RU(RACK UNIT)">RU(RACK UNIT)</option>
                                                        <option value="CORE">CORE</option>
                                                        <option value="PAIR">PAIR</option>


                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="alamat_pelanggan" class="col-sm-4 col-form-label">NNI</label>
                                                <div class="col-sm-8">
                                                    <select class="form-control" id="nni" name="nni">
                                                        <option value="">Pilih NNI</option>
                                                        <option value="-">-</option>
                                                        <option value="SURABAYA">SURABAYA</option>
                                                        <option value="YOGYAKARTA">YOGYAKARTA</option>
                                                        <option value="TBS">TBS</option>
                                                        <option value="NONE">NONE</option>
                                                        <option value="IDC3">IDC3</option>
                                                        <option value="MEDAN">MEDAN</option>
                                                        <option value="BALI">BALI</option>
                                                        <option value="MAKASSAR">MAKASSAR</option>
                                                        <option value="IDC BARU">IDC BARU</option>
                                                        <option value="INDOSAT">INDOSAT</option>
                                                        <option value="SANATEL">SANATEL</option>
                                                        <option value="CYBER 1">CYBER 1</option>
                                                        <option value="WATSON">WATSON</option>
                                                        <option value="DISTRIBUSI 1">DISTRIBUSI 1</option>
                                                        <option value="BALIKPAPAN">BALIKPAPAN</option>
                                                        <option value="BATAM">BATAM</option>
                                                        <option value="PONTIANAK">PONTIANAK</option>
                                                        <option value="BANDUNG">BANDUNG</option>
                                                        <option value="MUP">MUP</option>
                                                        <option value="MPPA">MPPA</option>
                                                        <option value="GASNET">GASNET</option>
                                                        <option value="DISTRIBUSI 2">DISTRIBUSI 2</option>
                                                        <option value="ICON-JKT">ICON-JKT</option>
                                                        <option value="PALAPA">PALAPA</option>
                                                        <option value="CHAROEN">CHAROEN</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="alamat_pelanggan" class="col-sm-4 col-form-label">Nama Vendor</label>
                                                <div class="col-sm-8">
                                                    <select class="form-control" id="vendor_id" name="vendor_id" required>
                                                        <option value="">Pilih Vendor</option>
                                                        @foreach ($vendors as $vendor)
                                                        <option value="{{ $vendor->id }}">{{ $vendor->nama_vendor }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Foto Pelanggan -->
                                            <div class="form-group row">
                                                <label for="foto_pelanggan" class="col-sm-4 col-form-label">Foto Pelanggan</label>
                                                <div class="col-sm-8">
                                                    <img id="foto" src="" alt="Foto Pelanggan" style="width: 200px; display: none;">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <!-- Combo box untuk nama instansi -->

                                            <div class="form-group row">
                                                <label for="alamat_pelanggan" class="col-sm-4 col-form-label">Nama Instansi</label>
                                                <div class="col-sm-8">
                                                    <select class="form-control" id="instansi_id" name="instansi_id" required>
                                                        <option value="">Pilih Instansi</option>
                                                        @foreach ($instansis as $instansi)
                                                        <option value="{{ $instansi->id }}">{{ $instansi->nama_instansi }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="no_pelanggan" class="col-sm-4 col-form-label">Nama Site</label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control" id="nama_site" name="nama_site" required>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="no_pelanggan" class="col-sm-4 col-form-label">Alamat Pemasangan</label>
                                                <div class="col-sm-8">
                                                    <textarea class="form-control"
                                                        id="alamat_pemasangan"
                                                        name="alamat_pemasangan"
                                                        rows="4"
                                                        required></textarea>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="alamat_pelanggan" class="col-sm-4 col-form-label">Nama Provinsi</label>
                                                <div class="col-sm-8">
                                                    <select class="form-control" id="provinsi" name="provinsi" required>
                                                        <option value="">Pilih Provinsi</option>
                                                        <option value="Aceh">Aceh</option>
                                                        <option value="Sumatera Utara">Sumatera Utara</option>
                                                        <option value="Sumatera Barat">Sumatera Barat</option>
                                                        <option value="Riau">Riau</option>
                                                        <option value="Kepulauan Riau">Kepulauan Riau</option>
                                                        <option value="Jambi">Jambi</option>
                                                        <option value="Sumatera Selatan">Sumatera Selatan</option>
                                                        <option value="Bangka Belitung">Bangka Belitung</option>
                                                        <option value="Bengkulu">Bengkulu</option>
                                                        <option value="Lampung">Lampung</option>
                                                        <option value="DKI Jakarta">DKI Jakarta</option>
                                                        <option value="Jawa Barat">Jawa Barat</option>
                                                        <option value="Banten">Banten</option>
                                                        <option value="Jawa Tengah">Jawa Tengah</option>
                                                        <option value="DI Yogyakarta">DI Yogyakarta</option>
                                                        <option value="Jawa Timur">Jawa Timur</option>
                                                        <option value="Bali">Bali</option>
                                                        <option value="Nusa Tenggara Barat">Nusa Tenggara Barat</option>
                                                        <option value="Nusa Tenggara Timur">Nusa Tenggara Timur</option>
                                                        <option value="Kalimantan Barat">Kalimantan Barat</option>
                                                        <option value="Kalimantan Tengah">Kalimantan Tengah</option>
                                                        <option value="Kalimantan Selatan">Kalimantan Selatan</option>
                                                        <option value="Kalimantan Timur">Kalimantan Timur</option>
                                                        <option value="Kalimantan Utara">Kalimantan Utara</option>
                                                        <option value="Sulawesi Utara">Sulawesi Utara</option>
                                                        <option value="Gorontalo">Gorontalo</option>
                                                        <option value="Sulawesi Tengah">Sulawesi Tengah</option>
                                                        <option value="Sulawesi Barat">Sulawesi Barat</option>
                                                        <option value="Sulawesi Selatan">Sulawesi Selatan</option>
                                                        <option value="Sulawesi Tenggara">Sulawesi Tenggara</option>
                                                        <option value="Maluku">Maluku</option>
                                                        <option value="Maluku Utara">Maluku Utara</option>
                                                        <option value="Papua">Papua</option>
                                                        <option value="Papua Barat">Papua Barat</option>
                                                        <option value="Papua Tengah">Papua Tengah</option>
                                                        <option value="Papua Pegunungan">Papua Pegunungan</option>
                                                        <option value="Papua Selatan">Papua Selatan</option>
                                                        <option value="Papua Barat Daya">Papua Barat Daya</option>

                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="no_pelanggan" class="col-sm-4 col-form-label">Nama PIC</label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control" id="nama_pic" name="nama_pic">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="no_pelanggan" class="col-sm-4 col-form-label">Nomor PIC</label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control" id="no_pic" name="no_pic">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="alamat_pelanggan" class="col-sm-4 col-form-label">Media</label>
                                                <div class="col-sm-8">
                                                    <select class="form-control" id="media" name="media" required>
                                                        <option value="">Pilih Media</option>
                                                        <option value="FIBER OPTIC">FIBER OPTIC</option>
                                                        <option value="WIRELESS">WIRELESS</option>
                                                        <option value="M2M">M2M</option>
                                                        <option value="NONE">NONE</option>
                                                        <option value="STARLINK">STARLINK</option>


                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label for="no_pelanggan" class="col-sm-4 col-form-label">Vlan</label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control" id="vlan" name="vlan">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="no_pelanggan" class="col-sm-4 col-form-label">No Jaringan</label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control" id="no_jaringan" name="no_jaringan">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="no_pelanggan" class="col-sm-4 col-form-label">Tanggal RFS</label>
                                                <div class="col-sm-8">
                                                    <input type="date" class="form-control" id="tanggal_rfs" name="tanggal_rfs" required>
                                                </div>
                                            </div>


                                        </div>
                                    </div>
                                    <br>
                                    <div class="text-center">
                                        <!-- Tombol submit -->
                                        <button type="submit" class="btn btn-info">Submit</button>
                                        <a href="{{ route('admin.survey') }}" class="btn btn-light">Kembali</a>
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