<!DOCTYPE html>
<html lang="en">

<head>
    @include('superadmin.partials.style')
</head>

<body>

    <div class="container-scroller">

        <!-- Navbar -->
        @include('superadmin.partials.navbar')


        <div class="container-fluid page-body-wrapper">
            <!-- Sidebar -->
            @include('superadmin.partials.sidebar')


            <!-- Main Panel -->
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
                                    <p><strong>No Order: </strong> {{ $getMaintenance->no_spk }}</p>
                                    <p><strong>Diterbitkan oleh:</strong> {{ $getMaintenance->admin->name }}</p>
                                    <p><strong>Tanggal Diterbitkan:</strong> {{ $getMaintenance->created_at->translatedFormat('d M Y, H:i:s') }}</p>
                                    <p><strong>Status:</strong>
                                        @if($getMaintenance->status=='Pending')
                                        <span class="badge badge-pill badge-danger">Pending</span>
                                        @elseif($getMaintenance->status=='On Progress')
                                        <span class="badge badge-pill badge-info">On Progress</span>
                                        @elseif($getMaintenance->status=='Shipped')
                                        <span class="badge badge-pill badge-primary">Shipped</span>
                                        @elseif($getMaintenance->status=='Rejected')
                                        <span class="badge badge-pill badge-dark">Rejected</span>
                                        @elseif($getMaintenance->status=='Canceled')
                                        <span class="badge badge-pill badge-warning">Cancelled</span>
                                        @elseif($getMaintenance->status=='Completed')
                                        <span class="badge badge-pill badge-success">Completed</span>
                                        @endif
                                    </p>
                                    <br>
                                    <!-- Foto Pelanggan -->
                                    @if($getMaintenance->onlineBilling->pelanggan && $getMaintenance->onlineBilling->pelanggan->foto)
                                    <img src="{{ asset('storage/pelanggan/' . $getMaintenance->onlineBilling->pelanggan->foto) }}" alt="Foto Pelanggan" style="width: 150px; height: auto;">
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
                                    <p><strong>Nama Pelanggan:</strong> {{ $getMaintenance->onlineBilling->pelanggan->nama_pelanggan }}</p>
                                    <p><strong>No Jaringan:</strong> {{ $getMaintenance->onlineBilling->no_jaringan}}</p>
                                    <p><strong>Nama Gedung:</strong> {{ $getMaintenance->onlineBilling->pelanggan->nama_gedung}}</p>
                                    <p><strong>Alamat:</strong> {{ $getMaintenance->onlineBilling->pelanggan->alamat}}</p>
                                    <p><strong>Layanan:</strong> {{ $getMaintenance->onlineBilling->layanan }}</p>
                                    <p>
                                        <strong>Bandwidth:</strong>
                                        {{ $getMaintenance->onlineBilling->bandwidth }} {{ $getMaintenance->onlineBilling->satuan }}

                                    </p>
                                    <p><strong>Vlan:</strong> {{ $getMaintenance->onlineBilling->vlan }}</p>
                                    <p><strong>Vendor:</strong> {{ $getMaintenance->onlineBilling->vendor->nama_vendor }}</p>

                                </div>
                            </div>
                        </div>

                        <!-- Card 3: Site -->
                        <div class="col">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Site: </h5>
                                    <p><strong>Nama Perusahaan:</strong> {{ $getMaintenance->onlineBilling->instansi?->nama_instansi }}</p>
                                    <p><strong>Nama Site:</strong> {{ $getMaintenance->onlineBilling->nama_site }}</p>
                                    <p><strong>Alamat:</strong> {{ $getMaintenance->onlineBilling->alamat_pemasangan }}</p>
                                    <p><strong>PIC:</strong> {{ $getMaintenance->onlineBilling->nama_pic }}</p>
                                    <p><strong>Nomer PIC:</strong> {{ $getMaintenance->onlineBilling->no_pic }}</p>
                                    <p><strong>Alamat Baru:</strong> {{ $getMaintenance->alamat_pemasangan_baru }}</p>
                                    <p><strong>Keterangan:</strong> {{ $getMaintenance->keterangan }}</p>
                                    <p><strong>Barang non stock:</strong> {{ $getMaintenance->non_stock }}</p>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="container mt-4">
                        @php
                        // Tahapan normal
                        $steps = ['Pending', 'On Progress', 'Shipped', 'Completed'];

                        // Ambil status dari progress
                        $currentStatus = ucfirst($getMaintenance->status ?? 'Pending');

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
                                                    <th>No</th>
                                                    <th>Jenis</th>
                                                    <th>Merek</th>
                                                    <th>Tipe</th>
                                                    <th>Jumlah</th>
                                                    <th>Kualitas</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($getMaintenance->WorkOrderMaintenanceDetail as $detail)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>

                                                    <td>{{ $detail->stockBarang->jenis->nama_jenis }}</td>
                                                    <td>{{ $detail->stockBarang->merek->nama_merek }}</td>
                                                    <td>{{ $detail->stockBarang->tipe->nama_tipe }}</td>
                                                    <td>{{ $detail->jumlah }}</td>
                                                    <td>{{ ucfirst($detail->stockBarang->kualitas) }}</td>
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
                                                    <th>No</th>
                                                    <th>GA</th>
                                                    <th>Jenis</th>
                                                    <th>Merek</th>
                                                    <th>Tipe</th>
                                                    <th>Serial Number</th>
                                                    <th>Jumlah</th>
                                                    <th>Kualitas</th>
                                                    <th>Status Konfigurasi</th>

                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($getMaintenance->barangKeluar as $barangKeluar)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $barangKeluar->user->name }}</td>
                                                    <td>{{ $barangKeluar->stockBarang->jenis->nama_jenis }}</td>
                                                    <td>{{ $barangKeluar->stockBarang->merek->nama_merek }}</td>
                                                    <td>{{ $barangKeluar->stockBarang->tipe->nama_tipe }}</td>
                                                    <td>{{ $barangKeluar->serial_number }}</td>
                                                    <td>{{ $barangKeluar->jumlah }}</td>
                                                    <td>{{ ucfirst($barangKeluar->stockBarang->kualitas) }}</td>
                                                    <td>
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
                                    <h4 class="card-title">Progres Maintenance</h4>

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
                                                                    <div class="modal-body text-center">
                                                                        @foreach ($photos as $photo)
                                                                        <div class="mb-2">
                                                                            <a href="{{ asset('uploads/' . $photo->file_path) }}" download class="btn btn-success mb-2">Download Foto</a>
                                                                            <img src="{{ asset('uploads/' . $photo->file_path) }}" alt="Foto Progress" class="photo-square" style="width: 200px; height: 200px; object-fit: cover;">
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
                                    <a href="{{ route('superadmin.maintenance') }}" class="btn btn-info mt-3">
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

    @include('superadmin.partials.script')
</body>

</html>