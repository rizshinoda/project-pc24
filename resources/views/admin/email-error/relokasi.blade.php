<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Email Notifikasi Gagal</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body class="bg-light">

    <div class="container d-flex justify-content-center align-items-center"
        style="min-height: 100vh;">

        <div class="card shadow-sm border-0"
            style="max-width: 600px; width: 100%;">

            <div class="card-body text-center p-5">

                <div class="mb-4">
                    <i class="fa-solid fa-triangle-exclamation text-warning"
                        style="font-size: 60px;">
                    </i>
                </div>

                <h4 class="fw-bold mb-3">
                    Email Notifikasi Gagal Dikirim
                </h4>

                <div class="alert alert-warning">
                    <strong>Work Order Relokasi berhasil diterbitkan</strong>,
                    tetapi email notifikasi tidak dapat dikirim secara otomatis.
                </div>

                <p class="text-muted">
                    Silakan lakukan pengiriman email secara manual.
                </p>



                <a href="{{ route('admin.relokasi') }}"
                    class="btn btn-primary">

                    <i class="fa-solid fa-arrow-left me-1"></i>
                    Kembali ke Relokasi

                </a>

            </div>

        </div>

    </div>

</body>

</html>