<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Jenis;
use App\Models\Status;
use App\Models\StockBarang;
use App\Models\BarangKeluar;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\OnlineBilling;
use App\Models\RequestBarang;
use App\Models\InstallProgress;
use App\Models\UpgradeProgress;
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
use App\Models\InstallProgressPhoto;
use App\Models\RequestBarangDetails;
use App\Models\UpgradeProgressPhoto;
use App\Models\WorkOrderGantiVendor;
use App\Models\WorkOrderMaintenance;
use Illuminate\Support\Facades\Auth;
use App\Models\RelokasiProgressPhoto;
use App\Models\DowngradeProgressPhoto;
use App\Models\GantiVendorProgressPhoto;
use App\Models\MaintenanceProgressPhoto;

class NocController extends Controller
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
            'foto.*' => 'nullable|image|max:2048', // Validasi untuk banyak file
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
            return redirect()->route('noc.instalasi', $id)->with('success', 'Progress berhasil diselesaikan.');
        }

        return redirect()->route('noc.instalasi.show', $id)->with('success', 'Progress berhasil ditambahkan.');
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
        return redirect()->route('noc.maintenance_show', $id)->with('success', 'Maintenance telah disetujui.');
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
        return redirect()->route('noc.maintenance_show', $id)->with('error', 'Maintenance telah ditolak.');
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
            'foto.*' => 'nullable|image|max:2048', // Validasi untuk banyak file
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

            // Dapatkan semua pengguna dengan role General Affair (misalnya role 2)
            $helpdeskUsers = User::where('is_role', 3)->get();
            // Dapatkan semua pengguna dengan role PSB (misalnya role 5)

            // Buat notifikasi untuk setiap pengguna PSB
            foreach ($helpdeskUsers as $helpdeskUser) {
                $url = route('hd.maintenance_show', ['id' => $getMaintenance->id]) . '#maintenance';

                Notification::create([
                    'user_id' => $helpdeskUser->id,
                    'message' => 'WO Maintenance baru telah diselesaikan dengan No Order: ' . $getMaintenance->no_spk,
                    'url' => $url, // URL dengan hash #instalasi
                ]);
            }
        } else {
            $progress->status = 'On Progress'; // Default status progress jika belum complete
            // Update status di tabel WorkOrderInstall
            if ($getMaintenance->status !== 'Completed') { // Hanya jika status belum Completed
                $getMaintenance->status = 'On Progress';
                $getMaintenance->save();
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
                MaintenanceProgressPhoto::create([
                    'maintenance_progress_id' => $progress->id,
                    'file_path' => $fileName
                ]);
            }
        }

        // Redirect ke view survey atau detail survey berdasarkan aksi
        if ($request->action === 'complete') {
            return redirect()->route('noc.maintenance_show', $id)->with('success', 'Progress berhasil diselesaikan.');
        }

        return redirect()->route('noc.maintenance_show', $id)->with('success', 'Progress berhasil ditambahkan.');
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
        return redirect()->route('noc.upgrade_show', $id)
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
            'foto.*' => 'nullable|image|max:2048', // Validasi untuk banyak file
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
                } else if ($admin->is_role == 4) { // Role Admin
                    $url = route('noc.upgrade_show', ['id' => $getUpgrade->id]) . '#upgrade'; // Tambahkan #no_spk untuk Admin
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
            return redirect()->route('noc.upgrade', $id)->with('success', 'Upgrade berhasil diselesaikan.');
        }

        return redirect()->route('noc.upgrade_show', $id)->with('success', 'Progress berhasil ditambahkan.');
    }
    public function downgrade(Request $request)
    {
        // Ambil parameter dari request
        $status = $request->get('status', 'all');
        $search = $request->get('search');
        $month = $request->get('month');
        $year = $request->get('year');
        $provinsi = $request->get('provinsi'); // Ambil provinsi

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
            'provinsi' => $provinsi

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
        return redirect()->route('noc.downgrade_show', $id)
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
            'foto.*' => 'nullable|image|max:2048', // Validasi untuk banyak file
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
                } else if ($admin->is_role == 4) { // Role Admin
                    $url = route('noc.downgrade_show', ['id' => $getDowngrade->id]) . '#downgrade'; // Tambahkan #no_spk untuk Admin
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
            return redirect()->route('noc.downgrade', $id)->with('success', 'Downgrade berhasil diselesaikan.');
        }

        return redirect()->route('noc.downgrade_show', $id)->with('success', 'Progress berhasil ditambahkan.');
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
        return redirect()->route('noc.dismantle_show', $id)->with('success', 'Eth ke arah Client berhasil di Disable');
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
            'foto.*' => 'nullable|image|max:2048', // Validasi untuk banyak file
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
            return redirect()->route('noc.relokasi.show', $id)->with('success', 'Progress berhasil diselesaikan.');
        }

        return redirect()->route('noc.relokasi.show', $id)->with('success', 'Progress berhasil ditambahkan.');
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

        // Ambil data survey
        $getGantivendor = WorkOrderGantiVendor::findOrFail($id);

        // Set status default atau complete sesuai tombol yang ditekan
        if ($request->has('action') && $request->action === 'complete') {
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
            return redirect()->route('noc.gantivendor.show', $id)->with('success', 'Progress berhasil diselesaikan.');
        }

        return redirect()->route('noc.gantivendor.show', $id)->with('success', 'Progress berhasil ditambahkan.');
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
            'no_penerima' => 'required|string|max:20',
            'keterangan' => 'nullable|string',
            'non_stock' => 'nullable|string',

            'cart' => 'nullable|array',
        ]);

        // Buat request barang baru di `request_barangs`
        $requestBarang = RequestBarang::create([
            'nama_penerima' => $validatedData['nama_penerima'],
            'alamat_penerima' => $validatedData['alamat_penerima'],
            'no_penerima' => $validatedData['no_penerima'],
            'keterangan' => $validatedData['keterangan'],
            'non_stock' => $validatedData['non_stock'],

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
                'message' => 'Request barang baru diajukan oleh NOC: ' . Auth::user()->name,
                'url' => $url, // URL dengan hash #request
            ]);
        }

        return redirect()->route('noc.request_barang')->with('success', 'Request barang berhasil diajukan.');
    }

    public function markAsReadnoc($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->is_read = true;
        $notification->save();

        return redirect()->to($notification->url);
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
            'no_penerima' => 'required|string|max:20',
            'keterangan' => 'nullable|string',
            'non_stock' => 'nullable|string',

            'cart' => 'nullable|array',
        ]);

        // Temukan request barang yang ingin diperbarui
        $requestBarang = RequestBarang::findOrFail($id);
        $requestBarang->update([
            'nama_penerima' => $validatedData['nama_penerima'],
            'alamat_penerima' => $validatedData['alamat_penerima'],
            'no_penerima' => $validatedData['no_penerima'],
            'keterangan' => $validatedData['keterangan'],
            'non_stock' => $validatedData['non_stock'],

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

        return redirect()->route('noc.request_barang')->with('success', 'Request barang berhasil diperbarui.');
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
}

// ambilDataRole: Mengambil data pengguna dan role dengan lebih bersih.
// renderView: Mengatur rendering view berdasarkan role, menghindari pengulangan switch case.
// Nama View: Menggunakan format penamaan view yang lebih dinamis.