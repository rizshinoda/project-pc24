<!DOCTYPE html>
<html lang="en">

<head>
    @include('admin.partials.style')
</head>

<body>

    <div class="container-scroller">

        <!-- Navbar -->
        @include('ga.partials.navbar')


        <div class="container-fluid page-body-wrapper">
            <!-- Sidebar -->
            @include('ga.partials.sidebar')


            <!-- Main Panel -->
            @include('ga.partials.main')
            <!-- main-panel ends -->
        </div>

    </div>

    @include('ga.partials.script')
</body>

</html>