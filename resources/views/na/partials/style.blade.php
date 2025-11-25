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
         border: 1px solid #ccc;
         border-radius: 8px;
         padding: 12px;
         background-color: #fdfdfd;
         color: #333;
         font-size: 14px;
         transition: all 0.3s ease;
         /* Animasi halus saat hover/focus */
     }

     /* Efek saat hover */
     .form-control:hover {
         border-color: #888;
         background-color: #f9f9f9;
     }

     /* Efek saat fokus */
     .form-control:focus {
         border-color: #007bff;
         background-color: #fff;
         box-shadow: 0 0 10px rgba(0, 123, 255, 0.25);
         outline: none;
     }

     select.form-control {
         background-color: #fff;
         color: #333;
         border: 1px solid #ccc;
         border-radius: 8px;
         padding: 12px;
         cursor: pointer;
         transition: all 0.3s ease;

         appearance: none;
         -webkit-appearance: none;
         -moz-appearance: none;

         background-image: url("data:image/svg+xml;utf8,<svg fill='black' height='16' viewBox='0 0 24 24' width='16' xmlns='http://www.w3.org/2000/svg'><path d='M7 10l5 5 5-5z'/></svg>");
         background-repeat: no-repeat;
         background-position: right 12px center;
         background-size: 16px;
         padding-right: 36px;
         /* supaya teks tidak menabrak icon */
     }

     /* Hover & Focus */
     select.form-control:hover {
         border-color: #007bff;
         background-color: #f8faff;
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

     .stepper {
         display: flex;
         justify-content: space-between;
         align-items: center;
         position: relative;
         margin: 25px auto;
         max-width: 750px;
     }

     .step {
         text-align: center;
         flex: 1;
         position: relative;
     }

     /* Garis penghubung antar step */
     .step:not(:last-child)::after {
         content: '';
         position: absolute;
         top: 14px;
         /* lebih kecil karena lingkaran lebih kecil */
         right: -50%;
         width: 100%;
         height: 3px;
         /* sebelumnya 4px */
         background-color: #d6d6d6;
         z-index: 0;
         transition: background-color 0.3s ease;
     }

     /* Garis aktif (yang sudah dilewati) */
     .step.completed:not(:last-child)::after {
         background-color: #0d6efd;
     }

     /* Lingkaran step */
     .step-circle {
         width: 28px;
         /* sebelumnya 40px */
         height: 28px;
         /* lebih kecil */
         border-radius: 50%;
         background-color: #d6d6d6;
         display: flex;
         align-items: center;
         justify-content: center;
         margin: 0 auto;
         font-weight: bold;
         color: white;
         z-index: 1;
         font-size: 13px;
         position: relative;
         transition: all 0.3s ease;
     }

     /* Step yang sudah selesai → warna biru dan ceklis */
     .step.completed .step-circle {
         background-color: #0d6efd;
         color: white;
     }

     .step.completed .step-circle::before {
         content: "✔";
         position: absolute;
         font-weight: 700;
         font-size: 13px;
         color: white;
     }

     /* Step aktif (sedang berlangsung) */
     .step.active .step-circle {
         background-color: #0d6efd;
         box-shadow: 0 0 8px rgba(13, 110, 253, 0.5);
     }

     /* Label di bawah step */
     .step-label {
         margin-top: 6px;
         font-size: 13px;
         color: #6c757d;
     }

     .step.completed .step-label,
     .step.active .step-label {
         color: #0d6efd;
         font-weight: 600;
     }

     #stock-table {
         position: relative;
         z-index: 10;
     }
 </style>