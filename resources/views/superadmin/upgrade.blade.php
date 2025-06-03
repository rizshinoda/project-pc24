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
                            </span> Upgrade
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
                                <h4>Daftar WO Upgrade</h4>
                                <!-- Form Pencarian dan Filter -->
                                <form method="GET" action="{{ route('superadmin.upgrade') }}" class="mb-4">
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
                                        <a class="nav-link {{ $status == 'all' ? 'active' : '' }}" id="all-tab" href="{{ route('superadmin.upgrade', ['status' => 'all', 'search' => request('search'), 'month' => request('month'), 'year' => request('year')]) }}" role="tab">Semua</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ $status == 'Pending' ? 'active' : '' }}" id="pending-tab" href="{{ route('superadmin.upgrade', ['status' => 'Pending', 'search' => request('search'), 'month' => request('month'), 'year' => request('year')]) }}" role="tab">Pending</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ $status == 'On Progress' ? 'active' : '' }}" id="on-progress-tab" href="{{ route('superadmin.upgrade', ['status' => 'On Progress', 'search' => request('search'), 'month' => request('month'), 'year' => request('year')]) }}" role="tab">On Progress</a>
                                    </li>

                                    <li class="nav-item">
                                        <a class="nav-link {{ $status == 'Completed' ? 'active' : '' }}" id="completed-tab" href="{{ route('superadmin.upgrade', ['status' => 'Completed', 'search' => request('search'), 'month' => request('month'), 'year' => request('year')]) }}" role="tab">Completed</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ $status == 'Canceled' ? 'active' : '' }}" id="canceled-tab" href="{{ route('superadmin.upgrade', ['status' => 'Canceled', 'search' => request('search'), 'month' => request('month'), 'year' => request('year')]) }}" role="tab">Cancelled</a>
                                    </li>
                                </ul>
                                <div class="table-responsive">
                                    <table class="table table-bordered wrap">
                                        <thead>
                                            <tr>
                                                <th style="text-align: center; vertical-align: middle;">No</th>
                                                <th style="text-align: center; vertical-align: middle;">Nomor Work <br> Order</th>
                                                <th style="text-align: center; vertical-align: middle;">Tanggal <br> Dibuat</th>
                                                <th style="text-align: center; vertical-align: middle;">Nama <br> Pelanggan</th>
                                                <th style="text-align: center; vertical-align: middle;">Perusahaan</th>
                                                <th style="text-align: center; vertical-align: middle;">Nama <br> Site</th>
                                                <th style="text-align: center; vertical-align: middle;">Alamat <br> Pemasangan</th>
                                                <th style="text-align: center; vertical-align: middle;">Bandwidth</th>
                                                <th style="text-align: center; vertical-align: middle;">Status</th>
                                                <th style="text-align: center; vertical-align: middle;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($getUpgrade as $key => $upgrade)
                                            <tr>
                                                <td style="text-align: center; vertical-align: middle;">{{ $getUpgrade->firstItem() + $key }}</td>
                                                <td style="text-align: center; vertical-align: middle;">{{ $upgrade->no_spk }}</td>
                                                <td style="text-align: center; vertical-align: middle;">{{ $upgrade->created_at->format('d M Y') }}</td>
                                                <td style="text-align: center; vertical-align: middle;">{{ $upgrade->onlineBilling->pelanggan->nama_pelanggan }}</td>
                                                <td style="text-align: center; vertical-align: middle;">{{ $upgrade->onlineBilling->instansi->nama_instansi}}</td>
                                                <td style="text-align: center; vertical-align: middle;">{{ $upgrade->onlineBilling->nama_site ?? 'Tidak Ada Nama Site' }}</td>
                                                <td style="text-align: center; vertical-align: middle;">
                                                    {{ \Illuminate\Support\Str::limit($upgrade->onlineBilling->alamat_pemasangan, 60, '...') }}
                                                </td>
                                                <td style="text-align: center; vertical-align: middle;">{{ $upgrade->onlineBilling->bandwidth }} {{ $upgrade->onlineBilling->satuan }}</td>

                                                <td style="text-align: center; vertical-align: middle;">
                                                    @if($upgrade->status == 'Pending')
                                                    <span class="badge badge-pill badge-danger">Pending</span>
                                                    @elseif($upgrade->status == 'On Progress')
                                                    <span class="badge badge-pill badge-info">On Progress</span>
                                                    @elseif($upgrade->status == 'Canceled')
                                                    <span class="badge badge-pill badge-warning">Cancelled</span>
                                                    @elseif($upgrade->status == 'Completed')
                                                    <span class="badge badge-pill badge-success">Completed</span>
                                                    @endif
                                                </td>

                                                <td style="text-align: center; vertical-align: middle;">
                                                    <a href="{{ route('superadmin.upgrade_show', $upgrade->id) }}" class="btn btn-success btn-sm"><i class="fa fa-eye"></i></a>

                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-3">
                                    Showing
                                    {{$getUpgrade->firstItem()}}
                                    to
                                    {{$getUpgrade->lastItem()}}
                                    of
                                    {{$getUpgrade->total()}}
                                    entries

                                </div>
                                <div class="pull-right">
                                    {{ $getUpgrade->links() }}
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