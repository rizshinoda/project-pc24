<!DOCTYPE html>
<html lang="en">

<head>
    @include('ga.partials.style')
</head>

<body>
    <div class="container-scroller">

        @include('ga.partials.navbar')
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
                        <a class="nav-link" href="{{ url('ga/dashboard') }}">
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
                                    <a class="nav-link" href="{{url('ga/jenis')}}">Data Jenis</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{url('ga/merek')}}">Data Merek</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{url('ga/tipe')}}">Data Tipe</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('ga/stockbarang') }}">
                            <span class="menu-title">Stock Barang</span>
                            <i class="mdi mdi-database-edit-outline menu-icon"></i>
                        </a>
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
                                    <a class="nav-link" href="{{url('ga/instalasi')}}">Instalasi</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{url('ga/maintenance')}}">Maintenance</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{url('ga/requestbarang')}}">Request Barang</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{url('ga/dismantle')}}">Dismantle</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{url('ga/relokasi')}}">Relokasi</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{url('ga/OB')}}">
                            <span class="menu-title">Online Billing</span>
                            <i class="mdi mdi-database-outline menu-icon"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('ga.sitedismantle') }}">
                            <span class="menu-title">Site Dismantle</span>
                            <i class="mdi mdi-delete-circle menu-icon"></i>
                        </a>
                    </li>
            </nav>

            <!-- Main Panel -->
            <div class="main-panel">
                <div class="content-wrapper">
                    <div class="page-header">
                        <h3 class="page-title">
                            <span class="page-title-icon bg-gradient-danger text-white me-2">
                                <i class="mdi mdi-home"></i>
                            </span> Input Barang
                        </h3>
                        {{-- Alert untuk menampilkan pesan sukses --}}
                        @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        @endif
                    </div>
                    <div class="col-lg-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">

                                <!-- resources/views/ga/input_barang.blade.php -->

                                <h4 class="card-title">Keranjang Barang</h4>

                                <form id="submitBarangForm" method="POST" action="{{ route('ga.input_barang_relokasi.store', $getRelokasi->id) }}">
                                    @csrf
                                    <input type="hidden" name="cartItems" id="cartItemsInput">
                                    <table class="table table-bordered" id="cartTable">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Jenis</th>
                                                <th>Merek</th>
                                                <th>Tipe</th>
                                                <th>Kualitas</th>
                                                <th>Jumlah</th>
                                                <th>Serial Number</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($cartItems as $index => $item)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $item['jenis'] }}</td>
                                                <td>{{ $item['merek'] }}</td>
                                                <td>{{ $item['tipe'] }}</td>
                                                <td>{{ ucfirst($item['kualitas']) }}</td>
                                                <td>{{ $item['jumlah'] }}</td>
                                                <td>{{ $item['serial_number'] }}</td>
                                                <td>
                                                    <button type="button" class="btn btn-danger" onclick="removeFromCart({{ $index }})">Hapus</button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <a href="{{ route('ga.relokasi.show', $getRelokasi->id) }}" class="btn btn-info mt-3"> <i class="fa fa-arrow-left"></i> Kembali</a>

                                    <!-- Tombol Submit -->
                                    <button type="submit" class="btn btn-info mt-3 pull-right">Submit Barang</button>

                                </form>

                                <h4 class="mt-3">Daftar Barang</h4>
                                <input type="text" id="searchInput" class="form-control mb-4" placeholder="Cari Barang">

                                <!-- Tabel Stok Barang -->
                                <div class="table-responsive">
                                    <table class="table table-bordered wrap">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Jenis</th>
                                                <th>Merek</th>
                                                <th>Tipe</th>
                                                <th>Serial Number</th>
                                                <th>Jumlah</th>
                                                <th>Kualitas</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="stockTableBody">
                                            @foreach($stockBarangs as $key => $stock)
                                            <tr data-jenis-id="{{ $stock->jenis_id }}" data-merek-id="{{ $stock->merek_id }}" data-tipe-id="{{ $stock->tipe_id }}">
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ $stock->jenis->nama_jenis }}</td>
                                                <td>{{ $stock->merek->nama_merek }}</td>
                                                <td>{{ $stock->tipe->nama_tipe }}</td>
                                                <td>{{ $stock->serial_number }}</td>
                                                <td>{{ $stock->jumlah }}</td>
                                                <td>{{ ucfirst($stock->kualitas) }}</td>
                                                <td>
                                                    <button class="btn btn-info" onclick="addToCart('{{ $stock->id }}', '{{ $stock->jenis->nama_jenis }}', '{{ $stock->merek->nama_merek }}', '{{ $stock->tipe->nama_tipe }}', '{{ $stock->serial_number }}', '{{ $stock->kualitas }}', {{ $stock->jumlah }})">Tambah ke Keranjang</button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

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
    @include('ga.partials.script')
</body>

</html>