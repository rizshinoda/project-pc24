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
                            </span> Detail Site Dismantle
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
                                    <p><strong>Tanggal Aktif:</strong> {{ $onlinebilling->created_at->translatedFormat('d M Y') }}</p>
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
                                    <br>

                                    <a href="{{ route('superadmin.sitedismantle') }}" class="btn btn-info mt-3"><i class="fa fa-arrow-left"></i> Kembali</a>

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
                                    <p><strong>Vendor:</strong> {{ $onlinebilling->vendor->nama_vendor }}</p>
                                    <p><strong>Status:</strong>
                                        @if($onlinebilling->status=='active')
                                        <span class="badge badge-pill badge-success">Aktif</span>
                                        @else($onlinebilling->status=='dismantle')
                                        <span class="badge badge-pill badge-danger">Dismantle</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- status aksi seperti upgrade dowgrade dan berikan status progres dan completednya -->


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