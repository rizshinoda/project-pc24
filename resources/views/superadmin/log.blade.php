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
                            </span> Log
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
                    <div class="row justify-content-center">
                        <div class="col-lg-8 grid-margin stretch-card">
                            <div class="card">
                                <div class="card-body">
                                    <ul class="list-group">
                                        @foreach($logs as $log)
                                        <li class="list-group-item d-flex align-items-start">
                                            <div class="me-3 mt-1" style="min-width: 90px;">
                                                <span class="badge rounded-pill {{ getLogBadgeColor($log->title) }}">
                                                    {{ $log->title }}
                                                </span>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-bold">{{ $log->description }}</div>
                                                <small class="text-muted">
                                                    @switch($log->action)
                                                    @case('edit')
                                                    Diedit oleh {{ $log->user->name ?? 'System' }}
                                                    @break
                                                    @case('delete')
                                                    Dihapus oleh {{ $log->user->name ?? 'System' }}
                                                    @break
                                                    @case('cancel')
                                                    Dibatalkan oleh {{ $log->user->name ?? 'System' }}
                                                    @break
                                                    @default
                                                    Dibuat oleh {{ $log->user->name ?? 'System' }}
                                                    @endswitch
                                                    – {{ \Carbon\Carbon::parse($log->created_at)->translatedFormat('d F Y, H:i') }}
                                                </small>

                                            </div>
                                        </li>
                                        @endforeach
                                    </ul>


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

    @include('superadmin.partials.script')
</body>

</html>