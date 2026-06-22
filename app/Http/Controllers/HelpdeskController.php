<?php

namespace App\Http\Controllers;

use App\Exports\OnlineBillingExport;
use App\Helpers\LogActivity;
use App\Imports\OnlineBillingImport;
use App\Mail\MaintenanceRequestMail;
use App\Mail\PermintaanBarangMail;
use App\Models\BarangKeluar;
use App\Models\DismantleDetail;
use App\Models\DismantleProgress;
use App\Models\DowngradeProgress;
use App\Models\DowngradeProgressPhoto;
use App\Models\GantiVendorProgress;
use App\Models\GantiVendorProgressPhoto;
use App\Models\InstallProgress;
use App\Models\InstallProgressPhoto;
use App\Models\Jenis;
use App\Models\MaintenanceProgress;
use App\Models\MaintenanceProgressPhoto;
use App\Models\NomorSurat;
use App\Models\Notification;
use App\Models\OnlineBilling;
use App\Models\RelokasiProgress;
use App\Models\RelokasiProgressPhoto;
use App\Models\ReqBarangProgress;
use App\Models\RequestBarang;
use App\Models\RequestBarangDetails;
use App\Models\RequestBarangStockBarang;
use App\Models\Status;
use App\Models\StockBarang;
use App\Models\UpgradeProgress;
use App\Models\UpgradeProgressPhoto;
use App\Models\User;
use App\Models\WorkOrderDismantle;
use App\Models\WorkOrderDowngrade;
use App\Models\WorkOrderGantiVendor;
use App\Models\WorkOrderInstall;
use App\Models\WorkOrderMaintenance;
use App\Models\WorkOrderMaintenanceDetail;
use App\Models\WorkOrderRelokasi;
use App\Models\WorkOrderSurvey;
use App\Models\WorkOrderUpgrade;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use setasign\Fpdi\Fpdi;

class HelpdeskController extends Controller
{
    private $roles = [
        0 => 'SuperAdmin',
        1 => 'Admin',
        2 => 'GA',
        3 => 'Helpdesk',
        4 => 'NOC',
        5 => 'PSB',
        6 => 'NA',
    ];

    private function ambilDataRole()
    {
        $role = Auth::user()->is_role;
        $roleText = $this->roles[$role] ?? 'Unknown Role';

        // return [
        //     'getRecord' => User::find(Auth::user()->id),
        //     'roleText' => $roleText,

        // ];
        // Alias untuk tampilan
        $displayRole = [
            'Helpdesk' => 'Support',
        ];

        return [
            'getRecord' => User::find(Auth::user()->id),
            'roleText' => $displayRole[$roleText] ?? $roleText,
        ];
    }

    private function renderView($namaView, $data = [])
    {
        $roleData = $this->ambilDataRole(); // Pasti array
        $role = Auth::user()->is_role;

        if (!isset($this->roles[$role])) {
            return redirect()->back()->with('error', 'Role tidak ditemukan.');
        }

        // Gabungkan data role dengan data tambahan
        if (is_array($roleData) && is_array($data)) {
            $data = array_merge($roleData, $data);
        }

        return view(strtolower($this->roles[$role]) . '.' . $namaView, $data);
    }
    // Fungsi untuk setiap view
    public function dashboard()
    {
        $models = [
            'Survey' => [
                'model' => WorkOrderSurvey::class
            ],

            'Instalasi' => [
                'model' => WorkOrderInstall::class,
                'jenis_pekerjaan' => 'Instalasi'
            ],

            'POC' => [
                'model' => WorkOrderInstall::class,
                'jenis_pekerjaan' => 'POC'
            ],

            'Jasa' => [
                'model' => WorkOrderInstall::class,
                'jenis_pekerjaan' => 'Jasa'
            ],

            'Upgrade' => [
                'model' => WorkOrderUpgrade::class
            ],

            'Downgrade' => [
                'model' => WorkOrderDowngrade::class
            ],

            'Dismantle' => [
                'model' => WorkOrderDismantle::class
            ],

            'Relokasi' => [
                'model' => WorkOrderRelokasi::class
            ],

            'Ganti Vendor' => [
                'model' => WorkOrderGantiVendor::class
            ],

            'Maintenance' => [
                'model' => WorkOrderMaintenance::class
            ],
        ];

        $statuses = [
            'Pending',
            'On Progress',
            'Shipped',
            'Completed',
        ];

        $closedStatuses = [
            'Completed',
            'Rejected',
            'Canceled'
        ];

        $rfsTypes = [
            'Survey',
            'Instalasi',
            'POC',
            'Jasa'
        ];

        $totalWO = 0;
        $overdueTotal = 0;
        $escalationWO = collect();
        $statusDistribution = array_fill_keys($statuses, 0);
        $woChart = [];
        $billingChart = [
            'Active' => OnlineBilling::where('status', 'active')->count(),
            'Dismantle' => OnlineBilling::where('status', 'dismantle')->count(),
        ];
        foreach ($models as $type => $config) {

            $model = $config['model'];

            $query = $model::query();

            // Filter jenis pekerjaan untuk instalasi
            if (isset($config['jenis_pekerjaan'])) {
                $query->where(
                    'jenis_pekerjaan',
                    $config['jenis_pekerjaan']
                );
            }

            $count = (clone $query)->count();
            $totalWO += $count;

            // Statistik status
            foreach ($statuses as $status) {

                $jumlah = (clone $query)
                    ->where('status', $status)
                    ->count();

                $woChart[$type][$status] = $jumlah;

                $statusDistribution[$status] += $jumlah;
            }

            // Statistik overdue
            $overdueCount = 0;

            if (in_array($type, $rfsTypes)) {

                $overdueCount = (clone $query)
                    ->whereDate('tanggal_rfs', '<', Carbon::today())
                    ->whereNotIn('status', $closedStatuses)
                    ->count();

                // Data escalation
                $dataWO = (clone $query)
                    ->with('pelanggan')
                    ->whereDate('tanggal_rfs', '<', Carbon::today())
                    ->whereNotIn('status', $closedStatuses)
                    ->get()
                    ->map(function ($item) use ($type) {

                        $item->jenis = $type;

                        $item->nama_pelanggan =
                            optional($item->pelanggan)->nama_pelanggan;

                        $item->hari_overdue =
                            Carbon::parse($item->tanggal_rfs)
                            ->diffInDays(Carbon::today());

                        switch ($type) {


                            case 'Instalasi':
                                $item->detail_url = route(
                                    'hd.instalasi.show',
                                    $item->id
                                );
                                break;

                            case 'POC':
                                $item->detail_url = route(
                                    'hd.poc_show',
                                    $item->id
                                );
                                break;

                            case 'Jasa':
                                $item->detail_url = route(
                                    'hd.jasa_show',
                                    $item->id
                                );
                                break;
                        }

                        return $item;
                    });

                $escalationWO = $escalationWO->merge($dataWO);
            }

            $woChart[$type]['Overdue'] = $overdueCount;
            $overdueTotal += $overdueCount;
        }

        $statusDistribution['Overdue'] = $overdueTotal;

        $escalationWO = $escalationWO
            ->sortByDesc('hari_overdue')
            ->take(10);

        $notifications = Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->get();

        $data = array_merge(
            $this->ambilDataRole(),
            [
                'totalWO' => $totalWO,
                'onProgress' => ($statusDistribution['On Progress'] ?? 0)
                    +
                    ($statusDistribution['Shipped'] ?? 0),
                'completed' => $statusDistribution['Completed'] ?? 0,
                'overdue' => $overdueTotal,
                'statusDistribution' => $statusDistribution,
                'woChart' => $woChart,
                'escalationWO' => $escalationWO,
                'notifications' => $notifications,
                'billingChart' => $billingChart,

            ]
        );

        return $this->renderView('dashboard', $data);
    }



    /**
     * Menghitung jumlah WO selesai dalam rentang waktu tertentu
     */
    protected function getCompletedWorkOrdersByWeek($model, $start, $end)
    {
        return $model::whereBetween('updated_at', [$start, $end]) // atau 'completed_at'
            ->where('status', 'completed')
            ->count();
    }
    public function requestbarang(Request $request)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();
        // Ambil parameter dari request
        $status = $request->get('status', 'all');
        $search = $request->get('search');
        $month = $request->get('month');
        $year = $request->get('year');

        // Query untuk mendapatkan data request barang
        $query = RequestBarang::orderBy('created_at', 'desc');

        // Filter berdasarkan status
        if ($status != 'all') {
            $query->where('status', $status);
        }

        // Pencarian hanya di kolom nama_penerima dan alamat_penerima
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_penerima', 'like', '%' . $search . '%') // Pencarian di kolom nama_penerima
                    ->orWhere('alamat_penerima', 'like', '%' . $search . '%')
                    ->orWhere('keterangan', 'like', '%' . $search . '%'); // Pencarian di kolom alamat_penerima
            });
        }

        // Filter berdasarkan bulan dan tahun
        if (!empty($month) && !empty($year)) {
            $query->whereMonth('created_at', $month)
                ->whereYear('created_at', $year);
        } elseif (!empty($year)) {
            $query->whereYear('created_at', $year);
        }

        // Dapatkan data request barang dengan pagination, dan tambahkan query ke pagination URL
        $requestBarangs = $query->paginate(10)->appends([
            'status' => $status,
            'search' => $search,
            'month' => $month,
            'year' => $year,
        ]);

        // Ambil notifikasi yang belum dibaca
        // $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // Gabungkan data request barang ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('requestBarangs', 'status', 'search', 'month', 'year', 'notifications'));

        // Render view berdasarkan role
        return $this->renderView('requestbarang', $data);
    }

    public function createrequest()
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();
        // Mengambil daftar jenis unik dari StockBarang
        $jenisList = Jenis::select('id', 'nama_jenis')->get();

        // Mengambil data stok dengan total jumlah berdasarkan tipe, merek, dan jenis, serta melakukan pagination
        $stockBarangs = StockBarang::with(['merek', 'tipe', 'jenis'])
            ->selectRaw('tipe_id, merek_id, jenis_id, kualitas, SUM(jumlah) as total_jumlah')
            ->groupBy('tipe_id', 'merek_id', 'jenis_id', 'kualitas')
            ->get();
        $clients = OnlineBilling::join('pelanggans', 'online_billings.pelanggan_id', '=', 'pelanggans.id')
            ->orderBy('pelanggans.nama_pelanggan', 'asc')
            ->orderBy('online_billings.nama_site', 'asc')
            ->select('online_billings.*') // pastikan hanya ambil kolom dari tabel utama
            ->with('pelanggan') // relasi tetap diload
            ->get();

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('clients', 'stockBarangs', 'jenisList', 'notifications'));

        return $this->renderView('requestbarang_create', $data);
    }


    public function storerequest(Request $request)
    {
        // Validasi input
        $validatedData = $request->validate([
            'online_billing_id' => 'nullable|exists:online_billings,id',
            'subject_manual' => 'nullable|string',
            'nama_penerima' => 'required|string|max:255',
            'alamat_penerima' => 'required|string|max:255',
            'no_penerima' => 'required|string',
            'keterangan' => 'nullable|string',
            'non_stock' => 'nullable|string',
            'kebutuhan' => 'nullable|string',
            'cart' => 'nullable|array',
        ]);

        // Simpan request barang
        $requestBarang = RequestBarang::create([
            'online_billing_id' => $validatedData['online_billing_id'] ?? null,
            'nama_penerima' => $validatedData['nama_penerima'],
            'alamat_penerima' => $validatedData['alamat_penerima'],
            'no_penerima' => $validatedData['no_penerima'],
            'keterangan' => $validatedData['keterangan'],
            'non_stock' => $validatedData['non_stock'],
            'kebutuhan' => $validatedData['kebutuhan'],
            'subject_manual' => $validatedData['subject_manual'] ?? null,
            'status' => 'pending',
            'user_id' => Auth::id(),
        ]);

        // Simpan detail stock barang jika ada
        if (!empty($validatedData['cart'])) {
            foreach ($validatedData['cart'] as $jenis => $merekArray) {
                foreach ($merekArray as $merek => $tipeArray) {
                    foreach ($tipeArray as $tipe => $kualitasArray) {
                        foreach ($kualitasArray as $kualitas => $item) {
                            $stockBarang = StockBarang::whereHas('merek', fn($q) => $q->where('nama_merek', $merek))
                                ->whereHas('tipe', fn($q) => $q->where('nama_tipe', $tipe))
                                ->where('kualitas', $kualitas)
                                ->first();

                            RequestBarangDetails::create([
                                'request_barang_id' => $requestBarang->id,
                                'stock_barang_id' => $stockBarang?->id,
                                'merek' => $merek,
                                'tipe' => $tipe,
                                'kualitas' => $kualitas,
                                'jumlah' => $item['total_jumlah'],
                            ]);
                        }
                    }
                }
            }
        }

        // Notifikasi ke GA
        $gaUsers = User::where('is_role', 2)->get();
        foreach ($gaUsers as $gaUser) {
            Notification::create([
                'user_id' => $gaUser->id,
                'message' => 'Request barang baru diajukan oleh HD: ' . Auth::user()->name,
                'url' => route('ga.request_barang.show', ['id' => $requestBarang->id]) . '#request',
            ]);
        }

        // Ambil semua barang stok
        $detailBarang = RequestBarangDetails::where('request_barang_id', $requestBarang->id)->get();
        $requestBarang->load('onlineBilling', 'user');

        // Parse non-stock
        $nonStockItems = [];

        if (!empty($validatedData['non_stock'])) {
            $lines = preg_split('/\r\n|\r|\n/', $validatedData['non_stock']);
            foreach ($lines as $line) {
                $parts = explode(',', $line);

                $nama   = trim($parts[0] ?? '');
                $jumlah = isset($parts[1]) ? trim($parts[1]) : '';
                $satuan = isset($parts[2]) ? trim($parts[2]) : '';

                if ($nama) {
                    $nonStockItems[] = [
                        'nama'   => $nama,
                        'jumlah' => $jumlah,
                        'satuan' => $satuan,
                    ];
                }
            }
        }

        // Ambil email user role GA
        $recipients = User::where('is_role', 2)
            ->whereNotNull('email')
            ->pluck('email')
            ->toArray();

        // Kirim email jika ada penerima
        if (!empty($recipients)) {
            Mail::to($recipients)->send(
                new \App\Mail\PermintaanBarangMail($requestBarang, $detailBarang)
            );
        }
        // Kirim email tanpa PDF jika tidak ada barang non-stock
        Mail::to($recipients)->send(new \App\Mail\PermintaanBarangMail($requestBarang, $detailBarang));



        return redirect()->route('hd.request_barang')->with('success', 'Request barang berhasil diajukan.');
    }

    public function printSuratRequest($id)
    {
        $RequestBarang = RequestBarang::findOrFail($id);
        $tanggalRequest = now()->format('d-m-Y');
        $formattedDate = Carbon::parse($tanggalRequest)->translatedFormat('l, d F Y');
        $templatePath = storage_path('app/public/pdf/form_pengajuan.pdf');

        $pdf = new FPDI();
        $pdf->AddPage();
        $pdf->setSourceFile($templatePath);
        $templateId = $pdf->importPage(1);
        $pdf->useTemplate($templateId, 0, 0, 210);
        // Setelah nomor surat, atur ulang font ke normal (non-bold)
        $pdf->SetFont('Times', '', 12); // Regular 12pt
        $pdf->SetTextColor(0, 0, 0);

        $y = 80; // posisi awal Y

        // Hari, Tgl
        $pdf->SetXY(79, $y);
        $pdf->Cell(0, 0, Carbon::now()->translatedFormat('l, d F Y'), 0, 1);

        // Lokasi Penempatan
        $y += 5;
        $pdf->SetXY(79, $y);
        $pdf->Cell(0, 0, $RequestBarang->penempatan_barang, 0, 1);

        // Site
        $y += 5;
        $pdf->SetXY(79, $y);
        $pdf->Cell(0, 0, $RequestBarang->subject_manual, 0, 1);

        // Jenis Pekerjaan
        $y += 5;
        $pdf->SetXY(79, $y);
        $pdf->Cell(0, 0, $RequestBarang->kebutuhan, 0, 1);

        $nonStockItems = []; // default kosong, supaya tidak undefined

        if (!empty($RequestBarang['non_stock'])) {
            $lines = preg_split('/\r\n|\r|\n/', $RequestBarang['non_stock']);
            foreach ($lines as $line) {
                $parts = explode(',', $line);

                $nama   = trim($parts[0] ?? '');
                $jumlah = isset($parts[1]) ? trim($parts[1]) : '';
                $satuan = isset($parts[2]) ? trim($parts[2]) : '';

                if ($nama) {
                    $nonStockItems[] = [
                        'nama'   => $nama,
                        'jumlah' => $jumlah,
                        'satuan' => $satuan,
                    ];
                }
            }
        }

        // Loop isi tabel
        $cellHeight = 45; // tinggi cell disesuaikan dengan tinggi baris di template

        $y = 97;
        $no = 1;

        foreach ($nonStockItems as $item) {
            $pdf->SetXY(23, $y); // No
            $pdf->Cell(15, $cellHeight, $no++, 0, 0,);

            $pdf->SetXY(30, $y); // Nama Barang
            $pdf->Cell(66, $cellHeight, $item['nama'], 0, 0);

            $pdf->SetXY(98, $y); // Qty
            $pdf->Cell(66, $cellHeight, $item['jumlah'] ?? '', 0, 0,);

            $pdf->SetXY(136, $y); // Satuan
            $pdf->Cell(66, $cellHeight, $item['satuan'] ?? '', 0, 1,);

            $y += 5.2;
        }

        return response()->stream(function () use ($pdf) {
            echo $pdf->Output('S'); // ambil PDF sebagai string dan echo ke output
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="surat_request.pdf"',
        ]);
    }
    // Menampilkan form edit untuk permintaan barang

    public function editrequest($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // Ambil data permintaan barang berdasarkan ID
        $requestBarang = RequestBarang::with('requestBarangDetails')->findOrFail($id);

        // Dapatkan daftar jenis barang
        $jenisList = Jenis::all();

        // Mengambil data stok dengan total jumlah berdasarkan tipe, merek, dan jenis, serta melakukan pagination
        $stockBarangs = StockBarang::with(['merek', 'tipe', 'jenis'])
            ->selectRaw('tipe_id, merek_id, jenis_id, kualitas, SUM(jumlah) as total_jumlah')
            ->groupBy('tipe_id', 'merek_id', 'jenis_id', 'kualitas')
            ->paginate(10); // Menggunakan pagination dengan 10 item per halaman

        // Siapkan data detail barang dalam format array, kosongkan keranjang
        $requestBarangDetails = []; // Kosongkan keranjang

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('stockBarangs', 'jenisList', 'requestBarang', 'requestBarangDetails', 'notifications'));

        return $this->renderView('requestbarang_edit', $data);
    }

    public function updateRequest(Request $request, $id)
    {
        // Validasi input data
        $validatedData = $request->validate([
            'nama_penerima' => 'required|string|max:255',
            'alamat_penerima' => 'required|string|max:255',
            'no_penerima' => 'required|string',
            'keterangan' => 'nullable|string',
            'non_stock' => 'nullable|string',
            'kebutuhan' => 'nullable|string',

            'cart' => 'nullable|array', // Keranjang tidak wajib
        ]);

        // Temukan request barang yang ingin diperbarui
        $requestBarang = RequestBarang::findOrFail($id);
        $requestBarang->update([
            'nama_penerima' => $validatedData['nama_penerima'],
            'alamat_penerima' => $validatedData['alamat_penerima'],
            'no_penerima' => $validatedData['no_penerima'],
            'keterangan' => $validatedData['keterangan'],
            'non_stock' => $validatedData['non_stock'],
            'kebutuhan' => $validatedData['kebutuhan'],

        ]);

        // Hapus detail barang yang ada sebelumnya
        RequestBarangDetails::where('request_barang_id', $requestBarang->id)->delete();

        // Simpan detail barang ke `request_barang_details` jika keranjang tidak kosong
        if (!empty($validatedData['cart'])) {
            foreach ($validatedData['cart'] as $jenis => $merekArray) {
                foreach ($merekArray as $merek => $tipeArray) {
                    foreach ($tipeArray as $tipe => $kualitasArray) {
                        foreach ($kualitasArray as $kualitas => $item) {
                            $stockBarang = StockBarang::whereHas('merek', fn($query) => $query->where('nama_merek', $merek))
                                ->whereHas('tipe', fn($query) => $query->where('nama_tipe', $tipe))
                                ->where('kualitas', $kualitas)
                                ->first();

                            RequestBarangDetails::create([
                                'request_barang_id' => $requestBarang->id,
                                'stock_barang_id' => $stockBarang?->id, // Gunakan null-safe operator untuk menghindari error jika tidak ditemukan
                                'merek' => $merek,
                                'tipe' => $tipe,
                                'kualitas' => $kualitas,
                                'jumlah' => $item['total_jumlah'],
                            ]);
                        }
                    }
                }
            }
        }
        return redirect()->route('hd.request_barang')->with('success', 'Request barang berhasil diperbarui.');
    }
    public function markAsReadhd($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->is_read = true;
        $notification->save();

        return redirect()->to($notification->url);
    }


    public function showrequest($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // Gabungkan data survey ke dalam data role
        $requestBarang = RequestBarang::with('requestBarangDetails.stockBarang')->findOrFail($id);
        $progressList = ReqBarangProgress::where('req_barang_id', $id)->get();

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('requestBarang', 'progressList', 'notifications'));


        return $this->renderView('requestbarang_show', $data);
    }


    public function maintenance(Request $request)
    {
        // Ambil parameter dari request
        $status = $request->get('status', 'all');
        $search = $request->get('search');
        $month = $request->get('month');
        $year = $request->get('year');

        // Query untuk mendapatkan data survey dengan eager loading
        $query = WorkOrderMaintenance::with([
            'onlineBilling.pelanggan',
            'onlineBilling.vendor',
            'onlineBilling.instansi'
        ])->orderBy('created_at', 'desc');

        // Filter berdasarkan status
        if ($status != 'all') {
            $query->where('status', $status);
        }

        // Pencarian di semua kolom yang relevan
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('no_spk', 'like', '%' . $search . '%')
                    ->orWhereHas('onlineBilling', function ($q) use ($search) {
                        $q->where('nama_site', 'like', '%' . $search . '%')
                            ->orWhereHas('pelanggan', function ($q) use ($search) {
                                $q->where('nama_pelanggan', 'like', '%' . $search . '%');
                            })
                            ->orWhereHas('instansi', function ($q) use ($search) {
                                $q->where('nama_instansi', 'like', '%' . $search . '%');
                            });
                    });
            });
        }

        // Filter berdasarkan bulan dan tahun
        if (!empty($month) && !empty($year)) {
            $query->whereMonth('created_at', $month)
                ->whereYear('created_at', $year);
        } elseif (!empty($year)) {
            $query->whereYear('created_at', $year);
        }

        // Dapatkan data survey dengan pagination
        $getMaintenance = $query->paginate(10)->appends([
            'status' => $status,
            'search' => $search,
            'month' => $month,
            'year' => $year,
        ]);

        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('getMaintenance', 'status', 'search', 'month', 'year', 'notifications'));

        return $this->renderView('maintenance', $data);
    }
    public function maintenanceCreate($id)
    {
        $onlineBilling = OnlineBilling::findOrFail($id);

        // Ambil daftar jenis barang
        $jenisList = Jenis::select('id', 'nama_jenis')->get();

        // Ambil stok barang
        $stockBarangs = StockBarang::with(['merek', 'tipe', 'jenis'])
            ->selectRaw('tipe_id, merek_id, jenis_id, kualitas, SUM(jumlah) as total_jumlah')
            ->groupBy('tipe_id', 'merek_id', 'jenis_id', 'kualitas')
            ->get();

        // Ambil nomor maintenance terakhir (TANPA reset)
        $lastMaintenance = WorkOrderMaintenance::orderBy('id', 'desc')->first();

        if ($lastMaintenance && preg_match('/\/(\d+)$/', $lastMaintenance->no_spk, $matches)) {
            $lastNumber = (int) $matches[1];
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        // Pad minimal 4 digit (kalau lebih, tampil apa adanya)
        $numberFormatted = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        // Generate nomor MAINTENANCE
        $no_spk = 'PC24Telin/PSB-MAINTENANCE/' . now()->format('Y-m-d') . '/' . $numberFormatted;

        // Notifikasi
        $notifications = Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->get();

        // Gabungkan data role
        $data = array_merge(
            $this->ambilDataRole(),
            compact('stockBarangs', 'jenisList', 'notifications', 'no_spk', 'onlineBilling')
        );

        return $this->renderView('maintenance_create', $data);
    }

    public function maintenanceStore(Request $request)
    {
        // Validasi request
        $validated = $request->validate([
            'no_spk' => 'required|string|unique:work_order_maintenances',
            'online_billing_id' => 'required|exists:online_billings,id',
            'keterangan' => 'nullable|string',
            'non_stock' => 'nullable|string',

            'tanggal_maintenance' => 'required|date',
            'cart' => 'nullable|array', // Keranjang tidak wajib

            // tambahkan validasi lain sesuai kebutuhan
        ]);
        // Inisialisasi variabel $filename sebagai null terlebih dahulu
        $filename = null;

        // Proses upload foto jika ada
        if ($request->hasFile('foto')) {
            // Ambil file
            $file = $request->file('foto');

            // Buat nama file unik
            $filename = time() . '_' . $file->getClientOriginalName();

            // Simpan file di folder storage/surveys
            $file->storeAs('public/surveys', $filename);
        }


        $getMaintenance = WorkOrderMaintenance::create([
            'online_billing_id' => $validated['online_billing_id'],
            'no_spk' => $validated['no_spk'],
            'tanggal_maintenance' => $validated['tanggal_maintenance'],
            'admin_id' => Auth::user()->id,
            'status' => 'Pending', // Default status
            'keterangan' => $validated['keterangan'],
            'non_stock' => $validated['non_stock'],

        ]);
        // Simpan detail barang ke `request_barang_details` jika keranjang tidak kosong
        if (!empty($validated['cart'])) {
            foreach ($validated['cart'] as $jenis => $merekArray) {
                foreach ($merekArray as $merek => $tipeArray) {
                    foreach ($tipeArray as $tipe => $kualitasArray) {
                        foreach ($kualitasArray as $kualitas => $item) {
                            $stockBarang = StockBarang::whereHas('merek', fn($query) => $query->where('nama_merek', $merek))
                                ->whereHas('tipe', fn($query) => $query->where('nama_tipe', $tipe))
                                ->where('kualitas', $kualitas)
                                ->first();

                            WorkOrderMaintenanceDetail::create([
                                'work_order_maintenance_id' => $getMaintenance->id,
                                'stock_barang_id' => $stockBarang?->id, // Gunakan null-safe operator untuk menghindari error jika tidak ditemukan
                                'merek' => $merek,
                                'tipe' => $tipe,
                                'kualitas' => $kualitas,
                                'jumlah' => $item['total_jumlah'],
                            ]);
                        }
                    }
                }
            }
        }
        Status::create([
            'work_orderable_id' => $getMaintenance->id,
            'work_orderable_type' => WorkOrderMaintenance::class,
            'online_billing_id' => $validated['online_billing_id'],
            'process' => 'Maintenance',
            'status' => 'Pending',
            'admin_id' => Auth::user()->id,
        ]);
        LogActivity::add(
            'Maintenance',
            $getMaintenance->onlineBilling->nama_site
        );
        // Dapatkan semua pengguna dengan role General Affair (misalnya role 2)
        $gaUsers = User::where('is_role', 2)->get();
        // Dapatkan semua pengguna dengan role PSB (misalnya role 5)
        $nocUsers = User::where('is_role', 4)->get();

        $psbUsers = User::where('is_role', 5)->get();
        // Buat notifikasi untuk setiap pengguna General Affair
        foreach ($gaUsers as $gaUser) {
            $url = route('ga.maintenance_show', ['id' => $getMaintenance->id]) . '#maintenance';

            Notification::create([
                'user_id' => $gaUser->id,
                'message' => 'WO Maintenance baru telah diterbitkan dengan No Order: ' . $getMaintenance->no_spk,
                'url' => $url, // URL dengan hash #request
            ]);
        }
        // Buat notifikasi untuk setiap pengguna PSB
        foreach ($psbUsers as $psbUser) {
            $url = route('psb.maintenance_show', ['id' => $getMaintenance->id]) . '#maintenance';

            Notification::create([
                'user_id' => $psbUser->id,
                'message' => 'WO Maintenance baru telah diterbitkan dengan No Order: ' . $getMaintenance->no_spk,
                'url' => $url, // URL dengan hash #instalasi
            ]);
        }
        // Buat notifikasi untuk setiap pengguna PSB
        foreach ($nocUsers as $nocUser) {
            $url = route('noc.maintenance_show', ['id' => $getMaintenance->id]) . '#maintenance';

            Notification::create([
                'user_id' => $nocUser->id,
                'message' => 'WO Maintenance baru telah diterbitkan dengan No Order: ' . $getMaintenance->no_spk,
                'url' => $url, // URL dengan hash #instalasi
            ]);
        }
        // Ambil semua detail barang dari request_barang_id ini
        $detailBarang = WorkOrderMaintenanceDetail::where('work_order_maintenance_id', $getMaintenance->id)->get();

        // Load relasi onlineBilling untuk email
        $getMaintenance->load('onlineBilling', 'admin');

        $gaUsers = User::where('is_role', 2)
            ->whereNotNull('email')
            ->get();

        foreach ($gaUsers as $ga) {
            Mail::to($ga->email)->send(
                new \App\Mail\MaintenanceRequestMail(
                    $getMaintenance,
                    $detailBarang,
                    2 // GA
                )
            );
        }
        $psbUsers = User::where('is_role', 5)
            ->whereNotNull('email')
            ->get();

        foreach ($psbUsers as $psb) {
            Mail::to($psb->email)->send(
                new \App\Mail\MaintenanceRequestMail(
                    $getMaintenance,
                    $detailBarang,
                    5 // PSB
                )
            );
        }


        return redirect()->route('hd.maintenance')->with('success', 'Work order berhasil diterbitkan.');
    }

    public function maintenanceShow($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // $progressList = SurveyProgress::where('work_order_survey_id', $id)->get();
        $progressList = MaintenanceProgress::where('work_order_maintenance_id', $id)->get();

        // Menampilkan detail work order
        $getMaintenance = WorkOrderMaintenance::with('WorkOrderMaintenanceDetail.stockBarang')->findOrFail($id);


        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('progressList', 'getMaintenance', 'notifications'));

        // Render view berdasarkan role
        return $this->renderView('maintenance_show', $data);
    }
    public function maintenanceEdit($id)
    {
        // Ambil data work order berdasarkan ID
        $workOrder = WorkOrderMaintenance::findOrFail($id);

        if ($workOrder->status === 'Completed') {
            return redirect()->back()->with('error', 'Status Maintenance sudah selesai, tidak bisa diedit.');
        }
        // Dapatkan daftar jenis barang
        $jenisList = Jenis::all();

        // Mengambil data stok dengan total jumlah berdasarkan tipe, merek, dan jenis, serta melakukan pagination
        $stockBarangs = StockBarang::with(['merek', 'tipe', 'jenis'])
            ->selectRaw('tipe_id, merek_id, jenis_id, kualitas, SUM(jumlah) as total_jumlah')
            ->groupBy(
                'tipe_id',
                'merek_id',
                'jenis_id',
                'kualitas'
            )
            ->paginate(10); // Menggunakan pagination dengan 10 item per halaman

        // Siapkan data detail barang dalam format array, kosongkan keranjang
        $WorkOrderMaintenanceDetail = []; // Kosongkan keranjang
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // Ambil data lain yang dibutuhkan, misalnya online billing
        $onlineBilling = $workOrder->onlineBilling;

        $data = array_merge($this->ambilDataRole(), compact('WorkOrderMaintenanceDetail', 'stockBarangs', 'jenisList', 'workOrder', 'onlineBilling', 'notifications'));
        // Kirim data ke view
        return view('helpdesk.maintenance_edit', $data);
    }

    public function maintenanceUpdate(Request $request, $id)
    {
        // Validasi request
        $validatedData = $request->validate([
            'online_billing_id' => 'required|exists:online_billings,id',
            'keterangan' => 'nullable|string',
            'cart' => 'nullable|array',
            'non_stock' => 'nullable|string',
            'tanggal_maintenance' => 'required|date',


            // tambahkan validasi lain sesuai kebutuhan
        ]);

        // Ambil data work order berdasarkan ID
        $workOrder = WorkOrderMaintenance::findOrFail($id);

        $workOrder->update([
            'online_billing_id' => $validatedData['online_billing_id'],
            'keterangan' => $validatedData['keterangan'],
            'non_stock' => $validatedData['non_stock'],
            'tanggal_maintenance' => $validatedData['tanggal_maintenance'],

        ]);
        LogActivity::add('Maintenance', $workOrder->onlineBilling->nama_site, 'edit');

        // Hapus detail barang yang ada sebelumnya
        WorkOrderMaintenanceDetail::where('work_order_maintenance_id', $workOrder->id)->delete();

        // Simpan detail barang ke `request_barang_details` jika keranjang tidak kosong
        if (!empty($validatedData['cart'])) {
            foreach ($validatedData['cart'] as $jenis => $merekArray) {
                foreach ($merekArray as $merek => $tipeArray) {
                    foreach ($tipeArray as $tipe => $kualitasArray) {
                        foreach ($kualitasArray as $kualitas => $item) {
                            $stockBarang = StockBarang::whereHas('merek', fn($query) => $query->where('nama_merek', $merek))
                                ->whereHas('tipe', fn($query) => $query->where('nama_tipe', $tipe))
                                ->where('kualitas', $kualitas)
                                ->first();

                            WorkOrderMaintenanceDetail::create([
                                'work_order_maintenance_id' => $workOrder->id,
                                'stock_barang_id' => $stockBarang?->id, // Gunakan null-safe operator untuk menghindari error jika tidak ditemukan
                                'merek' => $merek,
                                'tipe' => $tipe,
                                'kualitas' => $kualitas,
                                'jumlah' => $item['total_jumlah'],
                            ]);
                        }
                    }
                }
            }
        }
        // Redirect dengan pesan sukses
        return redirect()->route('hd.maintenance')->with('success', 'Work order berhasil diperbarui.');
    }

    public function maintenanceDestroy($id)
    {
        // Menghapus work order
        $getMaintenance = WorkOrderMaintenance::findOrFail($id);
        if ($getMaintenance->status !== 'Completed') {
            $getMaintenance->delete();
            LogActivity::add('Maintenance', $getMaintenance->onlineBilling->nama_site, 'delete');

            return redirect()->back()->with('success', 'Maintenance berhasil diHapus.');
        }

        return redirect()->back()->with('error', 'Maintenance sudah selesai dan tidak bisa dihapus.');
    }

    public function maintenanceCancel($id)
    {
        // Cari Work Order Upgrade berdasarkan ID
        $getMaintenance = WorkOrderMaintenance::findOrFail($id);

        // Jika status sudah "Completed", tidak bisa dibatalkan
        if ($getMaintenance->status === 'Completed') {
            return redirect()->back()->with('error', 'Maintenance sudah selesai dan tidak bisa dibatalkan.');
        }

        // Jika belum "Completed", ubah status menjadi "Canceled"
        $getMaintenance->status = 'Canceled';
        $getMaintenance->save();
        LogActivity::add('Maintenance', $getMaintenance->onlineBilling->nama_site, 'cancel');

        // Cari entri terkait di tabel statuses
        $status = Status::where('work_orderable_id', $getMaintenance->id)
            ->where('process', 'Maintenance') // Pastikan prosesnya adalah 'Upgrade'
            ->first();

        // Perbarui status di tabel statuses jika entri ditemukan
        if ($status) {
            $status->status = 'Canceled'; // Sesuaikan dengan status baru
            $status->save();
        }

        // Berikan notifikasi sukses
        return redirect()->back()->with('success', 'Maintenance berhasil dibatalkan.');
    }

    public function gantivendor(Request $request)
    {
        // Ambil parameter dari request
        $status = $request->get('status', 'all');
        $search = $request->get('search');
        $month = $request->get('month');
        $year = $request->get('year');

        // Query untuk mendapatkan data survey dengan eager loading
        $query = WorkOrderGantiVendor::with([
            'onlineBilling.pelanggan',
            'onlineBilling.vendor',
            'onlineBilling.instansi'
        ])->orderBy('created_at', 'desc');

        // Filter berdasarkan status
        if ($status != 'all') {
            $query->where('status', $status);
        }

        // Pencarian di semua kolom yang relevan
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('no_spk', 'like', '%' . $search . '%')
                    ->orWhereHas('onlineBilling', function ($q) use ($search) {
                        $q->where('nama_site', 'like', '%' . $search . '%')
                            ->orWhereHas('pelanggan', function ($q) use ($search) {
                                $q->where('nama_pelanggan', 'like', '%' . $search . '%');
                            })
                            ->orWhereHas('instansi', function ($q) use ($search) {
                                $q->where('nama_instansi', 'like', '%' . $search . '%');
                            });
                    });
            });
        }

        // Filter berdasarkan bulan dan tahun
        if (!empty($month) && !empty($year)) {
            $query->whereMonth('created_at', $month)
                ->whereYear('created_at', $year);
        } elseif (!empty($year)) {
            $query->whereYear('created_at', $year);
        }

        // Dapatkan data survey dengan pagination
        $getGantivendor = $query->paginate(10)->appends([
            'status' => $status,
            'search' => $search,
            'month' => $month,
            'year' => $year,
        ]);

        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('getGantivendor', 'status', 'search', 'month', 'year', 'notifications'));

        return $this->renderView('gantivendor', $data);
    }


    public function gantivendorCreate($id)
    {
        $onlineBilling = OnlineBilling::findOrFail($id);

        // Ambil nomor ganti vendor terakhir (TANPA reset)
        $lastGantiVendor = WorkOrderGantiVendor::orderBy('id', 'desc')->first();

        if ($lastGantiVendor && preg_match('/\/(\d+)$/', $lastGantiVendor->no_spk, $matches)) {
            $lastNumber = (int) $matches[1];
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        // Pad minimal 4 digit (kalau lebih, tampil apa adanya)
        $numberFormatted = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        // Generate nomor GANTI VENDOR
        $no_spk = 'PC24Telin/PSB-GANTI_LAYANAN/' . now()->format('Y-m-d') . '/' . $numberFormatted;

        // Notifikasi
        $notifications = Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->get();

        // Gabungkan data role
        $data = array_merge(
            $this->ambilDataRole(),
            compact('notifications', 'no_spk', 'onlineBilling')
        );

        return $this->renderView('gantivendor_create', $data);
    }

    public function gantivendorStore(Request $request)
    {

        // Validasi data yang masuk
        $validated = $request->validate([
            'no_spk' => 'required|string|unique:work_order_ganti_vendors',
            'online_billing_id' => 'required|exists:online_billings,id',
            'keterangan' => 'nullable|string',
            'attachments.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:5120', // Tambahkan ini

        ]);

        // Simpan data ke tabel work_order_upgrades
        $workOrder = WorkOrderGantiVendor::create([
            'online_billing_id' => $validated['online_billing_id'],
            'no_spk' => $validated['no_spk'],
            'keterangan' => $validated['keterangan'],
            'admin_id' => Auth::user()->id,
            'status' => 'Pending', // Default status
        ]);

        Status::create([
            'work_orderable_id' => $workOrder->id,
            'work_orderable_type' => WorkOrderGantiVendor::class,
            'online_billing_id' => $validated['online_billing_id'],
            'process' => 'Ganti Vendor',
            'status' => 'Pending',
            'admin_id' => Auth::user()->id,
        ]);
        LogActivity::add(
            'Ganti Vendor',
            $workOrder->onlineBilling->nama_site
        );
        $uploadedFiles = [];

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {

                // Simpan file dengan nama asli (sesuai input user)
                $path = $file->storeAs(
                    'attachments/gantivendor',
                    $file->getClientOriginalName(),
                    'public'
                );

                $uploadedFiles[] = $path;
            }
        }


        // Simpan path file ke kolom JSON attachments
        $workOrder->attachments = $uploadedFiles;
        $workOrder->save();
        // Dapatkan semua pengguna dengan role General Affair (misalnya role 2)
        $adminUsers = User::where('is_role', 1)->get();

        // Buat notifikasi untuk setiap pengguna General Affair
        foreach ($adminUsers as $adminUsers) {
            $url = route(
                'admin.gantivendor.show',
                ['id' => $workOrder->id]
            ) . '#gantivendor';

            Notification::create([
                'user_id' => $adminUsers->id,
                'message' => 'WO Ganti Vendor baru telah diterbitkan dengan No Order: ' . $workOrder->no_spk,
                'url' => $url, // URL dengan hash #request
            ]);
        }

        // Load relasi onlineBilling untuk email
        $workOrder->load('onlineBilling', 'admin');

        $adminUsers = User::where('is_role', 1)
            ->whereNotNull('email')
            ->get();

        foreach ($adminUsers as $admin) {
            Mail::to([$admin->email, 'presales@pc24.co.id'])->send(
                new \App\Mail\GantiVendorMail(
                    $workOrder,
                    1 // PSB
                )
            );
        }
        return redirect()->route('hd.gantivendor')->with('success', 'Work order berhasil diterbitkan.');
    }
    public function gantivendorShow($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();
        // Gabungkan data survey ke dalam data role
        $progressList = GantiVendorProgress::where('work_order_ganti_vendor_id', $id)->get();

        // Menampilkan detail work order dengan relasi ke onlineBilling dan admin
        $getGantivendor = WorkOrderGantiVendor::with([
            'admin',
            'onlineBilling.pelanggan',
            'onlineBilling.vendor',
            'onlineBilling.instansi'
        ])->findOrFail($id);

        // Gabungkan data ke dalam array data role
        $data = array_merge($this->ambilDataRole(), compact('progressList', 'getGantivendor', 'notifications'));

        // Render view berdasarkan role
        return $this->renderView('gantivendor_show', $data);
    }
    public function gantivendorEdit($id)
    {
        // Ambil data work order berdasarkan ID
        $workOrder = WorkOrderGantiVendor::findOrFail($id);

        if ($workOrder->status === 'Completed') {
            return redirect()->back()->with('error', 'Status Ganti Vendor sudah selesai, tidak bisa diedit.');
        }
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // Ambil data lain yang dibutuhkan, misalnya online billing
        $onlineBilling = $workOrder->onlineBilling;

        $data = array_merge($this->ambilDataRole(), compact('workOrder', 'onlineBilling', 'notifications'));
        // Kirim data ke view
        return $this->renderView('gantivendor_edit', $data);
    }
    public function gantivendorUpdate(Request $request, $id)
    {
        // Validasi data input
        $request->validate([
            'keterangan' => 'required|string',
            'online_billing_id' => 'required|exists:online_billings,id',
        ]);

        // Ambil data work order berdasarkan ID
        $workOrder = WorkOrderGantiVendor::findOrFail($id);

        // Update data work order
        $workOrder->update([
            'keterangan' => $request->keterangan,
            'online_billing_id' => $request->online_billing_id,
        ]);
        LogActivity::add('Ganti Vendor', $workOrder->onlineBilling->nama_site, 'edit');

        // Redirect dengan pesan sukses
        return redirect()->route('hd.gantivendor')->with('success', 'Work order berhasil diperbarui.');
    }
    public function OB(Request $request)
    {
        // Ambil parameter dari request
        $status = $request->get('status', 'active'); // Default status ke 'active'
        $search = $request->get('search');
        $month = $request->get('month');
        $year = $request->get('year');
        $provinsi = $request->get('provinsi'); // Ambil provinsi

        // Query untuk mendapatkan data survey
        $query = OnlineBilling::orderBy('created_at', 'desc');

        // Filter berdasarkan status
        if ($status != 'all') {
            $query->where('status', $status);
        }
        // Filter berdasarkan provinsi
        if (!empty($provinsi)) {
            $query->where('provinsi', $provinsi);
        }
        // Pencarian di semua kolom yang relevan (nama pelanggan, instansi, nama site, dan nama admin)
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->orWhereHas('pelanggan', function ($q) use ($search) { // Pencarian di relasi pelanggan
                    $q->where('nama_pelanggan', 'like', '%' . $search . '%');
                })
                    ->orWhereHas('instansi', function ($q) use ($search) { // Pencarian di relasi instansi
                        $q->where('nama_instansi', 'like', '%' . $search . '%');
                    })
                    ->orWhere('nama_site', 'like', '%' . $search . '%') // Pencarian di kolom nama_site
                    ->orWhere('no_jaringan', 'like', '%' . $search . '%')

                    ->orWhereHas('admin', function ($q) use ($search) { // Pencarian di kolom nama admin melalui relasi
                        $q->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        // Filter berdasarkan bulan dan tahun
        if (!empty($month) && !empty($year)) {
            $query->whereMonth('created_at', $month)
                ->whereYear('created_at', $year);
        } elseif (!empty($year)) {
            $query->whereYear('created_at', $year);
        }

        // Dapatkan data survey dengan pagination, dan tambahkan query ke pagination URL
        $onlinebilling = $query->paginate(10)->appends([
            'status' => $status,
            'search' => $search,
            'month' => $month,
            'year' => $year,
            'provinsi' => $provinsi

        ]);

        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('onlinebilling', 'status', 'search', 'month', 'year', 'provinsi', 'notifications'));

        return $this->renderView('OB', $data);
    }

    public function showOB($id)
    {  // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        $onlinebilling = OnlineBilling::findOrFail($id);
        // Ambil status yang terkait dengan online_billing_id tertentu
        $statuses = Status::where('online_billing_id', $id)->get(); // Menambahkan filter berdasarkan online_billing_id
        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('onlinebilling', 'statuses', 'notifications'));


        return $this->renderView('OB_show', $data);
    }
    public function sitedismantle(Request $request)
    {
        // Ambil parameter dari request
        $status = $request->get('status', 'dismantle'); // Default status ke 'active'
        $search = $request->get('search');
        $month = $request->get('month');
        $year = $request->get('year');

        // Query untuk mendapatkan data survey
        $query = OnlineBilling::orderBy('created_at', 'desc');

        // Filter berdasarkan status
        if ($status != 'all') {
            $query->where('status', $status);
        }

        // Pencarian di semua kolom yang relevan (nomor work order dan nama pembuat)
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->orWhereHas('pelanggan', function ($q) use ($search) { // Pencarian di relasi pelanggan
                    $q->where('nama_pelanggan', 'like', '%' . $search . '%');
                })
                    ->orWhereHas('instansi', function ($q) use ($search) { // Pencarian di relasi instansi
                        $q->where('nama_instansi', 'like', '%' . $search . '%');
                    })
                    ->orWhere('nama_site', 'like', '%' . $search . '%') // Pencarian di kolom nama_site
                    ->orWhereHas('admin', function ($q) use ($search) { // Pencarian di kolom nama admin melalui relasi
                        $q->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        // Filter berdasarkan bulan dan tahun
        if (!empty($month) && !empty($year)) {
            $query->whereMonth('created_at', $month)
                ->whereYear('created_at', $year);
        } elseif (!empty($year)) {
            $query->whereYear('created_at', $year);
        }

        // Dapatkan data survey dengan pagination, dan tambahkan query ke pagination URL
        $onlinebilling = $query->paginate(10)->appends([
            'status' => $status,
            'search' => $search,
            'month' => $month,
            'year' => $year,
        ]);

        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('onlinebilling', 'status', 'search', 'month', 'year', 'notifications'));

        return $this->renderView('sitedismantle', $data);
    }

    public function showsitedismantle($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // Ambil data online billing berdasarkan ID
        $onlinebilling = OnlineBilling::findOrFail($id);

        // Ambil status yang terkait dengan online_billing_id tertentu
        $statuses = Status::where('online_billing_id', $id)->get(); // Menambahkan filter berdasarkan online_billing_id

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('statuses', 'onlinebilling', 'notifications'));

        return $this->renderView('sitedismantle_show', $data);
    }

    public function instalasi(Request $request)
    {
        // Ambil parameter dari request
        $status = $request->get('status', 'all');
        $search = $request->get('search');
        $month = $request->get('month');
        $year = $request->get('year');

        // Query untuk mendapatkan data survey
        // Query untuk mendapatkan data survey
        $query = WorkOrderInstall::where('jenis_pekerjaan', 'instalasi')
            ->orderBy('created_at', 'desc');
        // Filter berdasarkan status
        if ($status != 'all') {
            $query->where('status', $status);
        }
        // filter overdue
        if ($request->filter == 'overdue') {
            $query->whereDate('tanggal_rfs', '<', today())
                ->whereNotIn('status', [
                    'Completed',
                    'Rejected',
                    'Canceled'
                ]);
        }
        // Pencarian di semua kolom yang relevan (nomor work order dan nama pembuat)
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('no_spk', 'like', '%' . $search . '%') // Pencarian di kolom no_spk
                    ->orWhereHas('pelanggan', function ($q) use ($search) { // Pencarian di relasi pelanggan
                        $q->where('nama_pelanggan', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('instansi', function ($q) use ($search) { // Pencarian di relasi instansi
                        $q->where('nama_instansi', 'like', '%' . $search . '%');
                    })
                    ->orWhere('nama_site', 'like', '%' . $search . '%') // Pencarian di kolom nama_site
                    ->orWhere('no_jaringan', 'like', '%' . $search . '%')

                    ->orWhereHas('admin', function ($q) use ($search) { // Pencarian di kolom nama admin melalui relasi
                        $q->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        // Filter berdasarkan bulan dan tahun
        if (!empty($month) && !empty($year)) {
            $query->whereMonth('created_at', $month)
                ->whereYear('created_at', $year);
        } elseif (!empty($year)) {
            $query->whereYear('created_at', $year);
        }

        // Dapatkan data survey dengan pagination, dan tambahkan query ke pagination URL
        $getInstall = $query->paginate(10)->appends([
            'status' => $status,
            'search' => $search,
            'month' => $month,
            'year' => $year,
        ]);
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();


        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('getInstall', 'status', 'search', 'month', 'year', 'notifications'));

        // Render view berdasarkan role
        return $this->renderView('instalasi', $data);
    }
    public function showinstalasi($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // // Gabungkan data survey ke dalam data role
        // $progressList = SurveyProgress::where('work_order_survey_id', $id)->get();
        $progressList = InstallProgress::where('work_order_install_id', $id)->get();

        // Menampilkan detail work order
        $getInstall = WorkOrderInstall::with('admin')->findOrFail($id);

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('progressList', 'getInstall', 'notifications'));

        // Render view berdasarkan role
        return $this->renderView('instalasi_show', $data);
    }

    public function upgrade(Request $request)
    {
        // Ambil parameter dari request
        $status = $request->get('status', 'all');
        $search = $request->get('search');
        $month = $request->get('month');
        $year = $request->get('year');

        // Query untuk mendapatkan data survey dengan eager loading
        $query = WorkOrderUpgrade::with([
            'onlineBilling.pelanggan',
            'onlineBilling.vendor',
            'onlineBilling.instansi'
        ])->orderBy('created_at', 'desc');

        // Filter berdasarkan status
        if ($status != 'all') {
            $query->where('status', $status);
        }

        // Pencarian di semua kolom yang relevan
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('no_spk', 'like', '%' . $search . '%')
                    ->orWhereHas('onlineBilling', function ($q) use ($search) {
                        $q->where('nama_site', 'like', '%' . $search . '%')
                            ->orWhereHas('pelanggan', function ($q) use ($search) {
                                $q->where('nama_pelanggan', 'like', '%' . $search . '%');
                            })
                            ->orWhereHas('instansi', function ($q) use ($search) {
                                $q->where('nama_instansi', 'like', '%' . $search . '%');
                            });
                    });
            });
        }

        // Filter berdasarkan bulan dan tahun
        if (!empty($month) && !empty($year)) {
            $query->whereMonth('created_at', $month)
                ->whereYear('created_at', $year);
        } elseif (!empty($year)) {
            $query->whereYear('created_at', $year);
        }

        // Dapatkan data survey dengan pagination
        $getUpgrade = $query->paginate(10)->appends([
            'status' => $status,
            'search' => $search,
            'month' => $month,
            'year' => $year,
        ]);

        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('getUpgrade', 'status', 'search', 'month', 'year', 'notifications'));

        return $this->renderView('upgrade', $data);
    }
    public function upgradeShow($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();
        // Gabungkan data survey ke dalam data role
        $progressList = UpgradeProgress::where('work_order_upgrade_id', $id)->get();

        // Menampilkan detail work order dengan relasi ke onlineBilling dan admin
        $getUpgrade = WorkOrderUpgrade::with([
            'admin',
            'onlineBilling.pelanggan',
            'onlineBilling.vendor',
            'onlineBilling.instansi'
        ])->findOrFail($id);

        // Gabungkan data ke dalam array data role
        $data = array_merge($this->ambilDataRole(), compact('progressList', 'getUpgrade', 'notifications'));

        // Render view berdasarkan role
        return $this->renderView('upgrade_show', $data);
    }

    public function downgrade(Request $request)
    {
        // Ambil parameter dari request
        $status = $request->get('status', 'all');
        $search = $request->get('search');
        $month = $request->get('month');
        $year = $request->get('year');

        // Query untuk mendapatkan data survey dengan eager loading
        $query = WorkOrderDowngrade::with([
            'onlineBilling.pelanggan',
            'onlineBilling.vendor',
            'onlineBilling.instansi'
        ])->orderBy('created_at', 'desc');

        // Filter berdasarkan status
        if ($status != 'all') {
            $query->where('status', $status);
        }

        // Pencarian di semua kolom yang relevan
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('no_spk', 'like', '%' . $search . '%')
                    ->orWhereHas('onlineBilling', function ($q) use ($search) {
                        $q->where('nama_site', 'like', '%' . $search . '%')
                            ->orWhereHas('pelanggan', function ($q) use ($search) {
                                $q->where('nama_pelanggan', 'like', '%' . $search . '%');
                            })
                            ->orWhereHas('instansi', function ($q) use ($search) {
                                $q->where('nama_instansi', 'like', '%' . $search . '%');
                            });
                    });
            });
        }
        // Filter berdasarkan bulan dan tahun
        if (!empty($month) && !empty($year)) {
            $query->whereMonth('created_at', $month)
                ->whereYear('created_at', $year);
        } elseif (!empty($year)) {
            $query->whereYear('created_at', $year);
        }

        // Dapatkan data survey dengan pagination
        $getDowngrade = $query->paginate(10)->appends([
            'status' => $status,
            'search' => $search,
            'month' => $month,
            'year' => $year,
        ]);

        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('getDowngrade', 'status', 'search', 'month', 'year', 'notifications'));

        return $this->renderView('downgrade', $data);
    }
    public function downgradeShow($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();
        // Gabungkan data survey ke dalam data role
        $progressList = DowngradeProgress::where('work_order_downgrade_id', $id)->get();

        // Menampilkan detail work order dengan relasi ke onlineBilling dan admin
        $getDowngrade = WorkOrderDowngrade::with([
            'admin',
            'onlineBilling.pelanggan',
            'onlineBilling.vendor',
            'onlineBilling.instansi'
        ])->findOrFail($id);

        // Gabungkan data ke dalam array data role
        $data = array_merge($this->ambilDataRole(), compact('progressList', 'getDowngrade', 'notifications'));

        // Render view berdasarkan role
        return $this->renderView('downgrade_show', $data);
    }

    public function relokasi(Request $request)
    {
        // Ambil parameter dari request
        $status = $request->get('status', 'all');
        $search = $request->get('search');
        $month = $request->get('month');
        $year = $request->get('year');

        // Query untuk mendapatkan data survey dengan eager loading
        $query = WorkOrderRelokasi::with([
            'onlineBilling.pelanggan',
            'onlineBilling.vendor',
            'onlineBilling.instansi'
        ])->orderBy('created_at', 'desc');

        // Filter berdasarkan status
        if ($status != 'all') {
            $query->where('status', $status);
        }

        // Pencarian di semua kolom yang relevan
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('no_spk', 'like', '%' . $search . '%')
                    ->orWhereHas('onlineBilling', function ($q) use ($search) {
                        $q->where('nama_site', 'like', '%' . $search . '%')
                            ->orWhereHas('pelanggan', function ($q) use ($search) {
                                $q->where('nama_pelanggan', 'like', '%' . $search . '%');
                            })
                            ->orWhereHas('instansi', function ($q) use ($search) {
                                $q->where('nama_instansi', 'like', '%' . $search . '%');
                            });
                    });
            });
        }

        // Filter berdasarkan bulan dan tahun
        if (!empty($month) && !empty($year)) {
            $query->whereMonth('created_at', $month)
                ->whereYear('created_at', $year);
        } elseif (!empty($year)) {
            $query->whereYear('created_at', $year);
        }

        // Dapatkan data survey dengan pagination
        $getRelokasi = $query->paginate(10)->appends([
            'status' => $status,
            'search' => $search,
            'month' => $month,
            'year' => $year,
        ]);

        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('getRelokasi', 'status', 'search', 'month', 'year', 'notifications'));


        return $this->renderView('relokasi', $data);
    }
    public function showrelokasi($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // $progressList = SurveyProgress::where('work_order_survey_id', $id)->get();
        $progressList = RelokasiProgress::where('work_order_relokasi_id', $id)->get();

        // Menampilkan detail work order
        $getRelokasi = WorkOrderRelokasi::with('WorkOrderRelokasiDetail.stockBarang')->findOrFail($id);


        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('progressList', 'getRelokasi', 'notifications'));

        // Render view berdasarkan role
        return $this->renderView('relokasi_show', $data);
    }

    public function dismantle(Request $request)
    {
        // Ambil parameter dari request
        $status = $request->get('status', 'all');
        $search = $request->get('search');
        $month = $request->get('month');
        $year = $request->get('year');

        // Query untuk mendapatkan data survey dengan eager loading
        $query = WorkOrderDismantle::with([
            'onlineBilling.pelanggan',
            'onlineBilling.vendor',
            'onlineBilling.instansi'
        ])->orderBy('created_at', 'desc');

        // Filter berdasarkan status
        if ($status != 'all') {
            $query->where('status', $status);
        }

        // Pencarian di semua kolom yang relevan
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('no_spk', 'like', '%' . $search . '%')
                    ->orWhereHas('onlineBilling', function ($q) use ($search) {
                        $q->where('nama_site', 'like', '%' . $search . '%')
                            ->orWhereHas('pelanggan', function ($q) use ($search) {
                                $q->where('nama_pelanggan', 'like', '%' . $search . '%');
                            })
                            ->orWhereHas('instansi', function ($q) use ($search) {
                                $q->where('nama_instansi', 'like', '%' . $search . '%');
                            });
                    });
            });
        }

        // Filter berdasarkan bulan dan tahun
        if (!empty($month) && !empty($year)) {
            $query->whereMonth('created_at', $month)
                ->whereYear('created_at', $year);
        } elseif (!empty($year)) {
            $query->whereYear('created_at', $year);
        }

        // Dapatkan data survey dengan pagination
        $getDismantle = $query->paginate(10)->appends([
            'status' => $status,
            'search' => $search,
            'month' => $month,
            'year' => $year,
        ]);

        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('getDismantle', 'status', 'search', 'month', 'year', 'notifications'));

        return $this->renderView('dismantle', $data);
    }
    public function dismantleShow($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();
        // Gabungkan data survey ke dalam data role
        $progressList = DismantleProgress::where('work_order_dismantle_id', $id)->get();
        $dismantleItems = DismantleDetail::where('dismantle_id', $id)
            ->with(['jenis', 'merek', 'tipe'])
            ->get();
        // Menampilkan detail work order dengan relasi ke onlineBilling dan admin
        $getDismantle = WorkOrderDismantle::with([
            'admin',
            'onlineBilling.pelanggan',
            'onlineBilling.vendor',
            'onlineBilling.instansi'
        ])->findOrFail($id);

        // Gabungkan data ke dalam array data role
        $data = array_merge($this->ambilDataRole(), compact('dismantleItems', 'progressList', 'getDismantle', 'notifications'));

        // Render view berdasarkan role
        return $this->renderView('dismantle_show', $data);
    }
    public function exportOB()
    {
        return Excel::download(new OnlineBillingExport, ' Laporan OnlineBilling.xlsx');
    }
    public function jasa(Request $request)
    {
        // Ambil parameter dari request
        $status = $request->get('status', 'all');
        $search = $request->get('search');
        $month = $request->get('month');
        $year = $request->get('year');

        // Query untuk mendapatkan data survey
        $query = WorkOrderInstall::where('jenis_pekerjaan', 'jasa')
            ->orderBy('created_at', 'desc');
        // Filter berdasarkan status
        if ($status != 'all') {
            $query->where('status', $status);
        }
        // filter overdue
        if ($request->filter == 'overdue') {
            $query->whereDate('tanggal_rfs', '<', today())
                ->whereNotIn('status', [
                    'Completed',
                    'Rejected',
                    'Canceled'
                ]);
        }
        // Pencarian di semua kolom yang relevan (nomor work order dan nama pembuat)
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('no_spk', 'like', '%' . $search . '%') // Pencarian di kolom no_spk
                    ->orWhereHas('pelanggan', function ($q) use ($search) { // Pencarian di relasi pelanggan
                        $q->where('nama_pelanggan', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('instansi', function ($q) use ($search) { // Pencarian di relasi instansi
                        $q->where('nama_instansi', 'like', '%' . $search . '%');
                    })
                    ->orWhere('nama_site', 'like', '%' . $search . '%') // Pencarian di kolom nama_site
                    ->orWhereHas('admin', function ($q) use ($search) { // Pencarian di kolom nama admin melalui relasi
                        $q->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        // Filter berdasarkan bulan dan tahun
        if (!empty($month) && !empty($year)) {
            $query->whereMonth('created_at', $month)
                ->whereYear('created_at', $year);
        } elseif (!empty($year)) {
            $query->whereYear('created_at', $year);
        }

        // Dapatkan data survey dengan pagination, dan tambahkan query ke pagination URL
        $getInstall = $query->paginate(10)->appends([
            'status' => $status,
            'search' => $search,
            'month' => $month,
            'year' => $year,
        ]);
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();


        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('getInstall', 'status', 'search', 'month', 'year', 'notifications'));

        // Render view berdasarkan role
        return $this->renderView('jasa', $data);
    }
    public function showjasa($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // $progressList = SurveyProgress::where('work_order_survey_id', $id)->get();
        $progressList = InstallProgress::where('work_order_install_id', $id)->get();

        // Menampilkan detail work order
        $getInstall = WorkOrderInstall::with('WorkOrderInstallDetail.stockBarang')->findOrFail($id);

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('progressList', 'getInstall', 'notifications'));

        // Render view berdasarkan role
        return $this->renderView('jasa_show', $data);
    }

    public function poc(Request $request)
    {
        // Ambil parameter dari request
        $status = $request->get('status', 'all');
        $search = $request->get('search');
        $month = $request->get('month');
        $year = $request->get('year');

        // Query untuk mendapatkan data survey
        $query = WorkOrderInstall::where('jenis_pekerjaan', 'poc')
            ->orderBy('created_at', 'desc');
        // Filter berdasarkan status
        if ($status != 'all') {
            $query->where('status', $status);
        }
        // filter overdue
        if ($request->filter == 'overdue') {
            $query->whereDate('tanggal_rfs', '<', today())
                ->whereNotIn('status', [
                    'Completed',
                    'Rejected',
                    'Canceled'
                ]);
        }
        // Pencarian di semua kolom yang relevan (nomor work order dan nama pembuat)
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('no_spk', 'like', '%' . $search . '%') // Pencarian di kolom no_spk
                    ->orWhereHas('pelanggan', function ($q) use ($search) { // Pencarian di relasi pelanggan
                        $q->where('nama_pelanggan', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('instansi', function ($q) use ($search) { // Pencarian di relasi instansi
                        $q->where('nama_instansi', 'like', '%' . $search . '%');
                    })
                    ->orWhere('nama_site', 'like', '%' . $search . '%') // Pencarian di kolom nama_site
                    ->orWhereHas('admin', function ($q) use ($search) { // Pencarian di kolom nama admin melalui relasi
                        $q->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        // Filter berdasarkan bulan dan tahun
        if (!empty($month) && !empty($year)) {
            $query->whereMonth('created_at', $month)
                ->whereYear('created_at', $year);
        } elseif (!empty($year)) {
            $query->whereYear('created_at', $year);
        }

        // Dapatkan data survey dengan pagination, dan tambahkan query ke pagination URL
        $getInstall = $query->paginate(10)->appends([
            'status' => $status,
            'search' => $search,
            'month' => $month,
            'year' => $year,
        ]);
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();


        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('getInstall', 'status', 'search', 'month', 'year', 'notifications'));

        // Render view berdasarkan role
        return $this->renderView('poc', $data);
    }

    public function showpoc($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // $progressList = SurveyProgress::where('work_order_survey_id', $id)->get();
        $progressList = InstallProgress::where('work_order_install_id', $id)->get();

        // Menampilkan detail work order
        $getInstall = WorkOrderInstall::with('WorkOrderInstallDetail.stockBarang')->findOrFail($id);

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('progressList', 'getInstall', 'notifications'));

        // Render view berdasarkan role
        return $this->renderView('poc_show', $data);
    }

    public function requestDestroy($id)
    {
        // Menghapus work order
        $requestBarang = RequestBarang::findOrFail($id);
        if ($requestBarang->status !== 'completed') {
            $requestBarang->delete();
            LogActivity::add('Request Barang', $requestBarang->nama_penerima, 'delete');

            return redirect()->back()->with('success', 'Request Barang berhasil diHapus.');
        }

        return redirect()->back()->with('error', 'Request Barang sudah selesai dan tidak bisa dihapus.');
    }
    public function configureBarang($id)
    {
        // Cari data barang keluar berdasarkan ID
        $barangKeluar = BarangKeluar::findOrFail($id);

        // Ubah status konfigurasi
        $barangKeluar->update(['is_configured' => true]);

        // Redirect dengan pesan sukses
        return redirect()->back()->with('success', 'Barang berhasil dikonfigurasi.');
    }
    public function addProgressInstalasi($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        $getInstall = WorkOrderInstall::findOrFail($id);
        $data = array_merge($this->ambilDataRole(), compact('getInstall', 'notifications'));

        return $this->renderView('wo_install_add', $data);
    }


    public function storeProgressInstalasi(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'keterangan' => 'required',
            'foto.*' => 'nullable|image|max:10240',
        ]);

        // Menyimpan progress baru
        $progress = new InstallProgress();
        $progress->work_order_install_id = $id;
        $progress->keterangan = $request->keterangan;



        // Set status default atau complete sesuai tombol yang ditekan
        if ($request->has('action') && $request->action === 'complete') {
            $progress->status = 'Completed'; // Ubah status progress jadi Completed

        } else {
            $progress->status = 'On Progress'; // Default status progress jika belum complete

        }

        // Menyimpan ID user PSB yang sedang login
        $progress->user_id = Auth::id();
        $progress->save();

        // Upload dan simpan banyak foto jika ada
        if ($request->hasFile('foto')) {
            foreach ($request->file('foto') as $foto) {
                $fileName = time() . '_' . $foto->getClientOriginalName();
                $foto->move(public_path('uploads'), $fileName);

                // Simpan foto ke tabel survey_progress_photos
                InstallProgressPhoto::create([
                    'install_progress_id' => $progress->id,
                    'file_path' => $fileName
                ]);
            }
        }

        // Redirect ke view upgrade atau detail upgrade berdasarkan aksi
        if ($request->action === 'complete') {
            return redirect()->route('hd.instalasi.show', $id)->with('success', 'Progress berhasil diselesaikan.');
        }

        return redirect()->route('hd.instalasi.show', $id)->with('success', 'Progress berhasil ditambahkan.');
    }
    public function approvemaintenance($id)
    {
        $getMaintenance = WorkOrderMaintenance::findOrFail($id);

        // Ubah status menjadi 'approved'
        $getMaintenance->status = 'On Progress';
        $getMaintenance->save();

        // Cari entri terkait di tabel statuses
        $status = Status::where('work_orderable_id', $getMaintenance->id)
            ->where('process', 'Maintenance') // Pastikan prosesnya adalah 'Upgrade'
            ->first();

        // Perbarui status di tabel statuses jika entri ditemukan
        if ($status) {
            $status->status = 'On Progress'; // Sesuaikan dengan status baru
            $status->save();
        }
        // Redirect ke halaman sebelumnya dengan pesan sukses
        return redirect()->route('hd.maintenance_show', $id)->with('success', 'Maintenance telah disetujui.');
    }
    public function rejectmaintenance($id)
    {
        $getMaintenance = WorkOrderMaintenance::findOrFail($id);

        // Ubah status menjadi 'rejected'
        $getMaintenance->status = 'Rejected';
        $getMaintenance->save();

        // Cari entri terkait di tabel statuses
        $status = Status::where('work_orderable_id', $getMaintenance->id)
            ->where('process', 'Maintenance') // Pastikan prosesnya adalah 'Upgrade'
            ->first();

        // Perbarui status di tabel statuses jika entri ditemukan
        if ($status) {
            $status->status = 'Rejected'; // Sesuaikan dengan status baru
            $status->save();
        }
        // Redirect ke halaman sebelumnya dengan pesan sukses
        return redirect()->route('hd.maintenance_show', $id)->with('error', 'Maintenance telah ditolak.');
    }
    public function addProgressMaintenance($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        $getMaintenance = WorkOrderMaintenance::findOrFail($id);
        $data = array_merge($this->ambilDataRole(), compact('getMaintenance', 'notifications'));

        return $this->renderView('wo_maintenance_add', $data);
    }


    public function storeProgressMaintenance(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'keterangan' => 'required',
            'foto.*' => 'nullable|image|max:10240',
        ]);

        // Menyimpan progress baru
        $progress = new MaintenanceProgress();
        $progress->work_order_maintenance_id = $id;
        $progress->keterangan = $request->keterangan;

        // Ambil data survey
        $getMaintenance = WorkOrderMaintenance::findOrFail($id);

        // Set status default atau complete sesuai tombol yang ditekan
        if ($request->has('action') && $request->action === 'complete') {
            $progress->status = 'Completed'; // Ubah status progress jadi Completed

            // Ubah status survey menjadi Completed
            $getMaintenance->status = 'Completed';
            $getMaintenance->save();
            // Perbarui status di tabel statuses
            $status = Status::where('work_orderable_id', $getMaintenance->id)
                ->where('process', 'Maintenance')
                ->first();
            if ($status) {
                $status->status = 'Completed';
                $status->save();
            }
        } else {
            $progress->status = 'On Progress'; // Default status progress jika belum complete
            // // Update status di tabel WorkOrderInstall
            // if ($getMaintenance->status !== 'Completed') { // Hanya jika status belum Completed
            //     $getMaintenance->status = 'On Progress';
            //     $getMaintenance->save();
            // }
        }

        // Menyimpan ID user PSB yang sedang login
        $progress->psb_id = Auth::id();
        $progress->save();

        // Upload dan simpan banyak foto jika ada
        if ($request->hasFile('foto')) {
            foreach ($request->file('foto') as $foto) {
                $fileName = time() . '_' . $foto->getClientOriginalName();
                $foto->move(public_path('uploads'), $fileName);

                // Simpan foto ke tabel survey_progress_photos
                MaintenanceProgressPhoto::create([
                    'maintenance_progress_id' => $progress->id,
                    'file_path' => $fileName
                ]);
            }
        }

        // Redirect ke view survey atau detail survey berdasarkan aksi
        if ($request->action === 'complete') {
            return redirect()->route('hd.maintenance_show', $id)->with('success', 'Progress berhasil diselesaikan.');
        }

        return redirect()->route('hd.maintenance_show', $id)->with('success', 'Progress berhasil ditambahkan.');
    }

    public function approveupgrade($id)
    {
        // Cari work order upgrade berdasarkan ID
        $getUpgrade = WorkOrderUpgrade::findOrFail($id);

        // Ubah status di tabel work_order_upgrades
        $getUpgrade->status = 'On Progress';
        $getUpgrade->save();

        // Cari entri terkait di tabel statuses
        $status = Status::where('work_orderable_id', $getUpgrade->id)
            ->where('process', 'Upgrade') // Pastikan prosesnya adalah 'upgrade'
            ->first();

        // Perbarui status jika entri ditemukan
        if ($status) {
            $status->status = 'On Progress'; // Sesuaikan dengan status baru
            $status->save();
        }

        // Redirect ke halaman sebelumnya dengan pesan sukses
        return redirect()->route('hd.upgrade_show', $id)
            ->with('success', 'Upgrade telah disetujui.');
    }


    public function addProgressUpgrade($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        $getUpgrade = WorkOrderUpgrade::findOrFail($id);
        $data = array_merge($this->ambilDataRole(), compact('getUpgrade', 'notifications'));

        return $this->renderView('wo_upgrade_add', $data);
    }
    public function storeProgressUpgrade(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'keterangan' => 'required',
            'foto.*' => 'nullable|image|max:10240',
        ]);

        // Menyimpan progress baru
        $progress = new UpgradeProgress();
        $progress->work_order_upgrade_id = $id;
        $progress->keterangan = $request->keterangan;

        // Ambil data upgrade
        $getUpgrade = WorkOrderUpgrade::findOrFail($id);

        // Set status default atau complete sesuai tombol yang ditekan
        if ($request->has('action') && $request->action === 'complete') {
            $progress->status = 'Completed'; // Ubah status progress jadi Completed

            // Ubah status upgrade menjadi Completed
            $getUpgrade->status = 'Completed';
            $getUpgrade->save();

            // Perbarui status di tabel statuses
            $status = Status::where('work_orderable_id', $getUpgrade->id)
                ->where('process', 'Upgrade')
                ->first();
            if ($status) {
                $status->status = 'Completed';
                $status->save();
            }

            // Update bandwidth lama dengan bandwidth baru di tabel online_billings
            $onlineBilling = $getUpgrade->onlineBilling; // Ambil data online billing terkait
            $onlineBilling->bandwidth = $getUpgrade->bandwidth_baru; // Set bandwidth baru
            $onlineBilling->satuan = $getUpgrade->satuan; // Update satuan jika perlu
            $onlineBilling->save(); // Simpan perubahan
            $adminUsers = User::where('is_role', 1)->get(); // 1 adalah role untuk admin

            // Buat notifikasi "Survey Completed" untuk setiap admin
            foreach ($adminUsers as $admin) {
                // Cek role pengguna
                if ($admin->is_role == 1) { // Role PSB
                    $url = route('admin.upgrade_show', ['id' => $getUpgrade->id]) . '#upgrade'; // Tambahkan #no_spk untuk PSB
                } else if ($admin->is_role == 3) { // Role Admin
                    $url = route('hd.upgrade_show', ['id' => $getUpgrade->id]) . '#upgrade'; // Tambahkan #no_spk untuk Admin
                }

                // Buat notifikasi
                Notification::create([
                    'user_id' => $admin->id,
                    'message' => 'WO Upgrade telah diselesaikan dengan No Order: ' . $getUpgrade->no_spk,
                    'url' => $url, // URL dengan hash #no_spk
                ]);
            }
        } else {
            $progress->status = 'On Progress'; // Default status progress jika belum complete

            // Update status di tabel WorkOrderUpgrade jika belum Completed
            if ($getUpgrade->status !== 'Completed') {
                $getUpgrade->status = 'On Progress';
                $getUpgrade->save();
            }

            // Perbarui status di tabel statuses
            $status = Status::where('work_orderable_id', $getUpgrade->id)
                ->where('process', 'Upgrade')
                ->first();
            if ($status) {
                $status->status = 'On Progress';
                $status->save();
            }
        }

        // Menyimpan ID user PSB yang sedang login
        $progress->psb_id = Auth::id();
        $progress->save();

        // Upload dan simpan banyak foto jika ada
        if ($request->hasFile('foto')) {
            foreach ($request->file('foto') as $foto) {
                $fileName = time() . '_' . $foto->getClientOriginalName();
                $foto->move(public_path('uploads'), $fileName);

                // Simpan foto ke tabel survey_progress_photos
                UpgradeProgressPhoto::create([
                    'upgrade_progress_id' => $progress->id,
                    'file_path' => $fileName
                ]);
            }
        }

        // Redirect ke view upgrade atau detail upgrade berdasarkan aksi
        if ($request->action === 'complete') {
            return redirect()->route('hd.upgrade_show', $id)->with('success', 'Upgrade berhasil diselesaikan.');
        }

        return redirect()->route('hd.upgrade_show', $id)->with('success', 'Progress berhasil ditambahkan.');
    }

    public function approvedowngrade($id)
    {
        // Cari work order upgrade berdasarkan ID
        $getDowngrade = WorkOrderDowngrade::findOrFail($id);

        // Ubah status di tabel work_order_upgrades
        $getDowngrade->status = 'On Progress';
        $getDowngrade->save();

        // Cari entri terkait di tabel statuses
        $status = Status::where('work_orderable_id', $getDowngrade->id)
            ->where('process', 'Downgrade') // Pastikan prosesnya adalah 'upgrade'
            ->first();

        // Perbarui status jika entri ditemukan
        if ($status) {
            $status->status = 'On Progress'; // Sesuaikan dengan status baru
            $status->save();
        }

        // Redirect ke halaman sebelumnya dengan pesan sukses
        return redirect()->route('hd.downgrade_show', $id)
            ->with('success', 'Downgrade telah disetujui.');
    }


    public function addProgressDowngrade($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        $getDowngrade = WorkOrderDowngrade::findOrFail($id);
        $data = array_merge($this->ambilDataRole(), compact('getDowngrade', 'notifications'));

        return $this->renderView('wo_downgrade_add', $data);
    }
    public function storeProgressDowngrade(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'keterangan' => 'required',
            'foto.*' => 'nullable|image|max:10240',
        ]);

        // Menyimpan progress baru
        $progress = new DowngradeProgress();
        $progress->work_order_downgrade_id = $id;
        $progress->keterangan = $request->keterangan;

        // Ambil data upgrade
        $getDowngrade = WorkOrderDowngrade::findOrFail($id);

        // Set status default atau complete sesuai tombol yang ditekan
        if ($request->has('action') && $request->action === 'complete') {
            $progress->status = 'Completed'; // Ubah status progress jadi Completed

            // Ubah status upgrade menjadi Completed
            $getDowngrade->status = 'Completed';
            $getDowngrade->save();

            // Perbarui status di tabel statuses
            $status = Status::where('work_orderable_id', $getDowngrade->id)
                ->where('process', 'Downgrade')
                ->first();
            if ($status) {
                $status->status = 'Completed';
                $status->save();
            }

            // Update bandwidth lama dengan bandwidth baru di tabel online_billings
            $onlineBilling = $getDowngrade->onlineBilling; // Ambil data online billing terkait
            $onlineBilling->bandwidth = $getDowngrade->bandwidth_baru; // Set bandwidth baru
            $onlineBilling->satuan = $getDowngrade->satuan; // Update satuan jika perlu
            $onlineBilling->save(); // Simpan perubahan
            // Dapatkan semua admin (atau role yang sesuai)
            // Dapatkan semua admin (atau role yang sesuai)
            $adminUsers = User::where('is_role', 1)->get(); // 1 adalah role untuk admin

            // Buat notifikasi "Survey Completed" untuk setiap admin
            foreach ($adminUsers as $admin) {
                // Cek role pengguna
                if ($admin->is_role == 1) { // Role PSB
                    $url = route('admin.downgrade_show', ['id' => $getDowngrade->id]) . '#downgrade'; // Tambahkan #no_spk untuk PSB
                } else if ($admin->is_role == 3) { // Role Admin
                    $url = route('hd.downgrade_show', ['id' => $getDowngrade->id]) . '#downgrade'; // Tambahkan #no_spk untuk Admin
                }

                // Buat notifikasi
                Notification::create([
                    'user_id' => $admin->id,
                    'message' => 'WO Downgrade telah diselesaikan dengan No Order: ' . $getDowngrade->no_spk,
                    'url' => $url, // URL dengan hash #no_spk
                ]);
            }
        } else {
            $progress->status = 'On Progress'; // Default status progress jika belum complete

            // Update status di tabel WorkOrderUpgrade jika belum Completed
            if ($getDowngrade->status !== 'Completed') {
                $getDowngrade->status = 'On Progress';
                $getDowngrade->save();
            }

            // Perbarui status di tabel statuses
            $status = Status::where('work_orderable_id', $getDowngrade->id)
                ->where('process', 'Downgrade')
                ->first();
            if ($status) {
                $status->status = 'On Progress';
                $status->save();
            }
        }

        // Menyimpan ID user PSB yang sedang login
        $progress->psb_id = Auth::id();
        $progress->save();

        // Upload dan simpan banyak foto jika ada
        if ($request->hasFile('foto')) {
            foreach ($request->file('foto') as $foto) {
                $fileName = time() . '_' . $foto->getClientOriginalName();
                $foto->move(public_path('uploads'), $fileName);

                // Simpan foto ke tabel survey_progress_photos
                DowngradeProgressPhoto::create([
                    'downgrade_progress_id' => $progress->id,
                    'file_path' => $fileName
                ]);
            }
        }

        // Redirect ke view upgrade atau detail upgrade berdasarkan aksi
        if ($request->action === 'complete') {
            return redirect()->route('hd.downgrade_show', $id)->with('success', 'Downgrade berhasil diselesaikan.');
        }

        return redirect()->route('hd.downgrade_show', $id)->with('success', 'Progress berhasil ditambahkan.');
    }

    public function inputsidbaru($id)
    {
        $getGantivendor = WorkOrderGantiVendor::findOrFail($id);

        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();


        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('notifications', 'getGantivendor'));

        // Menampilkan form untuk membuat work order baru dengan nomor SPK baru
        return $this->renderView('inputsidbaru', $data);
    }

    public function storeinputsidbaru(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'sid_baru' => 'nullable|string', // Vendor harus valid dan ada di tabel vendors
        ]);

        // Temukan Work Order berdasarkan ID
        $getGantivendor = WorkOrderGantiVendor::findOrFail($id);


        // Update kolom vendor_baru dengan nama vendor yang dipilih
        $getGantivendor->sid_baru = $request->sid_baru;
        $getGantivendor->save();

        // Redirect dengan pesan sukses
        return redirect()->route('hd.gantivendor.show', $id)->with('success', 'SID Baru berhasil disimpan.');
    }
    public function addProgressGantivendor($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        $getGantivendor = WorkOrderGantiVendor::findOrFail($id);
        $data = array_merge($this->ambilDataRole(), compact('getGantivendor', 'notifications'));

        return $this->renderView('wo_gantivendor_add', $data);
    }


    public function storeProgressGantivendor(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'keterangan' => 'required',
            'foto.*' => 'nullable|image|max:10240',
        ]);

        // Menyimpan progress baru
        $progress = new GantiVendorProgress();
        $progress->work_order_ganti_vendor_id = $id;
        $progress->keterangan = $request->keterangan;

        // Ambil data survey
        $getGantivendor = WorkOrderGantiVendor::findOrFail($id);

        // Set status default atau complete sesuai tombol yang ditekan
        if ($request->has('action') && $request->action === 'complete') {
            // ✅ Cek apakah sid_baru sudah diisi
            if (empty($getGantivendor->sid_baru)) {
                return redirect()
                    ->route('hd.gantivendor.show', $id) // arahkan tetap ke halaman detail WO
                    ->withErrors(['sid_baru' => 'SID Baru wajib diisi sebelum menyelesaikan WO Ganti Vendor.'])
                    ->withInput();
            }

            $progress->status = 'Completed'; // Ubah status progress jadi Completed

            // Ubah status survey menjadi Completed
            $getGantivendor->status = 'Completed';
            $getGantivendor->save();
            // Perbarui status di tabel statuses
            $status = Status::where('work_orderable_id', $getGantivendor->id)
                ->where('process', 'Ganti Vendor')
                ->first();
            if ($status) {
                $status->status = 'Completed';
                $status->save();
            }
            // **Update vendor_id di OnlineBilling**
            $onlineBilling = $getGantivendor->onlineBilling; // Relasi ke onlineBilling
            if ($onlineBilling) {
                $onlineBilling->vendor_id = $getGantivendor->vendor_id; // Ganti vendor_id dengan vendor_id baru
                $onlineBilling->sid_vendor = $getGantivendor->sid_baru;
                $onlineBilling->save();
            }
            // Dapatkan semua pengguna dengan role General Affair (misalnya role 2)
            $helpdeskUsers = User::where('is_role', 3)->get();
            // Dapatkan semua pengguna dengan role PSB (misalnya role 5)
            $adminUsers = User::where('is_role', 1)->get();
            // Buat notifikasi untuk setiap pengguna General Affair
            foreach ($adminUsers as $adminUser) {
                $url = route('admin.gantivendor.show', ['id' => $getGantivendor->id]) . '#gantivendor';

                Notification::create([
                    'user_id' => $adminUser->id,
                    'message' => 'WO Ganti Vendor baru telah diselesaikan dengan No Order: ' . $getGantivendor->no_spk,
                    'url' => $url, // URL dengan hash #request
                ]);
            }
            // Buat notifikasi untuk setiap pengguna PSB
            foreach ($helpdeskUsers as $helpdeskUser) {
                $url = route('hd.gantivendor.show', ['id' => $getGantivendor->id]) . '#gantivendor';

                Notification::create([
                    'user_id' => $helpdeskUser->id,
                    'message' => 'WO Ganti Vendor baru telah diselesaikan dengan No Order: ' . $getGantivendor->no_spk,
                    'url' => $url, // URL dengan hash #instalasi
                ]);
            }
        } else {
            $progress->status = 'On Progress'; // Default status progress jika belum complete
            // Update status di tabel WorkOrderInstall
            if ($getGantivendor->status !== 'Completed') { // Hanya jika status belum Completed
                $getGantivendor->status = 'On Progress';
                $getGantivendor->save();
            }
        }

        // Menyimpan ID user PSB yang sedang login
        $progress->psb_id = Auth::id();
        $progress->save();

        // Upload dan simpan banyak foto jika ada
        if ($request->hasFile('foto')) {
            foreach ($request->file('foto') as $foto) {
                $fileName = time() . '_' . $foto->getClientOriginalName();
                $foto->move(public_path('uploads'), $fileName);

                // Simpan foto ke tabel survey_progress_photos
                GantiVendorProgressPhoto::create([
                    'ganti_vendor_progress_id' => $progress->id,
                    'file_path' => $fileName
                ]);
            }
        }

        // Redirect ke view survey atau detail survey berdasarkan aksi
        if ($request->action === 'complete') {
            return redirect()->route('hd.gantivendor.show', $id)->with('success', 'Progress berhasil diselesaikan.');
        }

        return redirect()->route('hd.gantivendor.show', $id)->with('success', 'Progress berhasil ditambahkan.');
    }

    public function storeDisable(Request $request, $id)
    {
        // Validasi Work Order Instalasi
        $workOrderDismantle = WorkOrderDismantle::findOrFail($id);

        // Simpan progres baru dengan status "Shipped"
        $progress = new DismantleProgress();
        $progress->work_order_dismantle_id = $workOrderDismantle->id;
        $progress->keterangan = 'Done Disable Ethernet ke arah Client.';
        $progress->status = 'On Progress';
        $progress->psb_id = Auth::id(); // ID user yang menekan tombol
        $progress->save();

        // Update status Work Order Instalasi menjadi Shipped
        $workOrderDismantle->status = 'On Progress';
        $workOrderDismantle->save();

        // Redirect kembali dengan pesan sukses
        return redirect()->route('hd.dismantle_show', $id)->with('success', 'Eth ke arah Client berhasil di Disable');
    }

    public function addProgressRelokasi($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        $getRelokasi = WorkOrderRelokasi::findOrFail($id);
        $data = array_merge($this->ambilDataRole(), compact('getRelokasi', 'notifications'));

        return $this->renderView('wo_relokasi_add', $data);
    }
    public function storeProgressRelokasi(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'keterangan' => 'required',
            'foto.*' => 'nullable|image|max:10240',
        ]);

        // Menyimpan progress baru
        $progress = new RelokasiProgress();
        $progress->work_order_relokasi_id = $id;
        $progress->keterangan = $request->keterangan;



        // Set status default atau complete sesuai tombol yang ditekan
        if ($request->has('action') && $request->action === 'complete') {
            $progress->status = 'Completed'; // Ubah status progress jadi Completed

        } else {
            $progress->status = 'On Progress'; // Default status progress jika belum complete

        }

        // Menyimpan ID user PSB yang sedang login
        $progress->psb_id = Auth::id();
        $progress->save();

        // Upload dan simpan banyak foto jika ada
        if ($request->hasFile('foto')) {
            foreach ($request->file('foto') as $foto) {
                $fileName = time() . '_' . $foto->getClientOriginalName();
                $foto->move(public_path('uploads'), $fileName);

                // Simpan foto ke tabel survey_progress_photos
                RelokasiProgressPhoto::create([
                    'relokasi_progress_id' => $progress->id,
                    'file_path' => $fileName
                ]);
            }
        }

        // Redirect ke view upgrade atau detail upgrade berdasarkan aksi
        if ($request->action === 'complete') {
            return redirect()->route('hd.relokasi.show', $id)->with('success', 'Progress berhasil diselesaikan.');
        }

        return redirect()->route('hd.relokasi.show', $id)->with('success', 'Progress berhasil ditambahkan.');
    }

    public function approvejasa($id)
    {
        // Cari work order upgrade berdasarkan ID
        $getInstall = WorkOrderInstall::findOrFail($id);

        // Ubah status di tabel work_order_upgrades
        $getInstall->status = 'On Progress';
        $getInstall->save();




        // Redirect ke halaman sebelumnya dengan pesan sukses
        return redirect()->route('hd.jasa_show', $id)
            ->with('success', 'Jasa telah disetujui.');
    }


    public function addProgressjasa($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        $getInstall = WorkOrderInstall::findOrFail($id);
        $data = array_merge($this->ambilDataRole(), compact('getInstall', 'notifications'));

        return $this->renderView('wo_jasa_add', $data);
    }
    public function storeProgressjasa(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'keterangan' => 'required',
            'foto.*' => 'nullable|image|max:10240',
        ]);

        // Menyimpan progress baru
        $progress = new InstallProgress();
        $progress->work_order_install_id = $id;
        $progress->keterangan = $request->keterangan;

        // Ambil data upgrade
        $getInstall = WorkOrderInstall::findOrFail($id);

        // Set status default atau complete sesuai tombol yang ditekan
        if ($request->has('action') && $request->action === 'complete') {
            $progress->status = 'Completed'; // Ubah status progress jadi Completed

            // Ubah status upgrade menjadi Completed
            $getInstall->status = 'Completed';
            $getInstall->save();



            $adminUsers = User::where('is_role', 1)->get(); // 1 adalah role untuk admin

            // Buat notifikasi "Survey Completed" untuk setiap admin
            foreach ($adminUsers as $admin) {
                // Cek role pengguna
                if ($admin->is_role == 1) { // Role PSB
                    $url = route('admin.wo_jasa_show', ['id' => $getInstall->id]) . '#Jasa'; // Tambahkan #no_spk untuk PSB
                } else if ($admin->is_role == 3) { // Role Admin
                    $url = route('hd.jasa_show', ['id' => $getInstall->id]) . '#Jasa'; // Tambahkan #no_spk untuk Admin
                }

                // Buat notifikasi
                Notification::create([
                    'user_id' => $admin->id,
                    'message' => 'WO Jasa telah diselesaikan dengan No Order: ' . $getInstall->no_spk,
                    'url' => $url, // URL dengan hash #no_spk
                ]);
            }
        } else {
            $progress->status = 'On Progress'; // Default status progress jika belum complete


        }

        // Menyimpan ID user PSB yang sedang login
        $progress->user_id = Auth::id();
        $progress->save();

        // Upload dan simpan banyak foto jika ada
        if ($request->hasFile('foto')) {
            foreach ($request->file('foto') as $foto) {
                $fileName = time() . '_' . $foto->getClientOriginalName();
                $foto->move(public_path('uploads'), $fileName);

                // Simpan foto ke tabel survey_progress_photos
                InstallProgressPhoto::create([
                    'install_progress_id' => $progress->id,
                    'file_path' => $fileName
                ]);
            }
        }

        // Redirect ke view upgrade atau detail upgrade berdasarkan aksi
        if ($request->action === 'complete') {
            return redirect()->route('hd.jasa_show', $id)->with('success', 'Jasa berhasil diselesaikan.');
        }

        return redirect()->route('hd.jasa_show', $id)->with('success', 'Progress berhasil ditambahkan.');
    }

    public function approvepoc($id)
    {
        // Cari work order upgrade berdasarkan ID
        $getInstall = WorkOrderInstall::findOrFail($id);

        // Ubah status di tabel work_order_upgrades
        $getInstall->status = 'On Progress';
        $getInstall->save();




        // Redirect ke halaman sebelumnya dengan pesan sukses
        return redirect()->route('hd.poc_show', $id)
            ->with('success', 'POC telah disetujui.');
    }


    public function addProgresspoc($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        $getInstall = WorkOrderInstall::findOrFail($id);
        $data = array_merge($this->ambilDataRole(), compact('getInstall', 'notifications'));

        return $this->renderView('wo_poc_add', $data);
    }
    public function storeProgresspoc(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'keterangan' => 'required',
            'foto.*' => 'nullable|image|max:10240',
        ]);

        // Menyimpan progress baru
        $progress = new InstallProgress();
        $progress->work_order_install_id = $id;
        $progress->keterangan = $request->keterangan;

        // Ambil data upgrade
        $getInstall = WorkOrderInstall::findOrFail($id);

        // Set status default atau complete sesuai tombol yang ditekan
        if ($request->has('action') && $request->action === 'complete') {
            $progress->status = 'Completed'; // Ubah status progress jadi Completed

            // Ubah status upgrade menjadi Completed
            $getInstall->status = 'Completed';
            $getInstall->save();



            $adminUsers = User::where('is_role', 1)->get(); // 1 adalah role untuk admin

            // Buat notifikasi "Survey Completed" untuk setiap admin
            foreach ($adminUsers as $admin) {
                // Cek role pengguna
                if ($admin->is_role == 1) { // Role PSB
                    $url = route('admin.wo_poc_show', ['id' => $getInstall->id]) . '#POC'; // Tambahkan #no_spk untuk PSB
                } else if ($admin->is_role == 3) { // Role Admin
                    $url = route('hd.poc_show', ['id' => $getInstall->id]) . '#POC'; // Tambahkan #no_spk untuk Admin
                }

                // Buat notifikasi
                Notification::create([
                    'user_id' => $admin->id,
                    'message' => 'WO POC telah diselesaikan dengan No Order: ' . $getInstall->no_spk,
                    'url' => $url, // URL dengan hash #no_spk
                ]);
            }
        } else {
            $progress->status = 'On Progress'; // Default status progress jika belum complete


        }

        // Menyimpan ID user PSB yang sedang login
        $progress->user_id = Auth::id();
        $progress->save();

        // Upload dan simpan banyak foto jika ada
        if ($request->hasFile('foto')) {
            foreach ($request->file('foto') as $foto) {
                $fileName = time() . '_' . $foto->getClientOriginalName();
                $foto->move(public_path('uploads'), $fileName);

                // Simpan foto ke tabel survey_progress_photos
                InstallProgressPhoto::create([
                    'install_progress_id' => $progress->id,
                    'file_path' => $fileName
                ]);
            }
        }

        // Redirect ke view upgrade atau detail upgrade berdasarkan aksi
        if ($request->action === 'complete') {
            return redirect()->route('hd.poc_show', $id)->with('success', 'POC berhasil diselesaikan.');
        }

        return redirect()->route('hd.poc_show', $id)->with('success', 'Progress berhasil ditambahkan.');
    }
}

//ambilDataRole(): Fungsi ini digunakan untuk mendapatkan informasi role pengguna dan folder view yang sesuai.
//renderView(): Fungsi ini menggabungkan nama folder view yang sesuai berdasarkan role dan nama view yang diinginkan.
//Controller Function: Setiap method seperti dashboard(), requestbarang(), dll, sekarang lebih ringkas dan hanya perlu memanggil renderView() dengan parameter nama view.