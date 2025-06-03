<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Tipe;
use App\Models\User;
use App\Models\Jenis;
use App\Models\Merek;
use App\Models\Status;
use App\Models\StockBarang;
use App\Models\BarangKeluar;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\OnlineBilling;
use App\Models\RequestBarang;
use App\Models\InstallProgress;
use App\Models\RelokasiProgress;
use App\Models\WorkOrderInstall;
use App\Models\DismantleProgress;
use App\Models\ReqBarangProgress;
use App\Models\WorkOrderRelokasi;
use App\Models\WorkOrderDismantle;
use App\Models\MaintenanceProgress;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\InstallProgressPhoto;
use App\Models\RequestBarangDetails;
use App\Models\WorkOrderMaintenance;
use Illuminate\Support\Facades\Auth;
use App\Models\RelokasiProgressPhoto;
use App\Models\RequestBarangProgress;
use App\Models\DismantleProgressPhoto;
use App\Models\ReqBarangProgressPhoto;
use App\Models\MaintenanceProgressPhoto;
use Illuminate\Support\Facades\Validator;
use App\Models\RequestBarangProgressPhoto;

class GaController extends Controller
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

    // Fungsi untuk setiap view
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
        $progressList = InstallProgress::where('work_order_install_id', $id)->get();

        // Menampilkan detail work order
        $getInstall = WorkOrderInstall::with('WorkOrderInstallDetail.stockBarang')->findOrFail($id);
        $stockBarangs = StockBarang::with(['jenis', 'merek', 'tipe'])->get(); // Ambil data stock barang


        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('progressList', 'getInstall', 'notifications', 'stockBarangs'));

        // Render view berdasarkan role
        return $this->renderView('instalasi_show', $data);
    }
    public function approveinstalasi($id)
    {
        $getInstall = WorkOrderInstall::findOrFail($id);

        // Ubah status menjadi 'approved'
        $getInstall->status = 'On Progress';
        $getInstall->save();

        // Redirect ke halaman sebelumnya dengan pesan sukses
        return redirect()->route('ga.instalasi.show', $id)->with('success', 'Instalasi telah disetujui.');
    }
    public function rejectinstalasi($id)
    {
        $getInstall = WorkOrderInstall::findOrFail($id);

        // Ubah status menjadi 'rejected'
        $getInstall->status = 'Rejected';
        $getInstall->save();

        // Redirect ke halaman sebelumnya dengan pesan sukses
        return redirect()->route('ga.instalasi.show', $id)->with('error', 'Instalasi telah ditolak.');
    }
    public function inputBaranginstalasicreate(Request $request, $workOrderId)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();
        $getInstall = WorkOrderInstall::findOrFail($workOrderId);
        // Ambil data keranjang yang ada di session
        $cartItems = session()->get('cart', []);  // Jika tidak ada data di session, defaultkan ke array kosong
        // Ambil data stok barang dengan relasi yang diperlukan
        $search = $request->input('search', ''); // Ambil nilai pencarian dari input

        $stockBarangs = StockBarang::with(['jenis', 'merek', 'tipe'])
            ->when($search, function ($query, $search) {
                return $query->whereHas('jenis', function ($query) use ($search) {
                    $query->where('nama_jenis', 'like', '%' . $search . '%');
                })
                    ->orWhereHas('merek', function ($query) use ($search) {
                        $query->where('nama_merek', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('tipe', function ($query) use ($search) {
                        $query->where('nama_tipe', 'like', '%' . $search . '%');
                    });
            })
            ->orderBy('id', 'desc')
            ->get(); // Mengambil semua data tanpa paginasi

        return $this->renderView('input_barang_instalasi', compact('getInstall', 'stockBarangs', 'search', 'cartItems', 'notifications'));
    }
    public function inputBaranginstalasistore(Request $request, $workOrderId)
    {
        // Jika `cartItems` dikirim sebagai JSON string, decode menjadi array terlebih dahulu
        if (is_string($request->input('cartItems'))) {
            $request->merge(['cartItems' => json_decode($request->input('cartItems'), true)]);
        }

        // Validasi data `cartItems`
        $validated = $request->validate([
            'cartItems' => 'required|array',
            'cartItems.*.id' => 'required|exists:stock_barangs,id', // validasi ID barang dalam stok
            'cartItems.*.jumlah' => 'required|integer|min:1',
            'cartItems.*.serialNumber' => 'nullable|string',
            'cartItems.*.kualitas' => 'required|string|in:baru,bekas',
        ]);

        $cartItems = $validated['cartItems'];

        foreach ($cartItems as $item) {
            $stockBarang = StockBarang::find($item['id']);

            if ($stockBarang && $stockBarang->jumlah >= $item['jumlah']) {
                // Menyimpan entri baru di tabel barang_keluars
                BarangKeluar::create([
                    'work_order_install_id' => $workOrderId, // ID Work Order untuk instalasi
                    'stock_barang_id' => $item['id'],
                    'jumlah' => $item['jumlah'],
                    'serial_number' => $item['serialNumber'],
                    'kualitas' => $item['kualitas'],
                    'user_id' => Auth::user()->id,

                ]);

                // Update stok setelah barang dikirim
                $stockBarang->jumlah -= $item['jumlah'];
                $stockBarang->save();
            } else {
                // Redirect jika stok tidak mencukupi
                return redirect()->back()->with('error', 'Stok tidak mencukupi untuk barang: ' . $item['id']);
            }
        }

        // Kirim notifikasi ke pengguna NA setelah barang berhasil diinput
        $naUsers = User::where('is_role', 6)->get(); // Role NA adalah 4
        foreach ($naUsers as $naUser) {
            $url = route('na.instalasi.show', ['id' => $workOrderId]) . '#instalasi'; // URL menuju halaman konfigurasi NA
            Notification::create([
                'user_id' => $naUser->id,
                'message' => 'Barang untuk Instalasi telah diinput, Silahkan Konfigurasi Perangkat ',
                'url' => $url,
            ]);
        }
        return redirect()->route('ga.instalasi.show', $workOrderId)->with('success', 'Barang berhasil dikirim!');
    }

    public function cancelBaranginstalasi($barangKeluarId)
    {
        // Cari barang yang akan dibatalkan
        $barangKeluar = BarangKeluar::findOrFail($barangKeluarId);
        $getInstall = $barangKeluar->WorkOrderInstall;

        // Cek status permintaan, jika 'completed' maka pembatalan tidak diizinkan
        if ($getInstall->status === 'Completed') {
            return redirect()->back()->withErrors('Barang tidak dapat dibatalkan karena status sudah completed.');
        }

        // Kembalikan jumlah barang ke stok awal
        $stockBarang = $barangKeluar->stockBarang;
        $stockBarang->jumlah += $barangKeluar->jumlah;
        $stockBarang->save();

        // Hapus data barang keluar
        $barangKeluar->delete();



        // Arahkan ke halaman show requestBarang dengan id
        return redirect()->route('ga.instalasi.show', ['id' => $getInstall->id])
            ->with('success', 'Barang berhasil dibatalkan dan stok dikembalikan.');
    }

    public function instalasicreateShipped($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        $getInstall = WorkOrderInstall::findOrFail($id);
        $data = array_merge($this->ambilDataRole(), compact('getInstall', 'notifications'));

        return $this->renderView('wo_instalasi_createshipped', $data);
    }

    public function instalasistoreShipped(Request $request, $id)
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
        $progress->status = 'Shipped'; // Set status langsung menjadi Shipped
        $progress->user_id = Auth::id();
        $progress->save();

        // Ambil data WO Instalasi
        $getInstall = WorkOrderInstall::findOrFail($id);
        $getInstall->status = 'Shipped'; // Update status WO menjadi Shipped
        $getInstall->save();

        // Upload dan simpan banyak foto jika ada
        if ($request->hasFile('foto')) {
            foreach ($request->file('foto') as $foto) {
                $fileName = time() . '_' . $foto->getClientOriginalName();
                $foto->move(public_path('uploads'), $fileName);

                // Simpan foto ke tabel install_progress_photos
                InstallProgressPhoto::create([
                    'install_progress_id' => $progress->id,
                    'file_path' => $fileName
                ]);
            }
        }



        return redirect()->route('ga.instalasi.show', $id)->with('success', 'Status berhasil diubah menjadi Shipped.');
    }


    public function jenis()
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // Mengambil 5 item per halaman dan urutkan berdasarkan created_at secara descending
        $jeniss = Jenis::orderBy('created_at', 'desc')->paginate(5);

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('jeniss', 'notifications'));

        return $this->renderView('jenis', $data);
    }

    public function createJenis()
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();
        // Ambil data pelanggan
        $jeniss = Jenis::all();  // Mengambil semua data dari tabel pelanggans

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('jeniss', 'notifications'));

        return $this->renderView('jenis_create', $data);
    }
    // Menyimpan jenis baru
    public function storeJenis(Request $request)
    {
        $request->validate(['nama_jenis' => 'required|unique:jenis']);
        Jenis::create($request->all());

        return redirect()->route('ga.Jenis')->with('success', 'Jenis barang berhasil ditambahkan!');
    }

    public function EditJenis($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();
        // Ambil data pelanggan berdasarkan ID
        $jeniss = Jenis::findOrFail($id);


        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('jeniss', 'notifications'));

        // Tampilkan view edit pelanggan
        return $this->renderView('edit_jenis', $data);
    }

    public function UpdateJenis(Request $request, $id)
    {
        $request->validate(['nama_jenis' => 'required|unique:jenis,nama_jenis,' . $id]);
        $jeniss = Jenis::findOrFail($id);
        $jeniss->update($request->all());
        // Redirect ke halaman pelanggan dengan pesan sukses
        return redirect()->route('ga.Jenis')->with('success', 'Data Jenis berhasil diperbarui.');
    }
    // Hapus jenis
    public function HapusJenis($id)
    {
        $jenis = Jenis::findOrFail($id);
        $jenis->delete();
        return redirect()->route('ga.Jenis')->with('success', 'Data Jenis berhasil dihapus!');
    }
    public function merek()
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();
        // Mengambil 5 item per halaman dan urutkan berdasarkan created_at secara descending
        $mereks = Merek::orderBy('created_at', 'desc')->paginate(5);

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('mereks', 'notifications'));

        return $this->renderView('merek', $data);
    }

    public function createMerek()
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();
        // Ambil data pelanggan
        $mereks = Merek::all();  // Mengambil semua data dari tabel pelanggans

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('mereks', 'notifications'));

        return $this->renderView('merek_create', $data);
    }

    // Menyimpan merek baru
    public function storeMerek(Request $request)
    {
        $request->validate(['nama_merek' => 'required|unique:mereks']);
        Merek::create($request->all());
        return redirect()->route('ga.Merek')->with('success', 'Merek berhasil ditambahkan!');
    }

    public function EditMerek($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();
        // Ambil data pelanggan berdasarkan ID
        $mereks = Merek::findOrFail($id);


        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('mereks', 'notifications'));

        // Tampilkan view edit pelanggan
        return $this->renderView('edit_merek', $data);
    }

    // Update merek
    public function UpdateMerek(Request $request, $id)
    {
        $request->validate(['nama_merek' => 'required|unique:mereks,nama_merek,' . $id]);
        $mereks = Merek::findOrFail($id);
        $mereks->update($request->all());
        return redirect()->route('ga.Merek')->with('success', 'Merek berhasil diperbarui!');
    }

    // Hapus merek
    public function HapusMerek($id)
    {
        $mereks = Merek::findOrFail($id);
        $mereks->delete();
        return redirect()->route('ga.Merek')->with('success', 'Merek berhasil dihapus!');
    }
    public function tipe()
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();
        // Mengambil 5 item per halaman dan urutkan berdasarkan created_at secara descending
        $tipes = Tipe::orderBy('created_at', 'desc')->paginate(5);
        $mereks = Merek::orderBy('created_at', 'desc')->get(); // Ambil semua merek

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('tipes', 'mereks', 'notifications'));

        return $this->renderView('tipe', $data);
    }
    public function createTipe()
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();
        // Ambil data pelanggan
        $mereks = Merek::all();  // Mengambil semua data dari tabel pelanggans
        $tipes = Tipe::all();  // Mengambil semua data dari tabel pelanggans

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('mereks', 'tipes', 'notifications'));

        return $this->renderView('tipe_create', $data);
    }
    public function storeTipe(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'merek_id' => 'required|exists:mereks,id', // Validasi merek_id
            'nama_tipe' => 'required|unique:tipes,nama_tipe',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Simpan tipe jika validasi sukses
        Tipe::create($request->only('merek_id', 'nama_tipe'));

        return redirect()->route('ga.Tipe')->with('success', 'Tipe berhasil ditambahkan!');
    }
    public function EditTipe($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();
        $tipes = Tipe::findOrFail($id);
        $mereks = Merek::orderBy('created_at', 'desc')->get(); // Ambil semua merek


        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('tipes', 'mereks', 'notifications'));

        // Tampilkan view edit pelanggan
        return $this->renderView('edit_tipe', $data);
    }
    public function updateTipe(Request $request, $id)
    {
        $tipes = Tipe::findOrFail($id);

        // Validasi input
        $validator = Validator::make($request->all(), [
            'merek_id' => 'required|exists:mereks,id', // Validasi merek_id
            'nama_tipe' => 'required|unique:tipes,nama_tipe,' . $tipes->id,
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update tipe jika validasi sukses
        $tipes->update($request->only('merek_id', 'nama_tipe'));

        return redirect()->route('ga.Tipe')->with('success', 'Tipe berhasil diperbarui!');
    }

    public function HapusTipe($id)
    {

        $tipes = Tipe::findOrFail($id);
        $tipes->delete();

        return redirect()->route('ga.Tipe')->with('success', 'Tipe berhasil dihapus!');
    }
    public function stockbarang()
    {
        $jenisList = Jenis::orderBy('nama_jenis', 'asc')->get();
        $merekList = Merek::orderBy('nama_merek', 'asc')->get();
        $tipeList = Tipe::orderBy('nama_tipe', 'asc')->get();

        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();
        // Mengambil 5 item per halaman dan urutkan berdasarkan created_at secara descending
        $stocks = StockBarang::orderBy('created_at', 'desc')->get();

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('stocks', 'notifications', 'jenisList', 'merekList', 'tipeList'));

        // Render view berdasarkan role
        return $this->renderView('stockbarang', $data);
    }

    public function createstockbarang()
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();
        $jenis = Jenis::all();
        $mereks = Merek::all();
        $tipes = Tipe::all();
        // Mengambil 5 item per halaman dan urutkan berdasarkan created_at secara descending
        $stocks = StockBarang::orderBy('created_at', 'desc')->paginate(5);

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('stocks', 'jenis', 'mereks', 'tipes', 'notifications'));

        // Render view berdasarkan role
        return $this->renderView('stockbarang_create', $data);
    }

    public function storestockbarang(Request $request)
    {
        // Validasi input
        $validatedData = $request->validate([
            'jenis_id' => 'required|exists:jenis,id',
            'merek_id' => 'required|exists:mereks,id',
            'tipe_id' => 'required|exists:tipes,id',
            'jumlah' => 'nullable|integer|min:1',  // Jumlah opsional, minimal 1
            'serial_number' => 'nullable|string',
            'kualitas' => 'required|in:baru,bekas',
        ]);
        // Set default jumlah ke 1 jika tidak diisi atau jika nilainya 1
        $validatedData['jumlah'] = $request->input('jumlah') > 1 ? $request->input('jumlah') : 1;
        // Simpan data stok barang
        StockBarang::create($validatedData);

        // Redirect ke halaman sebelumnya dengan pesan sukses
        return redirect()->route('ga.stockbarang')->with('success', 'Stok barang berhasil ditambahkan');
    }

    public function editstockbarang($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();
        $jenis = Jenis::all();
        $mereks = Merek::all();
        $tipes = Tipe::all();
        $stockBarang = StockBarang::findOrFail($id); // Temukan stok berdasarkan ID
        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('stockBarang', 'jenis', 'mereks', 'tipes', 'notifications'));

        // Render view berdasarkan role
        return $this->renderView('stockbarang_edit', $data);
    }
    public function updatestockbarang(Request $request, $id)
    {
        // Validasi input
        $validatedData = $request->validate([
            'jenis_id' => 'required|exists:jenis,id',
            'merek_id' => 'required|exists:mereks,id',
            'tipe_id' => 'required|exists:tipes,id',
            'jumlah' => 'nullable|integer|min:1',  // Jumlah opsional, minimal 1
            'serial_number' => 'nullable|string',
            'kualitas' => 'required|in:baru,bekas',
        ]);

        // Set default jumlah ke 1 jika tidak diisi atau jika nilainya kurang dari 1
        $validatedData['jumlah'] = $request->input('jumlah') >= 1 ? $request->input('jumlah') : 1;

        // Temukan data stok barang berdasarkan ID
        $stockBarang = StockBarang::findOrFail($id);

        // Update data stok barang
        $stockBarang->update($validatedData);

        // Redirect ke halaman sebelumnya dengan pesan sukses
        return redirect()->route('ga.stockbarang')->with('success', 'Stok barang berhasil diperbarui');
    }


    public function hapusstockbarang($id)
    {
        // Temukan stok berdasarkan ID
        $stock = StockBarang::findOrFail($id);
        $stock->delete(); // Hapus stok

        // Redirect dengan pesan sukses
        return redirect()->route('ga.stockbarang')->with('success', 'Stok barang berhasil dihapus.');
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
        $notifications = Notification::where('user_id', Auth::user()->id)
            ->where('is_read', false)
            ->get();

        // Ambil daftar progress dismantle
        $progressList = DismantleProgress::where('work_order_dismantle_id', $id)->get();

        // Menampilkan detail work order dengan relasi ke onlineBilling dan admin
        $getDismantle = WorkOrderDismantle::with([
            'admin',
            'onlineBilling.pelanggan',
            'onlineBilling.vendor',
            'onlineBilling.instansi'
        ])->findOrFail($id);

        // Ambil data barang yang diinput ke stok barang berdasarkan dismantle ID
        $stockItems = StockBarang::where('dismantle_id', $id)->with(['jenis', 'merek', 'tipe'])->get();

        // Gabungkan data ke dalam array data role
        $data = array_merge(
            $this->ambilDataRole(),
            compact('progressList', 'getDismantle', 'notifications', 'stockItems')
        );

        // Render view berdasarkan role
        return $this->renderView('dismantle_show', $data);
    }

    public function approvedismantle($id)
    {
        // Cari work order upgrade berdasarkan ID
        $getDismantle = WorkOrderDismantle::findOrFail($id);

        // Ubah status di tabel work_order_upgrades
        $getDismantle->status = 'On Progress';
        $getDismantle->save();

        // Cari entri terkait di tabel statuses
        $status = Status::where('work_orderable_id', $getDismantle->id)
            ->where('process', 'Dismantle') // Pastikan prosesnya adalah 'upgrade'
            ->first();

        // Perbarui status jika entri ditemukan
        if ($status) {
            $status->status = 'On Progress'; // Sesuaikan dengan status baru
            $status->save();
        }

        // Redirect ke halaman sebelumnya dengan pesan sukses
        return redirect()->route('ga.dismantle_show', $id)
            ->with('success', 'Dismantle telah disetujui.');
    }




    public function inputBarangDismantle($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();
        $jenis = Jenis::all();
        $mereks = Merek::all();
        $tipes = Tipe::all();
        // Mengambil 5 item per halaman dan urutkan berdasarkan created_at secara descending
        $stocks = StockBarang::orderBy('created_at', 'desc')->paginate(5);
        $getDismantle = WorkOrderDismantle::findOrFail($id);

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('getDismantle', 'stocks', 'jenis', 'mereks', 'tipes', 'notifications'));

        // Render view berdasarkan role
        return $this->renderView('input_barang_dismantle', $data);
    }

    public function storeBarangDismantle(Request $request, $dismantleId)
    {
        // Validasi input
        $validatedData = $request->validate([
            'jenis_id' => 'required|exists:jenis,id',
            'merek_id' => 'required|exists:mereks,id',
            'tipe_id' => 'required|exists:tipes,id',
            'jumlah' => 'nullable|integer|min:1',  // Jumlah opsional, minimal 1
            'serial_number' => 'nullable|string',
            'kualitas' => 'required|in:baru,bekas',
        ]);

        // Set default jumlah ke 1 jika tidak diisi atau jika nilainya kurang dari 1
        $validatedData['jumlah'] = $request->input('jumlah', 1);

        // Tambahkan dismantle_id ke data yang akan disimpan
        $validatedData['dismantle_id'] = $dismantleId;

        // Simpan data stok barang
        StockBarang::create($validatedData);

        // Redirect ke halaman sebelumnya dengan pesan sukses
        return redirect()->route('ga.dismantle_show', $dismantleId)
            ->with('success', 'Stok barang berhasil ditambahkan');
    }
    public function cancelBarangDismantle($id)
    {
        // Cari barang berdasarkan ID
        $stockItem = StockBarang::findOrFail($id);

        // Hapus barang dari stok
        $stockItem->delete();

        // Redirect ke halaman sebelumnya dengan pesan sukses
        return redirect()->back()->with('success', 'Barang berhasil dibatalkan dari stok.');
    }
    public function completeDismantle($id)
    {
        $workOrder = WorkOrderDismantle::with('onlineBilling')->findOrFail($id);

        if ($workOrder->status !== 'On Progress') {
            return back()->with('error', 'Work order sudah diselesaikan.');
        }

        // Update status work order menjadi completed
        $workOrder->update(['status' => 'Completed']);

        // Update status online billing menjadi dismantle
        $onlineBilling = $workOrder->onlineBilling;
        $onlineBilling->update(['status' => 'dismantle']);

        // Update tabel statuses
        $status = Status::where('work_orderable_id', $workOrder->id)
            ->where('process', 'Dismantle')
            ->first();

        if ($status) {
            $status->update(['status' => 'Completed']);
        } else {
            // Tambahkan ke tabel statuses jika belum ada
            Status::create([
                'work_orderable_id' => $workOrder->id,
                'work_orderable_type' => WorkOrderDismantle::class,
                'process' => 'Dismantle',
                'status' => 'Completed',
            ]);
        }
        $adminUsers = User::where('is_role', 1)->get(); // 1 adalah role untuk admin

        // Buat notifikasi "Survey Completed" untuk setiap admin
        foreach ($adminUsers as $admin) {
            // Cek role pengguna
            if ($admin->is_role == 1) { // Role PSB
                $url = route('admin.dismantle_show', ['id' => $workOrder->id]) . '#dismantle'; // Tambahkan #no_spk untuk PSB
            } else if ($admin->is_role == 5) { // Role Admin
                $url = route('ga.dismantle_show', ['id' => $workOrder->id]) . '#dismantle'; // Tambahkan #no_spk untuk Admin
            }

            // Buat notifikasi
            Notification::create([
                'user_id' => $admin->id,
                'message' => 'WO Dismantle telah diselesaikan dengan No Order: ' . $workOrder->no_spk,
                'url' => $url, // URL dengan hash #no_spk
            ]);
        }
        return redirect()->route('ga.dismantle')->with('success', 'Work order berhasil diselesaikan dan status pelanggan diubah menjadi dismantle.');
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
        return redirect()->route('ga.maintenance_show', $id)->with('success', 'Maintenance telah disetujui.');
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
        return redirect()->route('ga.maintenance_show', $id)->with('error', 'Maintenance telah ditolak.');
    }
    public function inputBarangmaintenancecreate(Request $request, $maintenanceId)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();
        $getMaintenance = WorkOrderMaintenance::findOrFail($maintenanceId);
        // Ambil data keranjang yang ada di session
        $cartItems = session()->get('cart', []);  // Jika tidak ada data di session, defaultkan ke array kosong
        // Ambil data stok barang dengan relasi yang diperlukan
        $search = $request->input('search', ''); // Ambil nilai pencarian dari input

        $stockBarangs = StockBarang::with(['jenis', 'merek', 'tipe'])
            ->when($search, function ($query, $search) {
                return $query->whereHas('jenis', function ($query) use ($search) {
                    $query->where('nama_jenis', 'like', '%' . $search . '%');
                })
                    ->orWhereHas('merek', function ($query) use ($search) {
                        $query->where('nama_merek', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('tipe', function ($query) use ($search) {
                        $query->where('nama_tipe', 'like', '%' . $search . '%');
                    });
            })
            ->orderBy('id', 'desc')
            ->get(); // Mengambil semua data tanpa paginasi

        return $this->renderView('input_barang_maintenance', compact('getMaintenance', 'stockBarangs', 'search', 'cartItems', 'notifications'));
    }
    public function inputBarangmaintenancestore(Request $request, $maintenanceId)
    {
        // Jika `cartItems` dikirim sebagai JSON string, decode menjadi array terlebih dahulu
        if (is_string($request->input('cartItems'))) {
            $request->merge(['cartItems' => json_decode($request->input('cartItems'), true)]);
        }

        // Validasi data `cartItems`
        $validated = $request->validate([
            'cartItems' => 'required|array',
            'cartItems.*.id' => 'required|exists:stock_barangs,id', // validasi ID barang dalam stok
            'cartItems.*.jumlah' => 'required|integer|min:1',
            'cartItems.*.serialNumber' => 'nullable|string',
            'cartItems.*.kualitas' => 'required|string|in:baru,bekas',
        ]);

        $cartItems = $validated['cartItems'];

        foreach ($cartItems as $item) {
            $stockBarang = StockBarang::find($item['id']);

            if ($stockBarang && $stockBarang->jumlah >= $item['jumlah']) {
                // Menyimpan entri baru di tabel barang_keluars
                BarangKeluar::create([
                    'work_order_maintenance_id' => $maintenanceId, // ID Work Order untuk instalasi
                    'stock_barang_id' => $item['id'],
                    'jumlah' => $item['jumlah'],
                    'serial_number' => $item['serialNumber'],
                    'kualitas' => $item['kualitas'],
                    'user_id' => Auth::user()->id,
                ]);

                // Update stok setelah barang dikirim
                $stockBarang->jumlah -= $item['jumlah'];
                $stockBarang->save();
            } else {
                // Redirect jika stok tidak mencukupi
                return redirect()->back()->with('error', 'Stok tidak mencukupi untuk barang: ' . $item['id']);
            }
        }

        // Kirim notifikasi ke pengguna NA setelah barang berhasil diinput
        $naUsers = User::where('is_role', 6)->get(); // Role NA adalah 4
        foreach ($naUsers as $naUser) {
            $url = route('na.maintenance_show', ['id' => $maintenanceId]); // URL menuju halaman konfigurasi NA
            Notification::create([
                'user_id' => $naUser->id,
                'message' => 'Barang untuk Maintenance telah diinput, Silahkan Konfigurasi Perangkat ',
                'url' => $url,
            ]);
        }
        return redirect()->route('ga.maintenance_show', $maintenanceId)->with('success', 'Barang berhasil dikirim!');
    }
    public function cancelBarangmaintenance($barangKeluarId)
    {
        // Cari barang yang akan dibatalkan
        $barangKeluar = BarangKeluar::findOrFail($barangKeluarId);
        $getMaintenance = $barangKeluar->WorkOrderMaintenance;

        // Cek status permintaan, jika 'completed' maka pembatalan tidak diizinkan
        if ($getMaintenance->status === 'Completed') {
            return redirect()->back()->withErrors('Barang tidak dapat dibatalkan karena status sudah completed.');
        }

        // Kembalikan jumlah barang ke stok awal
        $stockBarang = $barangKeluar->stockBarang;
        $stockBarang->jumlah += $barangKeluar->jumlah;
        $stockBarang->save();

        // Hapus data barang keluar
        $barangKeluar->delete();


        // Arahkan ke halaman show requestBarang dengan id
        return redirect()->route('ga.maintenance_show', ['id' => $getMaintenance->id])
            ->with('success', 'Barang berhasil dibatalkan dan stok dikembalikan.');
    }
    public function maintenancecreateShipped($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        $getMaintenance = WorkOrderMaintenance::findOrFail($id);
        $data = array_merge($this->ambilDataRole(), compact('getMaintenance', 'notifications'));

        return $this->renderView('wo_maintenance_createshipped', $data);
    }

    public function maintenancestoreShipped(Request $request, $id)
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
        $progress->status = 'Shipped'; // Set status langsung menjadi Shipped
        $progress->psb_id = Auth::id();
        $progress->save();

        // Ambil data WO Instalasi
        $getMaintenance = WorkOrderMaintenance::findOrFail($id);
        $getMaintenance->status = 'Shipped'; // Update status WO menjadi Shipped
        $getMaintenance->save();

        // Upload dan simpan banyak foto jika ada
        if ($request->hasFile('foto')) {
            foreach ($request->file('foto') as $foto) {
                $fileName = time() . '_' . $foto->getClientOriginalName();
                $foto->move(public_path('uploads'), $fileName);

                // Simpan foto ke tabel install_progress_photos
                MaintenanceProgressPhoto::create([
                    'maintenance_progress_id' => $progress->id,
                    'file_path' => $fileName
                ]);
            }
        }



        return redirect()->route('ga.maintenance_show', $id)->with('success', 'Status berhasil diubah menjadi Shipped.');
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
    public function approverelokasi($id)
    {
        $getRelokasi = WorkOrderRelokasi::findOrFail($id);

        // Ubah status menjadi 'approved'
        $getRelokasi->status = 'On Progress';
        $getRelokasi->save();

        // Cari entri terkait di tabel statuses
        $status = Status::where('work_orderable_id', $getRelokasi->id)
            ->where('process', 'Relokasi') // Pastikan prosesnya adalah 'Upgrade'
            ->first();

        // Perbarui status di tabel statuses jika entri ditemukan
        if ($status) {
            $status->status = 'On Progress'; // Sesuaikan dengan status baru
            $status->save();
        }
        // Redirect ke halaman sebelumnya dengan pesan sukses
        return redirect()->route('ga.relokasi.show', $id)->with('success', 'Relokasi telah disetujui.');
    }
    public function rejectrelokasi($id)
    {
        $getRelokasi = WorkOrderRelokasi::findOrFail($id);

        // Ubah status menjadi 'rejected'
        $getRelokasi->status = 'Rejected';
        $getRelokasi->save();

        // Cari entri terkait di tabel statuses
        $status = Status::where('work_orderable_id', $getRelokasi->id)
            ->where('process', 'Relokasi') // Pastikan prosesnya adalah 'Upgrade'
            ->first();

        // Perbarui status di tabel statuses jika entri ditemukan
        if ($status) {
            $status->status = 'Rejected'; // Sesuaikan dengan status baru
            $status->save();
        }
        // Redirect ke halaman sebelumnya dengan pesan sukses
        return redirect()->route('ga.relokasi_show', $id)->with('error', 'Relokasi telah ditolak.');
    }

    public function inputBarangrelokasicreate(Request $request, $relokasiId)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();
        $getRelokasi = WorkOrderRelokasi::findOrFail($relokasiId);
        // Ambil data keranjang yang ada di session
        $cartItems = session()->get('cart', []);  // Jika tidak ada data di session, defaultkan ke array kosong
        // Ambil data stok barang dengan relasi yang diperlukan
        $search = $request->input('search', ''); // Ambil nilai pencarian dari input

        $stockBarangs = StockBarang::with(['jenis', 'merek', 'tipe'])
            ->when($search, function ($query, $search) {
                return $query->whereHas('jenis', function ($query) use ($search) {
                    $query->where('nama_jenis', 'like', '%' . $search . '%');
                })
                    ->orWhereHas('merek', function ($query) use ($search) {
                        $query->where('nama_merek', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('tipe', function ($query) use ($search) {
                        $query->where('nama_tipe', 'like', '%' . $search . '%');
                    });
            })
            ->orderBy('id', 'desc')
            ->get(); // Mengambil semua data tanpa paginasi

        return $this->renderView('input_barang_relokasi', compact('getRelokasi', 'stockBarangs', 'search', 'cartItems', 'notifications'));
    }
    public function inputBarangrelokasistore(Request $request, $relokasiId)
    {
        // Jika `cartItems` dikirim sebagai JSON string, decode menjadi array terlebih dahulu
        if (is_string($request->input('cartItems'))) {
            $request->merge(['cartItems' => json_decode($request->input('cartItems'), true)]);
        }

        // Validasi data `cartItems`
        $validated = $request->validate([
            'cartItems' => 'required|array',
            'cartItems.*.id' => 'required|exists:stock_barangs,id', // validasi ID barang dalam stok
            'cartItems.*.jumlah' => 'required|integer|min:1',
            'cartItems.*.serialNumber' => 'nullable|string',
            'cartItems.*.kualitas' => 'required|string|in:baru,bekas',
        ]);

        $cartItems = $validated['cartItems'];

        foreach ($cartItems as $item) {
            $stockBarang = StockBarang::find($item['id']);

            if ($stockBarang && $stockBarang->jumlah >= $item['jumlah']) {
                // Menyimpan entri baru di tabel barang_keluars
                BarangKeluar::create([
                    'work_order_relokasi_id' => $relokasiId, // ID Work Order untuk instalasi
                    'stock_barang_id' => $item['id'],
                    'jumlah' => $item['jumlah'],
                    'serial_number' => $item['serialNumber'],
                    'kualitas' => $item['kualitas'],
                    'user_id' => Auth::user()->id,
                ]);

                // Update stok setelah barang dikirim
                $stockBarang->jumlah -= $item['jumlah'];
                $stockBarang->save();
            } else {
                // Redirect jika stok tidak mencukupi
                return redirect()->back()->with('error', 'Stok tidak mencukupi untuk barang: ' . $item['id']);
            }
        }

        // Kirim notifikasi ke pengguna NA setelah barang berhasil diinput
        $naUsers = User::where('is_role', 6)->get(); // Role NA adalah 4
        foreach ($naUsers as $naUser) {
            $url = route('na.relokasi.show', ['id' => $relokasiId]); // URL menuju halaman konfigurasi NA
            Notification::create([
                'user_id' => $naUser->id,
                'message' => 'Barang untuk Relokasi telah diinput, Silahkan Konfigurasi Perangkat ',
                'url' => $url,
            ]);
        }
        return redirect()->route('ga.relokasi.show', $relokasiId)->with('success', 'Barang berhasil dikirim!');
    }
    public function cancelBarangrelokasi($barangKeluarId)
    {
        // Cari barang yang akan dibatalkan
        $barangKeluar = BarangKeluar::findOrFail($barangKeluarId);
        $getRelokasi = $barangKeluar->WorkOrderRelokasi;

        // Cek status permintaan, jika 'completed' maka pembatalan tidak diizinkan
        if ($getRelokasi->status === 'Completed') {
            return redirect()->back()->withErrors('Barang tidak dapat dibatalkan karena status sudah completed.');
        }

        // Kembalikan jumlah barang ke stok awal
        $stockBarang = $barangKeluar->stockBarang;
        $stockBarang->jumlah += $barangKeluar->jumlah;
        $stockBarang->save();

        // Hapus data barang keluar
        $barangKeluar->delete();

        // Arahkan ke halaman show requestBarang dengan id
        return redirect()->route('ga.relokasi.show', ['id' => $getRelokasi->id])
            ->with('success', 'Barang berhasil dibatalkan dan stok dikembalikan.');
    }
    public function relokasicreateShipped($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        $getRelokasi = WorkOrderRelokasi::findOrFail($id);
        $data = array_merge($this->ambilDataRole(), compact('getRelokasi', 'notifications'));

        return $this->renderView('wo_relokasi_createshipped', $data);
    }

    public function relokasistoreShipped(Request $request, $id)
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
        $progress->status = 'Shipped'; // Set status langsung menjadi Shipped
        $progress->psb_id = Auth::id();
        $progress->save();

        // Ambil data WO Instalasi
        $getRelokasi = WorkOrderRelokasi::findOrFail($id);
        $getRelokasi->status = 'Shipped'; // Update status WO menjadi Shipped
        $getRelokasi->save();

        // Upload dan simpan banyak foto jika ada
        if ($request->hasFile('foto')) {
            foreach ($request->file('foto') as $foto) {
                $fileName = time() . '_' . $foto->getClientOriginalName();
                $foto->move(public_path('uploads'), $fileName);

                // Simpan foto ke tabel install_progress_photos
                RelokasiProgressPhoto::create([
                    'relokasi_progress_id' => $progress->id,
                    'file_path' => $fileName
                ]);
            }
        }

        return redirect()->route('ga.relokasi.show', $id)->with('success', 'Status berhasil diubah menjadi Shipped.');
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
    public function markAsReadGa($id)
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
        $requestBarang = RequestBarang::with('requestBarangDetails.stockBarang')->findOrFail($id);
        $stockBarangs = StockBarang::with(['jenis', 'merek', 'tipe'])->get(); // Ambil data stock barang
        $progressList = ReqBarangProgress::where('req_barang_id', $id)->get();


        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('requestBarang', 'progressList', 'stockBarangs', 'notifications'));


        return $this->renderView('requestbarang_show', $data);
    }

    public function approve($id)
    {
        $requestBarang = RequestBarang::findOrFail($id);

        // Ubah status menjadi 'approved'
        $requestBarang->status = 'approved';
        $requestBarang->save();

        // Redirect ke halaman sebelumnya dengan pesan sukses
        return redirect()->route('ga.request_barang.show', $id)->with('success', 'Request barang telah disetujui.');
    }

    /**
     * Menolak request barang dan mengubah status menjadi 'rejected'.
     */
    public function reject($id)
    {
        $requestBarang = RequestBarang::findOrFail($id);

        // Ubah status menjadi 'rejected'
        $requestBarang->status = 'rejected';
        $requestBarang->save();

        // Redirect ke halaman sebelumnya dengan pesan sukses
        return redirect()->route('ga.request_barang.show', $id)->with('error', 'Request barang telah ditolak.');
    }

    public function inputBarangcreate(Request $request, $requestBarangId)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();
        $requestBarang = RequestBarang::findOrFail($requestBarangId);
        // Ambil data keranjang yang ada di session
        $cartItems = session()->get('cart', []);  // Jika tidak ada data di session, defaultkan ke array kosong
        // Ambil data stok barang dengan relasi yang diperlukan
        $search = $request->input('search', ''); // Ambil nilai pencarian dari input

        $stockBarangs = StockBarang::with(['jenis', 'merek', 'tipe'])
            ->when($search, function ($query, $search) {
                return $query->whereHas('jenis', function ($query) use ($search) {
                    $query->where('nama_jenis', 'like', '%' . $search . '%');
                })
                    ->orWhereHas('merek', function ($query) use ($search) {
                        $query->where('nama_merek', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('tipe', function ($query) use ($search) {
                        $query->where('nama_tipe', 'like', '%' . $search . '%');
                    });
            })
            ->orderBy('id', 'desc')
            ->get(); // Mengambil semua data tanpa paginasi

        return $this->renderView('input_barang', compact('requestBarang', 'stockBarangs', 'search', 'cartItems', 'notifications'));
    }


    public function inputBarangstore(Request $request, $requestBarangId)
    {
        // Jika `cartItems` dikirim sebagai JSON string, decode menjadi array terlebih dahulu
        if (is_string($request->input('cartItems'))) {
            $request->merge(['cartItems' => json_decode($request->input('cartItems'), true)]);
        }

        // Validasi data `cartItems`
        $validated = $request->validate([
            'cartItems' => 'required|array',
            'cartItems.*.id' => 'required|exists:stock_barangs,id', // validasi ID barang dalam stok
            'cartItems.*.jumlah' => 'required|integer|min:1',
            'cartItems.*.serialNumber' => 'nullable|string',
            'cartItems.*.kualitas' => 'required|string|in:baru,bekas',
        ]);

        $cartItems = $validated['cartItems'];

        foreach ($cartItems as $item) {
            $stockBarang = StockBarang::find($item['id']);

            if ($stockBarang && $stockBarang->jumlah >= $item['jumlah']) {
                // Menyimpan entri baru di tabel barang_keluars
                BarangKeluar::create([
                    'request_barang_id' => $requestBarangId,
                    'stock_barang_id' => $item['id'],
                    'jumlah' => $item['jumlah'],
                    'serial_number' => $item['serialNumber'],
                    'kualitas' => $item['kualitas'],
                    'user_id' => Auth::user()->id,

                ]);

                // Update stok setelah barang dikirim
                $stockBarang->jumlah -= $item['jumlah'];
                $stockBarang->save();
            } else {
                // Redirect jika stok tidak mencukupi
                return redirect()->back()->with('error', 'Stok tidak mencukupi untuk barang: ' . $item['id']);
            }
        }

        return redirect()->route('ga.request_barang.show', $requestBarangId)->with('success', 'Barang berhasil dikirim!');
    }

    public function requestcreateShipped($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        $getRequest = RequestBarang::findOrFail($id);
        $data = array_merge($this->ambilDataRole(), compact('getRequest', 'notifications'));

        return $this->renderView('wo_request_createshipped', $data);
    }

    public function requeststoreShipped(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'keterangan' => 'required',
            'foto.*' => 'nullable|image|max:2048', // Validasi untuk banyak file
        ]);

        // Menyimpan progress baru
        $progress = new ReqBarangProgress();
        $progress->req_barang_id = $id;
        $progress->keterangan = $request->keterangan;
        $progress->status = 'shipped'; // Set status langsung menjadi Shipped
        $progress->user_id = Auth::id();
        $progress->save();

        // Ambil data WO Instalasi
        $getRequest = RequestBarang::findOrFail($id);
        $getRequest->status = 'shipped'; // Update status WO menjadi Shipped
        $getRequest->save();

        // Upload dan simpan banyak foto jika ada
        if ($request->hasFile('foto')) {
            foreach ($request->file('foto') as $foto) {
                $fileName = time() . '_' . $foto->getClientOriginalName();
                $foto->move(public_path('uploads'), $fileName);

                // Simpan foto ke tabel install_progress_photos
                ReqBarangProgressPhoto::create([
                    'reqbarang_progress_id' => $progress->id,
                    'file_path' => $fileName
                ]);
            }
        }



        return redirect()->route('ga.request_barang.show', $id)->with('success', 'Status berhasil diubah menjadi Shipped.');
    }

    public function cancelBarang($barangKeluarId)
    {
        // Cari barang yang akan dibatalkan
        $barangKeluar = BarangKeluar::findOrFail($barangKeluarId);
        $requestBarang = $barangKeluar->requestBarang;

        // Cek status permintaan, jika 'completed' maka pembatalan tidak diizinkan
        if ($requestBarang->status === 'completed') {
            return redirect()->back()->withErrors('Barang tidak dapat dibatalkan karena status sudah completed.');
        }

        // Kembalikan jumlah barang ke stok awal
        $stockBarang = $barangKeluar->stockBarang;
        $stockBarang->jumlah += $barangKeluar->jumlah;
        $stockBarang->save();

        // Hapus data barang keluar
        $barangKeluar->delete();

        // Perbarui status request barang jika semua barang keluar sudah dibatalkan
        if ($requestBarang->barangKeluar->isEmpty()) {
            $requestBarang->status = 'pending';
            $requestBarang->save();
        }

        // Arahkan ke halaman show requestBarang dengan id
        return redirect()->route('ga.request_barang.show', ['id' => $requestBarang->id])
            ->with('success', 'Barang berhasil dibatalkan dan stok dikembalikan.');
    }

    public function requestcompleted($id)
    {
        $requestBarang = RequestBarang::findOrFail($id);

        // Ubah status menjadi 'approved'
        $requestBarang->status = 'completed';
        $requestBarang->save();

        // Redirect ke halaman sebelumnya dengan pesan sukses
        return redirect()->route('ga.request_barang.show', $id)->with('success', 'Request barang telah diselesaikan.');
    }
}

// ambilDataRole(): Fungsi ini mengembalikan informasi tentang role pengguna, termasuk teks role (roleText) dan folder view (viewFolder) yang diturunkan dari role.
// renderView(): Alih-alih menggunakan switch, fungsi ini secara dinamis menentukan folder view berdasarkan viewFolder yang diambil dari ambilDataRole() dan viewName yang merupakan nama file view.
// Controller Function: Setiap fungsi seperti dashboard(), instalasi(), dll, hanya memanggil renderView() dengan parameter nama view yang sesuai. Ini membuat kode lebih ringkas dan mudah dikelola jika Anda menambahkan view lain di masa depan.