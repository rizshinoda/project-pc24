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
                                <li class="nav-item">
                                    <a class="nav-link" href="{{url('helpdesk/instalasi')}}">Instalasi</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{url('helpdesk/upgrade')}}">Upgrade</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{url('helpdesk/downgrade')}}">Downgrade</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{url('helpdesk/relokasi')}}">Relokasi</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{url('helpdesk/dismantle')}}">Dismantle</a>
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
            <!-- Main Panel -->
            <div class="main-panel">
                <div class="content-wrapper">
                    <div class="page-header">
                        <h3 class="page-title">
                            <span class="page-title-icon bg-gradient-danger text-white me-2">
                                <i class="mdi mdi-home"></i>
                            </span> Site Dismantle
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
                                <h4>Daftar Dismantle</h4>
                                <!-- Form Pencarian dan Filter -->
                                <form method="GET" action="{{ route('hd.sitedismantle') }}" class="mb-4">
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

                                <div class=" table-responsive">
                                    <table class="table table-bordered wrap">
                                        <thead>
                                            <tr>
                                                <th style="text-align: center; vertical-align: middle;">No</th>
                                                <th style="text-align: center; vertical-align: middle;">No <br> Jaringan</th>
                                                <th style="text-align: center; vertical-align: middle;">Nama <br> Pelanggan</th>
                                                <th style="text-align: center; vertical-align: middle;">Perusahaan</th>
                                                <th style="text-align: center; vertical-align: middle;">Nama <br> Site</th>
                                                <th style="text-align: center; vertical-align: middle;">Alamat <br> Pemasangan</th>
                                                <th style="text-align: center; vertical-align: middle;">Vlan</th>
                                                <th style="text-align: center; vertical-align: middle;">Volume</th>
                                                <th style="text-align: center; vertical-align: middle;">Tanggal <br>Aktif</th>
                                                <th style="text-align: center; vertical-align: middle;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($onlinebilling as $key => $OB)
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
                                                <td style="text-align: center; vertical-align: middle;">{{ $OB->created_at->format('d M Y') }}</td>
                                                <td style="text-align: center; vertical-align: middle;">
                                                    <a href="{{ route('hd.showsitedismantle', $OB->id) }}" class="btn btn-success btn-sm " style="display:inline-block; padding: 8px 11px;"><i class="fa fa-eye"></i></a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-3">
                                    Showing
                                    {{$onlinebilling->firstItem()}}
                                    to
                                    {{$onlinebilling->lastItem()}}
                                    of
                                    {{$onlinebilling->total()}}
                                    entries

                                </div>
                                <div class="pull-right">
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
    @include('helpdesk.partials.script')
</body>

</html>