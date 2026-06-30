<?php

namespace App\Http\Controllers;

use App\Exports\WorkOrderDismantleExport;
use App\Exports\WorkOrderDowngradeExport;
use App\Exports\WorkOrderGantiVendorExport;
use App\Exports\WorkOrderInstallExport;
use App\Exports\WorkOrderRelokasiExport;
use App\Exports\WorkOrderSurveyExport;
use App\Exports\WorkOrderUpgradeExport;
use App\Helpers\LogActivity;
use App\Imports\OnlineBillingImport;
use App\Models\BeritaAcara;
use App\Models\DismantleDetail;
use App\Models\DismantleProgress;
use App\Models\DismantleProgressPhoto;
use App\Models\DowngradeProgress;
use App\Models\DowngradeProgressPhoto;
use App\Models\GantiVendorProgress;
use App\Models\GantiVendorProgressPhoto;
use App\Models\InstallProgress;
use App\Models\InstallProgressPhoto;
use App\Models\Instansi;
use App\Models\Jenis;
use App\Models\MaintenanceProgress;
use App\Models\Notification;
use App\Models\OnlineBilling;
use App\Models\Pelanggan;
use App\Models\Provinsi;
use App\Models\RelokasiProgress;
use App\Models\RelokasiProgressPhoto;
use App\Models\ReqBarangProgress;
use App\Models\RequestBarang;
use App\Models\RequestBarangDetails;
use App\Models\Status;
use App\Models\StockBarang;
use App\Models\SurveyProgress;
use App\Models\SurveyProgressPhoto;
use App\Models\UpgradeProgress;
use App\Models\UpgradeProgressPhoto;
use App\Models\User;
use App\Models\Vendor;
use App\Models\WorkOrderDismantle;
use App\Models\WorkOrderDowngrade;
use App\Models\WorkOrderGantiVendor;
use App\Models\WorkOrderInstall;
use App\Models\WorkOrderInstallDetail;
use App\Models\WorkOrderMaintenance;
use App\Models\WorkOrderRelokasi;
use App\Models\WorkOrderRelokasiDetail;
use App\Models\WorkOrderSurvey;
use App\Models\WorkOrderUpgrade;
use App\Models\WorkOrderUpgradeDetail;
use Carbon\Carbon;
use id;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Maatwebsite\Excel\Facades\Excel;
use setasign\Fpdi\Fpdi;

class AdminController extends Controller
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

        return [
            'getRecord' => User::find(Auth::user()->id),
            'roleText' => $roleText,

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
            'Jasa',
            'Upgrade',
            'Downgrade',
            'Relokasi',
            'Dismantle'
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
                $relations = in_array($type, ['Survey', 'Instalasi', 'POC', 'Jasa'])
                    ? ['pelanggan']
                    : ['onlineBilling.pelanggan'];

                $dataWO = (clone $query)
                    ->with($relations)
                    ->whereDate('tanggal_rfs', '<', Carbon::today())
                    ->whereNotIn('status', $closedStatuses)
                    ->get()
                    ->map(function ($item) use ($type) {

                        $item->jenis = $type;

                        if (in_array($type, ['Survey', 'Instalasi', 'POC', 'Jasa'])) {

                            $item->nama_pelanggan =
                                optional($item->pelanggan)->nama_pelanggan;

                            $item->nama_site =
                                $item->nama_site ?? '-';
                        } else {

                            $item->nama_site =
                                optional($item->onlineBilling)->nama_site;

                            $item->nama_pelanggan =
                                optional(
                                    optional($item->onlineBilling)->pelanggan
                                )->nama_pelanggan;
                        }
                        $item->hari_overdue =
                            Carbon::parse($item->tanggal_rfs)
                            ->diffInDays(Carbon::today());

                        switch ($type) {
                            case 'Survey':
                                $item->detail_url = route(
                                    'admin.wo_survey_show',
                                    $item->id
                                );
                                break;

                            case 'Instalasi':
                                $item->detail_url = route(
                                    'admin.wo_instalasi_show',
                                    $item->id
                                );
                                break;

                            case 'POC':
                                $item->detail_url = route(
                                    'admin.wo_poc_show',
                                    $item->id
                                );
                                break;

                            case 'Jasa':
                                $item->detail_url = route(
                                    'admin.wo_jasa_show',
                                    $item->id
                                );
                                break;
                            case 'Upgrade':
                                $item->detail_url = route(
                                    'admin.upgrade_show',
                                    $item->id
                                );
                                break;
                            case 'Downgrade':
                                $item->detail_url = route(
                                    'admin.downgrade_show',
                                    $item->id
                                );
                                break;
                            case 'Relokasi':
                                $item->detail_url = route(
                                    'admin.relokasi_show',
                                    $item->id
                                );
                                break;
                            case 'Dismantle':
                                $item->detail_url = route(
                                    'admin.dismantle_show',
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





    public function instalasi(Request $request)
    {
        // Ambil parameter dari request
        $status = $request->get('status', 'all');
        $search = $request->get('search');
        $month = $request->get('month');
        $year = $request->get('year');

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
                    ->orWhere('nama_site', 'like', '%' . $search . '%')
                    ->orWhere('no_jaringan', 'like', '%' . $search . '%')
                    // Pencarian di kolom nama_site
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

    public function createinstalasi()
    {
        // Ambil data master
        $pelanggans = Pelanggan::orderBy('nama_pelanggan', 'asc')->get();
        $instansis  = Instansi::orderBy('nama_instansi', 'asc')->get();
        $vendors    = Vendor::orderBy('nama_vendor', 'asc')->get();

        // Jenis barang
        $jenisList = Jenis::select('id', 'nama_jenis')->get();

        // Stok barang
        $stockBarangs = StockBarang::with(['merek', 'tipe', 'jenis'])
            ->selectRaw('tipe_id, merek_id, jenis_id, kualitas, SUM(jumlah) as total_jumlah')
            ->groupBy('tipe_id', 'merek_id', 'jenis_id', 'kualitas')
            ->get();

        // Notifikasi
        $notifications = Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->get();

        /*
    |--------------------------------------------------------------------------
    | Generate No SPK
    |--------------------------------------------------------------------------
    */
        $lastInstall = WorkOrderInstall::orderBy('id', 'desc')->first();

        if ($lastInstall && preg_match('/\/(\d+)$/', $lastInstall->no_spk, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        // Format nomor urut SPK (4 digit)
        $serial = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        // Generate No SPK
        $no_spk = 'PC24Telin/PSB-INSTALLASI/' . now()->format('Y-m-d') . '/' . $serial;

        /*
    |--------------------------------------------------------------------------
    | Preview No Jaringan
    |--------------------------------------------------------------------------
    | Nomor urut mengikuti No SPK
    */
        $no_jaringan = null;

        if ($pelanggans->count() > 0) {
            $firstPelanggan = $pelanggans->first();

            $kodePelanggan = 'C' . str_pad($firstPelanggan->id, 2, '0', STR_PAD_LEFT);
            $periode = now()->format('Y-m');

            // Pakai serial yang sama dengan SPK
            $no_jaringan = $kodePelanggan . '-' . $periode . $serial;
        }

        // Gabungkan data role
        $data = array_merge(
            $this->ambilDataRole(),
            compact(
                'stockBarangs',
                'jenisList',
                'notifications',
                'no_spk',
                'no_jaringan',
                'pelanggans',
                'instansis',
                'vendors'
            )
        );

        return $this->renderView('form_instalasi', $data);
    }

    public function storeinstalasi(Request $request)
    {
        // Validasi request
        $validatedData = $request->validate([
            'no_spk' => 'required|string|unique:work_order_installs',
            'pelanggan_id' => 'required|exists:pelanggans,id',
            'instansi_id' => 'required|exists:instansis,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'nama_site' => 'required|string',
            'alamat_pemasangan' => 'required|string',
            'nama_pic' => 'nullable|string',
            'no_pic' => 'nullable|string',
            'layanan' => 'required|string',
            'provinsi' => 'required|string',
            'media' => 'required|string',
            'bandwidth' => 'required|string',
            'satuan' => 'required|string',
            'nni' => 'nullable|string',
            'vlan' => 'nullable|string',
            'no_jaringan' => 'nullable|string',
            'tanggal_rfs' => 'required|date',
            'durasi' => 'required|integer|min:1',
            'nama_durasi' => 'required|string|in:hari,bulan,tahun',
            'harga_sewa_hidden' => 'required|integer',
            'harga_instalasi_hidden' => 'required|integer',
            'keterangan' => 'nullable|string',
            'non_stock' => 'nullable|string',
            'attachments.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:5120', // Tambahkan ini
            'jenis_pekerjaan' => 'required|in:instalasi,jasa,poc',

            'cart' => 'nullable|array', // Keranjang tidak wajib
            // tambahkan validasi lain sesuai kebutuhan
        ]);

        // TAMBAHKAN DI SINI
        $pelanggan = Pelanggan::findOrFail($validatedData['pelanggan_id']);

        $kodePelanggan = 'C' . str_pad($pelanggan->id, 2, '0', STR_PAD_LEFT);
        $periode = now()->format('Ym');

        // Ambil serial dari no_spk (bagian terakhir setelah slash)
        $serial = last(explode('/', $validatedData['no_spk']));

        // Final no_jaringan
        $noJaringan = $kodePelanggan . '-' . $periode . $serial;
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

        // Ambil nilai harga dari hidden input
        $hargaSewa = $request->input('harga_sewa_hidden');
        $hargaInstalasi = $request->input('harga_instalasi_hidden');
        $getInstall = WorkOrderInstall::create([
            'no_spk' => $validatedData['no_spk'],
            'admin_id' => Auth::user()->id,
            'status' => 'Pending',
            'pelanggan_id' => $validatedData['pelanggan_id'],
            'instansi_id' => $validatedData['instansi_id'],
            'vendor_id' => $validatedData['vendor_id'],
            'nama_site' => $validatedData['nama_site'],
            'alamat_pemasangan' => $validatedData['alamat_pemasangan'],
            'nama_pic' => $validatedData['nama_pic'],
            'no_pic' => $validatedData['no_pic'],
            'layanan' => $validatedData['layanan'],
            'media' => $validatedData['media'],
            'bandwidth' => $validatedData['bandwidth'],
            'satuan' => $validatedData['satuan'],
            'nni' => $validatedData['nni'],
            'provinsi' => $validatedData['provinsi'],
            'vlan' => $validatedData['vlan'],
            'no_jaringan' => $noJaringan,
            'tanggal_rfs' => $validatedData['tanggal_rfs'],
            'durasi' => $validatedData['durasi'],
            'nama_durasi' => $validatedData['nama_durasi'],
            'harga_sewa' => $hargaSewa,  // Simpan harga sewa (angka murni)
            'harga_instalasi' => $hargaInstalasi, // Simpan harga instalasi (angka murni)
            'keterangan' => $validatedData['keterangan'],
            'non_stock' => $validatedData['non_stock'],
            'jenis_pekerjaan' => $validatedData['jenis_pekerjaan'],


        ]);
        LogActivity::add(
            'Instalasi',
            $getInstall->nama_site
        );



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

                            WorkOrderInstallDetail::create([
                                'work_order_install_id' => $getInstall->id,
                                'stock_barang_id' => $stockBarang?->id, // Gunakan null-safe operator untuk menghindari error
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

        $uploadedFiles = [];

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {

                // Simpan file dengan nama asli (sesuai input user)
                $path = $file->storeAs(
                    'attachments/instalasi',
                    $file->getClientOriginalName(),
                    'public'
                );

                $uploadedFiles[] = $path;
            }
        }


        // Simpan path file ke kolom JSON attachments
        $getInstall->attachments = $uploadedFiles;
        $getInstall->save();
        // Dapatkan semua pengguna dengan role General Affair (misalnya role 2)
        $gaUsers = User::where('is_role', 2)->get();
        // Dapatkan semua pengguna dengan role PSB (misalnya role 5)
        $psbUsers = User::where('is_role', 5)->get();
        // Buat notifikasi untuk setiap pengguna General Affair
        $jenis = $getInstall->jenis_pekerjaan;

        $routeMap = [
            'instalasi' => [
                'ga'  => 'ga.instalasi.show',
                'psb' => 'psb.instalasi.show',
            ],
            'jasa' => [
                'ga'  => 'ga.jasa_show',
                'psb' => 'psb.jasa_show',
            ],
            'poc' => [
                'ga'  => 'ga.poc_show',
                'psb' => 'psb.poc_show',
            ],
        ];

        $hash = "#{$jenis}";
        foreach ($gaUsers as $gaUser) {
            $url = route($routeMap[$jenis]['ga'], ['id' => $getInstall->id]) . $hash;

            Notification::create([
                'user_id' => $gaUser->id,
                'message' => 'WO ' . ucfirst($jenis) . ' baru telah diterbitkan dengan No Order: ' . $getInstall->no_spk,
                'url' => $url,
            ]);
        }
        foreach ($psbUsers as $psbUser) {
            $url = route($routeMap[$jenis]['psb'], ['id' => $getInstall->id]) . $hash;

            Notification::create([
                'user_id' => $psbUser->id,
                'message' => 'WO ' . ucfirst($jenis) . ' baru telah diterbitkan dengan No Order: ' . $getInstall->no_spk,
                'url' => $url,
            ]);
        }


        $detailBarang = WorkOrderInstallDetail::where('work_order_install_id', $getInstall->id)->get();


        $mailClass = match ($getInstall->jenis_pekerjaan) {
            'instalasi' => \App\Mail\InstalasiMail::class,
            'jasa'      => \App\Mail\JasaMail::class,
            'poc'       => \App\Mail\POCMail::class,
        };

        foreach ($psbUsers as $psb) {
            Mail::to($psb->email)->send(
                new $mailClass($getInstall, $detailBarang, 5)
            );
        }
        foreach ($gaUsers as $ga) {
            Mail::to($ga->email)->send(
                new $mailClass($getInstall, $detailBarang, 2)
            );
        }

        $route = match ($getInstall->jenis_pekerjaan) {
            'instalasi' => 'admin.instalasi',
            'jasa'      => 'admin.jasa',
            'poc'       => 'admin.poc',
        };

        return redirect()
            ->route($route)
            ->with('success', 'Work order berhasil diterbitkan.');
    }
    public function showinstalasi($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // $progressList = SurveyProgress::where('work_order_survey_id', $id)->get();
        $progressList = InstallProgress::where('work_order_install_id', $id)->get();

        // Menampilkan detail work order
        $getInstall = WorkOrderInstall::with('WorkOrderInstallDetail.stockBarang')->findOrFail($id);
        // Cek apakah sudah ada di tabel online_billing
        $billingExists = OnlineBilling::where('work_order_install_id', $getInstall->id)->exists();
        // Mendapatkan berita acara yang terkait dengan work order ini
        $beritaAcara = $getInstall->beritaAcara;        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('billingExists', 'beritaAcara', 'progressList', 'getInstall', 'notifications'));

        // Render view berdasarkan role
        return $this->renderView('wo_instalasi_show', $data);
    }

    public function editinstalasi($id)
    {
        // Ambil data pelanggan dan instansi dari database
        $pelanggans = Pelanggan::orderBy('nama_pelanggan', 'asc')->get();
        $instansis = Instansi::orderBy('nama_instansi', 'asc')->get(); // Ambil semua instansi
        $vendors = Vendor::orderBy('nama_vendor', 'asc')->get(); // Ambil semua Vendor
        // Menampilkan form untuk mengedit work order
        $getInstall = WorkOrderInstall::with('admin')->findOrFail($id);
        // Periksa status, jika completed, kembali dengan error
        if ($getInstall->status === 'Completed') {
            return redirect()->back()->with('error', 'Status instalasi sudah selesai, tidak bisa diedit.');
        }
        // Dapatkan daftar jenis barang
        $jenisList = Jenis::all();

        // Mengambil data stok dengan total jumlah berdasarkan tipe, merek, dan jenis, serta melakukan pagination
        $stockBarangs = StockBarang::with(['merek', 'tipe', 'jenis'])
            ->selectRaw('tipe_id, merek_id, jenis_id, kualitas, SUM(jumlah) as total_jumlah')
            ->groupBy('tipe_id', 'merek_id', 'jenis_id', 'kualitas')
            ->paginate(10); // Menggunakan pagination dengan 10 item per halaman

        // Siapkan data detail barang dalam format array, kosongkan keranjang
        $WorkOrderInstallDetail = []; // Kosongkan keranjang

        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();


        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('getInstall', 'stockBarangs', 'jenisList', 'WorkOrderInstallDetail', 'notifications', 'pelanggans', 'instansis', 'vendors'));

        // Render view berdasarkan role
        return $this->renderView('wo_instalasi_edit', $data);
    }
    public function updateinstalasi(Request $request, $id)
    {
        // Validasi request
        $validatedData = $request->validate([
            'no_spk' => 'required|string|unique:work_order_installs,no_spk,' . $id,
            'pelanggan_id' => 'required|exists:pelanggans,id',
            'instansi_id' => 'required|exists:instansis,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'nama_site' => 'required|string',
            'alamat_pemasangan' => 'required|string',
            'nama_pic' => 'nullable|string',
            'no_pic' => 'nullable|string',
            'layanan' => 'required|string',
            'provinsi' => 'required|string',
            'media' => 'required|string',
            'bandwidth' => 'required|string',
            'satuan' => 'required|string',
            'nni' => 'nullable|string',
            'vlan' => 'nullable|string',
            'no_jaringan' => 'nullable|string',
            'tanggal_rfs' => 'required|date',
            'durasi' => 'required|integer|min:1',
            'nama_durasi' => 'required|string|in:hari,bulan,tahun',
            'harga_sewa_hidden' => 'required|integer',
            'harga_instalasi_hidden' => 'required|integer',
            'keterangan' => 'nullable|string',
            'non_stock' => 'nullable|string',
            'attachments.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:5120', // validasi file
            'jenis_pekerjaan' => 'required|in:instalasi,jasa,poc',

            'cart' => 'nullable|array',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi untuk foto
        ]);

        // Temukan data Work Order Install
        $workOrder = WorkOrderInstall::findOrFail($id);
        $uploadedFiles = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {

                // Simpan file dengan nama asli (sesuai input user)
                $path = $file->storeAs(
                    'attachments/instalasi',
                    $file->getClientOriginalName(),
                    'public'
                );

                $uploadedFiles[] = $path;
            }
        }

        // Update foto jika ada
        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($workOrder->foto) {
                Storage::delete('public/surveys/' . $workOrder->foto);
            }

            // Upload foto baru
            $file = $request->file('foto');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/surveys', $filename);

            $workOrder->foto = $filename;
        }

        // Update data Work Order Install
        $workOrder->update([
            'no_spk' => $validatedData['no_spk'],
            'pelanggan_id' => $validatedData['pelanggan_id'],
            'instansi_id' => $validatedData['instansi_id'],
            'vendor_id' => $validatedData['vendor_id'],
            'nama_site' => $validatedData['nama_site'],
            'alamat_pemasangan' => $validatedData['alamat_pemasangan'],
            'nama_pic' => $validatedData['nama_pic'],
            'no_pic' => $validatedData['no_pic'],
            'layanan' => $validatedData['layanan'],
            'media' => $validatedData['media'],
            'bandwidth' => $validatedData['bandwidth'],
            'satuan' => $validatedData['satuan'],
            'nni' => $validatedData['nni'],
            'provinsi' => $validatedData['provinsi'],
            'vlan' => $validatedData['vlan'],
            'no_jaringan' => $validatedData['no_jaringan'],
            'tanggal_rfs' => $validatedData['tanggal_rfs'],
            'durasi' => $validatedData['durasi'],
            'nama_durasi' => $validatedData['nama_durasi'],
            'harga_sewa' => $validatedData['harga_sewa_hidden'],
            'harga_instalasi' => $validatedData['harga_instalasi_hidden'],
            'keterangan' => $validatedData['keterangan'],
            'non_stock' => $validatedData['non_stock'],
            'attachments' => $uploadedFiles,
            'jenis_pekerjaan' => $validatedData['jenis_pekerjaan'],

        ]);
        LogActivity::add('Instalasi', $workOrder->nama_site, 'edit');

        // Hapus detail barang yang ada sebelumnya
        WorkOrderInstallDetail::where('work_order_install_id', $workOrder->id)->delete();

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

                            WorkOrderInstallDetail::create([
                                'work_order_install_id' => $workOrder->id,
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

        $route = match ($workOrder->jenis_pekerjaan) {
            'instalasi' => 'admin.instalasi',
            'jasa'      => 'admin.jasa',
            'poc'       => 'admin.poc',
        };

        return redirect()
            ->route($route)
            ->with('success', 'Work order berhasil diperbaharui.');
    }

    public function destroyinstalasi($id)
    {
        // Menghapus work order
        $getInstall = WorkOrderInstall::findOrFail($id);
        if ($getInstall->status !== 'Completed') {
            $getInstall->delete();
            LogActivity::add('Instalasi', $getInstall->nama_site, 'delete');

            return redirect()->back()->with('success', 'Instalasi berhasil diHapus.');
        }

        return redirect()->back()->with('error', 'Instalasi sudah selesai dan tidak bisa dihapus.');
    }

    public function cancelInstalasi($id)
    {
        // Cari survey berdasarkan ID
        $install = WorkOrderInstall::findOrFail($id);

        // Periksa apakah statusnya belum "Completed"
        if ($install->status !== 'Completed') {
            // Ubah status menjadi "Cancelled"
            $install->status = 'Canceled';
            $install->save();
            LogActivity::add('Instalasi', $install->nama_site, 'cancel');

            return redirect()->back()->with('success', 'Instalasi berhasil dibatalkan.');
        }

        // Jika sudah Completed, tidak bisa dibatalkan
        return redirect()->back()->with('error', 'Instalasi sudah selesai dan tidak bisa dibatalkan.');
    }
    public function storeBA(Request $request)
    {
        $request->validate([
            'work_order_id' => 'required',
            'work_order_type' => 'required',
            'attachment' => 'required|mimes:pdf,jpg,jpeg,png|max:10240'
        ]);

        // cek supaya 1 WO hanya punya 1 BA
        $exists = BeritaAcara::where('work_order_id', $request->work_order_id)
            ->where('work_order_type', $request->work_order_type)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Berita acara sudah ada.');
        }

        $filePath = $request->file('attachment')
            ->store('berita_acaras', 'public');

        BeritaAcara::create([
            'work_order_id' => $request->work_order_id,
            'work_order_type' => $request->work_order_type,
            'file_path' => $filePath,
            'user_id' => Auth::id(),
            'status' => 'draft'
        ]);

        return back()->with('success', 'Berita acara berhasil diupload.');
    }
    public function sendBA($id)
    {
        $beritaAcara = BeritaAcara::findOrFail($id);

        if (!$beritaAcara->file_path) {
            return back()->with('error', 'Upload berita acara terlebih dahulu.');
        }

        if ($beritaAcara->status !== 'draft') {
            return back()->with('error', 'Berita acara sudah dikirim.');
        }

        $beritaAcara->update([
            'tanggal_kirim' => now(),
            'status' => 'sent',
        ]);

        return back()->with('success', 'Berita acara berhasil dikirim.');
    }

    /**
     * Update status berita acara menjadi 'received'.
     */
    public function ReceiveBA(Request $request, $id)
    {
        $beritaAcara = BeritaAcara::findOrFail($id);

        $request->validate([
            'attachment' => 'required|mimes:pdf,jpg,jpeg,png|max:10240'
        ]);

        $receivedFile = $request->file('attachment')
            ->store('berita_acaras/received', 'public');

        $beritaAcara->update([
            'received_file_path' => $receivedFile,
            'user_id' => Auth::id(),
            'tanggal_terima' => now(),
            'status' => 'received',
        ]);

        return back()->with('success', 'Berita acara berhasil diterima.');
    }

    public function sidform($id)
    {
        $getInstall = WorkOrderInstall::findOrFail($id);

        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('notifications', 'getInstall'));

        return $this->renderView('form_sid', $data);
    }
    public function storebilling(Request $request, $id)
    {
        // Cari Work Order Install berdasarkan ID
        $workOrder = WorkOrderInstall::find($id);

        // Pastikan Work Order Install ditemukan
        if (!$workOrder) {
            return redirect()->back()->with('error', 'Work Order tidak ditemukan.');
        }
        // Contoh dalam Controller
        $durasi = $workOrder->durasi ?? 1;
        $jenisDurasi = $workOrder->nama_durasi ?? 'bulan'; // Default ke 'bulan' jika tidak ada

        // Hitung tanggal akhir
        $tanggalAkhir = match ($jenisDurasi) {
            'tahun' => now()->addYears($durasi),
            'bulan' => now()->addMonths($durasi),
            'hari' => now()->addDays($durasi),
            default => now()->addMonths(1), // Default 1 bulan jika tidak jelas
        };
        // Pindahkan data Work Order Install ke Online Billing
        $billing = OnlineBilling::create([
            'work_order_install_id' => $workOrder->id,
            'pelanggan_id' => $workOrder->pelanggan_id,
            'instansi_id' => $workOrder->instansi_id,
            'vendor_id' => $workOrder->vendor_id,
            'nama_site' => $workOrder->nama_site,
            'alamat_pemasangan' => $workOrder->alamat_pemasangan,
            'nama_pic' => $workOrder->nama_pic,
            'no_pic' => $workOrder->no_pic,
            'layanan' => $workOrder->layanan,
            'media' => $workOrder->media,
            'bandwidth' => $workOrder->bandwidth,
            'provinsi' => $workOrder->provinsi,
            'satuan' => $workOrder->satuan,
            'nni' => $workOrder->nni,
            'vlan' => $workOrder->vlan,
            'no_jaringan' => $workOrder->no_jaringan,
            'tanggal_instalasi' => $workOrder->tanggal_instalasi,
            'tanggal_mulai' => now(), // Set tanggal mulai sebagai sekarang
            'tanggal_akhir' => $tanggalAkhir, // Tambah durasi jika tersedia
            'durasi' => $workOrder->durasi,
            'nama_durasi' => $workOrder->nama_durasi,
            'harga_sewa' => $workOrder->harga_sewa,
            'sid_vendor' => $request->sid_vendor, // ← SID diinput manual
            'admin_id' => $workOrder->admin_id,
            'status' => 'active'
        ]);

        // Pastikan data berhasil disimpan
        if ($billing) {
            return redirect()->route('admin.OB')->with('success', 'Data berhasil dipindahkan ke Online Billing.');
        } else {
            return redirect()->back()->with('error', 'Gagal memindahkan data ke Online Billing.');
        }
    }

    public function pelanggan(Request $request)
    {
        $search = $request->get('search');

        $query = Pelanggan::query();

        if (!empty($search)) {
            $query->where('nama_pelanggan', 'like', '%' . $search . '%');
        }

        $getPelanggan = $query
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->appends([
                'search' => $search,
            ]);

        $notifications = Notification::where('user_id', Auth::user()->id)
            ->where('is_read', false)
            ->get();

        $data = array_merge(
            $this->ambilDataRole(),
            compact('getPelanggan', 'search', 'notifications')
        );

        return $this->renderView('pelanggan', $data);
    }

    public function CreatePelanggan()
    {
        // Ambil data pelanggan
        $pelanggans = Pelanggan::all();  // Mengambil semua data dari tabel pelanggans

        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('notifications', 'pelanggans'));

        return $this->renderView('form_pelanggan', $data);
    }

    public function StorePelanggan(Request $request)
    {
        $request->validate([
            'nama_pelanggan' => 'required|string|max:255',
            'nama_gedung' => 'nullable|string|max:255',
            'alamat' => 'nullable|string|max:255',
            'no_pelanggan' => 'nullable|string|max:50',
            'foto' => 'image|nullable|max:2048',

        ]);
        $filename = null;
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '_' . $file->getClientOriginalName();

            if ($file->isValid()) {
                // Buat gambar dari file upload
                $image = Image::make($file);

                $image->resize(1280, 720, function ($constraint) {
                    $constraint->aspectRatio();
                    // Jangan pakai $constraint->upsize(); agar gambar bisa dibesarkan
                })->resizeCanvas(1280, 720, 'center', false, 'ffffff');

                // Simpan ke storage
                $image->save(storage_path('app/public/pelanggan/' . $filename));
            }
        }
        Pelanggan::create([
            'nama_pelanggan' => $request->nama_pelanggan,
            'nama_gedung' => $request->nama_gedung,
            'alamat' => $request->alamat,
            'no_pelanggan' => $request->no_pelanggan,
            'foto' => $filename,
        ]);

        return redirect()->route('admin.pelanggan')->with('success', 'Pelanggan berhasil ditambahkan!');
    }
    public function EditPelanggan($id)
    {
        // Ambil data pelanggan berdasarkan ID
        $pelanggans = Pelanggan::findOrFail($id);

        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('notifications', 'pelanggans'));

        // Tampilkan view edit pelanggan
        return $this->renderView('edit_pelanggan', $data);
    }
    public function UpdatePelanggan(Request $request, $id)
    {
        // Validasi input dari form
        $request->validate([
            'nama_pelanggan' => 'required|string|max:255',
            'nama_gedung' => 'nullable|string|max:255',
            'alamat' => 'nullable|string|max:255',
            'no_pelanggan' => 'nullable|string|max:50',
            'foto' => 'image|nullable|max:2048',
        ]);

        // Ambil data pelanggan berdasarkan ID
        $pelanggan = Pelanggan::findOrFail($id);

        // Update data pelanggan
        $pelanggan->nama_pelanggan = $request->nama_pelanggan;
        $pelanggan->nama_gedung = $request->nama_gedung;
        $pelanggan->alamat = $request->alamat;
        $pelanggan->no_pelanggan = $request->no_pelanggan;

        // Proses foto jika ada
        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($pelanggan->foto && Storage::exists('public/pelanggan/' . $pelanggan->foto)) {
                Storage::delete('public/pelanggan/' . $pelanggan->foto);
            }

            $file = $request->file('foto');
            $filename = time() . '_' . $file->getClientOriginalName();

            if ($file->isValid()) {
                $image = Image::make($file);

                // Resize dengan rasio 16:9 tanpa crop
                $image->resize(1280, 720, function ($constraint) {
                    $constraint->aspectRatio();
                    // Tidak pakai upsize agar gambar bisa dibesarkan
                })->resizeCanvas(1280, 720, 'center', false, 'ffffff');

                // Simpan gambar
                $image->save(storage_path('app/public/pelanggan/' . $filename));

                // Simpan nama file ke DB
                $pelanggan->foto = $filename;
            }
        }

        $pelanggan->save();

        // Redirect dengan pesan sukses
        return redirect()->route('admin.pelanggan')->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    public function HapusPelanggan($id)
    {
        // Ambil data pelanggan berdasarkan ID
        $pelanggans = Pelanggan::findOrFail($id);
        if ($pelanggans->foto) {
            Storage::delete('public/pelanggan/' . $pelanggans->foto);
        }
        // Hapus data pelanggan
        $pelanggans->delete();

        // Redirect ke halaman pelanggan dengan pesan sukses
        return redirect()->route('admin.pelanggan')->with('success', 'Data pelanggan berhasil dihapus.');
    }
    public function namavendor(Request $request)
    {
        $search = $request->get('search');

        $query = Vendor::query();

        if (!empty($search)) {
            $query->where('nama_vendor', 'like', '%' . $search . '%');
        }

        $getVendor = $query
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->appends([
                'search' => $search,
            ]);

        $notifications = Notification::where('user_id', Auth::user()->id)
            ->where('is_read', false)
            ->get();

        $data = array_merge(
            $this->ambilDataRole(),
            compact('getVendor', 'search', 'notifications')
        );

        return $this->renderView('namavendor', $data);
    }
    public function CreateVendor()
    {
        // Ambil data pelanggan
        $vendors = Vendor::all();  // Mengambil semua data dari tabel pelanggans

        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('notifications', 'vendors'));

        return $this->renderView('form_vendor', $data);
    }
    public function StoreVendor(Request $request)
    {
        $request->validate([
            'nama_vendor' => 'required|string|max:255',
            'contact' => 'required|string|max:255',


        ]);

        Vendor::create([
            'nama_vendor' => $request->nama_vendor,
            'contact' => $request->contact,


        ]);

        return redirect()->route('admin.namavendor')->with('success', 'Vendor berhasil ditambahkan!');
    }

    public function EditVendor($id)
    {
        // Ambil data pelanggan berdasarkan ID
        $vendors = Vendor::findOrFail($id);

        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('notifications', 'vendors'));

        // Tampilkan view edit pelanggan
        return $this->renderView('edit_vendor', $data);
    }
    public function UpdateVendor(Request $request, $id)
    {
        // Validasi input dari form
        $request->validate([
            'nama_vendor' => 'required|string|max:255',
            'contact' => 'required|string|max:255',


        ]);

        // Ambil data pelanggan berdasarkan ID
        $vendors = Vendor::findOrFail($id);

        // Update data pelanggan
        $vendors->update([
            'nama_vendor' => $request->nama_vendor,
            'contact' => $request->contact,

        ]);

        // Redirect ke halaman pelanggan dengan pesan sukses
        return redirect()->route('admin.namavendor')->with('success', 'Data Vendor berhasil diperbarui.');
    }

    public function HapusVendor($id)
    {
        // Ambil data pelanggan berdasarkan ID
        $vendors = Vendor::findOrFail($id);

        // Hapus data pelanggan
        $vendors->delete();

        // Redirect ke halaman pelanggan dengan pesan sukses
        return redirect()->route('admin.namavendor')->with('success', 'Data Vendor berhasil dihapus.');
    }

    public function instansi(Request $request)
    {
        $search = $request->get('search');

        $query = Instansi::query();

        if (!empty($search)) {
            $query->where('nama_instansi', 'like', '%' . $search . '%');
        }

        $getInstansi = $query
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->appends([
                'search' => $search,
            ]);

        $notifications = Notification::where('user_id', Auth::user()->id)
            ->where('is_read', false)
            ->get();

        $data = array_merge(
            $this->ambilDataRole(),
            compact('getInstansi', 'search', 'notifications')
        );

        return $this->renderView('instansi', $data);
    }


    public function CreateInstansi()
    {
        // Ambil data pelanggan
        $instansis = Instansi::all();  // Mengambil semua data dari tabel pelanggans

        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('notifications', 'instansis'));

        return $this->renderView('form_instansi', $data);
    }

    public function StoreInstansi(Request $request)
    {
        $request->validate([
            'nama_instansi' => 'required|string|max:255',


        ]);

        Instansi::create([
            'nama_instansi' => $request->nama_instansi,


        ]);

        return redirect()->route('admin.instansi')->with('success', 'Instansi berhasil ditambahkan!');
    }

    public function EditInstansi($id)
    {
        // Ambil data pelanggan berdasarkan ID
        $instansis = Instansi::findOrFail($id);

        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('notifications', 'instansis'));

        // Tampilkan view edit pelanggan
        return $this->renderView('edit_instansi', $data);
    }

    public function UpdateInstansi(Request $request, $id)
    {
        // Validasi input dari form
        $request->validate([
            'nama_instansi' => 'required|string|max:255',

        ]);

        // Ambil data pelanggan berdasarkan ID
        $instansis = Instansi::findOrFail($id);

        // Update data pelanggan
        $instansis->update([
            'nama_instansi' => $request->nama_instansi,


        ]);

        // Redirect ke halaman pelanggan dengan pesan sukses
        return redirect()->route('admin.instansi')->with('success', 'Data Instansi berhasil diperbarui.');
    }

    public function HapusInstansi($id)
    {
        // Ambil data pelanggan berdasarkan ID
        $instansis = Instansi::findOrFail($id);

        // Hapus data pelanggan
        $instansis->delete();

        // Redirect ke halaman pelanggan dengan pesan sukses
        return redirect()->route('admin.instansi')->with('success', 'Data Instansi berhasil dihapus.');
    }

    public function survey(Request $request)
    {
        // Ambil parameter dari request
        $status = $request->get('status', 'all');
        $search = $request->get('search');
        $month = $request->get('month');
        $year = $request->get('year');

        // Query untuk mendapatkan data survey
        $query = WorkOrderSurvey::orderBy('created_at', 'desc');

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
        $getSurvey = $query->paginate(10)->appends([
            'status' => $status,
            'search' => $search,
            'month' => $month,
            'year' => $year,
        ]);
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();


        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('getSurvey', 'status', 'search', 'month', 'year', 'notifications'));

        // Render view berdasarkan role
        return $this->renderView('survey', $data);
    }

    public function create()
    {
        // Ambil data master
        $pelanggans = Pelanggan::orderBy('nama_pelanggan', 'asc')->get();
        $instansis  = Instansi::orderBy('nama_instansi', 'asc')->get();
        $vendors    = Vendor::orderBy('nama_vendor', 'asc')->get();

        // Notifikasi
        $notifications = Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->get();

        // Ambil nomor survey terakhir (TANPA reset)
        $lastSurvey = WorkOrderSurvey::orderBy('id', 'desc')->first();

        if ($lastSurvey && preg_match('/\/(\d+)$/', $lastSurvey->no_spk, $matches)) {
            $lastNumber = (int) $matches[1];
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        // Pad ke 4 digit minimal (kalau lebih, tampil apa adanya)
        $numberFormatted = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        // Nomor survey
        $no_spk = 'PC24Telin/PSB-SURVEY/' . now()->format('Y-m-d') . '/' . $numberFormatted;

        // Gabungkan data role
        $data = array_merge(
            $this->ambilDataRole(),
            compact('notifications', 'no_spk', 'pelanggans', 'instansis', 'vendors')
        );

        return $this->renderView('form_survey', $data);
    }



    public function store(Request $request)
    {
        // Validasi request
        $request->validate([
            'no_spk' => 'required|string|unique:work_order_surveys',
            'pelanggan_id' => 'required|exists:pelanggans,id',
            'instansi_id' => 'required|exists:instansis,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'nama_site' => 'required|string',
            'alamat_pemasangan' => 'required|string',
            'nama_pic' => 'nullable|string',
            'no_pic' => 'nullable|string',
            'layanan' => 'required|string',
            'provinsi' => 'required|string',
            'media' => 'required|string',
            'bandwidth' => 'required|string',
            'satuan' => 'required|string',
            'nni' => 'nullable|string',
            'vlan' => 'nullable|string',
            'no_jaringan' => 'nullable|string',
            'tanggal_rfs' => 'required|date',
            'keterangan' => 'nullable|string',


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

        $survey = WorkOrderSurvey::create([
            'no_spk' => $request->no_spk,
            'admin_id' => Auth::user()->id,
            'status' => 'Pending',
            'pelanggan_id' => $request->pelanggan_id,
            'instansi_id' => $request->instansi_id,
            'vendor_id' => $request->vendor_id,
            'nama_site' => $request->nama_site,
            'alamat_pemasangan' => $request->alamat_pemasangan,
            'nama_pic' => $request->nama_pic,
            'no_pic' => $request->no_pic,
            'layanan' => $request->layanan,
            'media' => $request->media,
            'bandwidth' => $request->bandwidth,
            'satuan' => $request->satuan,
            'nni' => $request->nni,
            'provinsi' => $request->provinsi,
            'vlan' => $request->vlan,
            'no_jaringan' => $request->no_jaringan,
            'tanggal_rfs' => $request->tanggal_rfs,
            'keterangan' => $request->keterangan,

        ]);
        LogActivity::add(
            'Survey',
            $survey->nama_site
        );

        $uploadedFiles = [];

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {

                // Simpan file dengan nama asli (sesuai input user)
                $path = $file->storeAs(
                    'attachments/survey',
                    $file->getClientOriginalName(),
                    'public'
                );

                $uploadedFiles[] = $path;
            }
        }


        // Simpan path file ke kolom JSON attachments
        $survey->attachments = $uploadedFiles;
        $survey->save();
        // Dapatkan semua pengguna dengan role PSB (misalnya role 5)
        $psbUsers = User::where('is_role', 5)->get();
        // Buat notifikasi untuk setiap pengguna General Affair
        foreach ($psbUsers as $psbUser) {
            $url = route('psb.survey_show', ['id' => $survey->id]) . '#survey';

            Notification::create([
                'user_id' => $psbUser->id,
                'message' => 'WO Survey baru telah diterbitkan dengan No Order: ' . $survey->no_spk,
                'url' => $url, // URL dengan hash #request
            ]);
        }
        $psbUsers = User::where('is_role', 5)
            ->whereNotNull('email')
            ->get();

        foreach ($psbUsers as $psb) {
            Mail::to($psb->email)->send(
                new \App\Mail\SurveyMail(
                    $survey,

                    5 // PSB
                )
            );
        }

        return redirect()->route('admin.survey')->with('success', 'Work order berhasil diterbitkan.');
    }

    public function markAsReadAdmin($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->is_read = true;
        $notification->save();

        return redirect()->to($notification->url);
    }

    public function show($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // Gabungkan data survey ke dalam data role
        $progressList = SurveyProgress::where('work_order_survey_id', $id)->get();

        // Menampilkan detail work order
        $getSurvey = WorkOrderSurvey::with('admin')->findOrFail($id);
        $beritaAcara = $getSurvey->beritaAcara;        // Gabungkan data survey ke dalam data role

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('beritaAcara', 'getSurvey', 'progressList', 'notifications'));

        // Render view berdasarkan role
        return $this->renderView('wo_survey_show', $data);
    }

    public function edit($id)
    {
        // Ambil data pelanggan dan instansi dari database
        $pelanggans = Pelanggan::orderBy('nama_pelanggan', 'asc')->get();
        $instansis = Instansi::orderBy('nama_instansi', 'asc')->get(); // Ambil semua instansi
        $vendors = Vendor::orderBy('nama_vendor', 'asc')->get(); // Ambil semua Vendor
        // Menampilkan form untuk mengedit work order
        $getSurvey = WorkOrderSurvey::with('admin')->findOrFail($id);
        if ($getSurvey->status === 'Completed') {
            return redirect()->back()->with('error', 'Status Survey sudah selesai, tidak bisa diedit.');
        }
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();


        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('getSurvey', 'notifications', 'pelanggans', 'instansis', 'vendors'));

        // Render view berdasarkan role
        return $this->renderView('wo_survey_edit', $data);
    }

    public function update(Request $request, $id)
    {
        // Validasi request
        $request->validate([
            'no_spk' => 'required|unique:work_order_surveys,no_spk,' . $id,
            'pelanggan_id' => 'required|exists:pelanggans,id',
            'instansi_id' => 'required|exists:instansis,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'nama_gedung' => 'nullable|string',
            'alamat_pelanggan' => 'nullable|string',
            'no_pelanggan' => 'nullable|string',
            'nama_site' => 'required|string',
            'alamat_pemasangan' => 'required|string',
            'nama_pic' => 'nullable|string',
            'no_pic' => 'nullable|string',
            'layanan' => 'required|string',
            'media' => 'required|string',
            'bandwidth' => 'required|string',
            'satuan' => 'required|string',
            'nni' => 'nullable|string',
            'vlan' => 'nullable|string',
            'provinsi' => 'nullable|string',
            'no_jaringan' => 'nullable|string',
            'tanggal_rfs' => 'required|date',
            'foto' => 'image|nullable|max:2048', // Validasi untuk foto
            'attachments.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:5120', // validasi file
            'keterangan' => 'nullable|string',

            // tambahkan validasi lain sesuai kebutuhan
        ]);
        // Cari work order survey berdasarkan ID
        $getSurvey = WorkOrderSurvey::findOrFail($id);
        $uploadedFiles = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {

                // Simpan file dengan nama asli (sesuai input user)
                $path = $file->storeAs(
                    'attachments/instalasi',
                    $file->getClientOriginalName(),
                    'public'
                );

                $uploadedFiles[] = $path;
            }
        }

        // Proses upload foto jika ada
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/surveys', $filename); // Simpan file di storage
        } else {
            $filename = $getSurvey->foto; // Tetap gunakan foto lama jika tidak ada yang diupload
        }


        // Update data work order survey
        $getSurvey->update([
            'no_spk' => $request->no_spk,
            'pelanggan_id' => $request->pelanggan_id,
            'instansi_id' => $request->instansi_id,
            'vendor_id' => $request->vendor_id,
            'nama_gedung' => $request->nama_gedung,
            'alamat_pelanggan' => $request->alamat_pelanggan,
            'no_pelanggan' => $request->no_pelanggan,
            'nama_site' => $request->nama_site,
            'alamat_pemasangan' => $request->alamat_pemasangan,
            'nama_pic' => $request->nama_pic,
            'no_pic' => $request->no_pic,
            'layanan' => $request->layanan,
            'media' => $request->media,
            'bandwidth' => $request->bandwidth,
            'satuan' => $request->satuan,
            'nni' => $request->nni,
            'provinsi' => $request->provinsi,
            'vlan' => $request->vlan,
            'no_jaringan' => $request->no_jaringan,
            'tanggal_rfs' => $request->tanggal_rfs,
            'foto' => $filename, // Simpan nama file atau null jika tidak ada foto
            'attachments' => $uploadedFiles,
            'keterangan' => $request->keterangan,

        ]);
        LogActivity::add('Survey', $getSurvey->nama_site, 'edit');

        return redirect()->route('admin.survey')->with('success', 'Work order berhasil diperbarui.');
    }

    public function destroy($id)
    {
        // Menghapus work order
        $getSurvey = WorkOrderSurvey::findOrFail($id);
        if ($getSurvey->status !== 'Completed') {
            $getSurvey->delete();
            LogActivity::add('Survey', $getSurvey->nama_site, 'delete');

            return redirect()->back()->with('success', 'Instalasi berhasil diHapus.');
        }

        return redirect()->back()->with('error', 'Instalasi sudah selesai dan tidak bisa dihapus.');
    }

    public function cancelSurvey($id)
    {
        // Cari survey berdasarkan ID
        $survey = WorkOrderSurvey::findOrFail($id);

        // Periksa apakah statusnya belum "Completed"
        if ($survey->status !== 'Completed') {
            // Ubah status menjadi "Cancelled"
            $survey->status = 'Canceled';
            $survey->save();
            LogActivity::add('Survey', $survey->nama_site, 'cancel');

            return redirect()->back()->with('success', 'Survey berhasil dibatalkan.');
        }

        // Jika sudah Completed, tidak bisa dibatalkan
        return redirect()->back()->with('error', 'Survey sudah selesai dan tidak bisa dibatalkan.');
    }
    public function printSurveyPDF($id)
    {
        // Ambil data dari work order survey berdasarkan ID
        $getSurvey = WorkOrderSurvey::findOrFail($id);
        // Misalkan ini data dari database
        $tanggalSurvey = $getSurvey->tanggal_survey;
        // Format tanggal ke dalam hari, bulan, tahun
        $formattedDate = Carbon::parse($tanggalSurvey)->translatedFormat('l, d F Y');
        // Path template PDF yang sudah ada
        $templatePath = storage_path('app/public/pdf/template_survey.pdf');

        // Inisialisasi FPDI
        $pdf = new FPDI();
        $pdf->AddPage();
        $pdf->setSourceFile($templatePath);
        $templateId = $pdf->importPage(1);
        $pdf->useTemplate($templateId, 0, 0, 210);
        // Atur font
        $pdf->SetFont('Arial', '', 7);


        // Cek apakah pelanggan memiliki foto
        if ($getSurvey->pelanggan && $getSurvey->pelanggan->foto) {
            // Buat path lengkap untuk gambar dengan path yang sesuai di storage
            $imagePath = storage_path('app/public/pelanggan/' . $getSurvey->pelanggan->foto);

            // Cek apakah file gambar ada di path yang sudah dibuat
            if (file_exists($imagePath)) {
                // Tambahkan gambar ke PDF di posisi dan ukuran yang diinginkan
                $pdf->Image($imagePath, 152, 14, 30); // Sesuaikan posisi X, Y dan ukuran gambar
            } else {
                // Jika file foto tidak ditemukan
                $pdf->SetXY(47, 30);
                $pdf->Write(0, "Foto tidak tersedia.");
            }
        } else {
            // Jika pelanggan tidak memiliki foto di database
            $pdf->SetXY(47, 30);
            $pdf->Write(0, "Foto tidak tersedia.");
        }

        // Tambahkan data ke template pada posisi yang diinginkan
        $pdf->SetXY(47, 30); // Atur posisi (x,y) pada template
        $pdf->Write(0,  $getSurvey->no_spk);

        $pdf->SetXY(47, 40);
        $pdf->Write(0, $getSurvey->pelanggan->nama_pelanggan);

        $pdf->SetXY(47, 46);
        $pdf->Write(0, $getSurvey->pelanggan->alamat);

        $pdf->SetXY(47, 59);
        $pdf->Write(1, $getSurvey->layanan);
        $pdf->SetXY(47, 67);
        $pdf->Write(1, $getSurvey->media);


        $pdf->SetXY(47, 81);
        $pdf->Write(1, $getSurvey->instansi->nama_instansi);

        $pdf->SetXY(47, 84);
        $pdf->Write(1, $getSurvey->alamat_pemasangan);

        $pdf->SetXY(136, 30);
        $pdf->Write(0, $formattedDate);

        $pdf->SetXY(136, 40);
        $pdf->Write(0, $getSurvey->nama_pic);

        $pdf->SetXY(136, 48);
        $pdf->Write(0, $getSurvey->no_pic);

        $pdf->SetXY(136, 59);
        $pdf->Write(1, $getSurvey->no_jaringan);

        $pdf->SetXY(136, 67);
        $pdf->Write(1, $getSurvey->bandwidth);

        $pdf->SetXY(141, 67);
        $pdf->Write(1, $getSurvey->satuan);



        $pdf->SetXY(136, 75);
        $pdf->Write(1, $getSurvey->vlan);
        // Outputkan file PDF (bisa disimpan atau langsung di-download)
        return response()->streamDownload(function () use ($pdf) {
            $pdf->Output();
        }, "work_order_survey_{$getSurvey->no_spk}.pdf");
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
        // filter overdue
        if ($request->filter == 'overdue') {
            $query->whereDate('tanggal_rfs', '<', today())
                ->whereNotIn('status', [
                    'Completed',
                    'Rejected',
                    'Canceled'
                ]);
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


    public function upgradecreate($id)
    {
        $onlineBilling = OnlineBilling::findOrFail($id);
        // Ambil daftar jenis barang
        $jenisList = Jenis::select('id', 'nama_jenis')->get();

        // Ambil stok barang
        $stockBarangs = StockBarang::with(['merek', 'tipe', 'jenis'])
            ->selectRaw('tipe_id, merek_id, jenis_id, kualitas, SUM(jumlah) as total_jumlah')
            ->groupBy('tipe_id', 'merek_id', 'jenis_id', 'kualitas')
            ->get();

        // Ambil nomor upgrade terakhir (TANPA reset)
        $lastUpgrade = WorkOrderUpgrade::orderBy('id', 'desc')->first();

        if ($lastUpgrade && preg_match('/\/(\d+)$/', $lastUpgrade->no_spk, $matches)) {
            $lastNumber = (int) $matches[1];
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        // Pad minimal 4 digit (kalau lebih, tetap tampil)
        $numberFormatted = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        // Generate nomor UPGRADE
        $no_spk = 'PC24Telin/PSB-UPGRADE/' . now()->format('Y-m-d') . '/' . $numberFormatted;

        // Notifikasi
        $notifications = Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->get();

        // Gabungkan data role
        $data = array_merge(
            $this->ambilDataRole(),
            compact('stockBarangs', 'jenisList', 'notifications', 'no_spk', 'onlineBilling')
        );

        return $this->renderView('upgrade_create', $data);
    }


    public function upgradeStore(Request $request)
    {

        // Validasi data yang masuk
        $validated = $request->validate([
            'no_spk' => 'required|string|unique:work_order_upgrades',
            'online_billing_id' => 'required|exists:online_billings,id',
            'bandwidth_baru' => 'required|string',
            'satuan' => 'required|string',
            'keterangan' => 'nullable|string',
            'non_stock' => 'nullable|string',

            'cart' => 'nullable|array', // Keranjang tidak wajib
            'attachments.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:5120', // Tambahkan ini
            'tanggal_rfs' => 'nullable|date',

        ]);
        // Simpan data ke tabel work_order_upgrades
        $workOrder = WorkOrderUpgrade::create([
            'online_billing_id' => $validated['online_billing_id'],
            'no_spk' => $validated['no_spk'],
            'bandwidth_baru' => $validated['bandwidth_baru'],
            'satuan' => $validated['satuan'],
            'admin_id' => Auth::user()->id,
            'keterangan' => $validated['keterangan'],
            'non_stock' => $validated['non_stock'],
            'status' => 'Pending', // Default status
            'tanggal_rfs' => $validated['tanggal_rfs'],

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

                            WorkOrderUpgradeDetail::create([
                                'work_order_upgrade_id' => $workOrder->id,
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
            'work_orderable_id' => $workOrder->id,
            'work_orderable_type' => WorkOrderUpgrade::class,
            'online_billing_id' => $validated['online_billing_id'],
            'process' => 'Upgrade',
            'status' => 'Pending',
            'admin_id' => Auth::user()->id,
        ]);

        LogActivity::add(
            'Upgrade',
            $workOrder->onlineBilling->nama_site
        );

        $uploadedFiles = [];

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {

                // Simpan file dengan nama asli (sesuai input user)
                $path = $file->storeAs(
                    'attachments/upgrade',
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
        $naUsers = User::where('is_role', 6)->get();
        // Dapatkan semua pengguna dengan role PSB (misalnya role 5)
        $psbUsers = User::where('is_role', 5)->get();
        $gaUsers = User::where('is_role', 2)->get();

        // Buat notifikasi untuk setiap pengguna General Affair
        foreach ($naUsers as $naUser) {
            $url = route(
                'na.upgrade_show',
                ['id' => $workOrder->id]
            ) . '#upgrade';

            Notification::create([
                'user_id' => $naUser->id,
                'message' => 'WO Upgrade baru telah diterbitkan dengan No Order: ' . $workOrder->no_spk,
                'url' => $url, // URL dengan hash #request
            ]);
        }
        // Buat notifikasi untuk setiap pengguna PSB
        foreach ($psbUsers as $psbUser) {
            $url = route('psb.upgrade_show', ['id' => $workOrder->id]) . '#upgrade';

            Notification::create([
                'user_id' => $psbUser->id,
                'message' => 'WO Upgrade baru telah diterbitkan dengan No Order: ' . $workOrder->no_spk,
                'url' => $url, // URL dengan hash #instalasi
            ]);
        }
        foreach ($gaUsers as $gaUser) {
            $url = route('ga.upgrade.show', ['id' => $workOrder->id]) . '#upgrade';

            Notification::create([
                'user_id' => $gaUser->id,
                'message' => 'WO Upgrade baru telah diterbitkan dengan No Order: ' . $workOrder->no_spk,
                'url' => $url, // URL dengan hash #instalasi
            ]);
        }
        $detailBarang = WorkOrderUpgradeDetail::where('work_order_upgrade_id', $workOrder->id)->get();

        // Load relasi onlineBilling untuk email
        $workOrder->load('onlineBilling', 'admin');


        $psbUsers = User::where('is_role', 5)
            ->whereNotNull('email')
            ->get();

        foreach ($psbUsers as $psb) {
            Mail::to($psb->email)->send(
                new \App\Mail\UpgradeMail(
                    $workOrder,
                    $detailBarang,
                    5 // PSB
                )
            );
        }

        $gaUsers = User::where('is_role', 2)
            ->whereNotNull('email')
            ->get();

        foreach ($gaUsers as $ga) {
            Mail::to($ga->email)->send(
                new \App\Mail\UpgradeMail(
                    $workOrder,
                    $detailBarang,
                    2 // PSB
                )
            );
        }
        return redirect()->route('admin.upgrade')->with('success', 'Work order berhasil diterbitkan.');
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
        $beritaAcara = $getUpgrade->beritaAcara;        // Gabungkan data survey ke dalam data role

        // Gabungkan data ke dalam array data role
        $data = array_merge($this->ambilDataRole(), compact('beritaAcara', 'progressList', 'getUpgrade', 'notifications'));

        // Render view berdasarkan role
        return $this->renderView('upgrade_show', $data);
    }
    public function upgradeEdit($id)
    {
        // Ambil data work order berdasarkan ID
        $workOrder = WorkOrderUpgrade::findOrFail($id);

        if ($workOrder->status === 'Completed') {
            return redirect()->back()->with('error', 'Status Upgrade sudah selesai, tidak bisa diedit.');
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
        $WorkOrderUpgradeDetail = []; // Kosongkan keranjang
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // Ambil data lain yang dibutuhkan, misalnya online billing
        $onlineBilling = $workOrder->onlineBilling;

        $data = array_merge($this->ambilDataRole(), compact('WorkOrderUpgradeDetail', 'stockBarangs', 'jenisList', 'workOrder', 'onlineBilling', 'notifications'));
        // Kirim data ke view
        return view('admin.upgrade_edit', $data);
    }
    public function upgradeUpdate(Request $request, $id)
    {
        // Validasi data input
        $validated = $request->validate([
            'bandwidth_baru' => 'required|numeric|min:1',
            'satuan' => 'required|in:Gbps,Mbps,Kbps,RU(RACK UNIT),CORE,PAIR',
            'online_billing_id' => 'required|exists:online_billings,id',
            'attachments.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:5120',
            'cart' => 'nullable|array',
            'keterangan' => 'nullable|string',
            'non_stock' => 'nullable|string',
            'tanggal_rfs' => 'nullable|date',

        ]);
        // Ambil data work order berdasarkan ID
        $workOrder = WorkOrderUpgrade::findOrFail($id);
        $uploadedFiles = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {

                // Simpan file dengan nama asli (sesuai input user)
                $path = $file->storeAs(
                    'attachments/upgrade',
                    $file->getClientOriginalName(),
                    'public'
                );

                $uploadedFiles[] = $path;
            }
        }


        // Update data work order
        $workOrder->update([
            'bandwidth_baru' => $request->bandwidth_baru,
            'satuan' => $request->satuan,
            'online_billing_id' => $request->online_billing_id,
            'attachments' => $uploadedFiles,
            'keterangan' => $validated['keterangan'],
            'non_stock' => $validated['non_stock'],
            'tanggal_rfs' => $validated['tanggal_rfs'],

        ]);
        LogActivity::add('Upgrade', $workOrder->onlineBilling->nama_site, 'edit');
        // Hapus detail barang yang ada sebelumnya
        WorkOrderUpgradeDetail::where('work_order_upgrade_id', $workOrder->id)->delete();

        // Simpan detail barang ke `request_barang_details` jika keranjang tidak kosong
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

                            WorkOrderUpgradeDetail::create([
                                'work_order_upgrade_id' => $workOrder->id,
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
        return redirect()->route('admin.upgrade')->with('success', 'Work order berhasil diperbarui.');
    }
    public function upgradeDestroy($id)
    {
        // Menghapus work order
        $getUpgrade = WorkOrderUpgrade::findOrFail($id);
        if ($getUpgrade->status !== 'Completed') {
            $getUpgrade->delete();
            LogActivity::add('Upgrade', $getUpgrade->onlineBilling->nama_site, 'delete');

            return redirect()->back()->with('success', 'Upgrade berhasil diHapus.');
        }

        return redirect()->back()->with('error', 'Upgrade sudah selesai dan tidak bisa dihapus.');
    }

    public function upgradeCancel($id)
    {
        // Cari Work Order Upgrade berdasarkan ID
        $getUpgrade = WorkOrderUpgrade::findOrFail($id);

        // Jika status sudah "Completed", tidak bisa dibatalkan
        if ($getUpgrade->status === 'Completed') {
            return redirect()->back()->with('error', 'Upgrade sudah selesai dan tidak bisa dibatalkan.');
        }

        // Jika belum "Completed", ubah status menjadi "Canceled"
        $getUpgrade->status = 'Canceled';
        $getUpgrade->save();
        LogActivity::add('Upgrade', $getUpgrade->onlineBilling->nama_site, 'cancel');

        // Cari entri terkait di tabel statuses
        $status = Status::where('work_orderable_id', $getUpgrade->id)
            ->where('process', 'Upgrade') // Pastikan prosesnya adalah 'Upgrade'
            ->first();

        // Perbarui status di tabel statuses jika entri ditemukan
        if ($status) {
            $status->status = 'Canceled'; // Sesuaikan dengan status baru
            $status->save();
        }

        // Berikan notifikasi sukses
        return redirect()->back()->with('success', 'Upgrade berhasil dibatalkan.');
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
        // filter overdue
        if ($request->filter == 'overdue') {
            $query->whereDate('tanggal_rfs', '<', today())
                ->whereNotIn('status', [
                    'Completed',
                    'Rejected',
                    'Canceled'
                ]);
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

    public function downgradeCreate($id)
    {
        $onlineBilling = OnlineBilling::findOrFail($id);

        // Ambil nomor downgrade terakhir (TANPA reset)
        $lastDowngrade = WorkOrderDowngrade::orderBy('id', 'desc')->first();

        if ($lastDowngrade && preg_match('/\/(\d+)$/', $lastDowngrade->no_spk, $matches)) {
            $lastNumber = (int) $matches[1];
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        // Pad minimal 4 digit (kalau lebih, tampil apa adanya)
        $numberFormatted = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        // Generate nomor DWG
        $no_spk = 'PC24Telin/PSB-DOWNGRADE/' . now()->format('Y-m-d') . '/' . $numberFormatted;

        // Notifikasi
        $notifications = Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->get();

        // Gabungkan data role
        $data = array_merge(
            $this->ambilDataRole(),
            compact('notifications', 'no_spk', 'onlineBilling')
        );

        return $this->renderView('downgrade_create', $data);
    }


    public function downgradeStore(Request $request)
    {

        // Validasi data yang masuk
        $validated = $request->validate([
            'no_spk' => 'required|string|unique:work_order_downgrades',
            'online_billing_id' => 'required|exists:online_billings,id',
            'bandwidth_baru' => 'required|string',
            'satuan' => 'required|string',
            'attachments.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:5120', // Tambahkan ini
            'keterangan' => 'nullable|string',
            'tanggal_rfs' => 'nullable|date',

        ]);

        // Simpan data ke tabel work_order_upgrades
        $workOrder = WorkOrderDowngrade::create([
            'online_billing_id' => $validated['online_billing_id'],
            'no_spk' => $validated['no_spk'],
            'bandwidth_baru' => $validated['bandwidth_baru'],
            'satuan' => $validated['satuan'],
            'admin_id' => Auth::user()->id,
            'status' => 'Pending', // Default status
            'keterangan' => $validated['keterangan'],
            'tanggal_rfs' => $validated['tanggal_rfs'],

        ]);

        Status::create([
            'work_orderable_id' => $workOrder->id,
            'work_orderable_type' => WorkOrderDowngrade::class,
            'online_billing_id' => $validated['online_billing_id'],
            'process' => 'Downgrade',
            'status' => 'Pending',
            'admin_id' => Auth::user()->id,
        ]);
        LogActivity::add(
            'Downgrade',
            $workOrder->onlineBilling->nama_site
        );
        $uploadedFiles = [];

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {

                // Simpan file dengan nama asli (sesuai input user)
                $path = $file->storeAs(
                    'attachments/downgrade',
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
        $naUsers = User::where('is_role', 6)->get();
        // Dapatkan semua pengguna dengan role PSB (misalnya role 5)
        $psbUsers = User::where('is_role', 5)->get();
        // Buat notifikasi untuk setiap pengguna General Affair
        foreach ($naUsers as $gaUser) {
            $url = route(
                'na.downgrade_show',
                ['id' => $workOrder->id]
            ) . '#downgrade';

            Notification::create([
                'user_id' => $gaUser->id,
                'message' => 'WO Downgrade baru telah diterbitkan dengan No Order: ' . $workOrder->no_spk,
                'url' => $url, // URL dengan hash #request
            ]);
        }
        // Buat notifikasi untuk setiap pengguna PSB
        foreach ($psbUsers as $psbUser) {
            $url = route('psb.downgrade_show', ['id' => $workOrder->id]) . '#downgrade';

            Notification::create([
                'user_id' => $psbUser->id,
                'message' => 'WO Downgrade baru telah diterbitkan dengan No Order: ' . $workOrder->no_spk,
                'url' => $url, // URL dengan hash #instalasi
            ]);
        }


        // Load relasi onlineBilling untuk email
        $workOrder->load('onlineBilling', 'admin');


        $psbUsers = User::where('is_role', 5)
            ->whereNotNull('email')
            ->get();

        foreach ($psbUsers as $psb) {
            Mail::to($psb->email)->send(
                new \App\Mail\DowngradeMail(
                    $workOrder,

                    5 // PSB
                )
            );
        }
        return redirect()->route('admin.downgrade')->with('success', 'Work order berhasil diterbitkan.');
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
        $beritaAcara = $getDowngrade->beritaAcara;        // Gabungkan data survey ke dalam data role

        // Gabungkan data ke dalam array data role
        $data = array_merge($this->ambilDataRole(), compact('beritaAcara', 'progressList', 'getDowngrade', 'notifications'));

        // Render view berdasarkan role
        return $this->renderView('downgrade_show', $data);
    }
    public function downgradeEdit($id)
    {
        // Ambil data work order berdasarkan ID
        $workOrder = WorkOrderDowngrade::findOrFail($id);

        if ($workOrder->status === 'Completed') {
            return redirect()->back()->with('error', 'Status Downgrade sudah selesai, tidak bisa diedit.');
        }
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // Ambil data lain yang dibutuhkan, misalnya online billing
        $onlineBilling = $workOrder->onlineBilling;

        $data = array_merge($this->ambilDataRole(), compact('workOrder', 'onlineBilling', 'notifications'));
        // Kirim data ke view
        return view('admin.downgrade_edit', $data);
    }
    public function downgradeUpdate(Request $request, $id)
    {
        // Validasi data input
        $request->validate([
            'bandwidth_baru' => 'required|numeric|min:1',
            'satuan' => 'required|in:Gbps,Mbps,Kbps,RU(RACK UNIT),CORE,PAIR',
            'online_billing_id' => 'required|exists:online_billings,id',
            'attachments.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:5120', // Tambahkan ini
            'keterangan' => 'nullable|string',
            'tanggal_rfs' => 'nullable|date',

        ]);

        // Ambil data work order berdasarkan ID
        $workOrder = WorkOrderDowngrade::findOrFail($id);
        $uploadedFiles = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {

                // Simpan file dengan nama asli (sesuai input user)
                $path = $file->storeAs(
                    'attachments/upgrade',
                    $file->getClientOriginalName(),
                    'public'
                );

                $uploadedFiles[] = $path;
            }
        }

        // Update data work order
        $workOrder->update([
            'bandwidth_baru' => $request->bandwidth_baru,
            'satuan' => $request->satuan,
            'online_billing_id' => $request->online_billing_id,
            'attachments' => $uploadedFiles,
            'keterangan' => $request->keterangan,
            'tanggal_rfs' => $request->tanggal_rfs,

        ]);
        LogActivity::add('Downgrade', $workOrder->onlineBilling->nama_site, 'edit');

        // Redirect dengan pesan sukses
        return redirect()->route('admin.downgrade')->with('success', 'Work order berhasil diperbarui.');
    }
    public function downgradeDestroy($id)
    {
        // Menghapus work order
        $getDowngrade = WorkOrderDowngrade::findOrFail($id);
        if ($getDowngrade->status !== 'Completed') {
            $getDowngrade->delete();
            LogActivity::add('Downgrade', $getDowngrade->onlineBilling->nama_site, 'delete');

            return redirect()->back()->with('success', 'Downgrade berhasil diHapus.');
        }

        return redirect()->back()->with('error', 'Downgrade sudah selesai dan tidak bisa dihapus.');
    }

    public function downgradeCancel($id)
    {
        // Cari survey berdasarkan ID
        $getDowngrade = WorkOrderDowngrade::findOrFail($id);

        // Periksa apakah statusnya belum "Completed"
        if ($getDowngrade->status !== 'Completed') {
            // Ubah status menjadi "Cancelled"
            $getDowngrade->status = 'Canceled';
            $getDowngrade->save();
            LogActivity::add('Downgrade', $getDowngrade->onlineBilling->nama_site, 'cancel');

            return redirect()->back()->with('success', 'Downgrade berhasil dibatalkan.');
        }

        // Jika sudah Completed, tidak bisa dibatalkan
        return redirect()->back()->with('error', 'Downgrade sudah selesai dan tidak bisa dibatalkan.');
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
        // filter overdue
        if ($request->filter == 'overdue') {
            $query->whereDate('tanggal_rfs', '<', today())
                ->whereNotIn('status', [
                    'Completed',
                    'Rejected',
                    'Canceled'
                ]);
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


    public function dismantlecreate($id)
    {
        $onlineBilling = OnlineBilling::findOrFail($id);

        // Ambil nomor dismantle terakhir (TANPA reset)
        $lastDismantle = WorkOrderDismantle::orderBy('id', 'desc')->first();

        if ($lastDismantle && preg_match('/\/(\d+)$/', $lastDismantle->no_spk, $matches)) {
            $lastNumber = (int) $matches[1];
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        // Pad minimal 4 digit (kalau lebih, tampil apa adanya)
        $numberFormatted = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        // Generate nomor DISMANTLE
        $no_spk = 'PC24Telin/PSB-DISMANTLE/' . now()->format('Y-m-d') . '/' . $numberFormatted;

        // Notifikasi
        $notifications = Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->get();

        // Gabungkan data role
        $data = array_merge(
            $this->ambilDataRole(),
            compact('notifications', 'no_spk', 'onlineBilling')
        );

        return $this->renderView('dismantle_create', $data);
    }


    public function dismantleStore(Request $request)
    {

        // Validasi data yang masuk
        $validated = $request->validate([
            'no_spk' => 'required|string|unique:work_order_dismantles',
            'online_billing_id' => 'required|exists:online_billings,id',
            'keterangan' => 'nullable|string',
            'attachments.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:5120', // Tambahkan ini
            'tanggal_rfs' => 'nullable|date',

        ]);

        // Simpan data ke tabel work_order_upgrades
        $workOrder = WorkOrderDismantle::create([
            'online_billing_id' => $validated['online_billing_id'],
            'no_spk' => $validated['no_spk'],
            'keterangan' => $validated['keterangan'],
            'admin_id' => Auth::user()->id,
            'status' => 'Pending', // Default status
            'tanggal_rfs' => $validated['tanggal_rfs'],

        ]);

        Status::create([
            'work_orderable_id' => $workOrder->id,
            'work_orderable_type' => WorkOrderDismantle::class,
            'online_billing_id' => $validated['online_billing_id'],
            'process' => 'Dismantle',
            'status' => 'Pending',
            'admin_id' => Auth::user()->id,
        ]);
        LogActivity::add(
            'Dismantle',
            $workOrder->onlineBilling->nama_site
        );
        $uploadedFiles = [];

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {

                // Simpan file dengan nama asli (sesuai input user)
                $path = $file->storeAs(
                    'attachments/dismantle',
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
        $gaUsers = User::where('is_role', 2)->get();
        // Dapatkan semua pengguna dengan role PSB (misalnya role 5)
        $psbUsers = User::where('is_role', 5)->get();
        // Buat notifikasi untuk setiap pengguna General Affair
        $naUsers = User::where('is_role', 6)->get();
        // Buat notifikasi untuk setiap pengguna General Affair
        foreach ($gaUsers as $gaUser) {
            $url = route(
                'ga.dismantle_show',
                ['id' => $workOrder->id]
            ) . '#dismantle';

            Notification::create([
                'user_id' => $gaUser->id,
                'message' => 'WO Dismantle baru telah diterbitkan dengan No Order: ' . $workOrder->no_spk,
                'url' => $url, // URL dengan hash #request
            ]);
        }
        // Buat notifikasi untuk setiap pengguna PSB
        foreach ($psbUsers as $psbUser) {
            $url = route('psb.dismantle_show', ['id' => $workOrder->id]) . '#dismantle';

            Notification::create([
                'user_id' => $psbUser->id,
                'message' => 'WO Dismantle baru telah diterbitkan dengan No Order: ' . $workOrder->no_spk,
                'url' => $url, // URL dengan hash #instalasi
            ]);
        }
        foreach ($naUsers as $naUsers) {
            $url = route('na.dismantle_show', ['id' => $workOrder->id]) . '#dismantle';

            Notification::create([
                'user_id' => $naUsers->id,
                'message' => 'WO Dismantle baru telah diterbitkan dengan No Order: ' . $workOrder->no_spk,
                'url' => $url, // URL dengan hash #instalasi
            ]);
        }


        // Load relasi onlineBilling untuk email
        $workOrder->load('onlineBilling', 'admin');

        $gaUsers = User::where('is_role', 2)
            ->whereNotNull('email')
            ->get();

        foreach ($gaUsers as $ga) {
            Mail::to($ga->email)->send(
                new \App\Mail\DismantleMail(
                    $workOrder,

                    2 // PSB
                )
            );
        }

        $psbUsers = User::where('is_role', 5)
            ->whereNotNull('email')
            ->get();

        foreach ($psbUsers as $psb) {
            Mail::to($psb->email)->send(
                new \App\Mail\DismantleMail(
                    $workOrder,

                    5 // PSB
                )
            );
        }
        return redirect()->route('admin.dismantle')->with('success', 'Work order berhasil diterbitkan.');
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
        $beritaAcara = $getDismantle->beritaAcara;        // Gabungkan data survey ke dalam data role

        // Gabungkan data ke dalam array data role
        $data = array_merge($this->ambilDataRole(), compact('beritaAcara', 'dismantleItems', 'progressList', 'getDismantle', 'notifications'));

        // Render view berdasarkan role
        return $this->renderView('dismantle_show', $data);
    }
    public function dismantleEdit($id)
    {
        // Ambil data work order berdasarkan ID
        $workOrder = WorkOrderDismantle::findOrFail($id);

        if ($workOrder->status === 'Completed') {
            return redirect()->back()->with('error', 'Status Dismantle sudah selesai, tidak bisa diedit.');
        }
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // Ambil data lain yang dibutuhkan, misalnya online billing
        $onlineBilling = $workOrder->onlineBilling;

        $data = array_merge($this->ambilDataRole(), compact('workOrder', 'onlineBilling', 'notifications'));
        // Kirim data ke view
        return view('admin.dismantle_edit', $data);
    }
    public function dismantleUpdate(Request $request, $id)
    {
        // Validasi data input
        $request->validate([
            'keterangan' => 'nullable|string',
            'online_billing_id' => 'required|exists:online_billings,id',
            'attachments.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:5120', // validasi file
            'tanggal_rfs' => 'nullable|date',

        ]);

        // Ambil data work order berdasarkan ID
        $workOrder = WorkOrderDismantle::findOrFail($id);

        // Siapkan array untuk menyimpan path file
        // Upload file baru dan reset file lama
        $uploadedFiles = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {

                // Simpan file dengan nama asli (sesuai input user)
                $path = $file->storeAs(
                    'attachments/dismantle',
                    $file->getClientOriginalName(),
                    'public'
                );

                $uploadedFiles[] = $path;
            }
        }


        // Update data work order + attachments baru (reset lama)
        $workOrder->update([
            'keterangan' => $request->keterangan,
            'online_billing_id' => $request->online_billing_id,
            'attachments' => $uploadedFiles,
            'tanggal_rfs' => $request->tanggal_rfs,

        ]);


        LogActivity::add('Dismantle', $workOrder->onlineBilling->nama_site, 'edit');

        // Redirect dengan pesan sukses
        return redirect()->route('admin.dismantle')->with('success', 'Work order berhasil diperbarui.');
    }


    public function dismantleDestroy($id)
    {
        // Menghapus work order
        $getDismantle = WorkOrderDismantle::findOrFail($id);
        if ($getDismantle->status !== 'Completed') {
            $getDismantle->delete();
            LogActivity::add('Dismantle', $getDismantle->onlineBilling->nama_site, 'delete');

            return redirect()->back()->with('success', 'Dismantle berhasil diHapus.');
        }

        return redirect()->back()->with('error', 'Dismantle sudah selesai dan tidak bisa dihapus.');
    }

    public function dismantleCancel($id)
    {
        // Cari survey berdasarkan ID
        $getDismantle = WorkOrderDismantle::findOrFail($id);

        // Periksa apakah statusnya belum "Completed"
        if ($getDismantle->status !== 'Completed') {
            // Ubah status menjadi "Cancelled"
            $getDismantle->status = 'Canceled';
            $getDismantle->save();
            LogActivity::add('Dismantle', $getDismantle->onlineBilling->nama_site, 'cancel');

            return redirect()->back()->with('success', 'Dismantle berhasil dibatalkan.');
        }

        // Jika sudah Completed, tidak bisa dibatalkan
        return redirect()->back()->with('error', 'Dismantle sudah selesai dan tidak bisa dibatalkan.');
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
        // filter overdue
        if ($request->filter == 'overdue') {
            $query->whereDate('tanggal_rfs', '<', today())
                ->whereNotIn('status', [
                    'Completed',
                    'Rejected',
                    'Canceled'
                ]);
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
    public function relokasiCreate($id)
    {
        $onlineBilling = OnlineBilling::findOrFail($id);

        // Ambil daftar jenis barang
        $jenisList = Jenis::select('id', 'nama_jenis')->get();

        // Ambil stok barang
        $stockBarangs = StockBarang::with(['merek', 'tipe', 'jenis'])
            ->selectRaw('tipe_id, merek_id, jenis_id, kualitas, SUM(jumlah) as total_jumlah')
            ->groupBy('tipe_id', 'merek_id', 'jenis_id', 'kualitas')
            ->get();

        // Ambil nomor relokasi terakhir (TANPA reset)
        $lastRelokasi = WorkOrderRelokasi::orderBy('id', 'desc')->first();

        if ($lastRelokasi && preg_match('/\/(\d+)$/', $lastRelokasi->no_spk, $matches)) {
            $lastNumber = (int) $matches[1];
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        // Pad minimal 4 digit (kalau lebih, tampil apa adanya)
        $numberFormatted = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        // Generate nomor RELOKASI
        $no_spk = 'PC24Telin/PSB-RELOKASI/' . now()->format('Y-m-d') . '/' . $numberFormatted;

        // Notifikasi
        $notifications = Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->get();

        // Gabungkan data role
        $data = array_merge(
            $this->ambilDataRole(),
            compact('stockBarangs', 'jenisList', 'notifications', 'no_spk', 'onlineBilling')
        );

        return $this->renderView('relokasi_create', $data);
    }

    public function relokasiStore(Request $request)
    {
        // Validasi request
        $validated = $request->validate([
            'no_spk' => 'required|string|unique:work_order_relokasis',
            'online_billing_id' => 'required|exists:online_billings,id',
            'alamat_pemasangan_baru' => 'required|string',
            'keterangan' => 'nullable|string',
            'non_stock' => 'nullable|string',

            'cart' => 'nullable|array', // Keranjang tidak wajib
            'attachments.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:5120', // Tambahkan ini
            'tanggal_rfs' => 'nullable|date',

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


        $getRelokasi = WorkOrderRelokasi::create([
            'online_billing_id' => $validated['online_billing_id'],
            'no_spk' => $validated['no_spk'],
            'alamat_pemasangan_baru' => $validated['alamat_pemasangan_baru'],
            'admin_id' => Auth::user()->id,
            'status' => 'Pending', // Default status
            'keterangan' => $validated['keterangan'],
            'non_stock' => $validated['non_stock'],
            'tanggal_rfs' => $validated['tanggal_rfs'],

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

                            WorkOrderRelokasiDetail::create([
                                'work_order_relokasi_id' => $getRelokasi->id,
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
            'work_orderable_id' => $getRelokasi->id,
            'work_orderable_type' => WorkOrderRelokasi::class,
            'online_billing_id' => $validated['online_billing_id'],
            'process' => 'Relokasi',
            'status' => 'Pending',
            'admin_id' => Auth::user()->id,
        ]);
        LogActivity::add(
            'Relokasi',
            $getRelokasi->onlineBilling->nama_site
        );
        $uploadedFiles = [];

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {

                // Simpan file dengan nama asli (sesuai input user)
                $path = $file->storeAs(
                    'attachments/relokasi',
                    $file->getClientOriginalName(),
                    'public'
                );

                $uploadedFiles[] = $path;
            }
        }


        // Simpan path file ke kolom JSON attachments
        $getRelokasi->attachments = $uploadedFiles;
        $getRelokasi->save();
        // Dapatkan semua pengguna dengan role General Affair (misalnya role 2)
        $gaUsers = User::where('is_role', 2)->get();
        // Dapatkan semua pengguna dengan role PSB (misalnya role 5)
        $naUsers = User::where('is_role', 6)->get();

        $psbUsers = User::where('is_role', 5)->get();
        // Buat notifikasi untuk setiap pengguna General Affair
        foreach ($gaUsers as $gaUser) {
            $url = route('ga.relokasi.show', ['id' => $getRelokasi->id]) . '#relokasi';

            Notification::create([
                'user_id' => $gaUser->id,
                'message' => 'WO Relokasi baru telah diterbitkan dengan No Order: ' . $getRelokasi->no_spk,
                'url' => $url, // URL dengan hash #request
            ]);
        }
        // Buat notifikasi untuk setiap pengguna PSB
        foreach ($psbUsers as $psbUser) {
            $url = route('psb.relokasi.show', ['id' => $getRelokasi->id]) . '#relokasi';

            Notification::create([
                'user_id' => $psbUser->id,
                'message' => 'WO Relokasi baru telah diterbitkan dengan No Order: ' . $getRelokasi->no_spk,
                'url' => $url, // URL dengan hash #instalasi
            ]);
        }
        // Buat notifikasi untuk setiap pengguna PSB
        foreach ($naUsers as $naUser) {
            $url = route('na.relokasi.show', ['id' => $getRelokasi->id]) . '#relokasi';

            Notification::create([
                'user_id' => $naUser->id,
                'message' => 'WO Relokasi baru telah diterbitkan dengan No Order: ' . $getRelokasi->no_spk,
                'url' => $url, // URL dengan hash #instalasi
            ]);
        }
        // Ambil semua detail barang dari request_barang_id ini
        $detailBarang = WorkOrderRelokasiDetail::where('work_order_relokasi_id', $getRelokasi->id)->get();
        // Load relasi onlineBilling untuk email
        $getRelokasi->load('onlineBilling', 'admin');

        $gaUsers = User::where('is_role', 2)
            ->whereNotNull('email')
            ->get();

        foreach ($gaUsers as $ga) {
            Mail::to($ga->email)->send(
                new \App\Mail\RelokasiMail(
                    $getRelokasi,
                    $detailBarang,
                    2 // PSB
                )
            );
        }

        $psbUsers = User::where('is_role', 5)
            ->whereNotNull('email')
            ->get();

        foreach ($psbUsers as $psb) {
            Mail::to($psb->email)->send(
                new \App\Mail\RelokasiMail(
                    $getRelokasi,
                    $detailBarang,

                    5 // PSB
                )
            );
        }
        return redirect()->route('admin.relokasi')->with('success', 'Work order berhasil diterbitkan.');
    }
    public function relokasiShow($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // $progressList = SurveyProgress::where('work_order_survey_id', $id)->get();
        $progressList = RelokasiProgress::where('work_order_relokasi_id', $id)->get();

        // Menampilkan detail work order
        $getRelokasi = WorkOrderRelokasi::with('WorkOrderRelokasiDetail.stockBarang')->findOrFail($id);

        $beritaAcara = $getRelokasi->beritaAcara;        // Gabungkan data survey ke dalam data role

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('beritaAcara', 'progressList', 'getRelokasi', 'notifications'));

        // Render view berdasarkan role
        return $this->renderView('relokasi_show', $data);
    }
    public function relokasiEdit($id)
    {
        // Ambil data work order berdasarkan ID
        $workOrder = WorkOrderRelokasi::findOrFail($id);

        if ($workOrder->status === 'Completed') {
            return redirect()->back()->with('error', 'Status Relokasi sudah selesai, tidak bisa diedit.');
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
        $WorkOrderRelokasiDetail = []; // Kosongkan keranjang
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // Ambil data lain yang dibutuhkan, misalnya online billing
        $onlineBilling = $workOrder->onlineBilling;

        $data = array_merge($this->ambilDataRole(), compact('WorkOrderRelokasiDetail', 'stockBarangs', 'jenisList', 'workOrder', 'onlineBilling', 'notifications'));
        // Kirim data ke view
        return view('admin.relokasi_edit', $data);
    }
    public function relokasiUpdate(Request $request, $id)
    {
        // Validasi request
        $validatedData = $request->validate([
            'online_billing_id' => 'required|exists:online_billings,id',
            'alamat_pemasangan_baru' => 'required|string',
            'keterangan' => 'nullable|string',
            'cart' => 'nullable|array',
            'non_stock' => 'nullable|string',
            'attachments.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:5120', // validasi file
            'tanggal_rfs' => 'nullable|date',

            // tambahkan validasi lain sesuai kebutuhan
        ]);

        // Ambil data work order berdasarkan ID
        $workOrder = WorkOrderRelokasi::findOrFail($id);
        // Upload file baru dan reset file lama
        $uploadedFiles = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {

                // Simpan file dengan nama asli (sesuai input user)
                $path = $file->storeAs(
                    'attachments/relokasi',
                    $file->getClientOriginalName(),
                    'public'
                );

                $uploadedFiles[] = $path;
            }
        }
        $workOrder->update([
            'online_billing_id' => $validatedData['online_billing_id'],
            'alamat_pemasangan_baru' => $validatedData['alamat_pemasangan_baru'],
            'keterangan' => $validatedData['keterangan'],
            'non_stock' => $validatedData['non_stock'],
            'attachments' => $uploadedFiles,
            'tanggal_rfs' => $validatedData['tanggal_rfs'],

        ]);
        LogActivity::add('Relokasi', $workOrder->onlineBilling->nama_site, 'edit');

        // Hapus detail barang yang ada sebelumnya
        WorkOrderRelokasiDetail::where('work_order_relokasi_id', $workOrder->id)->delete();

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

                            WorkOrderRelokasiDetail::create([
                                'work_order_relokasi_id' => $workOrder->id,
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
        return redirect()->route('admin.relokasi')->with('success', 'Work order berhasil diperbarui.');
    }
    public function relokasiDestroy($id)
    {
        // Menghapus work order
        $getRelokasi = WorkOrderRelokasi::findOrFail($id);
        if ($getRelokasi->status !== 'Completed') {
            $getRelokasi->delete();
            LogActivity::add('Relokasi', $getRelokasi->onlineBilling->nama_site, 'delete');

            return redirect()->back()->with('success', 'Relokasi berhasil diHapus.');
        }

        return redirect()->back()->with('error', 'Relokasi sudah selesai dan tidak bisa dihapus.');
    }

    public function relokasiCancel($id)
    {
        // Cari Work Order Upgrade berdasarkan ID
        $getRelokasi = WorkOrderRelokasi::findOrFail($id);

        // Jika status sudah "Completed", tidak bisa dibatalkan
        if ($getRelokasi->status === 'Completed') {
            return redirect()->back()->with('error', 'Relokasi sudah selesai dan tidak bisa dibatalkan.');
        }

        // Jika belum "Completed", ubah status menjadi "Canceled"
        $getRelokasi->status = 'Canceled';
        $getRelokasi->save();
        LogActivity::add('Relokasi', $getRelokasi->onlineBilling->nama_site, 'cancel');

        // Cari entri terkait di tabel statuses
        $status = Status::where('work_orderable_id', $getRelokasi->id)
            ->where('process', 'Relokasi') // Pastikan prosesnya adalah 'Upgrade'
            ->first();

        // Perbarui status di tabel statuses jika entri ditemukan
        if ($status) {
            $status->status = 'Canceled'; // Sesuaikan dengan status baru
            $status->save();
        }

        // Berikan notifikasi sukses
        return redirect()->back()->with('success', 'Relokasi berhasil dibatalkan.');
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
    public function showgantivendor($id)
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

    public function approvegantivendor($id)
    {
        $getGantivendor = WorkOrderGantiVendor::findOrFail($id);

        // ✅ Update status & simpan user yang ACC
        $getGantivendor->update([
            'status' => 'On Progress',
            'approved_by' => Auth::id(), // user yang ACC
        ]);
        $getGantivendor->save();
        // Cari entri terkait di tabel statuses
        $status = Status::where('work_orderable_id', $getGantivendor->id)
            ->where('process', 'Ganti Vendor') // Pastikan prosesnya adalah 'upgrade'
            ->first();

        // Perbarui status jika entri ditemukan
        if ($status) {
            $status->status = 'On Progress'; // Sesuaikan dengan status baru
            $status->save();
        }
        // Dapatkan semua pengguna dengan role PSB (misalnya role 5)
        $psbUsers = User::where('is_role', 5)->get();
        // Buat notifikasi untuk setiap pengguna General Affair
        $nocUsers = User::where('is_role', 4)->get();
        // Buat notifikasi untuk setiap pengguna PSB
        foreach ($psbUsers as $psbUser) {
            $url = route('psb.gantivendor.show', ['id' => $getGantivendor->id]) . '#gantivendor';

            Notification::create([
                'user_id' => $psbUser->id,
                'message' => 'WO Ganti Vendor baru telah diterbitkan dengan No Order: ' . $getGantivendor->no_spk,
                'url' => $url, // URL dengan hash #instalasi
            ]);
        }
        foreach ($nocUsers as $nocUsers) {
            $url = route('noc.gantivendor.show', ['id' => $getGantivendor->id]) . '#gantivendor';

            Notification::create([
                'user_id' => $nocUsers->id,
                'message' => 'WO Ganti Vendor baru telah diterbitkan dengan No Order: ' . $getGantivendor->no_spk,
                'url' => $url, // URL dengan hash #instalasi
            ]);
        }
        // Redirect ke halaman sebelumnya dengan pesan sukses
        return redirect()->route('admin.gantivendor.show', $id)->with('success', 'Ganti Vendor telah disetujui.');
    }
    public function rejectgantivendor($id)
    {
        $getGantivendor = WorkOrderGantiVendor::findOrFail($id);

        // Ubah status menjadi 'rejected'
        $getGantivendor->status = 'Rejected';
        $getGantivendor->save();

        $status = Status::where('work_orderable_id', $getGantivendor->id)
            ->where('process', 'Ganti Vendor') // Pastikan prosesnya adalah 'upgrade'
            ->first();

        // Perbarui status jika entri ditemukan
        if ($status) {
            $status->status = 'Rejected'; // Sesuaikan dengan status baru
            $status->save();
        }

        // Redirect ke halaman sebelumnya dengan pesan sukses

        return redirect()->route('admin.gantivendor.show', $id)->with('error', 'Ganti Vendor telah ditolak.');
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
            'foto.*' => 'nullable|image|max:2048', // Validasi untuk banyak file
        ]);

        // Menyimpan progress baru
        $progress = new GantiVendorProgress();
        $progress->work_order_ganti_vendor_id = $id;
        $progress->keterangan = $request->keterangan;
        $progress->status = 'Rejected'; // Tetapkan status ke Rejected
        $progress->psb_id = Auth::id(); // ID admin yang melakukan penolakan
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

        return redirect()->route('admin.gantivendor.show', $id)->with('success', 'Reason berhasil ditambahkan.');
    }

    public function inputvendor($id)
    {
        $vendors = Vendor::orderBy('nama_vendor', 'asc')->get(); // Ambil semua Vendor
        $getGantivendor = WorkOrderGantiVendor::findOrFail($id);

        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();


        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('notifications', 'vendors', 'getGantivendor'));

        // Menampilkan form untuk membuat work order baru dengan nomor SPK baru
        return $this->renderView('inputvendor', $data);
    }

    public function storeinputvendor(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id', // Vendor harus valid dan ada di tabel vendors
        ]);

        // Temukan Work Order berdasarkan ID
        $getGantivendor = WorkOrderGantiVendor::findOrFail($id);


        // Update kolom vendor_baru dengan nama vendor yang dipilih
        $getGantivendor->vendor_id = $request->vendor_id;
        $getGantivendor->save();

        // Redirect dengan pesan sukses
        return redirect()->route('admin.gantivendor.show', $id)->with('success', 'Nama Vendor berhasil disimpan.');
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
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // Ambil data online billing berdasarkan ID
        $onlinebilling = OnlineBilling::findOrFail($id);

        // Ambil status yang terkait dengan online_billing_id tertentu
        $statuses = Status::where('online_billing_id', $id)->get(); // Menambahkan filter berdasarkan online_billing_id

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('statuses', 'onlinebilling', 'notifications'));

        return $this->renderView('OB_show', $data);
    }

    public function editOB($id)
    {
        // Ambil data pelanggan dan instansi dari database
        $pelanggans = Pelanggan::orderBy('nama_pelanggan', 'asc')->get();
        $instansis = Instansi::orderBy('nama_instansi', 'asc')->get(); // Ambil semua instansi
        $vendors = Vendor::orderBy('nama_vendor', 'asc')->get(); // Ambil semua Vendor
        // Menampilkan form untuk mengedit work order
        $getOB = OnlineBilling::with('admin')->findOrFail($id);
        // Periksa status, jika completed, kembali dengan error

        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();


        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('getOB', 'notifications', 'pelanggans', 'instansis', 'vendors'));

        // Render view berdasarkan role
        return $this->renderView('OB_edit', $data);
    }



    public function updateOB(Request $request, $id)
    {
        // Validasi request
        $validatedData = $request->validate([
            'pelanggan_id' => 'required|exists:pelanggans,id',
            'vendor_id' => 'required|exists:vendors,id',
            'instansi_id' => 'required|exists:instansis,id',
            'nama_site' => 'required|string',
            'alamat_pemasangan' => 'required|string',
            'nama_pic' => 'nullable|string',
            'no_pic' => 'nullable|string',
            'layanan' => 'required|string',
            'media' => 'required|string',
            'bandwidth' => 'required|string',
            'provinsi' => 'required|string',
            'satuan' => 'required|string',
            'nni' => 'nullable|string',
            'vlan' => 'nullable|string',
            'no_jaringan' => 'nullable|string',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_akhir' => 'nullable|date',
            'durasi' => 'nullable|integer',
            'sid_vendor' => 'nullable|string',
            'nama_durasi' => 'nullable|string',
            'harga_sewa_hidden' => 'required|integer',
            'status' => 'required|in:active,dismantle',
        ]);

        // Temukan data Work Order Install
        $workOrder = OnlineBilling::findOrFail($id);

        // Update foto jika ada
        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($workOrder->foto) {
                Storage::delete('public/surveys/' . $workOrder->foto);
            }

            // Upload foto baru
            $file = $request->file('foto');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/surveys', $filename);

            $workOrder->foto = $filename;
        }

        // Update data Work Order Install
        $workOrder->update([
            'pelanggan_id' => $validatedData['pelanggan_id'],
            'instansi_id' => $validatedData['instansi_id'],
            'vendor_id' => $validatedData['vendor_id'],
            'nama_site' => $validatedData['nama_site'],
            'alamat_pemasangan' => $validatedData['alamat_pemasangan'],
            'nama_pic' => $validatedData['nama_pic'],
            'no_pic' => $validatedData['no_pic'],
            'layanan' => $validatedData['layanan'],
            'media' => $validatedData['media'],
            'bandwidth' => $validatedData['bandwidth'],
            'satuan' => $validatedData['satuan'],
            'nni' => $validatedData['nni'],
            'provinsi' => $validatedData['provinsi'],
            'vlan' => $validatedData['vlan'],
            'no_jaringan' => $validatedData['no_jaringan'],
            'tanggal_mulai' => $validatedData['tanggal_mulai'],
            'tanggal_akhir' => $validatedData['tanggal_akhir'],
            'durasi' => $validatedData['durasi'],
            'nama_durasi' => $validatedData['nama_durasi'],
            'harga_sewa' => $validatedData['harga_sewa_hidden'],
            'sid_vendor' => $validatedData['sid_vendor'],

        ]);



        return redirect()->route('admin.OB')->with('success', 'Online Billing berhasil diperbarui.');
    }
    public function showMonitoring($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // Ambil data online billing berdasarkan ID
        $onlinebilling = OnlineBilling::findOrFail($id);
        $site = OnlineBilling::findOrFail($id);

        // Ambil status yang terkait dengan online_billing_id tertentu
        $statuses = Status::where('online_billing_id', $id)->get(); // Menambahkan filter berdasarkan online_billing_id

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('site', 'statuses', 'onlinebilling', 'notifications'));

        return $this->renderView('OB_show_monitoring', $data);
    }

    public function updateMonitoring(Request $request, $id)
    {
        $request->validate([
            'cacti_link' => 'required|url'
        ]);

        $site = OnlineBilling::findOrFail($id);
        $site->cacti_link = $request->cacti_link;
        $site->save();

        return back()->with('success', 'Link Cacti berhasil disimpan.');
    }
    public function reaktifasi($id)
    {
        $onlinebilling = OnlineBilling::findOrFail($id);

        // Ubah status menjadi 'active'
        $onlinebilling->status = 'active';
        $onlinebilling->save();

        // Redirect ke halaman sebelumnya dengan pesan sukses

        return redirect()->route('admin.OB')->with('success', 'Reaktifasi Berhasil dilakukan.');
    }
    public function requestbarang(Request $request)
    {
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
                    ->orWhere('alamat_penerima', 'like', '%' . $search . '%') // Pencarian di kolom alamat_penerima
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
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // Gabungkan data request barang ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('notifications', 'requestBarangs', 'status', 'search', 'month', 'year'));

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

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('stockBarangs', 'jenisList', 'notifications'));

        return $this->renderView('requestbarang_create', $data);
    }

    public function storerequest(Request $request)
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

        // Buat request barang baru di `request_barangs`
        $requestBarang = RequestBarang::create([
            'nama_penerima' => $validatedData['nama_penerima'],
            'alamat_penerima' => $validatedData['alamat_penerima'],
            'no_penerima' => $validatedData['no_penerima'],
            'keterangan' => $validatedData['keterangan'],
            'non_stock' => $validatedData['non_stock'],
            'kebutuhan' => $validatedData['kebutuhan'],

            'status' => 'pending',
            'user_id' => Auth::user()->id,
        ]);

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
        // Dapatkan semua pengguna dengan role General Affair (misalnya role 2)
        $gaUsers = User::where('is_role', 2)->get();

        // Buat notifikasi untuk setiap pengguna General Affair
        foreach ($gaUsers as $gaUser) {
            $url = route('ga.request_barang.show', ['id' => $requestBarang->id]) . '#request';

            Notification::create([
                'user_id' => $gaUser->id,
                'message' => 'Request barang baru diajukan oleh Admin: ' . Auth::user()->name,
                'url' => $url, // URL dengan hash #request
            ]);
        }


        return redirect()->route('admin.request_barang')->with('success', 'Request barang berhasil diajukan.');
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

        return redirect()->route('admin.request_barang')->with('success', 'Request barang berhasil diperbarui.');
    }

    public function showrequest($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        $requestBarang = RequestBarang::with('requestBarangDetails.stockBarang')->findOrFail($id);
        $progressList = ReqBarangProgress::where('req_barang_id', $id)->get();

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('requestBarang', 'progressList', 'notifications'));


        return $this->renderView('requestbarang_show', $data);
    }

    public function sitedismantle(Request $request)
    {
        // Ambil parameter dari request
        $status = $request->get('status', 'dismantle'); // Default status ke 'active'
        $search = $request->get('search');
        $month = $request->get('month');
        $year = $request->get('year');

        // Query untuk mendapatkan data survey
        $query = OnlineBilling::orderBy('updated_at', 'desc');

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
    public function progressinstall($id)
    {

        $getSurvey = WorkOrderSurvey::with('admin')->findOrFail($id);

        // Ambil data master
        $pelanggans = Pelanggan::orderBy('nama_pelanggan', 'asc')->get();
        $instansis  = Instansi::orderBy('nama_instansi', 'asc')->get();
        $vendors    = Vendor::orderBy('nama_vendor', 'asc')->get();

        // Jenis barang
        $jenisList = Jenis::select('id', 'nama_jenis')->get();

        // Stok barang
        $stockBarangs = StockBarang::with(['merek', 'tipe', 'jenis'])
            ->selectRaw('tipe_id, merek_id, jenis_id, kualitas, SUM(jumlah) as total_jumlah')
            ->groupBy('tipe_id', 'merek_id', 'jenis_id', 'kualitas')
            ->get();

        // Notifikasi
        $notifications = Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->get();

        /*
    |--------------------------------------------------------------------------
    | Generate No SPK
    |--------------------------------------------------------------------------
    */
        $lastInstall = WorkOrderInstall::orderBy('id', 'desc')->first();

        if ($lastInstall && preg_match('/\/(\d+)$/', $lastInstall->no_spk, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        // Format nomor urut SPK (4 digit)
        $serial = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        // Generate No SPK
        $no_spk = 'PC24Telin/PSB-INSTALLASI/' . now()->format('Y-m-d') . '/' . $serial;

        /*
    |--------------------------------------------------------------------------
    | Preview No Jaringan
    |--------------------------------------------------------------------------
    | Nomor urut mengikuti No SPK
    */
        $no_jaringan = null;

        if ($pelanggans->count() > 0) {
            $firstPelanggan = $pelanggans->first();

            $kodePelanggan = 'C' . str_pad($firstPelanggan->id, 2, '0', STR_PAD_LEFT);
            $periode = now()->format('Y-m');

            // Pakai serial yang sama dengan SPK
            $no_jaringan = $kodePelanggan . '-' . $periode . $serial;
        }

        // Gabungkan data role
        $data = array_merge(
            $this->ambilDataRole(),
            compact(
                'stockBarangs',
                'jenisList',
                'notifications',
                'no_spk',
                'no_jaringan',
                'pelanggans',
                'instansis',
                'vendors',
                'getSurvey'
            )
        );

        return $this->renderView('progress_instalasi', $data);
    }

    public function storeprogressinstall(Request $request, $id)
    {
        // Validasi request
        $validatedData = $request->validate([
            'survey_id' => 'nullable|exists:work_order_surveys,id', // Tambahkan ini
            'no_spk' => 'required|string|unique:work_order_installs',
            'pelanggan_id' => 'required|exists:pelanggans,id',
            'instansi_id' => 'required|exists:instansis,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'nama_site' => 'required|string',
            'alamat_pemasangan' => 'required|string',
            'nama_pic' => 'nullable|string',
            'no_pic' => 'nullable|string',
            'layanan' => 'required|string',
            'provinsi' => 'required|string',
            'media' => 'required|string',
            'bandwidth' => 'required|string',
            'satuan' => 'required|string',
            'nni' => 'nullable|string',
            'vlan' => 'nullable|string',
            'no_jaringan' => 'nullable|string',
            'tanggal_rfs' => 'required|date',
            'durasi' => 'required|integer|min:1',
            'nama_durasi' => 'required|string|in:hari,bulan,tahun',
            'harga_sewa_hidden' => 'required|integer',
            'harga_instalasi_hidden' => 'required|integer',
            'keterangan' => 'nullable|string',
            'non_stock' => 'nullable|string',
            'attachments.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:5120', // Tambahkan ini
            'jenis_pekerjaan' => 'required|in:instalasi,jasa,poc',

            'cart' => 'nullable|array', // Keranjang tidak wajib
            // tambahkan validasi lain sesuai kebutuhan
        ]);

        // TAMBAHKAN DI SINI
        $pelanggan = Pelanggan::findOrFail($validatedData['pelanggan_id']);

        $kodePelanggan = 'C' . str_pad($pelanggan->id, 2, '0', STR_PAD_LEFT);
        $periode = now()->format('Ym');

        // Ambil serial dari no_spk (bagian terakhir setelah slash)
        $serial = last(explode('/', $validatedData['no_spk']));

        // Final no_jaringan
        $noJaringan = $kodePelanggan . '-' . $periode . $serial;
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

        // Ambil nilai harga dari hidden input
        $hargaSewa = $request->input('harga_sewa_hidden');
        $hargaInstalasi = $request->input('harga_instalasi_hidden');
        $getInstall = WorkOrderInstall::create([
            'survey_id' => $id,
            'no_spk' => $validatedData['no_spk'],
            'admin_id' => Auth::user()->id,
            'status' => 'Pending',
            'pelanggan_id' => $validatedData['pelanggan_id'],
            'instansi_id' => $validatedData['instansi_id'],
            'vendor_id' => $validatedData['vendor_id'],
            'nama_site' => $validatedData['nama_site'],
            'alamat_pemasangan' => $validatedData['alamat_pemasangan'],
            'nama_pic' => $validatedData['nama_pic'],
            'no_pic' => $validatedData['no_pic'],
            'layanan' => $validatedData['layanan'],
            'media' => $validatedData['media'],
            'bandwidth' => $validatedData['bandwidth'],
            'satuan' => $validatedData['satuan'],
            'nni' => $validatedData['nni'],
            'provinsi' => $validatedData['provinsi'],
            'vlan' => $validatedData['vlan'],
            'no_jaringan' => $noJaringan,
            'tanggal_rfs' => $validatedData['tanggal_rfs'],
            'durasi' => $validatedData['durasi'],
            'nama_durasi' => $validatedData['nama_durasi'],
            'harga_sewa' => $hargaSewa,  // Simpan harga sewa (angka murni)
            'harga_instalasi' => $hargaInstalasi, // Simpan harga instalasi (angka murni)
            'keterangan' => $validatedData['keterangan'],
            'non_stock' => $validatedData['non_stock'],
            'jenis_pekerjaan' => $validatedData['jenis_pekerjaan'],

        ]);
        LogActivity::add(
            'Instalasi',
            $getInstall->nama_site
        );


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

                            WorkOrderInstallDetail::create([
                                'work_order_install_id' => $getInstall->id,
                                'stock_barang_id' => $stockBarang?->id, // Gunakan null-safe operator untuk menghindari error
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
        $uploadedFiles = [];

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {

                // Simpan file dengan nama asli (sesuai input user)
                $path = $file->storeAs(
                    'attachments/instalasi',
                    $file->getClientOriginalName(),
                    'public'
                );

                $uploadedFiles[] = $path;
            }
        }


        // Simpan path file ke kolom JSON attachments
        $getInstall->attachments = $uploadedFiles;
        $getInstall->save();
        // Dapatkan semua pengguna dengan role General Affair (misalnya role 2)
        $gaUsers = User::where('is_role', 2)->get();
        // Dapatkan semua pengguna dengan role PSB (misalnya role 5)
        $psbUsers = User::where('is_role', 5)->get();
        // Buat notifikasi untuk setiap pengguna General Affair
        foreach ($gaUsers as $gaUser) {
            $url = route('ga.instalasi.show', ['id' => $getInstall->id]) . '#instalasi';

            Notification::create([
                'user_id' => $gaUser->id,
                'message' => 'WO Instalasi baru telah diterbitkan dengan No Order: ' . $getInstall->no_spk,
                'url' => $url, // URL dengan hash #request
            ]);
        }
        // Buat notifikasi untuk setiap pengguna PSB
        foreach ($psbUsers as $psbUser) {
            $url = route('psb.instalasi.show', ['id' => $getInstall->id]) . '#instalasi';

            Notification::create([
                'user_id' => $psbUser->id,
                'message' => 'WO Instalasi baru telah diterbitkan dengan No Order: ' . $getInstall->no_spk,
                'url' => $url, // URL dengan hash #instalasi
            ]);
        }

        $detailBarang = WorkOrderInstallDetail::where('work_order_install_id', $getInstall->id)->get();


        $mailClass = match ($getInstall->jenis_pekerjaan) {
            'instalasi' => \App\Mail\InstalasiMail::class,
            'jasa'      => \App\Mail\JasaMail::class,
            'poc'       => \App\Mail\POCMail::class,
        };

        foreach ($psbUsers as $psb) {
            Mail::to($psb->email)->send(
                new $mailClass($getInstall, $detailBarang, 5)
            );
        }
        foreach ($gaUsers as $ga) {
            Mail::to($ga->email)->send(
                new $mailClass($getInstall, $detailBarang, 2)
            );
        }

        $route = match ($getInstall->jenis_pekerjaan) {
            'instalasi' => 'admin.instalasi',
            'jasa'      => 'admin.jasa',
            'poc'       => 'admin.poc',
        };

        return redirect()
            ->route($route)
            ->with('success', 'Work order berhasil diterbitkan.');
    }
    public function exportWoSurvey()
    {
        return Excel::download(new WorkOrderSurveyExport, ' Laporan WorkOrderSurvey.xlsx');
    }
    public function exportWoInstall()
    {
        return Excel::download(new WorkOrderInstallExport, ' Laporan WorkOrderInstalasi.xlsx');
    }

    public function exportWoUpgrade()
    {
        return Excel::download(new WorkOrderUpgradeExport, ' Laporan WorkOrderUpgrade.xlsx');
    }
    public function exportWoDowngrade()
    {
        return Excel::download(new WorkOrderDowngradeExport, ' Laporan WorkOrderDowngrade.xlsx');
    }
    public function exportWoDismantle()
    {
        return Excel::download(new WorkOrderDismantleExport, ' Laporan WorkOrderDismantle.xlsx');
    }
    public function exportWoRelokasi()
    {
        return Excel::download(new WorkOrderRelokasiExport, ' Laporan WorkOrderRelokasi.xlsx');
    }

    public function exportWoGantiVendor()
    {
        return Excel::download(new WorkOrderGantiVendorExport, ' Laporan WorkOrderGantiVendor.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls|max:2048',
        ]);

        try {
            Excel::import(new OnlineBillingImport, $request->file('file'));
            return redirect()->back()->with('success', 'Berhasil mengimpor data!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengimpor: ' . $e->getMessage());
        }
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
        // Cek apakah sudah ada di tabel online_billing
        $billingExists = OnlineBilling::where('work_order_install_id', $getInstall->id)->exists();
        // Mendapatkan berita acara yang terkait dengan work order ini
        $beritaAcara = $getInstall->beritaAcara;        // Gabungkan data survey ke dalam data role
        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('billingExists', 'beritaAcara', 'progressList', 'getInstall', 'notifications'));

        // Render view berdasarkan role
        return $this->renderView('wo_jasa_show', $data);
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
        // Cek apakah sudah ada di tabel online_billing
        $billingExists = OnlineBilling::where('work_order_install_id', $getInstall->id)->exists();
        // Mendapatkan berita acara yang terkait dengan work order ini
        $beritaAcara = $getInstall->beritaAcara;        // Gabungkan data survey ke dalam data role
        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('billingExists', 'beritaAcara', 'progressList', 'getInstall', 'notifications'));

        // Render view berdasarkan role
        return $this->renderView('wo_poc_show', $data);
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
            'foto.*' => 'nullable|file|mimetypes:image/jpeg,image/png,application/pdf|max:10240',
        ]);

        // Menyimpan progress baru
        $progress = new InstallProgress();
        $progress->work_order_install_id = $id;
        $progress->keterangan = $request->keterangan;

        // Ambil data survey
        $getInstall = WorkOrderInstall::findOrFail($id);

        // Set status default atau complete sesuai tombol yang ditekan
        if ($request->has('action') && $request->action === 'complete') {
            $progress->status = 'Completed'; // Ubah status progress jadi Completed

            //Ubah status survey menjadi Completed
            $getInstall->status = 'Completed';
            $getInstall->save();

            // // Dapatkan semua admin (atau role yang sesuai)
            // $adminUsers = User::where('is_role', 1)->get(); // 1 adalah role untuk admin

            // // Buat notifikasi "Survey Completed" untuk setiap admin
            // foreach ($adminUsers as $admin) {
            //     // Cek role pengguna
            //     if ($admin->is_role == 1) { // Role PSB
            //         $url = route('admin.wo_instalasi_show', ['id' => $getInstall->id]) . '#instalasi'; // Tambahkan #no_spk untuk PSB
            //     } else if ($admin->is_role == 5) { // Role Admin
            //         $url = route('psb.instalasi.show', ['id' => $getInstall->id]) . '#instalasi'; // Tambahkan #no_spk untuk Admin
            //     }

            //     // Buat notifikasi
            //     Notification::create([
            //         'user_id' => $admin->id,
            //         'message' => 'WO Instalasi telah diselesaikan dengan No Order: ' . $getInstall->no_spk,
            //         'url' => $url, // URL dengan hash #no_spk
            //     ]);
            // }
        } else {
            $progress->status = 'On Progress'; // Default status progress jika belum complete
            // // Update status di tabel WorkOrderInstall
            // if ($getInstall->status !== 'Completed') { // Hanya jika status belum Completed
            //     $getInstall->status = 'On Progress';
            //     $getInstall->save();
            // }
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

        // Redirect ke view survey atau detail survey berdasarkan aksi
        if ($request->action === 'complete') {
            return redirect()->route('admin.wo_instalasi_show', $id)->with('success', 'Instalasi berhasil diselesaikan.');
        }

        return redirect()->route('admin.wo_instalasi_show', $id)->with('success', 'Progress berhasil ditambahkan.');
    }

    public function addProgressSurvey($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        $getSurvey = WorkOrderSurvey::findOrFail($id);
        $data = array_merge($this->ambilDataRole(), compact('getSurvey', 'notifications'));

        return $this->renderView('wo_survey_add', $data);
    }


    public function storeProgressSurvey(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'keterangan' => 'required',
            'foto.*' => 'nullable|file|mimetypes:image/jpeg,image/png,application/pdf|max:10240',
        ]);

        // Menyimpan progress baru
        $progress = new SurveyProgress();
        $progress->work_order_survey_id = $id;
        $progress->keterangan = $request->keterangan;

        // Ambil data survey
        $getSurvey = WorkOrderSurvey::findOrFail($id);

        // Set status default atau complete sesuai tombol yang ditekan
        if ($request->has('action') && $request->action === 'complete') {
            $progress->status = 'Completed'; // Ubah status progress jadi Completed

            //Ubah status survey menjadi Completed
            $getSurvey->status = 'Completed';
            $getSurvey->save();

            // // Dapatkan semua admin (atau role yang sesuai)
            // $adminUsers = User::where('is_role', 1)->get(); // 1 adalah role untuk admin

            // // Buat notifikasi "Survey Completed" untuk setiap admin
            // foreach ($adminUsers as $admin) {
            //     // Cek role pengguna
            //     if ($admin->is_role == 1) { // Role PSB
            //         $url = route('admin.wo_instalasi_show', ['id' => $getInstall->id]) . '#instalasi'; // Tambahkan #no_spk untuk PSB
            //     } else if ($admin->is_role == 5) { // Role Admin
            //         $url = route('psb.instalasi.show', ['id' => $getInstall->id]) . '#instalasi'; // Tambahkan #no_spk untuk Admin
            //     }

            //     // Buat notifikasi
            //     Notification::create([
            //         'user_id' => $admin->id,
            //         'message' => 'WO Instalasi telah diselesaikan dengan No Order: ' . $getInstall->no_spk,
            //         'url' => $url, // URL dengan hash #no_spk
            //     ]);
            // }
        } else {
            $progress->status = 'On Progress'; // Default status progress jika belum complete
            // // Update status di tabel WorkOrderInstall
            // if ($getInstall->status !== 'Completed') { // Hanya jika status belum Completed
            //     $getInstall->status = 'On Progress';
            //     $getInstall->save();
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
                SurveyProgressPhoto::create([
                    'survey_progress_id' => $progress->id,
                    'file_path' => $fileName
                ]);
            }
        }

        // Redirect ke view survey atau detail survey berdasarkan aksi
        if ($request->action === 'complete') {
            return redirect()->route('admin.wo_survey_show', $id)->with('success', 'Instalasi berhasil diselesaikan.');
        }

        return redirect()->route('admin.wo_survey_show', $id)->with('success', 'Progress berhasil ditambahkan.');
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
            'foto.*' => 'nullable|file|mimetypes:image/jpeg,image/png,application/pdf|max:10240',
        ]);

        // Menyimpan progress baru
        $progress = new UpgradeProgress();
        $progress->work_order_upgrade_id = $id;
        $progress->keterangan = $request->keterangan;

        // Ambil data survey
        $getUpgrade = WorkOrderUpgrade::findOrFail($id);

        // Set status default atau complete sesuai tombol yang ditekan
        if ($request->has('action') && $request->action === 'complete') {
            $progress->status = 'Completed'; // Ubah status progress jadi Completed

            //Ubah status survey menjadi Completed
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

            $onlineBilling = $getUpgrade->onlineBilling; // Ambil data online billing terkait
            $onlineBilling->bandwidth = $getUpgrade->bandwidth_baru; // Set bandwidth baru
            $onlineBilling->satuan = $getUpgrade->satuan; // Update satuan jika perlu
            $onlineBilling->save(); // Simpan perubahan
            // // Dapatkan semua admin (atau role yang sesuai)
            // $adminUsers = User::where('is_role', 1)->get(); // 1 adalah role untuk admin

            // // Buat notifikasi "Survey Completed" untuk setiap admin
            // foreach ($adminUsers as $admin) {
            //     // Cek role pengguna
            //     if ($admin->is_role == 1) { // Role PSB
            //         $url = route('admin.wo_instalasi_show', ['id' => $getInstall->id]) . '#instalasi'; // Tambahkan #no_spk untuk PSB
            //     } else if ($admin->is_role == 5) { // Role Admin
            //         $url = route('psb.instalasi.show', ['id' => $getInstall->id]) . '#instalasi'; // Tambahkan #no_spk untuk Admin
            //     }

            //     // Buat notifikasi
            //     Notification::create([
            //         'user_id' => $admin->id,
            //         'message' => 'WO Instalasi telah diselesaikan dengan No Order: ' . $getInstall->no_spk,
            //         'url' => $url, // URL dengan hash #no_spk
            //     ]);
            // }
        } else {
            $progress->status = 'On Progress'; // Default status progress jika belum complete
            // // Update status di tabel WorkOrderInstall
            // if ($getInstall->status !== 'Completed') { // Hanya jika status belum Completed
            //     $getInstall->status = 'On Progress';
            //     $getInstall->save();
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
                UpgradeProgressPhoto::create([
                    'upgrade_progress_id' => $progress->id,
                    'file_path' => $fileName
                ]);
            }
        }

        // Redirect ke view survey atau detail survey berdasarkan aksi
        if ($request->action === 'complete') {
            return redirect()->route('admin.upgrade_show', $id)->with('success', 'Instalasi berhasil diselesaikan.');
        }

        return redirect()->route('admin.upgrade_show', $id)->with('success', 'Progress berhasil ditambahkan.');
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
            'foto.*' => 'nullable|file|mimetypes:image/jpeg,image/png,application/pdf|max:10240',
        ]);

        // Menyimpan progress baru
        $progress = new DowngradeProgress();
        $progress->work_order_downgrade_id = $id;
        $progress->keterangan = $request->keterangan;

        // Ambil data survey
        $getDowngrade = WorkOrderDowngrade::findOrFail($id);

        // Set status default atau complete sesuai tombol yang ditekan
        if ($request->has('action') && $request->action === 'complete') {
            $progress->status = 'Completed'; // Ubah status progress jadi Completed

            //Ubah status survey menjadi Completed
            $getDowngrade->status = 'Completed';
            $getDowngrade->save();
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
            // // Dapatkan semua admin (atau role yang sesuai)
            // $adminUsers = User::where('is_role', 1)->get(); // 1 adalah role untuk admin

            // // Buat notifikasi "Survey Completed" untuk setiap admin
            // foreach ($adminUsers as $admin) {
            //     // Cek role pengguna
            //     if ($admin->is_role == 1) { // Role PSB
            //         $url = route('admin.wo_instalasi_show', ['id' => $getInstall->id]) . '#instalasi'; // Tambahkan #no_spk untuk PSB
            //     } else if ($admin->is_role == 5) { // Role Admin
            //         $url = route('psb.instalasi.show', ['id' => $getInstall->id]) . '#instalasi'; // Tambahkan #no_spk untuk Admin
            //     }

            //     // Buat notifikasi
            //     Notification::create([
            //         'user_id' => $admin->id,
            //         'message' => 'WO Instalasi telah diselesaikan dengan No Order: ' . $getInstall->no_spk,
            //         'url' => $url, // URL dengan hash #no_spk
            //     ]);
            // }
        } else {
            $progress->status = 'On Progress'; // Default status progress jika belum complete
            // // Update status di tabel WorkOrderInstall
            // if ($getInstall->status !== 'Completed') { // Hanya jika status belum Completed
            //     $getInstall->status = 'On Progress';
            //     $getInstall->save();
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
                DowngradeProgressPhoto::create([
                    'downgrade_progress_id' => $progress->id,
                    'file_path' => $fileName
                ]);
            }
        }

        // Redirect ke view survey atau detail survey berdasarkan aksi
        if ($request->action === 'complete') {
            return redirect()->route('admin.downgrade_show', $id)->with('success', 'Instalasi berhasil diselesaikan.');
        }

        return redirect()->route('admin.downgrade_show', $id)->with('success', 'Progress berhasil ditambahkan.');
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
            'foto.*' => 'nullable|file|mimetypes:image/jpeg,image/png,application/pdf|max:10240',
        ]);

        // Menyimpan progress baru
        $progress = new RelokasiProgress();
        $progress->work_order_relokasi_id = $id;
        $progress->keterangan = $request->keterangan;

        // Ambil data survey
        $getRelokasi = WorkOrderRelokasi::findOrFail($id);

        // Set status default atau complete sesuai tombol yang ditekan
        if ($request->has('action') && $request->action === 'complete') {
            $progress->status = 'Completed'; // Ubah status progress jadi Completed

            //Ubah status survey menjadi Completed
            $getRelokasi->status = 'Completed';
            $getRelokasi->save();
            $status = Status::where('work_orderable_id', $getRelokasi->id)
                ->where('process', 'Relokasi')
                ->first();
            if ($status) {
                $status->status = 'Completed';
                $status->save();
            }

            // Update bandwidth lama dengan bandwidth baru di tabel online_billings
            $onlineBilling = $getRelokasi->onlineBilling; // Ambil data online billing terkait
            $onlineBilling->alamat_pemasangan = $getRelokasi->alamat_pemasangan_baru; // Set bandwidth baru
            $onlineBilling->save(); // Simpan perubahan

            // // Dapatkan semua admin (atau role yang sesuai)
            // $adminUsers = User::where('is_role', 1)->get(); // 1 adalah role untuk admin

            // // Buat notifikasi "Survey Completed" untuk setiap admin
            // foreach ($adminUsers as $admin) {
            //     // Cek role pengguna
            //     if ($admin->is_role == 1) { // Role PSB
            //         $url = route('admin.wo_instalasi_show', ['id' => $getInstall->id]) . '#instalasi'; // Tambahkan #no_spk untuk PSB
            //     } else if ($admin->is_role == 5) { // Role Admin
            //         $url = route('psb.instalasi.show', ['id' => $getInstall->id]) . '#instalasi'; // Tambahkan #no_spk untuk Admin
            //     }

            //     // Buat notifikasi
            //     Notification::create([
            //         'user_id' => $admin->id,
            //         'message' => 'WO Instalasi telah diselesaikan dengan No Order: ' . $getInstall->no_spk,
            //         'url' => $url, // URL dengan hash #no_spk
            //     ]);
            // }
        } else {
            $progress->status = 'On Progress'; // Default status progress jika belum complete
            // // Update status di tabel WorkOrderInstall
            // if ($getInstall->status !== 'Completed') { // Hanya jika status belum Completed
            //     $getInstall->status = 'On Progress';
            //     $getInstall->save();
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
                RelokasiProgressPhoto::create([
                    'relokasi_progress_id' => $progress->id,
                    'file_path' => $fileName
                ]);
            }
        }

        // Redirect ke view survey atau detail survey berdasarkan aksi
        if ($request->action === 'complete') {
            return redirect()->route('admin.relokasi_show', $id)->with('success', 'Relokasi berhasil diselesaikan.');
        }

        return redirect()->route('admin.relokasi_show', $id)->with('success', 'Progress berhasil ditambahkan.');
    }

    public function addProgressDismantle($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        $getDismantle = WorkOrderDismantle::findOrFail($id);
        $data = array_merge($this->ambilDataRole(), compact('getDismantle', 'notifications'));

        return $this->renderView('wo_dismantle_add', $data);
    }


    public function storeProgressDismantle(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'keterangan' => 'required',
            'foto.*' => 'nullable|file|mimetypes:image/jpeg,image/png,application/pdf|max:10240',
        ]);

        // Menyimpan progress baru
        $progress = new DismantleProgress();
        $progress->work_order_dismantle_id = $id;
        $progress->keterangan = $request->keterangan;

        // Ambil data survey
        $getDismantle = WorkOrderDismantle::findOrFail($id);

        // Set status default atau complete sesuai tombol yang ditekan
        if ($request->has('action') && $request->action === 'complete') {
            $progress->status = 'Completed'; // Ubah status progress jadi Completed

            //Ubah status survey menjadi Completed
            $getDismantle->status = 'Completed';
            $getDismantle->save();
            $status = Status::where('work_orderable_id', $getDismantle->id)
                ->where('process', 'Dismantle')
                ->first();
            if ($status) {
                $status->status = 'Completed';
                $status->save();
            }
            // Update status online billing menjadi dismantle
            $onlineBilling = $getDismantle->onlineBilling;
            $onlineBilling->update(['status' => 'dismantle']);

            // // Dapatkan semua admin (atau role yang sesuai)
            // $adminUsers = User::where('is_role', 1)->get(); // 1 adalah role untuk admin

            // // Buat notifikasi "Survey Completed" untuk setiap admin
            // foreach ($adminUsers as $admin) {
            //     // Cek role pengguna
            //     if ($admin->is_role == 1) { // Role PSB
            //         $url = route('admin.wo_instalasi_show', ['id' => $getInstall->id]) . '#instalasi'; // Tambahkan #no_spk untuk PSB
            //     } else if ($admin->is_role == 5) { // Role Admin
            //         $url = route('psb.instalasi.show', ['id' => $getInstall->id]) . '#instalasi'; // Tambahkan #no_spk untuk Admin
            //     }

            //     // Buat notifikasi
            //     Notification::create([
            //         'user_id' => $admin->id,
            //         'message' => 'WO Instalasi telah diselesaikan dengan No Order: ' . $getInstall->no_spk,
            //         'url' => $url, // URL dengan hash #no_spk
            //     ]);
            // }
        } else {
            $progress->status = 'On Progress'; // Default status progress jika belum complete
            // // Update status di tabel WorkOrderInstall
            // if ($getInstall->status !== 'Completed') { // Hanya jika status belum Completed
            //     $getInstall->status = 'On Progress';
            //     $getInstall->save();
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
                DismantleProgressPhoto::create([
                    'dismantle_progress_id' => $progress->id,
                    'file_path' => $fileName
                ]);
            }
        }

        // Redirect ke view survey atau detail survey berdasarkan aksi
        if ($request->action === 'complete') {
            return redirect()->route('admin.dismantle', $id)->with('success', 'Dismantle berhasil diselesaikan dan status pelanggan diubah menjadi dismantle.');
        }

        return redirect()->route('admin.dismantle_show', $id)->with('success', 'Progress berhasil ditambahkan.');
    }

    public function addProgressJasa($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        $getInstall = WorkOrderInstall::findOrFail($id);
        $data = array_merge($this->ambilDataRole(), compact('getInstall', 'notifications'));

        return $this->renderView('wo_jasa_add', $data);
    }


    public function storeProgressJasa(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'keterangan' => 'required',
            'foto.*' => 'nullable|file|mimetypes:image/jpeg,image/png,application/pdf|max:10240',
        ]);

        // Menyimpan progress baru
        $progress = new InstallProgress();
        $progress->work_order_install_id = $id;
        $progress->keterangan = $request->keterangan;

        // Ambil data survey
        $getInstall = WorkOrderInstall::findOrFail($id);

        // Set status default atau complete sesuai tombol yang ditekan
        if ($request->has('action') && $request->action === 'complete') {
            $progress->status = 'Completed'; // Ubah status progress jadi Completed

            //Ubah status survey menjadi Completed
            $getInstall->status = 'Completed';
            $getInstall->save();



            // // Dapatkan semua admin (atau role yang sesuai)
            // $adminUsers = User::where('is_role', 1)->get(); // 1 adalah role untuk admin

            // // Buat notifikasi "Survey Completed" untuk setiap admin
            // foreach ($adminUsers as $admin) {
            //     // Cek role pengguna
            //     if ($admin->is_role == 1) { // Role PSB
            //         $url = route('admin.wo_instalasi_show', ['id' => $getInstall->id]) . '#instalasi'; // Tambahkan #no_spk untuk PSB
            //     } else if ($admin->is_role == 5) { // Role Admin
            //         $url = route('psb.instalasi.show', ['id' => $getInstall->id]) . '#instalasi'; // Tambahkan #no_spk untuk Admin
            //     }

            //     // Buat notifikasi
            //     Notification::create([
            //         'user_id' => $admin->id,
            //         'message' => 'WO Instalasi telah diselesaikan dengan No Order: ' . $getInstall->no_spk,
            //         'url' => $url, // URL dengan hash #no_spk
            //     ]);
            // }
        } else {
            $progress->status = 'On Progress'; // Default status progress jika belum complete
            // // Update status di tabel WorkOrderInstall
            // if ($getInstall->status !== 'Completed') { // Hanya jika status belum Completed
            //     $getInstall->status = 'On Progress';
            //     $getInstall->save();
            // }
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

        // Redirect ke view survey atau detail survey berdasarkan aksi
        if ($request->action === 'complete') {
            return redirect()->route('admin.wo_jasa_show', $id)->with('success', 'Jasa berhasil diselesaikan.');
        }

        return redirect()->route('admin.wo_jasa_show', $id)->with('success', 'Progress berhasil ditambahkan.');
    }
    public function addProgressPoc($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        $getInstall = WorkOrderInstall::findOrFail($id);
        $data = array_merge($this->ambilDataRole(), compact('getInstall', 'notifications'));

        return $this->renderView('wo_poc_add', $data);
    }


    public function storeProgressPoc(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'keterangan' => 'required',
            'foto.*' => 'nullable|file|mimetypes:image/jpeg,image/png,application/pdf|max:10240',
        ]);

        // Menyimpan progress baru
        $progress = new InstallProgress();
        $progress->work_order_install_id = $id;
        $progress->keterangan = $request->keterangan;

        // Ambil data survey
        $getInstall = WorkOrderInstall::findOrFail($id);

        // Set status default atau complete sesuai tombol yang ditekan
        if ($request->has('action') && $request->action === 'complete') {
            $progress->status = 'Completed'; // Ubah status progress jadi Completed

            //Ubah status survey menjadi Completed
            $getInstall->status = 'Completed';
            $getInstall->save();



            // // Dapatkan semua admin (atau role yang sesuai)
            // $adminUsers = User::where('is_role', 1)->get(); // 1 adalah role untuk admin

            // // Buat notifikasi "Survey Completed" untuk setiap admin
            // foreach ($adminUsers as $admin) {
            //     // Cek role pengguna
            //     if ($admin->is_role == 1) { // Role PSB
            //         $url = route('admin.wo_instalasi_show', ['id' => $getInstall->id]) . '#instalasi'; // Tambahkan #no_spk untuk PSB
            //     } else if ($admin->is_role == 5) { // Role Admin
            //         $url = route('psb.instalasi.show', ['id' => $getInstall->id]) . '#instalasi'; // Tambahkan #no_spk untuk Admin
            //     }

            //     // Buat notifikasi
            //     Notification::create([
            //         'user_id' => $admin->id,
            //         'message' => 'WO Instalasi telah diselesaikan dengan No Order: ' . $getInstall->no_spk,
            //         'url' => $url, // URL dengan hash #no_spk
            //     ]);
            // }
        } else {
            $progress->status = 'On Progress'; // Default status progress jika belum complete
            // // Update status di tabel WorkOrderInstall
            // if ($getInstall->status !== 'Completed') { // Hanya jika status belum Completed
            //     $getInstall->status = 'On Progress';
            //     $getInstall->save();
            // }
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

        // Redirect ke view survey atau detail survey berdasarkan aksi
        if ($request->action === 'complete') {
            return redirect()->route('admin.wo_poc_show', $id)->with('success', 'POC berhasil diselesaikan.');
        }

        return redirect()->route('admin.wo_poc_show', $id)->with('success', 'Progress berhasil ditambahkan.');
    }
}
