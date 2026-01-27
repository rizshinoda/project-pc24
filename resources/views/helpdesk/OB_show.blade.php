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
                            </span> Detail Site
                        </h3>
                        @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        @endif
                    </div>




                    <!-- Menampilkan detail survey -->
                    <div class="row row-cols-1 row-cols-md-3 g-3">
                        <!-- Card 1: WO Info -->
                        <div class="col">
                            <div class="card h-100">
                                <div class="card-body ">
                                    <h5 class="card-title">Details:</h5>
                                    <p><strong>Diterbitkan oleh:</strong> {{ $onlinebilling->admin->name }}</p>
                                    <p><strong>Tanggal Instalasi:</strong>
                                        {{ $onlinebilling->tanggal_instalasi ? \Carbon\Carbon::parse($onlinebilling->tanggal_instalasi)->translatedFormat('d M Y'): 'Belum diatur' }}
                                    </p>
                                    <p><strong>Durasi:</strong> {{ $onlinebilling->durasi }} {{ $onlinebilling->nama_durasi }}</p>
                                    <p><strong>Awal Kontrak:</strong>
                                        {{ $onlinebilling->tanggal_mulai ? \Carbon\Carbon::parse($onlinebilling->tanggal_mulai)->translatedFormat('d M Y'): 'Belum diatur' }}
                                    </p>
                                    <p><strong>Akhir Kontrak:</strong> {{ $onlinebilling->tanggal_akhir ?  \Carbon\Carbon::parse($onlinebilling->tanggal_akhir)->translatedFormat('d M Y'): 'Belum diatur'}} </p>

                                    <br>
                                    <!-- Foto Pelanggan -->
                                    @if($onlinebilling->pelanggan && $onlinebilling->pelanggan->foto)
                                    <img src="{{ asset('storage/pelanggan/' . $onlinebilling->pelanggan->foto) }}" alt="Foto Pelanggan" style="width: 150px; height: auto;">
                                    @else
                                    <p>Tidak ada foto pelanggan</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Card 2: Pelanggan -->
                        <div class="col">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Pelanggan: </h5>
                                    <p><strong>Nama Pelanggan:</strong> {{ $onlinebilling->pelanggan->nama_pelanggan }}</p>
                                    <p><strong>No Jaringan:</strong> {{ $onlinebilling->no_jaringan}}</p>
                                    <p><strong>Nama Gedung:</strong> {{ $onlinebilling->pelanggan->nama_gedung}}</p>
                                    <p><strong>Alamat:</strong> {{ $onlinebilling->pelanggan->alamat}}</p>
                                    <p><strong>Layanan:</strong> {{ $onlinebilling->layanan }}</p>
                                    <p><strong>Volume:</strong> {{ $onlinebilling->bandwidth }} {{ $onlinebilling->satuan }}</p>
                                    <p><strong>Vlan:</strong> {{ $onlinebilling->vlan }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Card 3: Site -->
                        <div class="col">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Site: </h5>
                                    <p><strong>Nama Perusahaan:</strong> {{ $onlinebilling->instansi?->nama_instansi }}</p>
                                    <p><strong>Nama Site:</strong> {{ $onlinebilling->nama_site }}</p>
                                    <p><strong>Alamat:</strong> {{ $onlinebilling->alamat_pemasangan }}</p>
                                    <p><strong>PIC:</strong> {{ $onlinebilling->nama_pic }}</p>
                                    <p><strong>Nomer PIC:</strong> {{ $onlinebilling->no_pic }}</p>
                                    <p><strong>Vendor:</strong> {{ $onlinebilling->vendor?->nama_vendor }} ({{ $onlinebilling->sid_vendor }})</p>
                                    <p><strong>Status:</strong> @if($onlinebilling->status=='active')
                                        <span class="badge badge-pill badge-success">Aktif</span>
                                        @else($onlinebilling->status=='dismantle')
                                        <span class="badge badge-pill badge-danger">Dismantle</span>
                                        @endif
                                    </p>

                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Card untuk tabel progress site -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div>
                                <div class="card-body text-center">
                                    <a href="{{ route('hd.maintenance_create', $onlinebilling->id) }}" class="btn btn-primary btn-sm ">
                                        Maintenance
                                    </a>
                                    <a href="{{ route('hd.gantivendor_create', $onlinebilling->id) }}" class="btn btn-dark btn-sm ">
                                        Ganti Vendor
                                    </a>

                                </div>
                            </div>
                        </div>
                    </div>



                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Status Proses</h4>
                                    <div class=" table-responsive">

                                        <!-- Tampilkan status untuk berbagai proses -->
                                        <table class=" table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Admin</th>
                                                    <th>Proses</th>
                                                    <th>Status</th>
                                                    <th>Tanggal Dibuat</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($statuses as $status)
                                                <tr>
                                                    <td>{{$loop->iteration }}</td>
                                                    <td>{{$status->admin->name }}</td>
                                                    <td> @if($status->process=='Upgrade')
                                                        <span class="badge badge badge-success">Upgrade</span>
                                                        @elseif($status->process=='Downgrade')
                                                        <span class="badge badge badge-warning">Downgrade</span>
                                                        @elseif($status->process=='Dismantle')
                                                        <span class="badge badge badge-danger">Dismantle</span>
                                                        @elseif($status->process=='Relokasi')
                                                        <span class="badge badge badge-info">Relokasi</span>
                                                        @elseif($status->process=='Ganti Vendor')
                                                        <span class="badge badge badge-dark">Ganti Vendor</span>
                                                        @elseif($status->process=='Maintenance')
                                                        <span class="badge badge badge-primary">Maintenance</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($status->status=='Pending')
                                                        <span class="badge badge-pill badge-danger">Pending</span>
                                                        @elseif($status->status=='On Progress')
                                                        <span class="badge badge-pill badge-info">On Progress</span>
                                                        @elseif($status->status=='Canceled')
                                                        <span class="badge badge-pill badge-warning">Cancelled</span>
                                                        @elseif($status->status=='Completed')
                                                        <span class="badge badge-pill badge-success">Completed</span>
                                                        @elseif($status->status=='Shipped')
                                                        <span class="badge badge-pill badge-primary">Shipped</span>
                                                        @elseif($status->status=='Rejected')
                                                        <span class="badge badge-pill badge-dark">Rejected</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $status->created_at->translatedFormat('d M Y') }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <a href="{{ route('hd.OB') }}" class="btn btn-info mt-3"><i class="fa fa-arrow-left"></i> Kembali</a>

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