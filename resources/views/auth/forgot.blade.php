<!DOCTYPE html>
<html lang="en">
<!-- Session Status -->


<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap">

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
    <link rel="shortcut icon" href="{{asset('/dist/assets/images/signal1.png')}}" />

</head>

<body>

    <div id="particles-js" style="position: fixed; width: 100%; height: 100%; z-index: -1;"></div>

    <div class="container" id="container">
        @include('_message')

        <div class="form-container sign-in">

            <form action="{{ url('forgot_post') }}" method="post" id="resetForm">
                @csrf

                <h1>Reset Password</h1>
                <span>Masukkan email Anda untuk mereset password</span>

                <input type="email" value="{{ old('email') }}" required name="email" placeholder="Email" />

                <button type="submit">Reset Password</button>
                <br>
                <a href="{{ url('login') }}">Menu Login</a>
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

</body>

</html>