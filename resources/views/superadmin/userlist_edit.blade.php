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
                            </span> Userlist
                        </h3>


                    </div>
                    <div class="row justify-content-center">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="mb-5 text-center">Form Edit User</h4>
                                    {{-- Menampilkan pesan error jika ada --}}
                                    @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @endif

                                    {{-- Form untuk mengedit work order --}}
                                    <form action="{{ route('superadmin.updateUser', $userlist->id) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT') {{-- Gunakan metode PUT untuk update --}}

                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group ">
                                                    <label for="nama_site">Nama</label>
                                                    <input type="text" class="form-control" id="name" name="name" value="{{ $userlist->name }}" readonly>
                                                </div>
                                                <div class="form-group ">
                                                    <label for="nama_site">Email</label>
                                                    <input type="text" class="form-control" id="email" name="email" value="{{ $userlist->email }}" readonly>
                                                </div>
                                                <div class="form-group ">
                                                    <label for="nama_site">Role</label>
                                                    <input type="text" class="form-control" id="is_role" name="is_role" value="{{ $userlist->is_role }}">
                                                </div>


                                            </div>
                                        </div>
                                        <br>
                                        <!-- Tombol submit -->
                                        <button type="submit" class="btn btn-info">Update</button>
                                        <a href="{{ route('superadmin.userlist') }}" class="btn btn-light">Kembali</a>
                                    </form>

                                </div>
                            </div>
                            <!-- main-panel ends -->
                        </div>
                    </div>
                </div>
                <footer class="footer">
                    <div class="d-sm-flex justify-content-center justify-content-sm-between">
                        <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Copyright © 2024</a>. All rights reserved.</span>
                        <span class="text-muted float-none float-sm-right d-block mt-1 mt-sm-0 text-center">Hand-crafted & made with Rizal<i class="mdi mdi-heart text-danger"></i></span>
                    </div>
                </footer>
                <!-- partial -->
            </div> <!-- Main Panel -->

            <!-- main-panel ends -->
        </div>

    </div>

    @include('superadmin.partials.script')
</body>

</html>