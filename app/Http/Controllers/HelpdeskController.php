<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Jenis;
use App\Models\Status;
use setasign\Fpdi\Fpdi;
use App\Models\StockBarang;
use Illuminate\Support\Str;
use App\Helpers\LogActivity;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\OnlineBilling;
use App\Models\RequestBarang;
use App\Models\WorkOrderInstall;
use App\Models\ReqBarangProgress;
use App\Mail\PermintaanBarangMail;
use App\Models\WorkOrderDismantle;
use App\Models\GantiVendorProgress;
use App\Models\MaintenanceProgress;
use Illuminate\Support\Facades\Log;
use App\Mail\MaintenanceRequestMail;
use App\Models\RequestBarangDetails;
use App\Models\WorkOrderGantiVendor;
use App\Models\WorkOrderMaintenance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\RequestBarangStockBarang;
use App\Models\WorkOrderMaintenanceDetail;

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
            'no_penerima' => 'required|string|max:20',
            'keterangan' => 'nullable|string',
            'non_stock' => 'nullable|string',
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
                $parts = explode('-', $line);
                $nama = trim($parts[0] ?? '');
                $jumlah = trim($parts[1] ?? '1');
                if ($nama) {
                    $nonStockItems[] = [
                        'nama' => $nama,
                        'jumlah' => $jumlah,
                    ];
                }
            }
        }

        // Kirim email
        $recipients = ['rizalkrenz1@gmail.com', 'm.rizal@pc24.net.id'];

        if (count($nonStockItems) > 0) {
            // Jika ada non-stock, generate PDF
            $pdfPath = storage_path('app/public/temp/surat_pengajuan_' . Str::uuid() . '.pdf');
            $templatePath = storage_path('app/public/pdf/template_surat_pengajuan.pdf');

            $pdf = new Fpdi();
            $pdf->AddPage();
            $pdf->setSourceFile($templatePath);
            $tpl = $pdf->importPage(1);
            $pdf->useTemplate($tpl);

            $pdf->SetFont('Arial', '', 11);
            $pdf->SetTextColor(0, 0, 0);

            $y = 120;
            $no = 1;


            // Tambahkan perangkat non-stock
            foreach ($nonStockItems as $item) {
                $pdf->SetXY(12, $y);
                $pdf->Cell(10, 94, $no++, 0, 0);
                $pdf->SetXY(20, $y);
                $pdf->Cell(110, 94, $item['nama'], 0, 0);
                $pdf->SetXY(100, $y);
                $pdf->Cell(20, 94, $item['jumlah'], 0, 1);
                $y += 8;
            }

            $pdf->Output($pdfPath, 'F');

            // Kirim email dengan lampiran
            Mail::to($recipients)->send(new \App\Mail\PermintaanBarangMail($requestBarang, $detailBarang, $pdfPath));
        } else {
            // Tanpa lampiran
            Mail::to($recipients)->send(new \App\Mail\PermintaanBarangMail($requestBarang, $detailBarang));
        }

        return redirect()->route('hd.request_barang')->with('success', 'Request barang berhasil diajukan.');
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
    public function maintenanceCreate($id)
    {
        $onlineBilling = OnlineBilling::findOrFail($id); // Ambil data pelanggan berdasarkan ID

        // Mendapatkan tahun 2 digit dan bulan saat ini
        $currentYear = date('y'); // Tahun dengan dua digit terakhir
        $currentMonth = date('m'); // Bulan saat ini

        // Mengambil daftar jenis unik dari StockBarang
        $jenisList = Jenis::select('id', 'nama_jenis')->get();

        // Mengambil data stok dengan total jumlah berdasarkan tipe, merek, dan jenis, serta melakukan pagination
        $stockBarangs = StockBarang::with(['merek', 'tipe', 'jenis'])
            ->selectRaw('tipe_id, merek_id, jenis_id, kualitas, SUM(jumlah) as total_jumlah')
            ->groupBy('tipe_id', 'merek_id', 'jenis_id', 'kualitas')
            ->get();

        // Mendapatkan nomor SPK terakhir dari tahun ini dan bulan ini
        $lastSpk = WorkOrderMaintenance::whereYear('created_at', date('Y')) // Tahun dengan 4 digit
            ->whereMonth('created_at', $currentMonth) // Filter berdasarkan bulan
            ->max('id'); // Dapatkan ID terbesar di bulan dan tahun yang sama

        // Jika ada nomor SPK di bulan ini, ambil nomor urut berikutnya
        // Jika tidak ada, mulai dari 001
        $nextNumber = $lastSpk ? ($lastSpk + 1) : 1;

        // Format nomor SPK ke dalam format SRV-bulan-tahun-xxx (dengan xxx direset setiap bulan dan tahun)
        $no_spk = 'MT-' . $currentMonth . $currentYear . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('stockBarangs', 'jenisList', 'notifications', 'no_spk', 'onlineBilling'));

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

        // Kirim email ke tim terkait
        $recipients = ['rizalkrenz1@gmail.com', 'm.rizal@pc24.net.id', 'fahrizavary4321@gmail.com'];
        Mail::to($recipients)->send(new \App\Mail\MaintenanceRequestMail($getMaintenance, $detailBarang));

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


            // tambahkan validasi lain sesuai kebutuhan
        ]);

        // Ambil data work order berdasarkan ID
        $workOrder = WorkOrderMaintenance::findOrFail($id);

        $workOrder->update([
            'online_billing_id' => $validatedData['online_billing_id'],
            'keterangan' => $validatedData['keterangan'],
            'non_stock' => $validatedData['non_stock'],

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


    public function gantivendorCreate($id)
    {
        $onlineBilling = OnlineBilling::findOrFail($id); // Ambil data pelanggan berdasarkan ID

        // Mendapatkan tahun 2 digit dan bulan saat ini
        $currentYear = date('y'); // Tahun dengan dua digit terakhir
        $currentMonth = date('m'); // Bulan saat ini

        // Mendapatkan nomor SPK terakhir dari tahun ini dan bulan ini
        $lastSpk = WorkOrderGantiVendor::whereYear('created_at', date('Y')) // Tahun dengan 4 digit
            ->whereMonth('created_at', $currentMonth) // Filter berdasarkan bulan
            ->max('id'); // Dapatkan ID terbesar di bulan dan tahun yang sama

        // Jika ada nomor SPK di bulan ini, ambil nomor urut berikutnya
        // Jika tidak ada, mulai dari 001
        $nextNumber = $lastSpk ? ($lastSpk + 1) : 1;

        // Format nomor SPK ke dalam format SRV-bulan-tahun-xxx (dengan xxx direset setiap bulan dan tahun)
        $no_spk = 'GV-' . $currentMonth . $currentYear . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('notifications', 'no_spk', 'onlineBilling'));

        return $this->renderView('gantivendor_create', $data);
    }
    public function gantivendorStore(Request $request)
    {

        // Validasi data yang masuk
        $validated = $request->validate([
            'no_spk' => 'required|string|unique:work_order_ganti_vendors',
            'online_billing_id' => 'required|exists:online_billings,id',
            'keterangan' => 'nullable|string',
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
        return $this->renderView('gantivendor_Edit', $data);
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
}

//ambilDataRole(): Fungsi ini digunakan untuk mendapatkan informasi role pengguna dan folder view yang sesuai.
//renderView(): Fungsi ini menggabungkan nama folder view yang sesuai berdasarkan role dan nama view yang diinginkan.
//Controller Function: Setiap method seperti dashboard(), requestbarang(), dll, sekarang lebih ringkas dan hanya perlu memanggil renderView() dengan parameter nama view.