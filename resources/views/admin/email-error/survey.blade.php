<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Email Notifikasi Gagal</title>

    {{-- Bootstrap --}}
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    {{-- Font Awesome --}}
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>

    <div class="container py-5">

        <div class="row justify-content-center">

            <div class="col-md-7 col-lg-6">

                <div class="card border-0 shadow-sm">

                    <div class="card-body text-center p-5">

                        <div class="mb-4">
                            <div
                                class="d-inline-flex align-items-center justify-content-center rounded-circle"
                                style="
                                width: 80px;
                                height: 80px;
                                background-color: #fff3cd;
                            ">
                                <i
                                    class="fas fa-exclamation-triangle"
                                    style="
                                    font-size: 36px;
                                    color: #f0ad4e;
                                "></i>
                            </div>
                        </div>

                        <h4 class="fw-bold mb-3">
                            Email Notifikasi Gagal Dikirim
                        </h4>

                        <p class="text-muted mb-4">
                            Work Order berhasil diterbitkan, tetapi
                            email notifikasi tidak dapat dikirim secara otomatis.
                        </p>

                        <div class="alert alert-warning text-start">
                            <div class="d-flex">
                                <div class="me-3">
                                    <i class="fas fa-info-circle"></i>
                                </div>

                                <div>
                                    <strong>Perhatian</strong>
                                    <br>
                                    Silakan lakukan pengiriman email secara
                                    manual kepada penerima.
                                </div>
                            </div>
                        </div>



                        <a
                            href="{{ route('admin.survey') }}"
                            class="btn btn-primary px-4">
                            <i class="fas fa-arrow-left me-2"></i>
                            Kembali ke Survey
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

</body>

</html>