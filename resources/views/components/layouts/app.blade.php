<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Chat</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <!-- style -->
    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

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
    <link rel="shortcut icon" href="{{asset('/dist/assets/images/signal1.png')}}" />
    <link href="{{asset('/mailler/src/sweetalert2.min.css')}}" rel="stylesheet" />
    <style>
        /* Mengatur ukuran card agar tidak melebar */
        .fixed-card {
            max-width: 400px;
            /* Mengatur maksimal lebar card */
            width: 200%;
            /* Membuat card responsif */
        }

        /* Mengatur tinggi minimal untuk card agar sama */
        .card-body {
            flex-grow: 1;
            /* Memastikan semua card-body akan sama tinggi */
        }

        .card-spacing {
            margin-right: 10px;
            /* Mengatur jarak antar card */
            margin-left: 10px;
            /* Mengatur jarak antar card */
        }

        p {
            margin-top: 5px;
            /* Memperkecil jarak sebelum paragraf */
            margin-bottom: 5px;
            /* Memperkecil jarak setelah paragraf */
            line-height: 1.4;
            /* Menjaga jarak antar baris agar tetap nyaman dibaca */
        }

        body {
            font-family: 'Arial', sans-serif;
            /* Contoh mengganti ke font Arial */
        }


        .nav-profile-text {
            white-space: normal;
            word-break: break-word;
        }

        .notifications-dropdown {
            max-height: 300px;
            /* Tinggi maksimal dropdown */
            overflow-y: auto;
            /* Aktifkan scroll jika konten melebihi tinggi maksimal */
        }

        /* Untuk semua browser modern */
        textarea::placeholder {
            color: #212020 !important;
            /* Ganti dengan warna yang diinginkan */
            opacity: 1 !important;
        }

        /* Chrome, Safari, Edge, Opera */
        ::-webkit-input-placeholder {
            color: #212020 !important;
        }

        /* Firefox */
        ::-moz-placeholder {
            color: #212020 !important;
        }

        /* Internet Explorer 10+ */
        :-ms-input-placeholder {
            color: #212020 !important;
        }

        /* Edge (versi lama) */
        :-moz-placeholder {
            color: #212020 !important;
        }

        /* Ubah warna teks placeholder di select */
        select:invalid {
            color: #212020 !important;

        }

        select {
            color: #212020 !important;
        }

        .form-control {
            border: 1px solid #000000;
            border-radius: 8px;
            padding: 12px;
        }

        select.form-control {
            border: 1px solid #000000;
            /* Border hitam */
            border-radius: 8px;
            /* Sudut membulat */
            padding: 12px;
            /* Padding sama dengan input */
            /* Ukuran teks */
            background-color: #fff;
            /* Warna latar belakang putih */
            color: #000;
            /* Warna teks hitam */
            appearance: none;
            /* Menghilangkan tampilan default (di beberapa browser) */
            -webkit-appearance: none;
            /* Untuk Safari */
            -moz-appearance: none;
            /* Untuk Firefox */
            cursor: pointer;
            /* Menunjukkan ini bisa dipilih */
        }

        /* Tambahkan ikon panah ke bawah */
        select.form-control {
            background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 4 5"><path fill="%23000" d="M2 5L0 0h4z"/></svg>');
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 10px;
            padding-right: 30px;
            /* Agar tidak menutupi teks */
        }

        /* Saat combo box di-focus */
        select.form-control:focus {
            border-color: #007bff;
            /* Biru saat fokus */
            box-shadow: 0 0 8px rgba(0, 123, 255, 0.5);
            outline: none;
        }

        .input-filled {
            background-color: #d4edda;
            /* Warna hijau muda */
        }

        table {
            border: 3px solid black;
            /* Mengatur ketebalan border */
            border-collapse: collapse;
            /* Menghilangkan double border */
        }

        .nav-tabs {
            border-bottom: none;
            /* Hapus garis bawah */
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .nav-tabs .nav-link {

            color: rgb(0, 0, 0);
            /* Warna teks biru */
            padding: 12px 20px;
            border-radius: 12px 12px 0 0;
            /* Membulatkan bagian atas */
            background-color: rgba(0, 0, 0, 0.05);
            /* Warna soft */
            transition: all 0.3s ease-in-out;
        }

        .nav-tabs .nav-link:hover {
            background-color: rgba(0, 0, 0, 0.1);
            /* Efek hover */
        }

        .nav-tabs .nav-link.active {
            color: blue !important;
            /* Warna teks hitam saat aktif */
            background-color: white;
            /* Tetap putih saat aktif */
            box-shadow: 0px -4px 10px rgba(0, 0, 0, 0.2);
            /* Efek floating */
            transform: translateY(-2px);
            /* Efek naik sedikit */
        }

        td.keterangan-cell {
            word-wrap: break-word;
            white-space: normal;
            /* Memastikan teks bisa turun ke bawah */
            max-width: 300px;
            /* Atur sesuai kebutuhan */
            overflow-wrap: break-word;
            /* Memastikan teks tetap dalam sel */
        }

        .text-xs {
            font-size: 0.75rem !important;
            /* 12px */
            line-height: 1 !important;
        }
    </style>

</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100 dark:bg-gray-100">

        @php
        // Menentukan route dashboard berdasarkan role
        $dashboardRoutes = [
        0 => 'superadmin.dashboard',
        1 => 'admin.dashboard',
        2 => 'ga.dashboard',
        3 => 'helpdesk.dashboard',
        4 => 'noc.dashboard',
        5 => 'psb.dashboard',
        6 => 'na.dashboard',
        ];

        $userRole = Auth::user()->is_role;
        $dashboardRoute = isset($dashboardRoutes[$userRole]) ? route($dashboardRoutes[$userRole]) : '#';
        @endphp
        <a href="{{ $dashboardRoute }}"
            class="inline-flex items-center gap-2 bg-gray-300 text-black px-4 py-2 rounded-lg shadow-md hover:bg-gray-400 transition text-lg font-semibold no-underline w-auto" style="text-decoration: none;">
            ⬅ Dashboard
        </a>


        <!-- Page Heading -->
        @isset($header)
        <header class="bg-white dark:bg-gray-800 shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
        @endisset

        <!-- Page Content -->
        <main class="pt-16">
            {{ $slot }}
        </main>
    </div>
    @livewireScripts
</body>

</html>