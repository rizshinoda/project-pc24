<!DOCTYPE html>
<html lang="en">

<head>
    @include('psb.partials.style')
</head>

<body>
    <div class="container-scroller">

        @include('psb.partials.navbar')
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
                        <a class="nav-link" href="{{ url('psb/dashboard') }}">
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
                                    <a class="nav-link" href="{{url('psb/survey')}}">Survey</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{url('psb/instalasi')}}">Instalasi</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{url('psb/maintenance')}}">Maintenance</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{url('psb/upgrade')}}">Upgrade</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{url('psb/downgrade')}}">Downgrade</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{url('psb/gantivendor')}}">Ganti Vendor</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{url('psb/dismantle')}}">Dismantle</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{url('psb/relokasi')}}">Relokasi</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{url('psb/requestbarang')}}">Request Barang</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{url('psb/OB')}}">
                            <span class="menu-title">Online Billing</span>
                            <i class="mdi mdi-database-outline menu-icon"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('psb.sitedismantle') }}">
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
                    </div>


                    <!-- Menampilkan detail survey -->
                    <div class="row row-cols-1 row-cols-md-3 g-3">
                        <!-- Card 1: WO Info -->
                        <div class="col">
                            <div class="card h-100">
                                <div class="card-body ">
                                    <h5 class="card-title">Details:</h5>
                                    <p><strong>No Order: </strong> {{ $getDowngrade->no_spk }}</p>
                                    <p><strong>Diterbitkan oleh:</strong> {{ $getDowngrade->admin->name }}</p>
                                    <p><strong>Tanggal Diterbitkan:</strong> {{ $getDowngrade->created_at->translatedFormat('d M Y, H:i:s') }}</p>
                                    <p><strong>Status:</strong>
                                        @if($getDowngrade->status=='Pending')
                                        <span class="badge badge-pill badge-danger">Pending</span>
                                        @elseif($getDowngrade->status=='On Progress')
                                        <span class="badge badge-pill badge-info">On Progress</span>
                                        @elseif($getDowngrade->status=='Canceled')
                                        <span class="badge badge-pill badge-warning">Cancelled</span>
                                        @elseif($getDowngrade->status=='Completed')
                                        <span class="badge badge-pill badge-success">Completed</span>
                                        @endif
                                    </p>
                                    <br>
                                    <!-- Foto Pelanggan -->
                                    @if($getDowngrade->onlineBilling->pelanggan && $getDowngrade->onlineBilling->pelanggan->foto)
                                    <img src="{{ asset('storage/pelanggan/' . $getDowngrade->onlineBilling->pelanggan->foto) }}" alt="Foto Pelanggan" style="width: 150px; height: auto;">
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
                                    <p><strong>Nama Pelanggan:</strong> {{ $getDowngrade->onlineBilling->pelanggan->nama_pelanggan }}</p>
                                    <p><strong>No Jaringan:</strong> {{ $getDowngrade->onlineBilling->no_jaringan}}</p>
                                    <p><strong>Nama Gedung:</strong> {{ $getDowngrade->onlineBilling->pelanggan->nama_gedung}}</p>
                                    <p><strong>Alamat:</strong> {{ $getDowngrade->onlineBilling->pelanggan->alamat}}</p>
                                    <p><strong>Layanan:</strong> {{ $getDowngrade->onlineBilling->layanan }}</p>
                                    <p>
                                        <strong>Bandwidth:</strong>
                                        {{ $getDowngrade->onlineBilling->bandwidth }} {{ $getDowngrade->onlineBilling->satuan }}
                                        <span style="font-size: larger;">➔</span>
                                        {{ $getDowngrade->bandwidth_baru }} {{ $getDowngrade->satuan }}
                                    </p>
                                    <p><strong>Vlan:</strong> {{ $getDowngrade->onlineBilling->vlan }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Card 3: Site -->
                        <div class="col">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Site: </h5>
                                    <p><strong>Nama Perusahaan:</strong> {{ $getDowngrade->onlineBilling->instansi?->nama_instansi }}</p>
                                    <p><strong>Nama Site:</strong> {{ $getDowngrade->onlineBilling->nama_site }}</p>
                                    <p><strong>Alamat:</strong> {{ $getDowngrade->onlineBilling->alamat_pemasangan }}</p>
                                    <p><strong>PIC:</strong> {{ $getDowngrade->onlineBilling->nama_pic }}</p>
                                    <p><strong>Nomer PIC:</strong> {{ $getDowngrade->onlineBilling->no_pic }}</p>
                                    <p><strong>Vendor:</strong> {{ $getDowngrade->onlineBilling->vendor?->nama_vendor }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="container mt-4">
                        @php
                        // Tahapan normal
                        $steps = ['Pending', 'On Progress', 'Completed'];

                        // Ambil status dari progress
                        $currentStatus = ucfirst($getDowngrade->status ?? 'Pending');

                        // Cek apakah status termasuk flow gagal
                        $isCanceledOrRejected = in_array(strtolower($currentStatus), ['canceled', 'cancelled', 'rejected']);
                        $currentStep = array_search($currentStatus, $steps);
                        if ($currentStep === false) $currentStep = 0;
                        @endphp

                        @if($isCanceledOrRejected)
                        {{-- Tampilan khusus untuk status gagal --}}
                        <div class="alert alert-danger text-center fw-bold py-4 rounded">
                            <i class="bi bi-x-circle-fill me-2"></i>
                            Work Order <span class="text-uppercase">{{ $currentStatus }}</span>
                        </div>
                        @else
                        {{-- Progress bar normal --}}
                        <div class="stepper">
                            @foreach($steps as $index => $step)
                            <div class="step 
                    {{ $index < $currentStep ? 'completed' : '' }} 
                    {{ $index == $currentStep ? 'active' : '' }}">
                                <div class="step-circle">
                                    @if($index < $currentStep)
                                        <i class="bi bi-check-lg"></i>
                                        @else
                                        {{ $index + 1 }}
                                        @endif
                                </div>
                                <div class="step-label">{{ $step }}</div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    <!-- Card untuk tabel progress survey -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    @if($getDowngrade->status == 'Pending')
                                    <!-- Tombol Approve dan Reject -->
                                    <!-- Tombol Approve -->
                                    <form action="{{ route('psb.downgrade.approve', $getDowngrade->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="button" class="btn btn-success" onclick="confirmApproval('{{ route('psb.downgrade.approve', $getDowngrade->id) }}')">
                                            <i class="fa fa-check"></i> Approve
                                        </button>
                                    </form>


                                    @endif
                                    <h4 class="card-title mt-2">Progres Downgrade</h4>
                                    @if ($getDowngrade->status === 'On Progress' || $getDowngrade->status === 'Completed')
                                    <a href="{{ route('psb_downgrade_add_progress', $getDowngrade->id) }}" class="btn btn-info mb-3">
                                        Add Progress</a>
                                    <a href="{{ route('psb.downgrade.print', $getDowngrade->id) }}" class="btn btn-info pull-right mb-3">Cetak SPK</a>

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
                                                        @elseif($progress->status=='Canceled')
                                                        <span class="badge badge-pill badge-warning">Cancelled</span>
                                                        @elseif($progress->status=='Completed')
                                                        <span class="badge badge-pill badge-success">Completed</span>
                                                        @elseif($progress->status=='Shipped')
                                                        <span class="badge badge-pill badge-primary">Shipped</span>
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
                                    <a href="{{ route('psb.downgrade') }}" class="btn btn-info mt-3">
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
                        <span class="text-muted float-none float-sm-right d-block mt-1 mt-sm-0 text-center">Hand-crafted & made with Rizal<i class="mdi mdi-heart text-danger"></i></span>
                    </div>
                </footer>
                <!-- partial -->
            </div>
            <!-- main-panel ends -->
        </div>

    </div>
    @include('psb.partials.script')
</body>

</html>