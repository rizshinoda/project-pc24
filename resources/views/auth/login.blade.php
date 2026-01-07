<!DOCTYPE html>
<html lang="en">
@if(auth()->check())
@php
$routes = [
0 => 'superadmin/dashboard',
1 => 'admin/dashboard',
2 => 'ga/dashboard',
3 => 'helpdesk/dashboard',
4 => 'noc/dashboard',
5 => 'psb/dashboard',
6 => 'na/dashboard',
];
@endphp
<script>
    document.addEventListener("DOMContentLoaded", function() {
        window.location.href = "{{ url($routes[Auth::user()->is_role] ?? 'default/dashboard') }}";
    });
</script>
@endif

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login</title>
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <!-- plugins:css -->
    <link rel="stylesheet" href="{{ asset('dist/assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/assets/vendors/ti-icons/css/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/assets/vendors/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/assets/css/stylelogin.css') }}">
    <link rel="shortcut icon" href="{{asset('/dist/assets/images/signal1.png')}}" />

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

        /* Tambahkan ini di style.css atau dalam <style> di HTML */
        .form-control,
        .custom-select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: #fff;
            font-size: 12px;
            box-sizing: border-box;
            transition: 0.3s;
        }

        .form-control:focus,
        .custom-select:focus {
            border-color: #4A90E2;
            outline: none;
        }
    </style>
</head>

<body>
    @include('_message')

    <div class="brand-logo text-center">
        <img src="{{asset('mailler/img/pc24.png')}}" height="50">
    </div>
    <div id="particles-js" style="position: fixed; width: 100%; height: 100%; z-index: -1;"></div>


    <div class="container" id="container" data-register-error="{{ $errors->any() ? 'true' : 'false' }}">

        <div class="form-container sign-up">

            <form action="{{url('register_post')}}" method="post">

                @csrf
                <h1>Create Account</h1>
                <div class="social-icons">
                    <a href="#" class="icon">
                        <i class="fa-brands fa-google-plus-g"></i>
                    </a>
                    <a href="#" class="icon">
                        <i class="fa-brands fa-facebook-f"></i>
                    </a>
                    <a href="#" class="icon">
                        <i class="fa-brands fa-github"></i>
                    </a>
                    <a href="#" class="icon">
                        <i class="fa-brands fa-linkedin-in"></i>
                    </a>
                </div>
                <span> gunakan email valid anda untuk registrasi</span>

                <input type="text" name="name" value="{{ old('name') }}" placeholder="Nama" required maxlength="100">
                @error('name')
                <div style="color: red; font-size: 12px; ">{{ $message }}</div>
                @enderror

                <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" required>
                @error('email')
                <div style="color: red; font-size: 12px;">{{ $message }}</div>
                @enderror

                <input type="password" name="password" placeholder="Password" required minlength="6">
                @error('password')
                <div style="color: red; font-size: 12px;">{{ $message }}</div>
                @enderror

                <input type="password" name="confirm_password" placeholder="Konfirmasi Password" required minlength="6">
                @error('confirm_password')
                <div style="color: red; font-size: 12px;">{{ $message }}</div>
                @enderror

                <select class="custom-select custom-select-lg" name="is_role" required>
                    <option value="">Pilih Divisi</option>
                    <!-- <option {{ old('is_role') == '0' ?  'selected' : ''}} value="0">Super Admin</option> -->
                    <option {{ old('is_role') == '1' ?  'selected' : ''}} value="1">Admin</option>
                    <option {{ old('is_role') == '2' ?  'selected' : ''}} value="2">GA</option>
                    <option {{ old('is_role') == '3' ?  'selected' : ''}} value="3">Helpdesk</option>
                    <option {{ old('is_role') == '4' ?  'selected' : ''}} value="4">NOC</option>
                    <option {{ old('is_role') == '5' ?  'selected' : ''}} value="5">PSB</option>
                    <option {{ old('is_role') == '6' ?  'selected' : ''}} value="6">NA</option>

                </select>
                @error('is_role')
                <div style="color: red; font-size: 12px;">{{ $message }}</div>
                @enderror

                <button>Sign Up</button>


            </form>
        </div>

        <div class="form-container sign-in">

            <form action="{{ url('login_post') }}" method="post" id="loginForm">
                @csrf

                <h1>Sign in</h1>
                <div class="social-icons">
                    <a href="#" class="icon">
                        <i class="fa-brands fa-google-plus-g"></i>
                    </a>
                    <a href="#" class="icon">
                        <i class="fa-brands fa-facebook-f"></i>
                    </a>
                    <a href="#" class="icon">
                        <i class="fa-brands fa-github"></i>
                    </a>
                    <a href="#" class="icon">
                        <i class="fa-brands fa-linkedin-in"></i>
                    </a>
                </div>

                <span> gunakan email dan password kamu</span>
                <input
                    type="email"
                    required
                    name="email"
                    placeholder="Email" />
                <input
                    type="password"
                    required
                    name="password"
                    placeholder="Password" />
                <a href="{{ url('forgot') }}">Lupa Password?</a>
                <button>Sign In</button>
            </form>
        </div>

        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <h1>Welcome back!</h1>
                    <p>Masukkan detail pribadi anda untuk mengakses web ini</p>
                    <button class="hidden" id="login">Sign in</button>
                </div>

                <div class="toggle-panel toggle-right">
                    <h1>Hello!</h1>
                    <p>Silahkan registrasi terlebih dahulu untuk mengakses web ini</p>
                    <button class="hidden" id="register">Sign Up</button>
                </div>
            </div>
        </div>
    </div>
    <script src="{{asset('/dist/assets/js/script.js')}}"></script>
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>

    <!-- container-scroller -->

    <!-- plugins:js -->
    <script src="{{ asset('dist/assets/vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="{{ asset('dist/assets/js/off-canvas.js') }}"></script>
    <script src="{{ asset('dist/assets/js/misc.js') }}"></script>
    <script src="{{ asset('dist/assets/js/settings.js') }}"></script>
    <script src="{{ asset('dist/assets/js/todolist.js') }}"></script>
    <script src="{{ asset('dist/assets/js/jquery.cookie.js') }}"></script>

    <script>
        // Tampilkan Spinner Saat Login Ditekan
        document.getElementById("loginForm").addEventListener("submit", function() {
            document.querySelector(".loading-spinner").style.display = "block";
        });
    </script>
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
    <script>
        particlesJS("particles-js", {
            "particles": {
                "number": {
                    "value": 10,
                    "density": {
                        "enable": true,
                        "value_area": 800
                    }
                },
                "color": {
                    "value": ["#fef649", "#d8b3e2", "#ff9797"] // Warna bubble & segitiga
                },
                "shape": {
                    "type": ["circle", "triangle"], // Bulat dan segitiga
                    "stroke": {
                        "width": 0,
                        "color": "#000000"
                    }
                },
                "opacity": {
                    "value": 0.5,
                    "random": true
                },
                "size": {
                    "value": 50,
                    "random": true
                },
                "line_linked": {
                    "enable": false
                },
                "move": {
                    "enable": true,
                    "speed": 1.5,
                    "direction": "top",
                    "random": true,
                    "straight": false,
                    "out_mode": "out"
                }
            },
            "interactivity": {
                "detect_on": "canvas",
                "events": {
                    "onhover": {
                        "enable": true,
                        "mode": "repulse"
                    },
                    "onclick": {
                        "enable": false
                    },
                    "resize": true
                }
            },
            "retina_detect": true
        });
    </script>
    <script>
        // Tunggu sampai semua konten dimuat
        document.addEventListener('DOMContentLoaded', function() {
            // Cari semua elemen dengan class 'alert'
            const alerts = document.querySelectorAll('.alert');

            // Set timeout (misalnya 5 detik)
            setTimeout(function() {
                alerts.forEach(function(alert) {
                    // Tambahkan efek fade out (opsional)
                    alert.style.transition = "opacity 0.5s ease";
                    alert.style.opacity = '0';

                    // Setelah efek selesai, hapus elemen dari DOM
                    setTimeout(function() {
                        alert.remove();
                    }, 500);
                });
            }, 5000); // 5000 ms = 5 detik
        });
    </script>



</body>

</html>