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
                                    <p><strong>No Order: </strong> {{ $getInstall->no_spk }}</p>
                                    <p><strong>No Survey:</strong> {{ $getInstall->survey_id ? $getInstall->survey->no_spk : '-' }}</p>
                                    <p><strong>Diterbitkan oleh:</strong> {{ $getInstall->admin->name }}</p>
                                    <p><strong>Tanggal Diterbitkan:</strong> {{ $getInstall->created_at->translatedFormat('d M Y, H:i:s') }}</p>
                                    <p><strong>Status:</strong> @if($getInstall->status=='Pending')
                                        <span class="badge badge-pill badge-danger">Pending</span>
                                        @elseif($getInstall->status=='On Progress')
                                        <span class="badge badge-pill badge-info">On Progress</span>
                                        @elseif($getInstall->status=='Shipped')
                                        <span class="badge badge-pill badge-primary">Shipped</span>
                                        @elseif($getInstall->status=='Rejected')
                                        <span class="badge badge-pill badge-dark">Rejected</span>
                                        @elseif($getInstall->status=='Canceled')
                                        <span class="badge badge-pill badge-warning">Cancelled</span>
                                        @elseif($getInstall->status=='Completed')
                                        <span class="badge badge-pill badge-success">Completed</span>
                                        @endif
                                    </p>
                                    <br>
                                    <!-- Foto Pelanggan -->
                                    @if($getInstall->pelanggan && $getInstall->pelanggan->foto)
                                    <img src="{{ asset('storage/pelanggan/' . $getInstall->pelanggan->foto) }}" alt="Foto Pelanggan" style="width: 150px; height: auto;">
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
                                    <p><strong>Nama Pelanggan:</strong> {{ $getInstall->pelanggan->nama_pelanggan }}</p>
                                    <p><strong>No Jaringan:</strong> {{ $getInstall->no_jaringan}}</p>
                                    <p><strong>Nama Gedung:</strong> {{ $getInstall->pelanggan->nama_gedung}}</p>
                                    <p><strong>Alamat:</strong> {{ $getInstall->pelanggan->alamat}}</p>
                                    <p><strong>Layanan:</strong> {{ $getInstall->layanan }}</p>
                                    <p><strong>Bandwidth:</strong> {{ $getInstall->bandwidth }} {{ $getInstall->satuan }}</p>
                                    <p><strong>NNI:</strong> {{ $getInstall->nni }}</p>

                                    <p><strong>Vlan:</strong> {{ $getInstall->vlan }}</p>
                                    <p><strong>Vendor:</strong> {{ $getInstall->vendor->nama_vendor }}</p>

                                </div>
                            </div>
                        </div>

                        <!-- Card 3: Site -->
                        <div class="col">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Site: </h5>
                                    <p><strong>Nama Perusahaan:</strong> {{ $getInstall->instansi->nama_instansi }}</p>
                                    <p><strong>Nama Site:</strong> {{ $getInstall->nama_site }}</p>
                                    <p><strong>Alamat:</strong> {{ $getInstall->alamat_pemasangan }}</p>
                                    <p><strong>PIC:</strong> {{ $getInstall->nama_pic }}</p>
                                    <p><strong>Nomer PIC:</strong> {{ $getInstall->no_pic }}</p>
                                    <p><strong>Tanggal RFS:</strong> {{ \Carbon\Carbon::parse($getInstall->tanggal_rfs)->translatedFormat('d M Y') }}</p>
                                    <p><strong>Durasi:</strong> {{ $getInstall->durasi }} {{ $getInstall->nama_durasi }}</p>
                                    <p><strong>Keterangan:</strong> {{ $getInstall->keterangan }}</p>
                                    <p><strong>Barang non stock:</strong> {{ $getInstall->non_stock }}</p>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="container mt-4">
                        @php
                        // Tahapan normal
                        $steps = ['Pending', 'On Progress', 'Shipped', 'Completed'];

                        // Ambil status dari progress
                        $currentStatus = ucfirst($getInstall->status ?? 'Pending');

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
                                                @foreach ($getInstall->WorkOrderInstallDetail as $detail)
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
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Status Barang yang Sudah Dikirim GA</h4>


                                    <div class=" table-responsive">

                                        <table class="table table-hover">
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
                                                    <th style=" text-align: center; vertical-align: middle;">Status Konfigurasi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($getInstall->barangKeluar as $barangKeluar)
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
                                                        @if($barangKeluar->is_configured)
                                                        <span class="badge badge-pill bg-success">Sudah Dikonfigurasi</span>
                                                        @else
                                                        <span class="badge badge-pill bg-warning">Belum Dikonfigurasi</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="9" class="text-center">Belum ada barang yang diinput</td>
                                                </tr>
                                                @endforelse
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
                                    <h4 class="card-title">Progres Instalasi</h4>

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
                                                    <td style=" text-align: center; vertical-align: middle;">{{ $progress->user->name }}</td>
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


                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Status Berita Acara</h4>



                                    <div class="form-group">
                                        <!-- Tombol Kirim Berita Acara hanya muncul jika status instalasi 'Completed' dan status BA 'pending' -->
                                        @if ($getInstall->status === 'Completed' && !$beritaAcaras->contains('status', 'sent') && !$beritaAcaras->contains('status', 'received'))
                                        <form action="{{ route('berita_acara.send', $getInstall->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="fa fa-paper-plane"></i> Kirim Berita Acara
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                    <div class=" table-responsive">

                                        <!-- Tabel Berita Acara -->
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th style=" text-align: center; vertical-align: middle;">No</th>
                                                    <th style=" text-align: center; vertical-align: middle;">Admin</th>
                                                    <th style=" text-align: center; vertical-align: middle;">Tanggal Kirim</th>
                                                    <th style=" text-align: center; vertical-align: middle;">Tanggal Terima</th>
                                                    <th style=" text-align: center; vertical-align: middle;">Status</th>
                                                    <th style=" text-align: center; vertical-align: middle;">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($beritaAcaras as $key => $ba)
                                                <tr>
                                                    <td style=" text-align: center; vertical-align: middle;">{{ $key + 1 }}</td>
                                                    <td style=" text-align: center; vertical-align: middle;">{{ $ba->user->name ?? 'N/A' }}</td>
                                                    <td style=" text-align: center; vertical-align: middle;">{{ $ba->tanggal_kirim ? $ba->tanggal_kirim->translatedFormat('d F Y, H:i') : '-' }}</td>
                                                    <td style=" text-align: center; vertical-align: middle;">{{ $ba->tanggal_terima ? $ba->tanggal_terima->translatedFormat('d F Y, H:i') : '-' }}</td>
                                                    <td style=" text-align: center; vertical-align: middle;">
                                                        @if($ba->status=='sent')
                                                        <span class="badge badge-pill badge-info">Sent</span>
                                                        @elseif($ba->status=='received')
                                                        <span class="badge badge-pill badge-success">Received</span>
                                                        @else
                                                        <span class="badge badge-pill badge-warning">Pending</span>
                                                        @endif
                                                    </td>
                                                    <td style=" text-align: center; vertical-align: middle;">
                                                        @if ($ba->status === 'pending')
                                                        <form action="{{ route('berita_acara.send', $getInstall->id) }}" method="POST">
                                                            @csrf
                                                            <button class="btn btn-primary btn-sm">Kirim</button>
                                                        </form>
                                                        @elseif ($ba->status === 'sent')
                                                        <form action="{{ route('berita_acara.received', $ba->id) }}" method="POST">
                                                            @csrf
                                                            <button class="btn btn-success btn-sm">Sudah Diterima</button>
                                                        </form>
                                                        @elseif ($ba->status === 'received')
                                                        <div>
                                                            @if (!$billingExists)
                                                            <button
                                                                class="btn btn-info btn-sm"
                                                                onclick="confirmRedirect('{{ route('sid.form', $getInstall->id) }}')">
                                                                <i class="fa fa-file-invoice"></i> Input Online Billing
                                                            </button>
                                                            @else
                                                            <span class="text-muted">Sudah diinput ke Online Billing</span>
                                                            @endif
                                                        </div>


                                                        @endif
                                                    </td>

                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="6" class="text-center">Belum ada berita acara</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- Tombol Kembali -->
                                    <a href="{{ route('admin.instalasi') }}" class="btn btn-info mt-3">
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
    @include('admin.partials.script')
</body>

</html>