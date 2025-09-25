<!DOCTYPE html>
<html lang="en">

<head>
    @include('noc.partials.style')
</head>

<body>
    <div class="container-scroller">

        @include('noc.partials.navbar')
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
                        <a class="nav-link" href="{{ url('noc/dashboard') }}">
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
                                    <a class="nav-link" href="{{url('noc/instalasi')}}">Instalasi</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{url('noc/maintenance')}}">Maintenance</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{url('noc/upgrade')}}">Upgrade</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{url('noc/downgrade')}}">Downgrade</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{url('noc/gantivendor')}}">Ganti Vendor</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{url('noc/dismantle')}}">Dismantle</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{url('noc/relokasi')}}">Relokasi</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{url('noc/requestbarang')}}">Request Barang</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{url('noc/OB')}}">
                            <span class="menu-title">Online Billing</span>
                            <i class="mdi mdi-database-outline menu-icon"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('noc.sitedismantle') }}">
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
                            </span> Detail Work Order
                        </h3>
                        @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        @endif

                        @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            @foreach ($errors->all() as $error)
                            {{ $error }}
                            @endforeach
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
                                    <p><strong>No Order: </strong> {{ $getGantivendor->no_spk }}</p>
                                    <p><strong>Diterbitkan oleh:</strong> {{ $getGantivendor->admin->name }}</p>
                                    <p><strong>Tanggal Diterbitkan:</strong> {{ $getGantivendor->created_at->translatedFormat('d M Y, H:i:s') }}</p>
                                    <p><strong>Status:</strong>
                                        @if($getGantivendor->status=='Pending')
                                        <span class="badge badge-pill badge-danger">Pending</span>
                                        @elseif($getGantivendor->status=='On Progress')
                                        <span class="badge badge-pill badge-info">On Progress</span>
                                        @elseif($getGantivendor->status=='Rejected')
                                        <span class="badge badge-pill badge-dark">Rejected</span>
                                        @elseif($getGantivendor->status=='Completed')
                                        <span class="badge badge-pill badge-success">Completed</span>
                                        @endif
                                    </p>
                                    <br>
                                    <!-- Foto Pelanggan -->
                                    @if($getGantivendor->onlineBilling->pelanggan && $getGantivendor->onlineBilling->pelanggan->foto)
                                    <img src="{{ asset('storage/pelanggan/' . $getGantivendor->onlineBilling->pelanggan->foto) }}" alt="Foto Pelanggan" style="width: 150px; height: auto;">
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
                                    <p><strong>Nama Pelanggan:</strong> {{ $getGantivendor->onlineBilling->pelanggan->nama_pelanggan }}</p>
                                    <p><strong>No Jaringan:</strong> {{ $getGantivendor->onlineBilling->no_jaringan}}</p>
                                    <p><strong>Nama Gedung:</strong> {{ $getGantivendor->onlineBilling->pelanggan->nama_gedung}}</p>
                                    <p><strong>Alamat:</strong> {{ $getGantivendor->onlineBilling->pelanggan->alamat}}</p>
                                    <p><strong>Layanan:</strong> {{ $getGantivendor->onlineBilling->layanan }}</p>
                                    <p>
                                        <strong>Bandwidth:</strong>
                                        {{ $getGantivendor->onlineBilling->bandwidth }} {{ $getGantivendor->onlineBilling->satuan }}

                                    </p>
                                    <p><strong>Vlan:</strong> {{ $getGantivendor->onlineBilling->vlan }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Card 3: Site -->
                        <div class="col">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Site: </h5>
                                    <p><strong>Nama Perusahaan:</strong> {{ $getGantivendor->onlineBilling->instansi?->nama_instansi }}</p>
                                    <p><strong>Nama Site:</strong> {{ $getGantivendor->onlineBilling->nama_site }}</p>
                                    <p><strong>Alamat:</strong> {{ $getGantivendor->onlineBilling->alamat_pemasangan }}</p>
                                    <p><strong>PIC:</strong> {{ $getGantivendor->onlineBilling->nama_pic }}</p>
                                    <p><strong>Nomer PIC:</strong> {{ $getGantivendor->onlineBilling->no_pic }}</p>
                                    <p><strong>Vendor:</strong> {{ $getGantivendor->onlineBilling->vendor?->nama_vendor }} ({{$getGantivendor->onlineBilling->sid_vendor}})</p>
                                    <p><strong>Keterangan:</strong> {{ $getGantivendor->keterangan }}</p>

                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Card untuk tabel progress survey -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Vendor Baru</h4>
                                    @if ($getGantivendor->status === 'On Progress')
                                    <a href="{{ route('noc.gantivendor.inputsidbaru', $getGantivendor->id) }}" class="btn btn-info mb-3">
                                        Input SID Baru</a>
                                    @endif
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th style=" text-align: center; vertical-align: middle;">No</th>
                                                    <th style=" text-align: center; vertical-align: middle;">Nama Vendor</th>
                                                    <th style=" text-align: center; vertical-align: middle;">Contact</th>
                                                    <th style=" text-align: center; vertical-align: middle;">SID Baru</th>

                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td style=" text-align: center; vertical-align: middle;">1</td>
                                                    <td style=" text-align: center; vertical-align: middle;">{{ optional($getGantivendor->vendor)->nama_vendor ?? 'Belum Dipilih' }}</td>
                                                    <td style=" text-align: center; vertical-align: middle;">{{ optional($getGantivendor->vendor)->contact ?? '-' }}</td>
                                                    <td style=" text-align: center; vertical-align: middle;"> {{ $getGantivendor->sid_baru ?? 'Belum diisi' }}</td>

                                                </tr>
                                            </tbody>
                                        </table>
                                        <p style="font-size: small;"><em>*Kolom SID Baru, wajib diisi</em></p>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Card untuk tabel progress survey -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Progres Ganti Vendor</h4>
                                    @if ($getGantivendor->status === 'On Progress' || $getGantivendor->status === 'Completed')
                                    <a href="{{ route('noc_gantivendor_add_progress', $getGantivendor->id) }}" class="btn btn-info mb-3">
                                        Add Progress</a>

                                    @endif
                                    <div class=" table-responsive">

                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th style=" text-align: center; vertical-align: middle;">No</th>
                                                    <th style=" text-align: center; vertical-align: middle;">Tanggal</th>
                                                    <th style=" text-align: center; vertical-align: middle;">User</th>
                                                    <th style=" text-align: center; vertical-align: middle;">Status</th>
                                                    <th style=" text-align: center; vertical-align: middle;">Foto</th>
                                                    <th style=" text-align: center; vertical-align: middle;">Keterangan</th>

                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($progressList as $progress)
                                                <tr>
                                                    <td style=" text-align: center; vertical-align: middle;">{{ $loop->iteration }}</td>
                                                    <td style=" text-align: center; vertical-align: middle;">{{ $progress->created_at->translatedFormat('d F Y, H:i') }}</td>
                                                    <td style=" text-align: center; vertical-align: middle;">{{ $progress->userPSB->name }}</td>
                                                    <td style=" text-align: center; vertical-align: middle;">@if($progress->status=='Pending')
                                                        <span class="badge badge-pill badge-danger">Pending</span>
                                                        @elseif($progress->status=='On Progress')
                                                        <span class="badge badge-pill badge-info">On Progress</span>
                                                        @elseif($progress->status=='Rejected')
                                                        <span class="badge badge-pill badge-dark">Rejected</span>
                                                        @elseif($progress->status=='Completed')
                                                        <span class="badge badge-pill badge-success">Completed</span>

                                                        @endif
                                                    </td>
                                                    <td style=" text-align: center; vertical-align: middle;">
                                                        @php
                                                        $photos = $progress->photos;
                                                        @endphp
                                                        @if ($photos->isNotEmpty())
                                                        <!-- Tombol untuk membuka modal -->
                                                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#photoModal{{ $progress->id }}">
                                                            Lihat Foto
                                                        </button>

                                                        <!-- Modal untuk menampilkan foto -->
                                                        <div class="modal fade" id="photoModal{{ $progress->id }}" tabindex="-1" role="dialog" aria-labelledby="photoModalLabel{{ $progress->id }}" aria-hidden="true">
                                                            <div class="modal-dialog modal-lg" role="document">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="photoModalLabel{{ $progress->id }}">Foto</h5>
                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                            <span aria-hidden="true">&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    <div class="modal-body d-flex flex-row flex-wrap justify-content-center">
                                                                        @foreach ($photos as $photo)
                                                                        <div class="m-2 d-flex flex-column align-items-center">
                                                                            <img src="{{ asset('uploads/' . $photo->file_path) }}" alt="Foto Progress"
                                                                                style="width: 200px; height: 200px; object-fit: cover; border-radius: 0;">
                                                                            <a href="{{ asset('uploads/' . $photo->file_path) }}" download class="btn btn-info mt-2">Download Foto</a>
                                                                        </div>
                                                                        @endforeach
                                                                    </div>


                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @else
                                                        Tidak ada foto
                                                        @endif
                                                    </td>
                                                    <td style="text-align: center justify; vertical-align: middle; padding: 8px; line-height: 1.3;" class="keterangan-cell">
                                                        {{ $progress->keterangan }}
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="6" class="text-center">Tidak ada progress yang tersedia.</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- Tombol Kembali -->
                                    <a href="{{ route('noc.gantivendor') }}" class="btn btn-info mt-3">
                                        <i class="fa fa-arrow-left"></i> Kembali
                                    </a>

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
    @include('noc.partials.script')
</body>

</html>