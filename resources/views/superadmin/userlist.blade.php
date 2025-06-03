<!DOCTYPE html>
<html lang="en">

<head>
    <!-- style -->

    <meta charset="utf-8">

    <meta name="csrf-token" content="{{ csrf_token() }} ">

    <title>Dashboard</title>
    <link href="{{asset('/mailler/src/sweetalert2.min.css')}}" rel="stylesheet" />

    <!-- plugins:css -->
    <link rel="stylesheet" href="{{asset('/dist/assets/vendors/mdi/css/materialdesignicons.min.css')}}">
    <link rel="stylesheet" href="{{asset('/dist/assets/vendors/ti-icons/css/themify-icons.css')}}">
    <link rel="stylesheet" href="{{asset('/dist/assets/vendors/css/vendor.bundle.base.css')}}">
    <link rel="stylesheet" href="{{asset('/dist/assets/vendors/font-awesome/css/font-awesome.min.css')}}">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="{{asset('/dist/assets/vendors/font-awesome/css/font-awesome.min.css')}}" />
    <link rel="stylesheet" href="{{asset('/dist/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css')}}">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="{{asset('/dist/assets/css/style.css')}}">
    <!-- End layout styles -->
    <link rel="shortcut icon" href="{{asset('/dist/assets/images/favicon.png')}}" />
    <link href="{{asset('/mailler/src/sweetalert2.min.css')}}" rel="stylesheet" />
</head>

<body>
    <div class="container-scroller">

        <nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
            <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">
                <a class="navbar-brand brand-logo" href="index.html"><img src="{{asset('mailler/img/pc24.png')}}" alt="logo" /></a>
                <a class="navbar-brand brand-logo-mini" href="index.html"><img src="{{asset('mailler/img/pc24.png')}}" alt="logo" /></a>
            </div>
            <div class="navbar-menu-wrapper d-flex align-items-stretch">
                <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
                    <span class="mdi mdi-menu"></span>
                </button>



                <ul class="navbar-nav navbar-nav-right">

                    <li class="nav-item nav-profile dropdown">
                        <a class="nav-link dropdown-toggle" id="profileDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="nav-profile-img">
                                <img src="{{asset('/dist/assets/images/faces/face1.jpg')}}" alt="image">
                                <span class="availability-status online"></span>
                            </div>
                            <div class="nav-profile-text">
                                <p class="mb-1 text-black">{{ Auth::user()->name }}</p>
                            </div>
                        </a>


                        <form action="">
                            @csrf
                            <div class="dropdown-menu navbar-dropdown" aria-labelledby="profileDropdown">
                                <!-- <a class="dropdown-item" href="#">
                                    <i class="mdi mdi-cached me-2 text-success"></i> Activity Log </a> -->
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item btn-logout" href="{{url('logout')}}" id="logout">
                                    <i class="mdi mdi-logout me-2 text-primary"></i> Logout </a>
                            </div>
                        </form>
                    </li>

                </ul>
                <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
                    <span class="mdi mdi-menu"></span>
                </button>
            </div>
        </nav>
        <div class="container-fluid page-body-wrapper">
            <!-- partial:partials/_sidebar.html -->
            <nav class="sidebar sidebar-offcanvas" id="sidebar">
                <ul class="nav">
                    <li class="nav-item nav-profile">
                        <a href="#" class="nav-link">
                            <div class="nav-profile-image">
                                <img src="{{asset('/dist/assets/images/faces/face1.jpg')}}" alt="profile" />
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
                    <li class="nav-item ">
                        <a class="nav-link" href="{{ url('/superadmin/dashboard') }}">
                            <span class="menu-title">Dashboard</span>
                            <i class="mdi mdi-home menu-icon"></i>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/superadmin/userlist') }}">
                            <span class="menu-title">User List</span>
                            <i class="fa fa-user-circle menu-icon"></i>
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
                            </span> Users
                        </h3>
                        <nav aria-label="breadcrumb">
                          <ol class="breadcrumb">
                             <li class="breadcrumb-item"><a href="{{url('superadmin/userlist')}}">Users</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Tables</li>
                                </ol>
                     </nav>
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
                          <th>email_verified_at</th>
                          <th>Role</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      
                      <tbody>
                        @foreach($getRecord as $key => $value)                           
                        <tr>
                         <td> {{$getRecord->firstItem()+ $key}} </td>
                          <td>{{$value->name}}</td>
                          <td>{{$value->email}}</td>
                          <td class="text-success"> {{date('d-m-Y', strtotime( $value->email_verified_at))}}</i></td>
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
                  
                          <td>  
                          <form id="deleteForm-{{ $value->id }}" action="{{ route('userlist.destroy', $value->id) }}" method="POST" >
                          @csrf
                          @method('DELETE') <!-- Metode Spoofing untuk DELETE -->
                         <button type="button" class="deleteBtn btn btn-danger btn-rounded btn-sm" data-id="{{ $value->id }}">Hapus</button>
                        </form>
                        </tr>
                        @endforeach
                        
                      </tbody>                  
                    </table>
          <div class="mt-3">
        Showing
        {{$getRecord->firstItem()}}
        to 
        {{$getRecord->lastItem()}}
        of 
        {{$getRecord->total()}}
        entries

    </div>          
    <div class="pull-right">
    {{ $getRecord->links() }}
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
    <!-- container-scroller -->
    <!-- plugins:js -->
    <script src="{{asset('/dist/assets/vendors/js/vendor.bundle.base.js')}}"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <script src="{{asset('/dist/assets/vendors/chart.js/chart.umd.js')}}"></script>
    <script src="{{asset('/dist/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js')}}"></script>
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="{{asset('/dist/assets/js/off-canvas.js')}}"></script>
    <script src="{{asset('/dist/assets/js/misc.js')}}"></script>
    <script src="{{asset('/dist/assets/js/settings.js')}}"></script>
    <script src="{{asset('/dist/assets/js/todolist.js')}}"></script>
    <script src="{{asset('/dist/assets/js/jquery.cookie.js')}}"></script>
    <!-- endinject -->
    <!-- Custom js for this page -->
    <script src="{{asset('/dist/assets/js/dashboard.js')}}"></script>
    <!-- End custom js for this page -->

    <script src="{{asset('jquery.min.js')}}"></script>
    <script src="{{asset('mailler/src/sweetalert2.min.js')}}"></script>
    <script type="text/javascript">
        $('.btn-logout').on('click', function(e) {
            e.preventDefault();
            const href = $(this).attr('href');
            Swal.fire({
                title: "Logout",
                text: "Apakah Anda yakin ingin keluar?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Ya"
            }).then((result) => {
                if (result.isConfirmed) {
                    document.location.href = href

                }
            });
        })
      
    </script>
     
     <script>
document.querySelectorAll('.deleteBtn').forEach(button => {
    button.addEventListener('click', function(event) {
        var productId = this.getAttribute('data-id');
        var currentPage = new URL(window.location.href).searchParams.get('page') || 1; // Mendapatkan halaman saat ini

        Swal.fire({
            title: 'Delete',
            text: "Apakah yakin ingin menghapusnya?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Hapus'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/superadmin/delete/${productId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Tampilkan pesan sukses
                        Swal.fire(
                            'Deleted!',
                            'Item telah dihapus.',
                            'success'
                        ).then(() => {
                            // Cek jumlah data pada halaman
                            const itemCount = document.querySelectorAll('.item-selector').length; // Ganti .item-selector dengan selector yang sesuai untuk item

                            if (itemCount === 0) {
                                // Jika tidak ada item, arahkan ke halaman sebelumnya
                                window.location.href = `/superadmin/userlist?page=${currentPage - 1}`;
                            } else {
                                // Refresh halaman jika masih ada item
                                location.reload();
                            }
                        });
                    } else {
                        Swal.fire(
                            'Error!',
                            data.error || 'Gagal menghapus item.',
                            'error'
                        );
                    }
                })
                .catch(error => {
                    Swal.fire(
                        'Error!',
                        'Terjadi kesalahan saat menghapus item.',
                        'error'
                    );
                });
            }
        });
    });
});
</script>
</body>

</html>