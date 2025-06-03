<!DOCTYPE html>
<html lang="en">

<head>
    @include('na.partials.style')
</head>

<body>

    <div class="container-scroller">

        <!-- Navbar -->
        @include('na.partials.navbar')


        <div class="container-fluid page-body-wrapper">
            <!-- Sidebar -->
            @include('na.partials.sidebar')


            <!-- Main Panel -->
            @include('na.partials.main')
            <!-- main-panel ends -->
        </div>

    </div>

    @include('na.partials.script')
</body>

</html>