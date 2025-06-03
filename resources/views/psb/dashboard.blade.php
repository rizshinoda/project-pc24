<!DOCTYPE html>
<html lang="en">

<head>
    @include('psb.partials.style')
</head>

<body>

    <div class="container-scroller">

        <!-- Navbar -->
        @include('psb.partials.navbar')


        <div class="container-fluid page-body-wrapper">
            <!-- Sidebar -->
            @include('psb.partials.sidebar')


            <!-- Main Panel -->
            @include('psb.partials.main')
            <!-- main-panel ends -->
        </div>

    </div>

    @include('psb.partials.script')
</body>

</html>