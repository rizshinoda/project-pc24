<!DOCTYPE html>
<html lang="en">

<head>
    @include('noc.partials.style')
</head>

<body>

    <div class="container-scroller">

        <!-- Navbar -->
        @include('noc.partials.navbar')


        <div class="container-fluid page-body-wrapper">
            <!-- Sidebar -->
            @include('noc.partials.sidebar')


            <!-- Main Panel -->
            @include('noc.partials.main')
            <!-- main-panel ends -->
        </div>

    </div>

    @include('noc.partials.script')
</body>

</html>