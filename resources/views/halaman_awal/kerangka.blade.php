<!DOCTYPE html>
<html lang="en">

<head>
    @include('halaman_awal.style')

</head>

<body>
    <!-- Spinner Start -->
    @include('halaman_awal.spinner')
    <!-- Spinner End -->

    <!-- Navbar & Hero Start -->
    @include('halaman_awal.navbar')
    @include('halaman_awal.main')
    <!-- Navbar & Hero End -->


    <!-- Back to Top -->
    @include('halaman_awal.script')
</body>

</html>