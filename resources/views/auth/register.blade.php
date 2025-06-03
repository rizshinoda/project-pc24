<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Register</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="{{asset('/dist/assets/vendors/mdi/css/materialdesignicons.min.css')}}">
    <link rel="stylesheet" href="{{asset('/dist/assets/vendors/ti-icons/css/themify-icons.css')}}">
    <link rel="stylesheet" href="{{asset('/dist/assets/vendors/css/vendor.bundle.base.css')}}">
    <link rel="stylesheet" href="{{asset('/dist/assets/vendors/font-awesome/css/font-awesome.min.css')}}">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="{{asset('/dist/assets/css/style.css')}}">
    <!-- End layout styles -->
    <link rel="shortcut icon" href="{{asset('/dist/assets/images/favicon.png')}}" />
    <style>
        /* Efek Loading */
        .loading-spinner {
            display: none;
            text-align: center;
            margin-top: 10px;
        }

        /* Warna hijau saat input diisi */
        .form-control:valid,
        .form-control:focus {
            border-color: #28a745 !important;
            box-shadow: 0 0 5px rgba(40, 167, 69, 0.5) !important;
        }

        /* Efek warna hijau pada combo box */
        .form-select:valid,
        .form-select:focus {
            border-color: #28a745 !important;
            box-shadow: 0 0 5px rgba(40, 167, 69, 0.5) !important;
        }

        /* Background hijau muda setelah diisi */
        .form-control:valid,
        .form-select:valid {
            background-color: #d4edda !important;
        }

        .form-select:valid {
            color: black !important;
        }
    </style>
</head>

<body>
    <div class="container-scroller">

        <div class="container-fluid page-body-wrapper full-page-wrapper">

            <div class="content-wrapper d-flex align-items-center auth">
                <div class="row flex-grow">
                    <div class="col-lg-4 mx-auto">
                        @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif


                        <div class="auth-form-light text-left p-5">
                            <div class="brand-logo text-center">
                                <img src="{{asset('mailler/img/pc24.png')}}" height="50">
                            </div>
                            <h4>Daftar disini dengan mudah</h4>
                            <h6 class="font-weight-light"> *Wajib menggunakan email valid</h6>
                            <form action="{{url('register_post')}}" method="post" class="pt-3">
                                @csrf
                                <div class="form-group">
                                    <input type="text" value="{{old('name')}}" class="form-control form-control-lg" required name="name" placeholder="Nama">
                                </div>
                                <div class="form-group">
                                    <input type="email" value="{{old('email')}}" class="form-control form-control-lg" required name="email" placeholder="Email">
                                </div>

                                <div class="form-group">
                                    <input type="password" class="form-control form-control-lg" required name="password" placeholder="Password">
                                </div>

                                <div class="form-group">
                                    <input type="password" class="form-control form-control-lg" required name="confirm_password" placeholder="Confirm Password">
                                </div>

                                <div class="form-group">
                                    <select class="form-select form-select-lg" name="is_role" required>
                                        <option value="">Pilih Divisi</option>
                                        <option {{ old('is_role') == '0' ?  'selected' : ''}} value="0">Super Admin</option>
                                        <option {{ old('is_role') == '1' ?  'selected' : ''}} value="1">Admin</option>
                                        <option {{ old('is_role') == '2' ?  'selected' : ''}} value="2">GA</option>
                                        <option {{ old('is_role') == '3' ?  'selected' : ''}} value="3">Helpdesk</option>
                                        <option {{ old('is_role') == '4' ?  'selected' : ''}} value="4">NOC</option>
                                        <option {{ old('is_role') == '5' ?  'selected' : ''}} value="5">PSB</option>
                                        <option {{ old('is_role') == '6' ?  'selected' : ''}} value="6">NA</option>
                                    </select>
                                </div>

                                <div class="mt-3 d-grid gap-2">
                                    <input type="submit" class="btn btn-block  btn btn-gradient-danger btn-rounded btn-fw font-weight-medium auth-form-btn" value="DAFTAR">
                                </div>
                                <div class="text-center mt-4 font-weight-light"> Sudah punya akun? <a href="{{url('login')}}" class="text-danger">Login</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- content-wrapper ends -->
        </div>
        <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
    <!-- plugins:js -->
    <script src="../../assets/vendors/js/vendor.bundle.base.js"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="../../assets/js/off-canvas.js"></script>
    <script src="../../assets/js/misc.js"></script>
    <script src="../../assets/js/settings.js"></script>
    <script src="../../assets/js/todolist.js"></script>
    <script src="../../assets/js/jquery.cookie.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Cek setiap input field dan select ketika diisi
            document.querySelectorAll(".form-control, .form-select").forEach(function(el) {
                el.addEventListener("change", function() {
                    if (el.value.trim() !== "") {
                        el.classList.add("is-valid");
                    } else {
                        el.classList.remove("is-valid");
                    }
                });
            });
        });
    </script>
    <!-- endinject -->
</body>

</html>