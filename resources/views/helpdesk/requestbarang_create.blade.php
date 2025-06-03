<!DOCTYPE html>
<html lang="en">

<head>
    @include('helpdesk.partials.style')
</head>

<body>
    <div class="container-scroller">
        @include('helpdesk.partials.navbar')
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
                        <a class="nav-link" href="{{ url('helpdesk/dashboard') }}">
                            <span class="menu-title">Dashboard</span>
                            <i class="mdi mdi-home menu-icon"></i>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#ui-basic" aria-expanded="false" aria-controls="ui-basic">
                            <span class="menu-title">Work Order</span>
                            <i class="menu-arrow"></i>
                            <i class="mdi mdi-format-list-bulleted menu-icon"></i>
                        </a>
                        <div class="collapse" id="ui-basic">
                            <ul class="nav flex-column sub-menu">
                                <li class="nav-item">
                                    <a class="nav-link" href="{{url('helpdesk/requestbarang')}}">Request Barang</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{url('helpdesk/maintenance')}}">Maintenance</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{url('helpdesk/gantivendor')}}">Ganti Vendor</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{url('helpdesk/OB')}}">
                            <span class="menu-title">Online Billing</span>
                            <i class="mdi mdi-database-outline menu-icon"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('hd.sitedismantle') }}">
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
                            </span> Form Request
                        </h3>
                    </div>

                    <div class="col-lg-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="mb-5 text-center">Form Request Barang</h4>
                                <form action="{{ route('hd.request_barang.store') }}" method="POST">
                                    @csrf

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label for="alamat_pelanggan" class="col-sm-4 col-form-label">Nama Penerima</label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control" id="nama_penerima" name="nama_penerima" required>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label for="alamat_penerima" class="col-sm-4 col-form-label">Alamat Penerima</label>
                                                <div class="col-sm-8">
                                                    <textarea id="alamat_penerima" name="alamat_penerima" class="form-control" rows="4" required></textarea>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label for="no_penerima" class="col-sm-4 col-form-label">No. Penerima</label>
                                                <div class="col-sm-8">
                                                    <input type="text" id="no_penerima" name="no_penerima" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="no_penerima" class="col-sm-4 col-form-label">Keterangan</label>
                                                <div class="col-sm-8">
                                                    <textarea id="keterangan" name="keterangan" class="form-control" rows="4"></textarea>
                                                </div>
                                            </div>
                                            <!-- Dropdown Jenis Barang -->
                                            <div class="form-group row">
                                                <label for="jenis_id" class="col-sm-4 col-form-label">Jenis Barang</label>
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
                                                <label class="col-sm-4 col-form-label">Data Barang</label>
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

                                            <!-- Tabel Keranjang -->
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">Keranjang</label>
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

                                            <!-- Submit Button -->
                                            <button style="text-align: center;" type="submit" class="btn btn-info">Kirim Permintaan</button>
                                            <!-- Tombol Kembali -->
                                            <a href="{{ route('hd.request_barang',) }}" class="btn btn-light">Kembali</a>

                                        </div>
                                    </div>


                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- content-wrapper ends -->
                <!-- partial:partials/_footer.html -->
                <footer class="footer">
                    <div class="d-sm-flex justify-content-center justify-content-sm-between">
                        <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Copyright © 2024</a>. All rights reserved.</span>
                        <span class="float-none float-sm-right d-block mt-1 mt-sm-0 text-center">Hand-crafted & made with Rizal<i class="mdi mdi-heart text-danger"></i></span>
                    </div>
                </footer>
                <!-- partial -->
            </div>
            <!-- main-panel ends -->
        </div>

    </div>
    @include('helpdesk.partials.script')
</body>

</html>