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
                                    <a class="nav-link" href="{{route('admin.maintenance')}}">Maintenance</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{route('admin.request_barang')}}">Request Barang</a>
                                </li>

                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{route('admin.jasa')}}">
                            <span class="menu-title">Jasa</span>
                            <i class="mdi mdi-wrench menu-icon"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{route('admin.poc')}}">
                            <span class="menu-title">POC</span>
                            <i class="mdi mdi-wrench menu-icon"></i>
                        </a>
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
                            </span> Online Billing
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
                                <h4>Daftar Online Billing</h4>
                                <!-- Form Import Excel -->
                                <!-- <form action="{{ route('import.proses') }}" method="POST" enctype="multipart/form-data" class="mb-3">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-4">
                                            <input type="file" name="file" class="form-control" required>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="submit" class="btn btn-sm btn-success">Import Excel</button>
                                        </div>
                                    </div>
                                </form> -->
                                <!-- Form Pencarian dan Filter -->
                                <form id="filterForm" method="GET" action="{{ route('admin.OB') }}" class="mb-4">
                                    <div class="row">

                                        {{-- Search --}}
                                        <div class="col-md-4 mb-3">

                                            <input
                                                type="text"
                                                name="search"
                                                class="form-control"
                                                placeholder="Cari Data"
                                                value="{{ request('search') }}">


                                        </div>

                                        {{-- Tahun --}}
                                        <div class="col-md-2 mb-3">

                                            <select
                                                name="year"
                                                class="form-control">

                                                <option value="">Pilih Tahun</option>

                                                @for($y = date('Y'); $y >= 2020; $y--)

                                                <option
                                                    value="{{ $y }}"
                                                    {{ request('year') == $y ? 'selected' : '' }}>

                                                    {{ $y }}

                                                </option>

                                                @endfor

                                            </select>
                                        </div>

                                        {{-- Bulan --}}
                                        <div class="col-md-2 mb-3">


                                            <select
                                                name="month"
                                                class="form-control">

                                                <option value="">Pilih Bulan</option>

                                                @for($m = 1; $m <= 12; $m++)

                                                    <option
                                                    value="{{ $m }}"
                                                    {{ request('month') == $m ? 'selected' : '' }}>

                                                    {{ date('F', mktime(0,0,0,$m,1)) }}

                                                    </option>

                                                    @endfor

                                            </select>

                                        </div>

                                        {{-- Filter Berdasarkan --}}
                                        <div class="col-md-2 mb-3">


                                            <select
                                                name="field"
                                                id="field"
                                                class="form-control">

                                                <option value="">Pilih Filter</option>

                                                <option value="vendor"
                                                    {{ request('field')=='vendor' ? 'selected' : '' }}>
                                                    Vendor
                                                </option>

                                                <option value="pelanggan"
                                                    {{ request('field')=='pelanggan' ? 'selected' : '' }}>
                                                    Pelanggan
                                                </option>

                                                <option value="instansi"
                                                    {{ request('field')=='instansi' ? 'selected' : '' }}>
                                                    Instansi
                                                </option>

                                                <option value="provinsi"
                                                    {{ request('field')=='provinsi' ? 'selected' : '' }}>
                                                    Provinsi
                                                </option>
                                                <option value="kelengkapan"
                                                    {{ request('field')=='kelengkapan' ? 'selected' : '' }}>
                                                    Kelengkapan Data
                                                </option>

                                            </select>

                                        </div>

                                        {{-- Nilai --}}
                                        <div class="col-md-2 mb-3">


                                            <select
                                                name="value"
                                                class="form-control">

                                                <option value="">Semua</option>

                                                @foreach($filterValues as $id => $text)

                                                <option
                                                    value="{{ $id }}"
                                                    {{ request('value') == $id ? 'selected' : '' }}>

                                                    {{ $text }}

                                                </option>

                                                @endforeach

                                            </select>

                                        </div>

                                    </div>

                                    <div class="row">

                                        <div class="col-md-12">
                                            <input type="hidden" name="auto" id="auto" value="">
                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-info">

                                                <i class="fa fa-search"></i>
                                                Cari

                                            </button>

                                            <!-- <a
                                                href="{{ route('admin.OB') }}"
                                                class="btn btn-sm btn-secondary">

                                                <i class="fa fa-refresh"></i>
                                                Reset

                                            </a> -->

                                            <a
                                                href="{{ route('admin.work-OB.export', request()->query()) }}"
                                                class="btn btn-sm btn-success pull-right">

                                                <i class="fa fa-file-excel-o"></i>
                                                Export Excel

                                            </a>

                                        </div>

                                    </div>

                                </form>

                                <div class="table-responsive">
                                    <table class="table table-bordered wrap">

                                        <thead class="text-center">
                                            <tr>
                                                <th width="5%">No</th>
                                                <th>No Jaringan</th>
                                                <th>Nama Pelanggan</th>
                                                <th>Perusahaan</th>
                                                <th>Nama Site</th>
                                                <th>Alamat Pemasangan</th>
                                                <th>VLAN</th>
                                                <th>Volume</th>
                                                <th>Tanggal Mulai</th>
                                                <th width="10%">Aksi</th>
                                            </tr>
                                        </thead>

                                        <tbody>

                                            @forelse($onlinebilling as $key => $OB)

                                            <tr>

                                                <td style=" text-align: center; vertical-align: middle;">{{$onlinebilling->firstItem()+ $key}} </td>
                                                <td style="text-align: center; vertical-align: middle;">{{ $OB->no_jaringan }}</td>
                                                <td style="text-align: center; vertical-align: middle;">{{ $OB->pelanggan->nama_pelanggan }}</td>
                                                <td style="text-align: center; vertical-align: middle;">{{ $OB->instansi?->nama_instansi }}</td>
                                                <td style="text-align: center; vertical-align: middle;">{{ $OB->nama_site }}</td>
                                                <td style="text-align: center; vertical-align: middle;">
                                                    {{ \Illuminate\Support\Str::limit($OB->alamat_pemasangan, 60, '...') }}
                                                </td>
                                                <td style="text-align: center; vertical-align: middle;">{{ $OB->vlan }}</td>
                                                <td style="text-align: center; vertical-align: middle;">{{ $OB->bandwidth }} {{ $OB->satuan }}</td>
                                                <td style="text-align: center; vertical-align: middle;"> {{ $OB->tanggal_mulai ? $OB->tanggal_mulai->format('d M Y') : '-' }}
                                                </td>
                                                <td class="text-center">

                                                    <a href="{{ route('admin.OB_show',$OB->id) }}"
                                                        class="btn btn-success btn-sm"
                                                        title="Detail">

                                                        <i class="fa fa-eye"></i>

                                                    </a>

                                                    <a href="{{ route('admin.OB_edit',$OB->id) }}"
                                                        class="btn btn-info btn-sm"
                                                        title="Edit">

                                                        <i class="fa fa-edit"></i>

                                                    </a>

                                                </td>

                                            </tr>

                                            @empty

                                            <tr>

                                                <td colspan="10" class="text-center">

                                                    Tidak ada data ditemukan.

                                                </td>

                                            </tr>

                                            @endforelse

                                        </tbody>

                                    </table>
                                </div>
                                <div class="row mt-3">

                                    <div class="col-md-6">

                                        @if($onlinebilling->count())

                                        Showing

                                        {{ $onlinebilling->firstItem() }}

                                        to

                                        {{ $onlinebilling->lastItem() }}

                                        of

                                        {{ $onlinebilling->total() }}

                                        entries

                                        @else

                                        Showing 0 entries

                                        @endif

                                    </div>


                                </div>

                                <div class=" pull-right">

                                    {{ $onlinebilling->links() }}

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