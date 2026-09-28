<script src="{{asset('/dist/assets/vendors/js/vendor.bundle.base.js')}}"></script>
<!-- endinject -->
<!-- Plugin js for this page -->
<script src="{{asset('/dist/assets/vendors/chart.js/chart.umd.js')}}"></script>
<script src="{{asset('/dist/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js')}}"></script>
<!-- End plugin js for this page -->
<!-- inject:js -->
<script src="{{asset('/dist/assets/js/off-canvas.js')}}"></script>
<script src="{{asset('/dist/assets/js/misc.js')}}"></script>
<script src="{{asset('/dist/assets/js/settings.js')}}"></script>
<script src="{{asset('/dist/assets/js/todolist.js')}}"></script>
<script src="{{asset('/dist/assets/js/jquery.cookie.js')}}"></script>
<!-- endinject -->
<!-- Custom js for this page -->
<script src="{{asset('/dist/assets/js/dashboard.js')}}"></script>
<!-- End custom js for this page -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script src="{{asset('jquery.min.js')}}"></script>
<script src="{{asset('mailler/src/sweetalert2.min.js')}}"></script>
<script type="text/javascript">
    $('.btn-logout').on('click', function(e) {
        e.preventDefault();
        const href = $(this).attr('href');
        Swal.fire({
            title: "Logout",
            text: "Apakah Anda yakin ingin keluar?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Ya"
        }).then((result) => {
            if (result.isConfirmed) {
                document.location.href = href

            }
        });
    })
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<!-- <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script> -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const alertSuccess = document.querySelector('.alert-success');
        if (alertSuccess) {
            // Menghilangkan alert setelah 5 detik
            setTimeout(function() {
                alertSuccess.style.display = 'none';
            }, 5000);
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const alertDanger = document.querySelector('.alert-danger');

        if (alertDanger) {
            setTimeout(function() {
                alertDanger.style.display = 'none';
            }, 5000);
        }
    });

    function markAsReadAndRedirect(notificationId, url) {
        // Kirim form untuk menandai notifikasi sebagai dibaca
        document.getElementById('mark-as-read-form-' + notificationId).submit();

        // Tunggu sebentar agar form terkirim, lalu alihkan ke URL notifikasi
        setTimeout(function() {
            window.location.href = url;
        }, 500); // Delay 500ms untuk memastikan form terkirim
    }
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const jenisSelect = document.getElementById('jenis_id');
        const stockRows = document.querySelectorAll('#stock-table-body tr');
        const cartTableBody = document.getElementById('cart-table-body');
        const searchInput = document.getElementById('search');
        const submitButton = document.getElementById("submit-request");

        // Sembunyikan semua baris saat dimuat
        stockRows.forEach(row => {
            row.style.display = 'none';
        });

        // Filter berdasarkan jenis barang
        jenisSelect.addEventListener('change', function() {
            const selectedJenisId = this.value;

            // Sembunyikan semua baris terlebih dahulu
            stockRows.forEach(row => {
                row.style.display = 'none';
            });

            // Tampilkan baris yang sesuai dengan jenis yang dipilih
            stockRows.forEach(row => {
                const rowJenisId = row.getAttribute('data-jenis-id');
                if (rowJenisId === selectedJenisId || !selectedJenisId) {
                    row.style.display = '';
                }
            });

            // Reset pencarian
            searchInput.value = '';
        });

        // Pencarian di tabel
        searchInput.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const selectedJenisId = jenisSelect.value;

            stockRows.forEach(row => {
                const merek = row.cells[0].textContent.toLowerCase();
                const tipe = row.cells[1].textContent.toLowerCase();
                const rowJenisId = row.getAttribute('data-jenis-id');

                // Filter berdasarkan jenis dan input pencarian
                if (
                    (merek.includes(filter) || tipe.includes(filter)) &&
                    (rowJenisId === selectedJenisId || !selectedJenisId)
                ) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', function() {
                const stockId = this.getAttribute('data-id');
                const merek = this.getAttribute('data-merek');
                const tipe = this.getAttribute('data-tipe');
                const kualitas = this.getAttribute('data-kualitas');
                const jumlahInput = parseInt(this.closest('tr').querySelector('.quantity').value);
                const maxStock = parseInt(this.getAttribute('data-jumlah'));

                // Validasi jumlah input
                if (jumlahInput > 0 && jumlahInput <= maxStock) {
                    const uniqueKey = `${stockId}-${merek}-${tipe}-${kualitas}`;

                    const existingRow = Array.from(cartTableBody.children).find(row => row.getAttribute('data-unique-key') === uniqueKey);

                    if (existingRow) {
                        const jumlahCell = existingRow.querySelector('.cart-quantity');
                        const newJumlah = parseInt(jumlahCell.value) + jumlahInput;

                        if (newJumlah <= maxStock) {
                            jumlahCell.value = newJumlah;
                        } else {
                            Swal.fire('Peringatan', 'Jumlah yang diminta melebihi stok yang tersedia.', 'warning');
                        }
                    } else {
                        const row = document.createElement('tr');
                        row.setAttribute('data-unique-key', uniqueKey);
                        row.innerHTML = `
                <td>${merek}</td>
                <td>${tipe}</td>
                <td>
                    <input type="number" name="cart[${stockId}][${merek}][${tipe}][${kualitas}][total_jumlah]" value="${jumlahInput}" class="form-control cart-quantity" min="1" max="${maxStock}" style="width: 80px;" />
                </td>
                <td>${kualitas}</td>
                <td><button type="button" class="btn btn-danger remove-from-cart"><i class="fa fa-close"></i></button></td>
            `;
                        cartTableBody.appendChild(row);

                        // Event listener untuk mengedit jumlah barang di keranjang
                        row.querySelector('.cart-quantity').addEventListener('input', function() {
                            const newJumlah = parseInt(this.value);
                            if (newJumlah > maxStock) {
                                Swal.fire('Peringatan', 'Jumlah yang diminta melebihi stok yang tersedia.', 'warning');
                                this.value = maxStock;
                            } else if (newJumlah < 1) {
                                Swal.fire('Peringatan', 'Jumlah tidak boleh kurang dari 1.', 'warning');
                                this.value = 1;
                            }
                        });
                    }
                } else {
                    Swal.fire('Error', 'Jumlah yang diminta tidak valid atau melebihi stok yang tersedia.', 'error');
                }
            });
        });

        // Delegation untuk remove item dari keranjang
        cartTableBody.addEventListener('click', function(event) {
            if (event.target.classList.contains('remove-from-cart') || event.target.closest('.remove-from-cart')) {
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Barang ini akan dihapus dari keranjang.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, hapus'
                }).then((result) => {
                    if (result.isConfirmed) {
                        event.target.closest('tr').remove();
                        Swal.fire('Dihapus!', 'Barang telah dihapus dari keranjang.', 'success');
                    }
                });
            }
        });

        // Validasi sebelum submit form
        submitButton.addEventListener("click", function(event) {
            if (cartTableBody.children.length === 0) {
                event.preventDefault();
                Swal.fire('Error', 'Keranjang tidak boleh kosong. Silakan tambahkan barang ke keranjang sebelum mengirim permintaan.', 'error');
            }
        });

    });

    function confirmApproval(url) {
        Swal.fire({
            title: 'Apakah Anda yakin ingin menyetujui permintaan ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Setujui',
            cancelButtonText: 'Batal',
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect to the approve route
                let form = document.createElement('form');
                form.action = url;
                form.method = 'POST';
                form.innerHTML = '@csrf';
                document.body.appendChild(form);
                form.submit();
            }
        });
    }


    // Fungsi konfirmasi untuk reject
    function confirmRejection(url) {
        Swal.fire({
            title: 'Apakah Anda yakin ingin menolak permintaan ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Tolak',
            cancelButtonText: 'Batal',
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect to the reject route
                let form = document.createElement('form');
                form.action = url;
                form.method = 'POST';
                form.innerHTML = '@csrf';
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
    document.querySelectorAll('.form-control').forEach(input => {
        input.addEventListener('input', function() {
            if (this.value.trim() !== '') {
                // Jika input diisi
                this.style.backgroundColor = '#d4edda'; // Hijau muda
            } else {
                // Jika input kosong
                this.style.backgroundColor = '#ffffff'; // Merah muda
            }
        });
    });
</script>
<!-- <script>
    document.addEventListener("DOMContentLoaded", function() {
        let monthlyStats = @json($monthlyStats ?? ['instalasi' => [], 'maintenance' => [], 'dismantle' => []]);

        let options = {
            chart: {
                type: 'area',
                height: 350
            },
            series: [{
                    name: "Instalasi",
                    data: monthlyStats.instalasi
                },
                {
                    name: "Maintenance",
                    data: monthlyStats.maintenance
                },
                {
                    name: "Dismantle",
                    data: monthlyStats.dismantle
                }
            ],
            xaxis: {
                categories: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"]
            },
            stroke: {
                width: 3
            },
            colors: ["#06b99e", "#0794ff", "#ff6969"],
            legend: {
                position: 'top'
            }
        };

        let chart = new ApexCharts(document.querySelector("#chart"), options);
        chart.render();
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let totalInstalasi = <?= isset($totalInstalasi) ? $totalInstalasi : 0 ?>;
        let totalMaintenance = <?= isset($totalMaintenance) ? $totalMaintenance : 0 ?>;
        let totalDismantle = <?= isset($totalDismantle) ? $totalDismantle : 0 ?>;

        let options = {
            chart: {
                type: "pie",
                height: 350
            },
            series: [totalInstalasi, totalMaintenance, totalDismantle],
            labels: ["Instalasi", "Maintenance", "Dismantle"],
            colors: ["#00c292", "#03a9f3", "#fe7096"], // Warna dasar
            fill: {
                type: "gradient",
                gradient: {
                    shade: "light",
                    type: "vertical", // Bisa 'horizontal' atau 'diagonal1', 'diagonal2'
                    shadeIntensity: 0.5,
                    gradientToColors: ["#5cb5a0", "#3e84ab", "#f09b67"], // Warna gradasi tujuan
                    inverseColors: false,
                    opacityFrom: 0.9,
                    opacityTo: 0.7,
                    stops: [0, 100]
                }
            },
            legend: {
                position: "bottom"
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 300
                    },
                    legend: {
                        position: "bottom"
                    }
                }
            }]
        };

        let chart = new ApexCharts(document.querySelector("#traffic-chart"), options);
        chart.render();
    });
</script> -->
<script>
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-complete')) {

            const btn = e.target;

            Swal.fire({
                title: btn.dataset.title ?? 'Yakin?',
                text: btn.dataset.text ?? 'Data akan diproses.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Selesaikan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('actionInput').value = btn.dataset.action;
                    btn.closest('form').submit();
                }
            });
        }
    });


    function confirmDelete(formId) {
        // SweetAlert2 Konfirmasi
        Swal.fire({
            title: 'Hapus',
            text: "Apakah Anda yakin untuk menghapusnya?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Jika dikonfirmasi, submit form
                document.getElementById('delete-form-' + formId).submit();
            }
        });
    }


    // DATA CHART
    const categories = Object.keys(woChart);

    const pending =
        categories.map(
            item => woChart[item]['Pending'] || 0
        );

    const progress =
        categories.map(
            item => woChart[item]['On Progress'] || 0
        );

    const shipped =
        categories.map(
            item => woChart[item]['Shipped'] || 0
        );

    const completed =
        categories.map(
            item => woChart[item]['Completed'] || 0
        );

    const overdue =
        categories.map(
            item => woChart[item]['Overdue'] || 0
        );


    // TAMBAHKAN DI SINI
    const routeMap = {

        'Survey': '{{ route("psb.survey") }}',

        'Instalasi': '{{ route("psb.instalasi") }}',

        'POC': '{{ route("psb.poc") }}',

        'Jasa': '{{ route("psb.jasa") }}',

        'Maintenance': '{{ route("psb.maintenance") }}',

        'Dismantle': '{{ route("psb.dismantle") }}',

        'Upgrade': '{{ route("psb.upgrade") }}',

        'Downgrade': '{{ route("psb.downgrade") }}',

        'Relokasi': '{{ route("psb.relokasi") }}',

        'Ganti Vendor': '{{ route("psb.gantivendor") }}',

    };


    const overdueAllowed = ['Survey', 'Instalasi', 'POC',
        'Jasa', 'Upgrade', 'Downgrade', 'Relokasi', 'Dismantle'
    ];


    // BARU OPTIONS
    var options = {

        series: [

            {
                name: 'Pending',
                data: pending
            },

            {
                name: 'On Progress',
                data: progress
            },

            {
                name: 'Shipped',
                data: shipped
            },

            {
                name: 'Completed',
                data: completed
            },

            {
                name: 'Overdue',
                data: overdue
            }

        ],

        chart: {

            type: 'bar',

            height: 400,

            stacked: true,

            events: {

                dataPointSelection: function(
                    event,
                    chartContext,
                    config
                ) {

                    const status =
                        config.w.config.series[
                            config.seriesIndex
                        ].name;


                    // ambil kategori
                    const wo =
                        config.w.globals.labels[
                            config.dataPointIndex
                        ];


                    // ambil route
                    const route =
                        routeMap[wo];

                    if (!route) {
                        return;
                    }


                    // // redirect
                    // window.location.href =
                    //     `${route}?status=${encodeURIComponent(status)}`;

                    if (status === 'Overdue') {

                        if (!overdueAllowed.includes(wo)) {
                            return;
                        }

                        window.location.href =
                            `${route}?filter=overdue`;

                    } else {

                        window.location.href =
                            `${route}?status=${encodeURIComponent(status)}`;
                    }
                }

            }

        },

        colors: ["#fab300", "#038bf3", "#f24cf8", "#23e088", "#fd6060"], // Warna dasar
        fill: {
            type: "gradient",
            gradient: {
                shade: "light",
                type: "vertical", // Bisa 'horizontal' atau 'diagonal1', 'diagonal2'
                shadeIntensity: 0.5,
                gradientToColors: ["#f9c10c", "#03a9f3", "#eb2ff1", "#00ff88", "#ee4874"], // Warna gradasi tujuan
                inverseColors: false,
                opacityFrom: 0.9,
                opacityTo: 0.7,
                stops: [0, 100]
            }
        },
        plotOptions: {
            bar: {
                horizontal: true
            }
        },

        xaxis: {
            categories: categories
        }

    };


    // RENDER
    new ApexCharts(
        document.querySelector("#chart"),
        options
    ).render();


    var trafficOptions = {
        series: Object.values(statusData),
        chart: {
            type: 'pie',
            height: 350
        },
        labels: Object.keys(statusData),

        colors: ["#fab300", "#038bf3", "#f24cf8", "#23e088", "#fd6060"], // Warna dasar
        fill: {
            type: "gradient",
            gradient: {
                shade: "light",
                type: "vertical", // Bisa 'horizontal' atau 'diagonal1', 'diagonal2'
                shadeIntensity: 0.5,
                gradientToColors: ["#f9c10c", "#03a9f3", "#eb2ff1", "#00ff88", "#ee4874"], // Warna gradasi tujuan
                inverseColors: false,
                opacityFrom: 0.9,
                opacityTo: 0.7,
                stops: [0, 100]
            }
        },
    };

    var trafficChart = new ApexCharts(
        document.querySelector("#traffic-chart"),
        trafficOptions
    );

    trafficChart.render();

    var billingOptions = {
        series: [{
            name: 'Total',
            data: [
                billingData.Active,
                billingData.Dismantle
            ]
        }],

        chart: {
            type: 'bar',
            height: 320
        },

        plotOptions: {
            bar: {
                horizontal: false,
                borderRadius: 4,
                distributed: true
            }
        },

        colors: [
            '#23e088', // Active
            '#dc3545' // Dismantle
        ],

        dataLabels: {
            enabled: true
        },

        xaxis: {
            categories: [
                'Active',
                'Dismantle'
            ]
        }
    };

    new ApexCharts(
        document.querySelector("#billing-chart"),
        billingOptions
    ).render();
</script>