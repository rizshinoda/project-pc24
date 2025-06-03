<!DOCTYPE html>
<html lang="en">
<!-- Session Status -->


<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Reset Password</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="{{asset('dist/assets/vendors/mdi/css/materialdesignicons.min.css')}}">
    <link rel="stylesheet" href="{{asset('dist/assets/vendors/ti-icons/css/themify-icons.css')}}">
    <link rel="stylesheet" href="{{asset('dist/assets/vendors/css/vendor.bundle.base.css')}}">
    <link rel="stylesheet" href="{{asset('dist/assets/vendors/font-awesome/css/font-awesome.min.css')}}">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="{{asset('dist/assets/css/styleforgot.css')}}">
    <!-- End layout styles -->
    <link rel="shortcut icon" href="{{asset('dist/assets/images/signal1.png')}}" />
</head>

<body>
    <div id="particles-js" style="position: fixed; width: 100%; height: 100%; z-index: -1;"></div>


    <div class="container" id="container">
        @include('_message')

        <div class="form-container sign-in">

            <form class="pt-3" action="{{url('reset_post/'.$token)}}" method="post">
                <h1>Reset Password</h1>
                <span>Masukkan email dan password baru</span>

                @csrf
                <input type="password" class="form-control form-control-lg" required name="password" placeholder="Password">
                @error('password')
                <div style="color: red; font-size: 12px;">{{ $message }}</div>
                @enderror

                <input type="password" value="" class="form-control form-control-lg" required name="confirm_password" placeholder="Confirm Password">
                @error('confirm_password')
                <div style="color: red; font-size: 12px;">{{ $message }}</div>
                @enderror

                <button>RESET</button>

            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>

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

    <!-- endinject -->
</body>

</html>