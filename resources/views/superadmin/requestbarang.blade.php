<!DOCTYPE html>
<html lang="en">

<head>
    @include('superadmin.partials.style')
</head>

<body>

    <div class="container-scroller">

        <!-- Navbar -->
        @include('superadmin.partials.navbar')


        <div class="container-fluid page-body-wrapper">
            <!-- Sidebar -->
            @include('superadmin.partials.sidebar')


            <!-- Main Panel -->
            <div class="main-panel">
                <div class="content-wrapper">
                    <div class="page-header">
                        <h3 class="page-title">
                            <span class="page-title-icon bg-gradient-danger text-white me-2">
                                <i class="mdi mdi-home"></i>
                            </span> Request Barang
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
                        @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        @endif
                    </div>

                    <div class="col-lg-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <h4>Daftar Request Barang</h4>

                                <!-- Form Pencarian dan Filter -->
                                <form method="GET" action="{{ route('superadmin.request_barang') }}" class="mb-4">
                                    <div class="row">
                                        <!-- Kolom Pencarian -->
                                        <div class="col-md-6 mb-3">
                                            <input type="text" name="search" class="form-control contoh1" placeholder="Cari Data" value="{{ request('search') }}">
                                        </div>

                                        <!-- Filter Bulan -->
                                        <div class="col-md-3 mb-3">
                                            <select name="month" class="form-control">
                                                <option value="">Pilih Bulan</option>
                                                @for($m = 1; $m <= 12; $m++)
                                                    <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                                    </option>
                                                    @endfor
                                            </select>
                                        </div>

                                        <!-- Filter Tahun -->
                                        <div class="col-md-3 mb-3">
                                            <select name="year" class="form-control">
                                                <option value="">Pilih Tahun</option>
                                                @for($y = date('Y'); $y >= 2020; $y--)
                                                <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                                                    {{ $y }}
                                                </option>
                                                @endfor
                                            </select>
                                        </div>

                                        <!-- Tombol Filter -->
                                        <div class="">
                                            <button type="submit" class="btn btn-info btn-sm mb-4 ">Cari</button>

                                        </div>
                                    </div>
                                </form>
                                <!-- Tab Status -->
                                <ul class="nav nav-tabs justify-content-center mb-4" id="surveyTab" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link {{ $status == 'all' ? 'active' : '' }}" href="{{ route('superadmin.request_barang', ['status' => 'all', 'search' => request('search'), 'month' => request('month'), 'year' => request('year')]) }}">Semua</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ $status == 'pending' ? 'active' : '' }}" href="{{ route('superadmin.request_barang', ['status' => 'pending', 'search' => request('search'), 'month' => request('month'), 'year' => request('year')]) }}">Pending</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ $status == 'approved' ? 'active' : '' }}" href="{{ route('superadmin.request_barang', ['status' => 'approved', 'search' => request('search'), 'month' => request('month'), 'year' => request('year')]) }}">Approved</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ $status == 'rejected' ? 'active' : '' }}" href="{{ route('superadmin.request_barang', ['status' => 'rejected', 'search' => request('search'), 'month' => request('month'), 'year' => request('year')]) }}">Rejected</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ $status == 'shipped' ? 'active' : '' }}" href="{{ route('superadmin.request_barang', ['status' => 'shipped', 'search' => request('search'), 'month' => request('month'), 'year' => request('year')]) }}">Shipped</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ $status == 'completed' ? 'active' : '' }}" href="{{ route('superadmin.request_barang', ['status' => 'completed', 'search' => request('search'), 'month' => request('month'), 'year' => request('year')]) }}">Completed</a>
                                    </li>
                                </ul>
                                <div class=" table-responsive">
                                    <!-- Tabel Request Barang -->
                                    <table class="table table-bordered" style="table-layout: fixed; width: 100%;">
                                        <thead>
                                            <tr>
                                                <th style="width: 80px; text-align: center; vertical-align: middle;">No</th>
                                                <th style="width: 120px; text-align: center; vertical-align: middle;">Tanggal</th>
                                                <th style="width: 250px; text-align: center; vertical-align: middle;">Nama Penerima</th>
                                                <th style="width: 300px; text-align: center; vertical-align: middle;">Alamat</th>
                                                <th style="width: 150px; text-align: center; vertical-align: middle;">Nomer Telepon</th>
                                                <th style="width: 350px; text-align: center; vertical-align: middle;">Keterangan</th>
                                                <th style="width: 120px; text-align: center; vertical-align: middle;">Status</th>
                                                <th style="width: 120px; text-align: center; vertical-align: middle;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($requestBarangs as $key => $requestBarang)
                                            <tr>
                                                <td style=" text-align: center; vertical-align: middle;">{{$requestBarangs->firstItem()+ $key}} </td>
                                                <td style="text-align: center; vertical-align: middle;">{{ $requestBarang->created_at->format('d M Y') }}</td>
                                                <td style="text-align: center; vertical-align: middle; word-wrap: break-word; white-space: normal; padding: 8px; line-height: 1.5;">{{ $requestBarang->nama_penerima }}</td>
                                                <td style="text-align: center; vertical-align: middle; word-wrap: break-word; white-space: normal; padding: 8px; line-height: 1.5;">{{ $requestBarang->alamat_penerima }}</td>
                                                <td style="text-align: center; vertical-align: middle; ">{{ $requestBarang->no_penerima }}</td>
                                                <td style="text-align: justify; vertical-align: middle; word-wrap: break-word; white-space: normal; padding: 8px; line-height: 1.5;">
                                                    {{ $requestBarang->keterangan }}
                                                </td>
                                                <td style="text-align: center; vertical-align: middle;"> @if($requestBarang->status=='pending')
                                                    <span class="badge badge-pill badge-danger">Pending</span>
                                                    @elseif($requestBarang->status=='approved')
                                                    <span class="badge badge-pill badge-info">Approved</span>
                                                    @elseif($requestBarang->status=='rejected')
                                                    <span class="badge badge-pill badge-warning">Rejected</span>
                                                    @elseif($requestBarang->status=='completed')
                                                    <span class="badge badge-pill badge-success">Completed</span>
                                                    @elseif($requestBarang->status=='shipped')
                                                    <span class="badge badge-pill badge-primary">Shipped</span>
                                                    @endif
                                                </td>
                                                <td style="text-align: center; vertical-align: middle;">

                                                    <a href="{{ route('superadmin.request_barang.show', $requestBarang->id) }}" class="btn btn-success btn-sm">
                                                        <i class="fa fa-eye"></i>
                                                    </a>

                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-3">
                                    Showing
                                    {{$requestBarangs->firstItem()}}
                                    to
                                    {{$requestBarangs->lastItem()}}
                                    of
                                    {{$requestBarangs->total()}}
                                    entries

                                </div>
                                <div class="pull-right">
                                    {{ $requestBarangs->links() }}
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

    @include('superadmin.partials.script')
</body>

</html>