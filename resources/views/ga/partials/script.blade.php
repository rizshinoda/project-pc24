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
    function hideButtons(event) {
        // Prevent multiple form submissions by disabling the buttons
        document.getElementById('approveButton').style.display = 'none';
        document.getElementById('rejectButton').style.display = 'none';
    }
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');

        if (!searchInput) return; // safety

        searchInput.addEventListener('keyup', function() {
            const searchQuery = this.value.toLowerCase();
            const rows = document.querySelectorAll('#stockTableBody tr');

            rows.forEach(row => {
                const jenis = row.cells[1]?.textContent.toLowerCase() || '';
                const merek = row.cells[2]?.textContent.toLowerCase() || '';
                const tipe = row.cells[3]?.textContent.toLowerCase() || '';
                const serial = row.cells[4]?.textContent.toLowerCase() || '';

                if (
                    jenis.includes(searchQuery) ||
                    merek.includes(searchQuery) ||
                    tipe.includes(searchQuery) ||
                    serial.includes(searchQuery)
                ) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let cartItems = [];

    function addToCart(id, jenis, merek, tipe, serialNumber, kualitas, stokJumlah) {
        Swal.fire({
            title: "Masukkan jumlah barang",
            input: "number",
            inputAttributes: {
                min: 1,
                max: stokJumlah,
                placeholder: `Maksimum ${stokJumlah}`
            },
            showCancelButton: true,
            confirmButtonText: "Tambahkan",
            cancelButtonText: "Batal",
            preConfirm: (jumlah) => {
                jumlah = parseInt(jumlah);

                if (isNaN(jumlah) || jumlah <= 0) {
                    Swal.showValidationMessage("Jumlah tidak valid.");
                    return false;
                }
                if (jumlah > stokJumlah) {
                    Swal.showValidationMessage(`Jumlah melebihi stok (${stokJumlah}).`);
                    return false;
                }

                return jumlah;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const jumlah = result.value;

                let existingItem = cartItems.find(item => item.id === id);
                if (existingItem) {
                    if ((existingItem.jumlah + jumlah) > stokJumlah) {
                        Swal.fire("Error", "Total jumlah di keranjang melebihi stok tersedia.", "error");
                        return;
                    }
                    existingItem.jumlah += jumlah;
                } else {
                    cartItems.push({
                        id,
                        jenis,
                        merek,
                        tipe,
                        serialNumber,
                        kualitas,
                        jumlah
                    });
                }

                renderCartTable();
                updateCartInput();
            }
        });
    }

    function renderCartTable() {
        const cartTableBody = document.querySelector("#cartTable tbody");
        cartTableBody.innerHTML = "";

        cartItems.forEach((item, index) => {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${index + 1}</td>
                <td>${item.jenis}</td>
                <td>${item.merek}</td>
                <td>${item.tipe}</td>
                <td>${item.kualitas}</td>
                <td>${item.jumlah}</td>
                <td>${item.serialNumber}</td>
                <td>
                    <button type="button" class="btn btn-danger" onclick="removeFromCart(${index})">Hapus</button>
                </td>
            `;
            cartTableBody.appendChild(row);
        });
    }

    function removeFromCart(index) {
        Swal.fire({
            title: "Konfirmasi Penghapusan",
            text: "Apakah Anda yakin ingin menghapus barang ini dari keranjang?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya, Hapus",
            cancelButtonText: "Batal",
        }).then((result) => {
            if (result.isConfirmed) {
                cartItems.splice(index, 1);
                renderCartTable();
                updateCartInput();
                Swal.fire("Dihapus!", "Barang berhasil dihapus dari keranjang.", "success");
            }
        });
    }


    function updateCartInput() {
        document.getElementById('cartItemsInput').value = JSON.stringify(cartItems);
    }

    // Fungsi konfirmasi untuk approve
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

    // Fungsi konfirmasi untuk menyelesaikan barang
    function confirmCompletion(url) {
        Swal.fire({
            title: 'Perangkat sudah diterima?',
            text: "Pilih Ya untuk menyelesaikan Proses ini",

            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya',
            cancelButtonText: 'Batal',
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect to the completion route
                let form = document.createElement('form');
                form.action = url;
                form.method = 'POST';
                form.innerHTML = '@csrf'; // CSRF token
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // Fungsi konfirmasi untuk mengirim barang
    function confirmShipment(url) {
        Swal.fire({
            title: 'Apakah Anda yakin ingin mengirim barang ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Kirim',
            cancelButtonText: 'Batal',
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect to the shipment route
                let form = document.createElement('form');
                form.action = url;
                form.method = 'POST';
                form.innerHTML = '@csrf'; // CSRF token
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function confirmCancellation(barangKeluarId) {
        Swal.fire({
            title: 'Apakah Anda yakin ingin membatalkan barang ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Batalkan',
            cancelButtonText: 'Batal',
        }).then((result) => {
            if (result.isConfirmed) {
                // Menyembunyikan form dan mengirimnya melalui JavaScript
                document.getElementById('cancelForm' + barangKeluarId).submit();
            }
        });
    }

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

    function markAsReadAndRedirect(notificationId, url) {
        // Kirim form untuk menandai notifikasi sebagai dibaca
        document.getElementById('mark-as-read-form-' + notificationId).submit();

        // Tunggu sebentar agar form terkirim, lalu alihkan ke URL notifikasi
        setTimeout(function() {
            window.location.href = url;
        }, 500); // Delay 500ms untuk memastikan form terkirim
    }

    function canceldismantle(dismantleId) {
        Swal.fire({
            title: 'Apakah Anda yakin ingin membatalkan barang ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Batalkan',
            cancelButtonText: 'Batal',
        }).then((result) => {
            if (result.isConfirmed) {
                // Menyembunyikan form dan mengirimnya melalui JavaScript
                document.getElementById('cancelForm' + dismantleId).submit();
            }
        });
    }

    function completeDismantle() {
        if (confirm('Apakah Anda yakin ingin menyelesaikan Work Order ini?')) {
            document.getElementById('completeForm').submit();
        }
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
<script>
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
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const searchInput = document.getElementById("searchInput");
        const jenisFilter = document.getElementById("jenisFilter");
        const merekFilter = document.getElementById("merekFilter");
        const tipeFilter = document.getElementById("tipeFilter");
        const tableRows = document.querySelectorAll("#stockTableBody tr");

        function filterTable() {
            const searchText = searchInput.value.toLowerCase();
            const selectedJenis = jenisFilter.value;
            const selectedMerek = merekFilter.value;
            const selectedTipe = tipeFilter.value;

            tableRows.forEach(row => {
                const jenis = row.getAttribute("data-jenis");
                const merek = row.getAttribute("data-merek");
                const tipe = row.getAttribute("data-tipe");
                const rowText = row.innerText.toLowerCase();

                const matchesSearch = rowText.includes(searchText);
                const matchesJenis = !selectedJenis || jenis === selectedJenis;
                const matchesMerek = !selectedMerek || merek === selectedMerek;
                const matchesTipe = !selectedTipe || tipe === selectedTipe;

                if (matchesSearch && matchesJenis && matchesMerek && matchesTipe) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }

        searchInput.addEventListener("keyup", filterTable);
        jenisFilter.addEventListener("change", filterTable);
        merekFilter.addEventListener("change", filterTable);
        tipeFilter.addEventListener("change", filterTable);
    });
</script>
{{-- SweetAlert + redirect --}}
<script>
    document.getElementById('btn-kirim-perangkat')?.addEventListener('click', function(e) {
        const url = this.dataset.url;

        Swal.fire({
            title: 'Kirim Perangkat?',
            text: "Isi form terlebih dahulu.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#aaa',
            confirmButtonText: 'Ya'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    });


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
</script>