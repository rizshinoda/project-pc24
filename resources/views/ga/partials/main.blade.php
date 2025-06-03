<div class="main-panel">
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-danger text-white me-2">
                    <i class="mdi mdi-home"></i>
                </span> Dashboard
            </h3>

        </div>
        <div class="row">
            <div class="col-md-4 stretch-card grid-margin">
                <div class="card bg-gradient-success card-img-holder text-white">
                    <div class="card-body">
                        <img src="{{ asset('/dist/assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image" />
                        <h3 class="font-weight-normal mb-3">Weekly Instalasi <i class="mdi mdi-chart-line mdi-24px float-end"></i></h3>
                        <h1 class="mb-5">{{ $instalasiCount }}</h1>
                    </div>
                </div>
            </div>
            <div class="col-md-4 stretch-card grid-margin">
                <div class="card bg-gradient-info card-img-holder text-white">
                    <div class="card-body">
                        <img src="{{ asset('/dist/assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image" />
                        <h3 class="font-weight-normal mb-3">Weekly Maintenance<i class="mdi mdi-chart-line mdi-24px float-end"></i></h3>
                        <h1 class="mb-5">{{ $maintenanceCount }}</h1>
                    </div>
                </div>
            </div>
            <div class="col-md-4 stretch-card grid-margin">
                <div class="card bg-gradient-danger card-img-holder text-white">
                    <div class="card-body">
                        <img src="{{ asset('/dist/assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image" />
                        <h3 class="font-weight-normal mb-3">Weekly Dismantle <i class="mdi mdi-chart-line mdi-24px float-end"></i></h3>
                        <h2 class="mb-5">{{ $dismantleCount }}</h2>
                    </div>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-md-7 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Statistik Bulanan</h4>
                        <div id="chart"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-5 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Total </h4>

                        <div id="traffic-chart"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- content-wrapper ends -->
    <!-- partial:partials/_footer.html -->
    <footer class="footer">
        <div class="d-sm-flex justify-content-center justify-content-sm-between">
            <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Copyright © 2024</a>. All rights reserved.</span>
            <span class="float-none float-sm-right d-block mt-1 mt-sm-0 text-center">Hand-crafted & made with Rizal<i class="mdi mdi-heart text-danger"></i></span>
        </div>
    </footer>
    <!-- partial -->
</div>