<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Status;
use App\Models\BeritaAcara;
use App\Models\LogActivity;
use App\Models\StockBarang;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\OnlineBilling;
use App\Models\RequestBarang;
use App\Models\SurveyProgress;
use App\Models\InstallProgress;
use App\Models\UpgradeProgress;
use App\Models\WorkOrderSurvey;
use App\Models\RelokasiProgress;
use App\Models\WorkOrderInstall;
use App\Models\WorkOrderUpgrade;
use App\Models\DismantleProgress;
use App\Models\DowngradeProgress;
use App\Models\ReqBarangProgress;
use App\Models\WorkOrderRelokasi;
use App\Models\WorkOrderDismantle;
use App\Models\WorkOrderDowngrade;
use App\Models\GantiVendorProgress;
use App\Models\MaintenanceProgress;
use App\Models\WorkOrderGantiVendor;
use App\Models\WorkOrderMaintenance;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
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
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        $startOfYear = Carbon::now()->startOfYear();
        $endOfYear = Carbon::now()->endOfYear();

        // Statistik Mingguan
        $instalasiCount = $this->getCompletedWorkOrdersByWeek(WorkOrderInstall::class, $startOfWeek, $endOfWeek);
        $maintenanceCount = $this->getCompletedWorkOrdersByWeek(WorkOrderMaintenance::class, $startOfWeek, $endOfWeek);
        $dismantleCount = $this->getCompletedWorkOrdersByWeek(WorkOrderDismantle::class, $startOfWeek, $endOfWeek);

        // Statistik Bulanan
        $monthlyStats = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyStats['instalasi'][] = WorkOrderInstall::whereMonth('updated_at', $i)
                ->whereYear('updated_at', Carbon::now()->year)
                ->where('status', 'completed')
                ->count();

            $monthlyStats['maintenance'][] = WorkOrderMaintenance::whereMonth('updated_at', $i)
                ->whereYear('updated_at', Carbon::now()->year)
                ->where('status', 'completed')
                ->count();

            $monthlyStats['dismantle'][] = WorkOrderDismantle::whereMonth('updated_at', $i)
                ->whereYear('updated_at', Carbon::now()->year)
                ->where('status', 'completed')
                ->count();
        }

        // Statistik Total (Keseluruhan) dengan nilai default
        $totalInstalasi = WorkOrderInstall::where('status', 'completed')->count() ?? 0;
        $totalMaintenance = WorkOrderMaintenance::where('status', 'completed')->count() ?? 0;
        $totalDismantle = WorkOrderDismantle::where('status', 'completed')->count() ?? 0;

        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->get();

        // Gabungkan semua data
        $data = array_merge(
            $this->ambilDataRole(),
            compact(
                'notifications',
                'instalasiCount',
                'maintenanceCount',
                'dismantleCount',
                'monthlyStats',
                'totalInstalasi',
                'totalMaintenance',
                'totalDismantle'
            )
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
        $getSurvey = $query->paginate(5)->appends([
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

    public function show($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // Gabungkan data survey ke dalam data role
        $progressList = SurveyProgress::where('work_order_survey_id', $id)->get();

        // Menampilkan detail work order
        $getSurvey = WorkOrderSurvey::with('admin')->findOrFail($id);

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('getSurvey', 'progressList', 'notifications'));

        // Render view berdasarkan role
        return $this->renderView('wo_survey_show', $data);
    }

    public function instalasi(Request $request)
    {
        // Ambil parameter dari request
        $status = $request->get('status', 'all');
        $search = $request->get('search');
        $month = $request->get('month');
        $year = $request->get('year');

        // Query untuk mendapatkan data survey
        $query = WorkOrderInstall::orderBy('created_at', 'desc');

        // Filter berdasarkan status
        if ($status != 'all') {
            $query->where('status', $status);
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
        $getInstall = $query->paginate(5)->appends([
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

        // $progressList = SurveyProgress::where('work_order_survey_id', $id)->get();
        $progressList = InstallProgress::where('work_order_install_id', $id)->get();

        // Menampilkan detail work order
        $getInstall = WorkOrderInstall::with('WorkOrderInstallDetail.stockBarang')->findOrFail($id);
        // Cek apakah sudah ada di tabel online_billing
        $billingExists = OnlineBilling::where('work_order_install_id', $getInstall->id)->exists();
        // Mendapatkan berita acara yang terkait dengan work order ini
        $beritaAcaras = BeritaAcara::where('work_order_install_id', $id)->get();
        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('billingExists', 'beritaAcaras', 'progressList', 'getInstall', 'notifications'));

        // Render view berdasarkan role
        return $this->renderView('wo_instalasi_show', $data);
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
                    ->orWhereHas('onlineBilling.pelanggan', function ($q) use ($search) {
                        $q->where('nama_pelanggan', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('onlineBilling.instansi', function ($q) use ($search) {
                        $q->where('nama_instansi', 'like', '%' . $search . '%');
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
        $getUpgrade = $query->paginate(5)->appends([
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
                    ->orWhereHas('onlineBilling.pelanggan', function ($q) use ($search) {
                        $q->where('nama_pelanggan', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('onlineBilling.instansi', function ($q) use ($search) {
                        $q->where('nama_instansi', 'like', '%' . $search . '%');
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
        $getDowngrade = $query->paginate(5)->appends([
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
                    ->orWhereHas('onlineBilling.pelanggan', function ($q) use ($search) {
                        $q->where('nama_pelanggan', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('onlineBilling.instansi', function ($q) use ($search) {
                        $q->where('nama_instansi', 'like', '%' . $search . '%');
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
        $getGantivendor = $query->paginate(5)->appends([
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
                    ->orWhereHas('onlineBilling.pelanggan', function ($q) use ($search) {
                        $q->where('nama_pelanggan', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('onlineBilling.instansi', function ($q) use ($search) {
                        $q->where('nama_instansi', 'like', '%' . $search . '%');
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
        $getDismantle = $query->paginate(5)->appends([
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
        $stockItems = StockBarang::where('dismantle_id', $id)->with(['jenis', 'merek', 'tipe'])->get();

        // Menampilkan detail work order dengan relasi ke onlineBilling dan admin
        $getDismantle = WorkOrderDismantle::with([
            'admin',
            'onlineBilling.pelanggan',
            'onlineBilling.vendor',
            'onlineBilling.instansi'
        ])->findOrFail($id);

        // Gabungkan data ke dalam array data role
        $data = array_merge($this->ambilDataRole(), compact('stockItems', 'progressList', 'getDismantle', 'notifications'));

        // Render view berdasarkan role
        return $this->renderView('dismantle_show', $data);
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
                    ->orWhereHas('onlineBilling.pelanggan', function ($q) use ($search) {
                        $q->where('nama_pelanggan', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('onlineBilling.instansi', function ($q) use ($search) {
                        $q->where('nama_instansi', 'like', '%' . $search . '%');
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
        $getRelokasi = $query->paginate(5)->appends([
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

    public function relokasiShow($id)
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
                    ->orWhereHas('onlineBilling.pelanggan', function ($q) use ($search) {
                        $q->where('nama_pelanggan', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('onlineBilling.instansi', function ($q) use ($search) {
                        $q->where('nama_instansi', 'like', '%' . $search . '%');
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
        $getMaintenance = $query->paginate(5)->appends([
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
                    ->orWhere('alamat_penerima', 'like', '%' . $search . '%'); // Pencarian di kolom alamat_penerima
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
        $requestBarangs = $query->paginate(5)->appends([
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
        $onlinebilling = $query->paginate(5)->appends([
            'status' => $status,
            'search' => $search,
            'month' => $month,
            'year' => $year,
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
        $onlinebilling = $query->paginate(5)->appends([
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

    public function logact(Request $request)
    {
        $logs = LogActivity::with('user')->latest()->get(); // ambil log terbaru dengan relasi user

        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('logs', 'notifications'));

        return $this->renderView('log', $data);
    }
}
