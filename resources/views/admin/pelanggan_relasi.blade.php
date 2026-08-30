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
                            </span> Data Pelanggan
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

                                {{-- Header --}}
                                <div class="d-flex justify-content-between align-items-center mb-4">

                                    <div>
                                        <h4 class="card-title mb-1">
                                            Relasi Pelanggan
                                        </h4>

                                        <p class="text-muted mb-0">
                                            {{ $pelanggan->nama_pelanggan }}
                                        </p>
                                    </div>

                                    <a
                                        href="{{ route('admin.pelanggan') }}"
                                        class="btn btn-info btn-sm">

                                        <i class="fa fa-arrow-left"></i>
                                        Kembali

                                    </a>

                                </div>


                                {{-- Informasi Pelanggan --}}
                                <div class="row mb-4">

                                    <div class="col-md-4">
                                        <strong>Nama Pelanggan</strong>
                                        <br>
                                        {{ $pelanggan->nama_pelanggan }}
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Kode Pelanggan</strong>
                                        <br>
                                        {{ $pelanggan->kode_pelanggan }}
                                    </div>
                                    <div class="col-md-4">
                                        <strong>No Pelanggan</strong>
                                        <br>
                                        {{ $pelanggan->no_pelanggan }}
                                    </div>



                                </div>


                                {{-- ============================= --}}
                                {{-- SURVEY --}}
                                {{-- ============================= --}}

                                <h5 class="mb-3">
                                    Survey

                                    <span class="badge badge-info">
                                        {{ $survey->count() }}
                                    </span>
                                </h5>

                                <div class="table-responsive mb-5">

                                    <table class="table table-hover">

                                        <thead>
                                            <tr>

                                                <th style="text-align:center;">
                                                    <input
                                                        type="checkbox"
                                                        id="checkAllSurvey">
                                                </th>

                                                <th style="text-align:center;">
                                                    ID
                                                </th>

                                                <th>
                                                    No SPK
                                                </th>

                                                <th>
                                                    Nama Site
                                                </th>

                                                <th style="text-align:center;">
                                                    Status
                                                </th>

                                            </tr>
                                        </thead>

                                        <tbody>

                                            @forelse ($survey as $item)

                                            <tr>

                                                <td style="text-align:center;">

                                                    <input
                                                        type="checkbox"
                                                        name="survey_ids[]"
                                                        value="{{ $item->id }}"
                                                        class="survey-checkbox"
                                                        form="formPindahkanRelasi">

                                                </td>

                                                <td style="text-align:center;">
                                                    {{ $item->id }}
                                                </td>

                                                <td>
                                                    {{ $item->no_spk }}
                                                </td>

                                                <td>
                                                    {{ $item->nama_site }}
                                                </td>

                                                <td style="text-align:center;">
                                                    {{ $item->status }}
                                                </td>

                                            </tr>

                                            @empty

                                            <tr>
                                                <td colspan="5" class="text-center text-muted">
                                                    Tidak ada relasi Survey.
                                                </td>
                                            </tr>

                                            @endforelse

                                        </tbody>

                                    </table>

                                </div>

                                {{-- ============================= --}}
                                {{-- INSTALASI --}}
                                {{-- ============================= --}}

                                <h5 class="mb-3">
                                    Instalasi

                                    <span class="badge badge-info">
                                        {{ $instalasi->count() }}
                                    </span>
                                </h5>

                                <div class="table-responsive mb-5">

                                    <table class="table table-hover">

                                        <thead>
                                            <tr>

                                                <th style="text-align:center;">
                                                    <input
                                                        type="checkbox"
                                                        id="checkAllInstalasi">
                                                </th>

                                                <th style="text-align:center;">
                                                    ID
                                                </th>

                                                <th>
                                                    No SPK
                                                </th>

                                                <th>
                                                    No Jaringan
                                                </th>

                                                <th>
                                                    Nama Site
                                                </th>

                                                <th style="text-align:center;">
                                                    Status
                                                </th>

                                            </tr>
                                        </thead>

                                        <tbody>

                                            @forelse ($instalasi as $item)

                                            <tr>

                                                <td style="text-align:center;">

                                                    <input
                                                        type="checkbox"
                                                        name="instalasi_ids[]"
                                                        value="{{ $item->id }}"
                                                        class="instalasi-checkbox"
                                                        form="formPindahkanRelasi">

                                                </td>

                                                <td style="text-align:center;">
                                                    {{ $item->id }}
                                                </td>

                                                <td>
                                                    {{ $item->no_spk }}
                                                </td>

                                                <td>
                                                    {{ $item->no_jaringan }}
                                                </td>

                                                <td>
                                                    {{ $item->nama_site }}
                                                </td>

                                                <td style="text-align:center;">
                                                    {{ $item->status }}
                                                </td>

                                            </tr>

                                            @empty

                                            <tr>
                                                <td colspan="6" class="text-center text-muted">
                                                    Tidak ada relasi Instalasi.
                                                </td>
                                            </tr>

                                            @endforelse

                                        </tbody>

                                    </table>

                                </div>

                                {{-- ============================= --}}
                                {{-- ONLINE BILLING --}}
                                {{-- ============================= --}}

                                <h5 class="mb-3">
                                    Online Billing

                                    <span class="badge badge-info">
                                        {{ $onlineBilling->count() }}
                                    </span>
                                </h5>

                                <div class="table-responsive mb-5">

                                    <table class="table table-hover">

                                        <thead>
                                            <tr>

                                                <th style="text-align:center;">
                                                    <input
                                                        type="checkbox"
                                                        id="checkAllBilling">
                                                </th>

                                                <th style="text-align:center;">
                                                    ID
                                                </th>

                                                <th>
                                                    No Jaringan
                                                </th>

                                                <th>
                                                    Nama Site
                                                </th>

                                                <th>
                                                    Vendor
                                                </th>

                                                <th style="text-align:center;">
                                                    Status
                                                </th>

                                            </tr>
                                        </thead>

                                        <tbody>

                                            @forelse ($onlineBilling as $item)

                                            <tr>

                                                <td style="text-align:center;">

                                                    <input
                                                        type="checkbox"
                                                        name="online_billing_ids[]"
                                                        value="{{ $item->id }}"
                                                        class="billing-checkbox"
                                                        form="formPindahkanRelasi">

                                                </td>

                                                <td style="text-align:center;">
                                                    {{ $item->id }}
                                                </td>

                                                <td>
                                                    {{ $item->no_jaringan }}
                                                </td>

                                                <td>
                                                    {{ $item->nama_site }}
                                                </td>

                                                <td>
                                                    {{ $item->vendor?->nama_vendor ?? '-' }}
                                                </td>

                                                <td style="text-align:center;">
                                                    {{ $item->status }}
                                                </td>

                                            </tr>

                                            @empty

                                            <tr>
                                                <td colspan="6" class="text-center text-muted">
                                                    Tidak ada relasi Online Billing.
                                                </td>
                                            </tr>

                                            @endforelse

                                        </tbody>

                                    </table>

                                </div>
                            </div>
                            <div class="card mt-4">

                                <div class="card-body">

                                    <h5 class="mb-3">
                                        Pindahkan Relasi
                                    </h5>

                                    <form
                                        id="formPindahkanRelasi"
                                        method="POST"
                                        action="{{ route('pelanggan.pindahkan-relasi', $pelanggan->id) }}">

                                        @csrf

                                        <div class="row align-items-end">

                                            <div class="col-md-6">

                                                <label>
                                                    Pindahkan ke Pelanggan
                                                </label>

                                                <select
                                                    name="pelanggan_tujuan_id"
                                                    class="form-control"
                                                    required>

                                                    <option value="">
                                                        Pilih pelanggan tujuan
                                                    </option>

                                                    @foreach (
                                                    \App\Models\Pelanggan::where(
                                                    'id',
                                                    '!=',
                                                    $pelanggan->id
                                                    )
                                                    ->orderBy('nama_pelanggan')
                                                    ->get()
                                                    as $tujuan
                                                    )

                                                    <option value="{{ $tujuan->id }}">

                                                        {{ $tujuan->nama_pelanggan }} - ({{ $tujuan->kode_pelanggan }})



                                                    </option>

                                                    @endforeach

                                                </select>

                                            </div>


                                            <div class="col-md-4">

                                                <button
                                                    type="submit"
                                                    id="btnPindahkan"
                                                    class="btn btn-info"
                                                    disabled>
                                                    <i class="fa fa-exchange"></i>
                                                    Pindahkan Terpilih
                                                    (<span id="jumlahDipilih">0</span>)
                                                </button>

                                            </div>

                                        </div>

                                    </form>

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