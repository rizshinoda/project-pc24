<!DOCTYPE html>
<html lang="en">

<head>
    @include('helpdesk.partials.style')
</head>

<body>

    <div class="container-scroller">

        <!-- Navbar -->
        @include('helpdesk.partials.navbar')


        <div class="container-fluid page-body-wrapper">
            <!-- Sidebar -->
            @include('helpdesk.partials.sidebar')


            <!-- Main Panel -->
            @include('helpdesk.partials.main')
            <!-- main-panel ends -->
        </div>

    </div>

    @include('helpdesk.partials.script')
</body>

</html>