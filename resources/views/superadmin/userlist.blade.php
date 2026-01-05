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

            <div class="main-panel">
                <div class="content-wrapper">
                    <div class="page-header">
                        <h3 class="page-title">
                            <span class="page-title-icon bg-gradient-danger text-white me-2">
                                <i class="mdi mdi-home"></i>
                            </span> Users
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

                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>No </th>
                                            <th>Nama</th>
                                            <th>Email</th>
                                            <th>Tanggal Verifikasi</th>
                                            <th>Role</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach($users as $key => $value)
                                        <tr>
                                            <td>{{ $users->firstItem() + $key }}</td>
                                            <td>{{ $value->name }}</td>
                                            <td>{{ $value->email }}</td>
                                            <td>
                                                {{ $value->email_verified_at ? date('d-m-Y', strtotime($value->email_verified_at)) : 'Belum terverifikasi' }}
                                            </td>
                                            <td>
                                                @if($value->is_role == '0')
                                                <span class="badge badge-pill badge-primary">Super Admin</span>
                                                @elseif($value->is_role == '1')
                                                <span class="badge badge-pill badge-secondary">Admin</span>
                                                @elseif($value->is_role == '2')
                                                <span class="badge badge-pill badge-success">GA</span>
                                                @elseif($value->is_role == '3')
                                                <span class="badge badge-pill badge-danger">Helpdesk</span>
                                                @elseif($value->is_role == '4')
                                                <span class="badge badge-pill badge-warning">NOC</span>
                                                @elseif($value->is_role == '5')
                                                <span class="badge badge-pill badge-info">PSB</span>
                                                @elseif($value->is_role == '6')
                                                <span class="badge badge-pill badge-dark">NA</span>
                                                @endif
                                            </td>
                                            <td>@if ($value->status =='active')
                                                <span class="badge badge-pill badge-success">Active</span>
                                                @else ($value->status =='non_active')
                                                <span class="badge badge-pill badge-danger">Non Active</span>
                                                @endif
                                            </td>
                                            <td>

                                                @if ($value->status == 'active')
                                                <button type="button"
                                                    class="btn btn-danger btn-sm btn-unverify"
                                                    data-id="{{ $value->id }}">
                                                    Hapus Akses
                                                </button>
                                                @else
                                                @endif


                                                <form id="unverify-form-{{ $value->id }}"
                                                    action="{{ route('superadmin.unverify', $value->id) }}"
                                                    method="GET" style="display: none;">
                                                </form>
                                            </td>

                                        </tr>
                                        @endforeach
                                    </tbody>

                                </table>
                                <div class="mt-3">
                                    Showing
                                    {{ $users->firstItem() }}
                                    to
                                    {{ $users->lastItem() }}
                                    of
                                    {{ $users->total() }}
                                    entries
                                </div>

                                <div class="pull-right">
                                    {{ $users->links() }}
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