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
                            </span> Downgrade
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
                                <h4>Daftar WO Downgrade</h4>
                                <!-- Form Pencarian dan Filter -->
                                <form method="GET" action="{{ route('admin.downgrade') }}" class="mb-4">
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
                                            <a href="{{ route('work-order-downgrade.export') }}" class="btn btn-sm btn-success pull-right">Export Excel</a>

                                        </div>
                                    </div>
                                </form>
                                <!-- Tab Status -->
                                <ul class="nav nav-tabs justify-content-center mb-4" id="surveyTab" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link {{ $status == 'all' ? 'active' : '' }}" id="all-tab" href="{{ route('admin.downgrade', ['status' => 'all', 'search' => request('search'), 'month' => request('month'), 'year' => request('year')]) }}" role="tab">Semua</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ $status == 'Pending' ? 'active' : '' }}" id="pending-tab" href="{{ route('admin.downgrade', ['status' => 'Pending', 'search' => request('search'), 'month' => request('month'), 'year' => request('year')]) }}" role="tab">Pending</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ $status == 'On Progress' ? 'active' : '' }}" id="on-progress-tab" href="{{ route('admin.downgrade', ['status' => 'On Progress', 'search' => request('search'), 'month' => request('month'), 'year' => request('year')]) }}" role="tab">On Progress</a>
                                    </li>

                                    <li class="nav-item">
                                        <a class="nav-link {{ $status == 'Completed' ? 'active' : '' }}" id="completed-tab" href="{{ route('admin.downgrade', ['status' => 'Completed', 'search' => request('search'), 'month' => request('month'), 'year' => request('year')]) }}" role="tab">Completed</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ $status == 'Canceled' ? 'active' : '' }}" id="canceled-tab" href="{{ route('admin.downgrade', ['status' => 'Canceled', 'search' => request('search'), 'month' => request('month'), 'year' => request('year')]) }}" role="tab">Cancelled</a>
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
                                            @foreach ($getDowngrade as $key => $downgrade)
                                            <tr>
                                                <td style="text-align: center; vertical-align: middle;">{{ $getDowngrade->firstItem() + $key }}</td>
                                                <td style="text-align: center; vertical-align: middle;">{{ $downgrade->no_spk }}</td>
                                                <td style="text-align: center; vertical-align: middle;">{{ $downgrade->created_at->format('d M Y') }}</td>
                                                <td style="text-align: center; vertical-align: middle;">{{ $downgrade->onlineBilling->pelanggan->nama_pelanggan }}</td>
                                                <td style="text-align: center; vertical-align: middle;">{{ $downgrade->onlineBilling->instansi?->nama_instansi}}</td>
                                                <td style="text-align: center; vertical-align: middle;">{{ $downgrade->onlineBilling->nama_site ?? 'Tidak Ada Nama Site' }}</td>
                                                <td style="text-align: center; vertical-align: middle;">
                                                    {{ \Illuminate\Support\Str::limit($downgrade->onlineBilling->alamat_pemasangan, 60, '...') }}
                                                </td>
                                                <td style="text-align: center; vertical-align: middle;">{{ $downgrade->onlineBilling->bandwidth }} {{ $downgrade->onlineBilling->satuan }}</td>

                                                <td style="text-align: center; vertical-align: middle;">
                                                    @if($downgrade->status == 'Pending')
                                                    <span class="badge badge-pill badge-danger">Pending</span>
                                                    @elseif($downgrade->status == 'On Progress')
                                                    <span class="badge badge-pill badge-info">On Progress</span>
                                                    @elseif($downgrade->status == 'Canceled')
                                                    <span class="badge badge-pill badge-warning">Cancelled</span>
                                                    @elseif($downgrade->status == 'Completed')
                                                    <span class="badge badge-pill badge-success">Completed</span>
                                                    @endif
                                                </td>

                                                <td style="text-align: center; vertical-align: middle;">
                                                    <a href="{{ route('admin.downgrade_show', $downgrade->id) }}" class="btn btn-success btn-sm"><i class="fa fa-eye"></i></a>
                                                    <a href="{{ route('admin.downgrade_edit', $downgrade->id) }}" class="btn btn-info btn-sm"><i class="fa fa-edit"></i></a>
                                                    <form id="delete-form-{{ $downgrade->id }}" action="{{ route('admin.downgrade_destroy', $downgrade->id) }}" method="POST" style="display:inline-block;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete({{ $downgrade->id }})">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </form>

                                                    <form id="cancel-form-{{ $downgrade->id }}" action="{{ route('admin.downgrade_cancel', $downgrade->id) }}" method="POST" style="display:inline-block;">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="button" class="btn btn-warning btn-sm" onclick="confirmCancel({{ $downgrade->id }})">
                                                            <i class="fa fa-close"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-3">
                                    Showing
                                    {{$getDowngrade->firstItem()}}
                                    to
                                    {{$getDowngrade->lastItem()}}
                                    of
                                    {{$getDowngrade->total()}}
                                    entries

                                </div>
                                <div class="pull-right">
                                    {{ $getDowngrade->links() }}
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
    @include('admin.partials.script')
</body>

</html>