<!DOCTYPE html>
<html lang="en">

<head>
    @include('ga.partials.style')
</head>

<body>
    <div class="container-scroller">

        @include('ga.partials.navbar')
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
                        <a class="nav-link" href="{{ url('ga/dashboard') }}">
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
                                    <a class="nav-link" href="{{url('ga/jenis')}}">Data Jenis</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{url('ga/merek')}}">Data Merek</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{url('ga/tipe')}}">Data Tipe</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('ga/stockbarang') }}">
                            <span class="menu-title">Stock Barang</span>
                            <i class="mdi mdi-database-edit-outline menu-icon"></i>
                        </a>
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
                                    <a class="nav-link" href="{{url('ga/instalasi')}}">Instalasi</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{url('ga/maintenance')}}">Maintenance</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{url('ga/requestbarang')}}">Request Barang</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{url('ga/dismantle')}}">Dismantle</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{url('ga/relokasi')}}">Relokasi</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{url('ga/OB')}}">
                            <span class="menu-title">Online Billing</span>
                            <i class="mdi mdi-database-outline menu-icon"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('ga.sitedismantle') }}">
                            <span class="menu-title">Site Dismantle</span>
                            <i class="mdi mdi-delete-circle menu-icon"></i>
                        </a>
                    </li>
            </nav>

            <!-- Main Panel -->
            <div class="main-panel">
                <div class="content-wrapper">
                    <div class="page-header">
                        <h3 class="page-title">
                            <span class="page-title-icon bg-gradient-danger text-white me-2">
                                <i class="mdi mdi-home"></i>
                            </span> Detail Request Barang
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

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title">Penerima:</h5>
                                    <p><strong>Nama:</strong> {{ $requestBarang->nama_penerima }}</p>
                                    <p><strong>Alamat:</strong> {{ $requestBarang->alamat_penerima }}</p>
                                    <p><strong>No HP:</strong> {{ $requestBarang->no_penerima }}</p>
                                    <p><strong>Request by:</strong> {{ $requestBarang->user->name }}</p>
                                    <p><strong>Status:</strong>
                                        @if($requestBarang->status == 'pending')
                                        <span class="badge badge-pill badge-danger">Pending</span>
                                        @elseif($requestBarang->status == 'approved')
                                        <span class="badge badge-pill badge-info">Approved</span>
                                        @elseif($requestBarang->status == 'rejected')
                                        <span class="badge badge-pill badge-warning">Rejected</span>
                                        @elseif($requestBarang->status == 'completed')
                                        <span class="badge badge-pill badge-success">Completed</span>
                                        @elseif($requestBarang->status == 'shipped')
                                        <span class="badge badge-pill badge-primary">Shipped</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title">Detail Request:</h5>
                                    <p><strong>Keterangan:</strong> {{ $requestBarang->keterangan }}</p>
                                    <p><strong>Barang non stock:</strong> {{ $requestBarang->non_stock }}</p>

                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card untuk tabel detail barang -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Detail Barang</h4>
                                    <div class=" table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th style=" text-align: center; vertical-align: middle;">No</th>
                                                    <th style=" text-align: center; vertical-align: middle;">Jenis</th>
                                                    <th style=" text-align: center; vertical-align: middle;">Merek</th>
                                                    <th style=" text-align: center; vertical-align: middle;">Tipe</th>
                                                    <th style=" text-align: center; vertical-align: middle;">Jumlah</th>
                                                    <th style=" text-align: center; vertical-align: middle;">Kualitas</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($requestBarang->requestBarangDetails as $detail)
                                                <tr>
                                                    <td style=" text-align: center; vertical-align: middle;">{{ $loop->iteration }}</td>
                                                    <td style=" text-align: center; vertical-align: middle;">{{ $detail->stockBarang->jenis->nama_jenis }}</td>
                                                    <td style=" text-align: center; vertical-align: middle;">{{ $detail->stockBarang->merek->nama_merek }}</td>
                                                    <td style=" text-align: center; vertical-align: middle;">{{ $detail->stockBarang->tipe->nama_tipe }}</td>
                                                    <td style=" text-align: center; vertical-align: middle;">{{ $detail->jumlah }}</td>
                                                    <td style=" text-align: center; vertical-align: middle;">{{ ucfirst($detail->stockBarang->kualitas) }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- Tombol Terima dan Tolak, hanya ditampilkan jika statusnya pending -->
                                    @if($requestBarang->status == 'pending')
                                    <!-- Tombol Approve dan Reject -->
                                    <div class="mt-3">
                                        <!-- Tombol Approve -->
                                        <form action="{{ route('ga.request_barang.approve', $requestBarang->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="button" class="btn btn-success" onclick="confirmApproval('{{ route('ga.request_barang.approve', $requestBarang->id) }}')">
                                                <i class="fa fa-check"></i> Approve
                                            </button>
                                        </form>

                                        <!-- Tombol Reject -->
                                        <form action="{{ route('ga.request_barang.reject', $requestBarang->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="button" class="btn btn-danger" onclick="confirmRejection('{{ route('ga.request_barang.reject', $requestBarang->id) }}')">
                                                <i class="fa fa-times"></i> Reject
                                            </button>
                                        </form>
                                    </div>
                                    @endif

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Status Barang yang Sudah Dikirim GA</h4>
                                    <div class="d-flex justify-content-between mb-3">
                                        <!-- Tombol untuk menambah input barang -->
                                        @if ($requestBarang->status === 'approved' || $requestBarang->status === 'shipped')
                                        <a href="{{ route('ga.input_barang.create', $requestBarang->id) }}" class="btn btn-info">Input Barang</a>
                                        @endif

                                        <!-- Tombol aksi di kanan -->
                                        <div>
                                            @if ($requestBarang->status === 'approved' || $requestBarang->status === 'shipped' )
                                            <a href="javascript:void(0);" id="btn-kirim-perangkat" data-url="{{ route('ga.request.create.shipped', $requestBarang->id) }}" class="btn btn-primary">
                                                <i class="fa fa-truck"></i> Kirim Perangkat
                                            </a>

                                            @endif
                                        </div>
                                    </div>


                                    <div class=" table-responsive">

                                        <table class="table table-hover ">
                                            <thead>
                                                <tr>
                                                    <th style=" text-align: center; vertical-align: middle;">No</th>
                                                    <th style=" text-align: center; vertical-align: middle;">GA</th>
                                                    <th style=" text-align: center; vertical-align: middle;">Jenis</th>
                                                    <th style=" text-align: center; vertical-align: middle;">Merek</th>
                                                    <th style=" text-align: center; vertical-align: middle;">Tipe</th>
                                                    <th style=" text-align: center; vertical-align: middle;">Serial Number</th>
                                                    <th style=" text-align: center; vertical-align: middle;">Jumlah</th>
                                                    <th style=" text-align: center; vertical-align: middle;">Kualitas</th>
                                                    <th style=" text-align: center; vertical-align: middle;">Aksi</th> <!-- Kolom tambahan untuk aksi pembatalan -->
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($requestBarang->barangKeluar as $barangKeluar)
                                                <tr>
                                                    <td style=" text-align: center; vertical-align: middle;">{{ $loop->iteration }}</td>
                                                    <td style=" text-align: center; vertical-align: middle;">{{ $barangKeluar->user->name }}</td>
                                                    <td style=" text-align: center; vertical-align: middle;">{{ $barangKeluar->stockBarang->jenis->nama_jenis }}</td>
                                                    <td style=" text-align: center; vertical-align: middle;">{{ $barangKeluar->stockBarang->merek->nama_merek }}</td>
                                                    <td style=" text-align: center; vertical-align: middle;">{{ $barangKeluar->stockBarang->tipe->nama_tipe }}</td>
                                                    <td style=" text-align: center; vertical-align: middle;">{{ $barangKeluar->serial_number }}</td>
                                                    <td style=" text-align: center; vertical-align: middle;">{{ $barangKeluar->jumlah }}</td>
                                                    <td style=" text-align: center; vertical-align: middle;">{{ ucfirst($barangKeluar->stockBarang->kualitas) }}</td>

                                                    <td style=" text-align: center; vertical-align: middle;">
                                                        <!-- Tampilkan tombol Batalkan hanya jika status bukan 'completed' -->
                                                        <!-- Tampilkan tombol Batalkan hanya jika status bukan 'completed' -->
                                                        @if ($requestBarang->status !== 'completed')
                                                        <form id="cancelForm{{ $barangKeluar->id }}" action="{{ route('ga.cancel_barang', $barangKeluar->id) }}" method="POST" style="display: none;">
                                                            @csrf
                                                            @method('DELETE')
                                                        </form>
                                                        <button type="button" class="btn btn-danger btn-sm" onclick="confirmCancellation({{ $barangKeluar->id }})">Batalkan</button>
                                                        @else
                                                        <span class="text-muted">Tidak bisa dibatalkan</span>
                                                        @endif

                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>


                                </div>

                            </div>

                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Status Proses</h4>
                                    @if ($requestBarang->status === 'approved' || $requestBarang->status === 'shipped')
                                    <!-- Tombol Completed -->
                                    <div class=" d-flex justify-content-end">
                                        <form action="{{ route('ga.request_barang.completed', $requestBarang->id) }}" method="POST">
                                            @csrf
                                            <button type="button" class="btn btn-success" onclick="confirmCompletion('{{ route('ga.request_barang.completed', $requestBarang->id) }}')">
                                                <i class="fa fa-check"></i> Selesaikan Work Order
                                            </button>
                                        </form>
                                    </div>


                                    @endif
                                    <div class=" table-responsive mt-3">

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
                                                    <td style=" text-align: center; vertical-align: middle;">{{ $progress->user->name }}</td>
                                                    <td style=" text-align: center; vertical-align: middle;">
                                                        @if($progress->status=='pending')
                                                        <span class="badge badge-pill badge-danger">Pending</span>
                                                        @elseif($progress->status=='approved')
                                                        <span class="badge badge-pill badge-info">Approved</span>
                                                        @elseif($progress->status=='rejected')
                                                        <span class="badge badge-pill badge-warning">Rejected</span>
                                                        @elseif($progress->status=='completed')
                                                        <span class="badge badge-pill badge-success">Completed</span>
                                                        @elseif($progress->status=='shipped')
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
                                    <a href="{{ route('ga.request_barang') }}" class="btn btn-info mt-3"> <i class="fa fa-arrow-left"></i> Kembali</a>


                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <footer class="footer">
                    <div class="d-sm-flex justify-content-center justify-content-sm-between">
                        <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Copyright © 2024. All rights reserved.</span>
                        <span class="float-none float-sm-right d-block mt-1 mt-sm-0 text-center">Hand-crafted & made with Rizal<i class="mdi mdi-heart text-danger"></i></span>
                    </div>
                </footer>
            </div>
        </div>

        <!-- main-panel ends -->
    </div>
    <!-- main-panel ends -->
    </div>

    </div>
    @include('ga.partials.script')
</body>

</html>