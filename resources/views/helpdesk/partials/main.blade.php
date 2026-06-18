<div class="main-panel">
    <div class="content-wrapper">

        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-danger text-white me-2">
                    <i class="mdi mdi-home"></i>
                </span>
                Dashboard
            </h3>
        </div>

        <!-- Summary Cards -->
        <div class="row">

            <div class="col-md-3 stretch-card grid-margin">
                <div class="card bg-gradient-primary card-img-holder text-white">
                    <div class="card-body">
                        <img src="{{ asset('/dist/assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image" />

                        <h4>Total WO</h4>
                        <h2>{{ $totalWO }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3 stretch-card grid-margin">
                <div class="card bg-gradient-info card-img-holder text-white">
                    <div class="card-body">
                        <img src="{{ asset('/dist/assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image" />

                        <h4>On Progress</h4>
                        <h2>{{ $onProgress }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3 stretch-card grid-margin">
                <div class="card bg-gradient-success card-img-holder text-white">
                    <div class="card-body">
                        <img src="{{ asset('/dist/assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image" />

                        <h4>Completed</h4>
                        <h2>{{ $completed }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3 stretch-card grid-margin">
                <div class="card bg-gradient-danger card-img-holder text-white">
                    <div class="card-body">
                        <img src="{{ asset('/dist/assets/images/dashboard/circle.svg') }}" class="card-img-absolute" alt="circle-image" />

                        <h4>Overdue</h4>
                        <h2>{{ $overdue }}</h2>
                    </div>
                </div>
            </div>

        </div>

        <!-- Charts -->
        <div class="row">

            <!-- Bar Chart -->
            <div class="col-md-7 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Jumlah WO per Jenis</h4>
                        <div id="chart"></div>
                    </div>
                </div>
            </div>

            <!-- Donut Chart -->
            <div class="col-md-5 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Distribusi Status WO</h4>
                        <div id="traffic-chart"></div>
                    </div>
                </div>
            </div>

        </div>

        <!-- WO Escalation + Billing Chart -->
        <div class="row">

            <!-- Table Escalation -->
            <div class="col-md-8 grid-margin">
                <div class="card">
                    <div class="card-body">

                        <h4 class="card-title">WO Escalation (TOP 10)</h4>

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>No SPK</th>
                                        <th>Jenis WO</th>
                                        <th>Nama Pelanggan</th>
                                        <th>Nama Site</th>
                                        <th>Status</th>
                                        <th>Tanggal RFS</th>
                                        <th>Terlambat</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach($escalationWO as $wo)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>

                                        <td>
                                            <a href="{{ $wo->detail_url }}">
                                                {{ $wo->no_spk }}
                                            </a>
                                        </td>

                                        <td>
                                            <span class="badge badge-info">
                                                {{ $wo->jenis }}
                                            </span>
                                        </td>

                                        <td>{{ $wo->nama_pelanggan }}</td>
                                        <td>{{ $wo->nama_site }}</td>

                                        <td>
                                            @if($wo->status=='Pending')
                                            <span class="badge badge-pill badge-danger">Pending</span>
                                            @elseif($wo->status=='On Progress')
                                            <span class="badge badge-pill badge-info">On Progress</span>
                                            @elseif($wo->status=='Shipped')
                                            <span class="badge badge-pill badge-primary">Shipped</span>
                                            @elseif($wo->status=='Rejected')
                                            <span class="badge badge-pill badge-dark">Rejected</span>
                                            @elseif($wo->status=='Canceled')
                                            <span class="badge badge-pill badge-warning">Cancelled</span>
                                            @elseif($wo->status=='Completed')
                                            <span class="badge badge-pill badge-success">Completed</span>
                                            @endif
                                        </td>

                                        <td>
                                            {{ \Carbon\Carbon::parse($wo->tanggal_rfs)->format('d-m-Y') }}
                                        </td>

                                        <td>
                                            <span class="badge badge-danger">
                                                {{ $wo->hari_overdue }} Hari
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Billing Chart -->
            <div class="col-md-4 grid-margin ">
                <div class="card">
                    <div class="card-body">

                        <h4 class="card-title">Online Billing Status</h4>

                        <div id="billing-chart"></div>

                        <div class="mt-4 text-center">
                            <div>
                                <span class="badge badge-success">
                                    Active: {{ $billingChart['Active'] }}
                                </span>
                            </div>

                            <div class="mt-2">
                                <span class="badge badge-danger">
                                    Dismantle: {{ $billingChart['Dismantle'] }}
                                </span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>

    <footer class="footer">
        <div class="d-sm-flex justify-content-center justify-content-sm-between">
            <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">
                Copyright © 2024.
            </span>
        </div>
    </footer>
</div>

<script>
    const woChart = @json($woChart);
    const statusData = @json($statusDistribution);
    const billingData = @json($billingChart);
</script>