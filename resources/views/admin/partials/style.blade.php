 <!-- style -->
 <meta charset="utf-8">

 <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

 <title>PC24 Telekomunikasi</title>
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
         color: rgb(234, 56, 104) !important;

         /* Warna teks hitam saat aktif */
         background-color: white;
         /* Tetap putih saat aktif */
         box-shadow: 0px -4px 10px rgba(0, 0, 0, 0.2);
         /* Efek floating */
         transform: translateY(-2px);
         /* Efek naik sedikit */
     }

     .nav-item .nav-link:hover {
         color: rgb(234, 56, 104) !important;

         /* membuat teks jadi tebal saat hover */
     }

     .nav-item .nav-link.active {
         color: rgb(234, 56, 104) !important;
     }

     .sidebar .nav .nav-item.active>.nav-link .menu-title {
         color: rgb(234, 56, 104) !important;
     }

     .sidebar .nav .nav-item.active>.nav-link i.mdi {
         color: rgb(234, 56, 104) !important;
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

 <style>
     .container-scroller {
         display: flex;
         height: 100vh;
         overflow: hidden;
     }

     .page-body-wrapper {
         display: flex;
         flex: 1;
         overflow: hidden;
     }

     .main-panel {
         flex: 1;
         overflow-y: auto;
         height: 100vh;
     }

     .sidebar {
         overflow-y: auto;
         scrollbar-width: thin;

         /* Firefox */
     }

     .sidebar::-webkit-scrollbar {
         width: 6px;
         /* Chrome */

     }
 </style>