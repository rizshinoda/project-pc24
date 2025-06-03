<!DOCTYPE html>
<html lang="en">

<head>

    @include('admin.partials.style')

</head>

<body>

    <div class="container-scroller">

        <!-- Navbar -->
        @include('admin.partials.navbar')


        <div class="container-fluid page-body-wrapper">
            <!-- Sidebar -->
            @include('admin.partials.sidebar')


            <!-- Main Panel -->
            @include('admin.partials.main')
            <!-- main-panel ends -->
        </div>

    </div>

    @include('admin.partials.script')
</body>

</html>