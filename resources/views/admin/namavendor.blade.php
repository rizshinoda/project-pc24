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
                            </span> Data Vendor
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

                                {{-- =========================
                SEARCH & BUTTON
            ========================== --}}
                                <form method="GET"
                                    action="{{ route('admin.namavendor') }}"
                                    class="mb-4">

                                    <div class="row">

                                        {{-- Pencarian --}}
                                        <div class="col-md-6 mb-3">
                                            <input type="text"
                                                name="search"
                                                class="form-control contoh1"
                                                placeholder="Cari Data"
                                                value="{{ request('search') }}">
                                        </div>

                                        {{-- Tombol --}}
                                        <div class="">

                                            <button type="submit"
                                                class="btn btn-info btn-sm mb-4">
                                                <i class="fa fa-search"></i>
                                                Cari
                                            </button>

                                            <a href="{{ route('vendor.create') }}"
                                                class="btn btn-info btn-sm mb-4">
                                                <i class="fa fa-plus"></i>
                                                Tambah Data
                                            </a>
                                            <a href="{{ route('vendor.export') }}"
                                                class="btn btn-sm btn-success pull-right">

                                                Export Excel

                                            </a>
                                        </div>

                                    </div>
                                </form>


                                {{-- =========================
                TABLE
            ========================== --}}
                                <div class="table-responsive">

                                    <table class="table table-hover wrap">

                                        <thead>
                                            <tr>
                                                <th style="text-align: center; vertical-align: middle;">No</th>
                                                <th style="text-align: center; vertical-align: middle;">Nama Vendor</th>
                                                <th style="text-align: center; vertical-align: middle;">Contact</th>
                                                <th style="text-align: center; vertical-align: middle;">Total Relasi</th>
                                                <th style="text-align: center; vertical-align: middle;">Aksi</th>
                                            </tr>
                                        </thead>

                                        <tbody>

                                            @forelse ($getVendor as $key => $vendor)

                                            @php
                                            $totalRelasi =
                                            $vendor->surveys_count +
                                            $vendor->installs_count +
                                            $vendor->gantivendor_count +
                                            $vendor->online_billings_count;
                                            @endphp

                                            <tr>

                                                {{-- No --}}
                                                <td style="text-align: center; vertical-align: middle;">
                                                    {{ $getVendor->firstItem() + $key }}
                                                </td>

                                                {{-- Nama Vendor --}}
                                                <td style="vertical-align: middle;">
                                                    {{ $vendor->nama_vendor }}
                                                </td>

                                                {{-- Contact --}}
                                                <td style="text-align: center; vertical-align: middle;">
                                                    {{ $vendor->contact ?? '-' }}
                                                </td>

                                                {{-- Total Relasi --}}
                                                <td style="text-align: center; vertical-align: middle;">

                                                    <span class="badge {{ $totalRelasi == 0 ? 'badge-danger' : 'badge-info' }}">
                                                        {{ $totalRelasi }}
                                                    </span>

                                                </td>

                                                {{-- Aksi --}}
                                                <td style="text-align: center; vertical-align: middle;">

                                                    {{-- Lihat Relasi --}}
                                                    <a
                                                        href="{{ route('vendor.relasi', $vendor->id) }}"
                                                        class="btn btn-sm btn-info"
                                                        title="Lihat Relasi">
                                                        <i class="fa fa-search"></i>
                                                    </a>

                                                    {{-- Edit --}}
                                                    <a
                                                        href="{{ route('vendor.edit', $vendor->id) }}"
                                                        class="btn btn-sm btn-warning"
                                                        title="Edit Vendor">
                                                        <i class="fa fa-edit"></i>
                                                    </a>

                                                    {{-- Hapus --}}
                                                    @if ($totalRelasi == 0)

                                                    <form
                                                        action="{{ route('vendor.destroy', $vendor->id) }}"
                                                        method="POST"
                                                        style="display:inline-block;"
                                                        class="form-hapus-vendor">

                                                        @csrf
                                                        @method('DELETE')

                                                        <button
                                                            type="submit"
                                                            class="btn btn-sm btn-danger"
                                                            title="Hapus">

                                                            <i class="fa fa-trash"></i>

                                                        </button>

                                                    </form>

                                                    @else

                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-secondary"
                                                        title="Tidak dapat dihapus karena masih memiliki relasi"
                                                        disabled>

                                                        <i class="fa fa-trash"></i>

                                                    </button>

                                                    @endif

                                                </td>

                                            </tr>

                                            @empty

                                            <tr>
                                                <td colspan="5"
                                                    class="text-center"
                                                    style="padding: 30px;">
                                                    Tidak ada data vendor.
                                                </td>
                                            </tr>

                                            @endforelse

                                        </tbody>
                                    </table>

                                </div>


                                {{-- =========================
                INFORMATION
            ========================== --}}
                                <div class="mt-3">

                                    Showing
                                    {{ $getVendor->firstItem() ?? 0 }}
                                    to
                                    {{ $getVendor->lastItem() ?? 0 }}
                                    of
                                    {{ $getVendor->total() }}
                                    entries

                                </div>


                                {{-- =========================
                PAGINATION
            ========================== --}}
                                <div class="d-flex justify-content-end">

                                    {{ $getVendor->links() }}

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