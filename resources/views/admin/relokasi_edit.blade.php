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
                            </span> Relokasi
                        </h3>


                    </div>

                    <div class="col-lg-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="mb-5 text-center">Form Relokasi</h4>
                                {{-- Menampilkan pesan error jika ada --}}
                                @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endif

                                {{-- Form untuk membuat work order --}}
                                <form action="{{ route('admin.relokasi_update', $workOrder->id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT') {{-- Gunakan method PUT untuk proses update --}}

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label for="nama_site" class="col-sm-4 col-form-label">No SPK</label>
                                                <div class="col-sm-8">
                                                    <!-- Nomor SPK tidak bisa diubah -->
                                                    <input type="text" class="form-control" id="no_spk" name="no_spk" value="{{ $workOrder->no_spk }}" readonly>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label for="bandwidth" class="col-sm-4 col-form-label">Alamat Pemasangan Baru</label>
                                                <div class="col-sm-8">
                                                    <textarea id="alamat_pemasangan_baru" name="alamat_pemasangan_baru" class="form-control" rows="4" required>{{ $workOrder->alamat_pemasangan_baru }}</textarea>
                                                </div>



                                                <!-- Kirimkan online_billing_id sebagai hidden -->
                                                <input type="hidden" name="online_billing_id" value="{{ $workOrder->online_billing_id }}">

                                            </div>
                                            <div class="form-group row">
                                                <label for="bandwidth" class="col-sm-4 col-form-label">Keterangan</label>
                                                <div class="col-sm-8">
                                                    <textarea id="keterangan" name="keterangan" class="form-control" rows="4">{{ $workOrder->keterangan }}</textarea>
                                                </div>

                                            </div>
                                            <!-- Dropdown Jenis Barang -->
                                            <div class="form-group row">
                                                <label for="jenis_id" class="col-sm-4 col-form-label">Jenis Barang</label>
                                                <div class="col-sm-8">
                                                    <select id="jenis_id" name="jenis_id" class="form-control">
                                                        <option value="">Pilih Jenis Barang</option>
                                                        @foreach ($jenisList as $jenis)
                                                        <option value="{{ $jenis->id }}" {{ $jenis->id == $workOrder->jenis_id ? 'selected' : '' }}>{{ $jenis->nama_jenis }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

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

                                                            @foreach ($WorkOrderRelokasiDetail as $detail)
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

                                    </div>
                                    <br>
                                    <!-- Tombol submit -->
                                    <button type="submit" class="btn btn-info">Update</button>
                                    <a href="{{ route('admin.relokasi') }}" class="btn btn-light">Kembali</a>

                                </form>
                            </div>
                        </div>
                        <!-- main-panel ends -->
                    </div>
                </div>
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
    @include('admin.partials.script')
</body>

</html>