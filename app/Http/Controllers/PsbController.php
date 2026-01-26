<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Jenis;
use App\Models\Status;
use setasign\Fpdi\Fpdi;
use Endroid\QrCode\QrCode;
use App\Models\StockBarang;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\OnlineBilling;
use App\Models\RequestBarang;
use App\Models\SurveyProgress;
use App\Models\InstallProgress;
use App\Models\UpgradeProgress;
use App\Models\WorkOrderSurvey;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Label\Label;
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
use App\Models\SurveyProgressPhoto;
use Endroid\QrCode\Builder\Builder;
use App\Models\InstallProgressPhoto;
use App\Models\RequestBarangDetails;
use App\Models\UpgradeProgressPhoto;
use App\Models\WorkOrderGantiVendor;
use App\Models\WorkOrderMaintenance;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Auth;
use App\Models\RelokasiProgressPhoto;
use Endroid\QrCode\Encoding\Encoding;
use Intervention\Image\Facades\Image;
use App\Models\DismantleProgressPhoto;
use App\Models\DowngradeProgressPhoto;
use App\Notifications\SurveyCompleted;
use Endroid\QrCode\RoundBlockSizeMode;
use App\Models\GantiVendorProgressPhoto;
use App\Models\MaintenanceProgressPhoto;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;

class PsbController extends Controller
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

        // Ambil data survey
        $getInstall = WorkOrderInstall::findOrFail($id);

        // Set status default atau complete sesuai tombol yang ditekan
        if ($request->has('action') && $request->action === 'complete') {
            $progress->status = 'Completed'; // Ubah status progress jadi Completed

            // Ubah status survey menjadi Completed
            $getInstall->status = 'Completed';
            $getInstall->save();

            // Dapatkan semua admin (atau role yang sesuai)
            $adminUsers = User::where('is_role', 1)->get(); // 1 adalah role untuk admin

            // Buat notifikasi "Survey Completed" untuk setiap admin
            foreach ($adminUsers as $admin) {
                // Cek role pengguna
                if ($admin->is_role == 1) { // Role PSB
                    $url = route('admin.wo_instalasi_show', ['id' => $getInstall->id]) . '#instalasi'; // Tambahkan #no_spk untuk PSB
                } else if ($admin->is_role == 5) { // Role Admin
                    $url = route('psb.instalasi.show', ['id' => $getInstall->id]) . '#instalasi'; // Tambahkan #no_spk untuk Admin
                }

                // Buat notifikasi
                Notification::create([
                    'user_id' => $admin->id,
                    'message' => 'WO Instalasi telah diselesaikan dengan No Order: ' . $getInstall->no_spk,
                    'url' => $url, // URL dengan hash #no_spk
                ]);
            }
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
            return redirect()->route('psb.instalasi', $id)->with('success', 'Instalasi berhasil diselesaikan.');
        }

        return redirect()->route('psb.instalasi.show', $id)->with('success', 'Progress berhasil ditambahkan.');
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
                $q->where('no_spk', 'like', '%' . $search . '%')
                    ->orWhere('nama_pelanggan', 'like', '%' . $search . '%')
                    ->orWhere('nama_instansi', 'like', '%' . $search . '%')
                    ->orWhere('nama_site', 'like', '%' . $search . '%')
                    ->orWhereHas('admin', function ($q) use ($search) {
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
    public function markAsReadPsb($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->is_read = true;
        $notification->save();

        return redirect()->to($notification->url);
    }
    public function approvesurvey($id)
    {
        // Cari work order upgrade berdasarkan ID
        $getSurvey = WorkOrderSurvey::findOrFail($id);

        // Ubah status di tabel work_order_upgrades
        $getSurvey->status = 'On Progress';
        $getSurvey->save();


        // Redirect ke halaman sebelumnya dengan pesan sukses
        return redirect()->route('psb.survey_show', $id)
            ->with('success', 'Survey telah disetujui.');
    }
    // Menampilkan detail survey
    public function show($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        // Ambil detail Work Order Survey
        $getSurvey = WorkOrderSurvey::findOrFail($id);
        // Ambil progress survey yang terkait dengan survey ini
        $progressList = SurveyProgress::where('work_order_survey_id', $id)->get();
        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('getSurvey', 'progressList', 'notifications'));

        // Render view berdasarkan role
        return $this->renderView('wo_survey_show', $data);
    }

    public function addProgressSurvey($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();

        $getSurvey = WorkOrderSurvey::findOrFail($id);
        $data = array_merge($this->ambilDataRole(), compact('getSurvey', 'notifications'));

        return $this->renderView('wo_survey_add', $data);
    }

    public function StoreProgressSurvey(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'keterangan' => 'required',
            'foto.*' => 'nullable|image|max:10240',
        ]);

        // Menyimpan progress baru
        $progress = new SurveyProgress();
        $progress->work_order_survey_id = $id;
        $progress->keterangan = $request->keterangan;

        // Ambil data survey
        $survey = WorkOrderSurvey::findOrFail($id);

        // Set status default atau complete sesuai tombol yang ditekan
        if ($request->has('action') && $request->action === 'complete') {
            $progress->status = 'Completed'; // Ubah status progress jadi Completed

            // Ubah status survey menjadi Completed
            $survey->status = 'Completed';
            $survey->save();

            // Dapatkan semua admin (atau role yang sesuai)
            $adminUsers = User::where('is_role', 1)->get(); // 1 adalah role untuk admin

            // Buat notifikasi "Survey Completed" untuk setiap admin
            foreach ($adminUsers as $admin) {
                // Cek role pengguna
                if ($admin->is_role == 1) { // Role PSB
                    $url = route('admin.wo_survey_show', ['id' => $survey->id]) . '#survey'; // Tambahkan #no_spk untuk PSB
                } else if ($admin->is_role == 5) { // Role Admin
                    $url = route('psb.survey', ['id' => $survey->id]) . '#survey'; // Tambahkan #no_spk untuk Admin
                }

                // Buat notifikasi
                Notification::create([
                    'user_id' => $admin->id,
                    'message' => 'WO Survey telah diselesaikan dengan No Order: ' . $survey->no_spk,
                    'url' => $url, // URL dengan hash #no_spk
                ]);
            }
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
                SurveyProgressPhoto::create([
                    'survey_progress_id' => $progress->id,
                    'file_path' => $fileName
                ]);
            }
        }

        // Redirect ke view survey atau detail survey berdasarkan aksi
        if ($request->action === 'complete') {
            return redirect()->route('psb.survey', $id)->with('success', 'Survey berhasil diselesaikan.');
        }

        return redirect()->route('psb.survey_show', $id)->with('success', 'Progress berhasil ditambahkan.');
    }


    // Method untuk menampilkan form edit progress survey
    public function editProgress($id, $progressId)
    {

        // Ambil detail Work Order Survey
        $getSurvey = WorkOrderSurvey::findOrFail($id);
        // Ambil progress survey yang terkait dengan survey ini
        $progress = SurveyProgress::findOrFail($progressId);
        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('getSurvey', 'progress'));

        return $this->renderView('wo_survey_edit', $data);
    }

    // Method untuk mengupdate progress survey
    public function updateProgress(Request $request, $id, $progressId)
    {
        $request->validate([
            'keterangan' => 'required',
            'foto.*' => 'nullable|image|max:10240',
        ]);

        // Update keterangan progress
        $progress = SurveyProgress::findOrFail($progressId);
        $progress->keterangan = $request->keterangan;
        $progress->save();

        // Cek jika ada file foto baru yang diupload
        if ($request->hasFile('foto')) {
            foreach ($request->file('foto') as $foto) {
                $fileName = time() . '_' . $foto->getClientOriginalName();
                $foto->move(public_path('uploads'), $fileName);

                // Simpan foto baru di tabel survey_progress_photos
                SurveyProgressPhoto::create([
                    'survey_progress_id' => $progress->id,
                    'file_path' => $fileName,
                ]);
            }
        }

        return redirect()->route('psb_survey_show', $id)->with('success', 'Progress berhasil diupdate.');
    }
    // Method untuk menghapus progress survey
    public function deleteProgress($id, $progressId)
    {
        $progress = SurveyProgress::findOrFail($progressId);

        // Hapus file foto dari server jika ada
        if ($progress->foto) {
            unlink(public_path('uploads/' . $progress->foto));
        }

        $progress->delete();

        return redirect()->route('psb_survey_show', $id)->with('success', 'Progress berhasil dihapus.');
    }

    public function cancelcomplete($id)
    {
        // Ambil notifikasi yang belum dibaca
        $notifications = Notification::where('user_id', Auth::user()->id)->where('is_read', false)->get();


        // Ambil detail Work Order Survey
        $getSurvey = WorkOrderSurvey::findOrFail($id);
        // Ambil progress survey yang terkait dengan survey ini
        $progressList = SurveyProgress::where('work_order_survey_id', $id)->get();

        // Gabungkan data survey ke dalam data role
        $data = array_merge($this->ambilDataRole(), compact('getSurvey', 'progressList', 'notifications'));

        return $this->renderView('wo_survey_cancelcomplete', $data);
    }

    private function resizeToBox($sourcePath, $boxWidthPx = 150, $boxHeightPx = 180)
    {
        [$originalWidth, $originalHeight, $type] = getimagesize($sourcePath);

        switch ($type) {
            case IMAGETYPE_JPEG:
                $src = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $src = imagecreatefrompng($sourcePath);
                break;
            case IMAGETYPE_GIF:
                $src = imagecreatefromgif($sourcePath);
                break;
            default:
                throw new \Exception('Unsupported image type');
        }

        $scale = min($boxWidthPx / $originalWidth, $boxHeightPx / $originalHeight);
        $newWidth = (int)($originalWidth * $scale);
        $newHeight = (int)($originalHeight * $scale);

        $canvas = imagecreatetruecolor($boxWidthPx, $boxHeightPx);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefill($canvas, 0, 0, $white);

        $x = ($boxWidthPx - $newWidth) / 2;
        $y = ($boxHeightPx - $newHeight) / 2;

        imagecopyresampled($canvas, $src, $x, $y, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

        $outputPath = storage_path('app/public/temp/resized_image.jpg');
        imagejpeg($canvas, $outputPath, 90);

        imagedestroy($src);
        imagedestroy($canvas);

        return $outputPath;
    }

    public function printSurveyPDF($id)
    {
        $getSurvey = WorkOrderSurvey::findOrFail($id);
        $tanggalSurvey = now()->format('d-m-Y');
        $formattedDate = Carbon::parse($tanggalSurvey)->translatedFormat('l, d F Y');
        $templatePath = resource_path('pdf/survey.pdf');

        $pdf = new FPDI();
        $pdf->AddPage();
        $pdf->setSourceFile($templatePath);
        $templateId = $pdf->importPage(1);
        $pdf->useTemplate($templateId, 0, 0, 210);
        $pdf->SetFont('Arial', '', 6);

        if ($getSurvey->pelanggan && $getSurvey->pelanggan->foto) {
            $imagePath = storage_path('app/public/pelanggan/' . $getSurvey->pelanggan->foto);
            if (file_exists($imagePath)) {
                list($imgWidth, $imgHeight) = getimagesize($imagePath);
                $boxWidth = 19;
                $boxHeight = 30;
                $widthScale = $boxWidth / $imgWidth;
                $heightScale = $boxHeight / $imgHeight;
                $scale = min($widthScale, $heightScale);
                $finalWidth = $imgWidth * $scale;
                $finalHeight = $imgHeight * $scale;
                $x = 164 + ($boxWidth - $finalWidth) / 2;
                $y = 0 + ($boxHeight - $finalHeight) / 2;
                $pdf->Image($imagePath, $x, $y, $finalWidth, $finalHeight);
            }
        }

        // Teks isi template
        $pdf->SetXY(30, 36);
        $pdf->Write(0,  $getSurvey->no_spk);
        $pdf->SetXY(29.3, 42.5);
        $pdf->Write(0, $getSurvey->pelanggan->nama_pelanggan);
        $pdf->SetXY(29.3, 48.2);
        $pdf->MultiCell(90, 3, $getSurvey->pelanggan->alamat, 0, 'L');
        $pdf->SetXY(29.3, 56.2);
        $pdf->Write(1, $getSurvey->layanan);
        $pdf->SetXY(29.5, 63);
        $pdf->Write(1, $getSurvey->media);
        $pdf->SetXY(29.3, 71.5);
        $pdf->Write(1, $getSurvey->nama_site);
        $pdf->SetXY(29.3, 74.8);
        $pdf->MultiCell(150, 3, $getSurvey->alamat_pemasangan, 0, 'L');
        $pdf->SetXY(137, 36.2);
        $pdf->Write(0, $formattedDate);
        $pdf->SetXY(137, 42.2);
        $pdf->Write(0, $getSurvey->nama_pic);
        $pdf->SetXY(137, 45.4);
        $pdf->Write(0, $getSurvey->no_pic);
        $pdf->SetXY(137, 49.5);
        // Ubah format tanggal dulu
        $tanggal = date('d F Y', strtotime($getSurvey->tanggal_rfs));
        $pdf->Write(1, $tanggal);
        $pdf->SetXY(137.2, 56.4);
        $pdf->Write(1, $getSurvey->no_jaringan);
        $x = 137;
        $y = 63.1;

        $bandwidth = $getSurvey->bandwidth;
        $satuan    = $getSurvey->satuan;

        $pdf->SetXY($x, $y);
        $pdf->Write(1, $bandwidth);

        // hitung lebar teks bandwidth
        $textWidth = $pdf->GetStringWidth($bandwidth);

        // jarak kecil antar teks (atur sesuai kebutuhan)
        $gap = 1;

        // set posisi satuan tepat setelah bandwidth
        $pdf->SetXY($x + $textWidth + $gap, $y);
        $pdf->Write(1, $satuan);

        $pdf->SetXY(56, 132);
        $pdf->Write(1, $getSurvey->vlan);
        $pdf->SetXY(56, 129);
        $pdf->Write(1, $getSurvey->nni);
        $writer = new PngWriter();

        // Ambil data user login dan survey
        $namaUser = $getSurvey->admin->name ?? 'Unknown';
        $noSpk = $getSurvey->no_spk ?? 'N/A';
        $tanggalSurvey = now()->format('d-m-Y');

        // Format teks QR
        $qrText = "=== Tanda Tangan Digital ===\n" .
            "Nama Penerbit : {$namaUser}\n" .
            "Jabatan        : Admin\n" .
            "Tanggal Survey : {$formattedDate}\n" .
            "Nomor SPK      : {$noSpk}\n" .
            "Status Surat   : Disetujui ✔\n";

        // Buat QR code
        $qrCode = new QrCode(
            data: $qrText,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Low,
            size: 300,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,

        );

        // Tulis hasil
        $result = $writer->write($qrCode, null);
        $tempQrPath = storage_path('app/public/temp_qr.png');
        $result->saveToFile($tempQrPath);
        $sanitizedFileName = str_replace(['/', '\\'], '-', $getSurvey->no_spk);

        if (file_exists($tempQrPath)) {
            $pdf->Image($tempQrPath, 29.5, 260, 16, 16); // kanan bawah
        }

        return response()->streamDownload(function () use ($pdf, $tempQrPath) {
            $pdf->Output();
            if (file_exists($tempQrPath)) {
                unlink($tempQrPath);
            }
        }, "work_order_survey_{$sanitizedFileName}.pdf");
    }

    public function printInstalasiPDF($id)
    {
        $getInstall = WorkOrderInstall::findOrFail($id);
        $tanggalInstalasi = now()->format('d-m-Y');

        $formattedDate = Carbon::parse($tanggalInstalasi)->translatedFormat('l, d F Y');
        $templatePath = resource_path('pdf/instalasi.pdf');


        $pdf = new FPDI();
        $pdf->AddPage();
        $pdf->setSourceFile($templatePath);
        $templateId = $pdf->importPage(1);
        $pdf->useTemplate($templateId, 0, 0, 210);
        $pdf->SetFont('Arial', '', 6);

        if ($getInstall->pelanggan && $getInstall->pelanggan->foto) {
            $imagePath = storage_path('app/public/pelanggan/' . $getInstall->pelanggan->foto);
            if (file_exists($imagePath)) {
                list($imgWidth, $imgHeight) = getimagesize($imagePath);
                $boxWidth = 19;
                $boxHeight = 30;
                $widthScale = $boxWidth / $imgWidth;
                $heightScale = $boxHeight / $imgHeight;
                $scale = min($widthScale, $heightScale);
                $finalWidth = $imgWidth * $scale;
                $finalHeight = $imgHeight * $scale;
                $x = 164 + ($boxWidth - $finalWidth) / 2;
                $y = 0 + ($boxHeight - $finalHeight) / 2;
                $pdf->Image($imagePath, $x, $y, $finalWidth, $finalHeight);
            }
        }

        // Teks isi template
        $pdf->SetXY(30, 36);
        $pdf->Write(0,  $getInstall->no_spk);
        $pdf->SetXY(29.3, 42.5);
        $pdf->Write(0, $getInstall->pelanggan->nama_pelanggan);
        $pdf->SetXY(29.3, 48.2);
        $pdf->MultiCell(90, 3, $getInstall->pelanggan->alamat, 0, 'L');
        $pdf->SetXY(29.3, 56.2);
        $pdf->Write(1, $getInstall->layanan);
        $pdf->SetXY(29.5, 63);
        $pdf->Write(1, $getInstall->media);
        $pdf->SetXY(29.3, 71.5);
        $pdf->Write(1, $getInstall->nama_site);
        $pdf->SetXY(29.3, 74.8);
        $pdf->MultiCell(150, 3, $getInstall->alamat_pemasangan, 0, 'L');
        $pdf->SetXY(137, 36.2);
        $pdf->Write(0, $formattedDate);
        $pdf->SetXY(137, 42.2);
        $pdf->Write(0, $getInstall->nama_pic);
        $pdf->SetXY(137, 45.4);
        $pdf->Write(0, $getInstall->no_pic);
        $pdf->SetXY(137, 49.5);
        // Ubah format tanggal dulu
        $tanggal = date('d F Y', strtotime($getInstall->tanggal_rfs));
        $pdf->Write(1, $tanggal);
        $pdf->SetXY(137.2, 56.4);
        $pdf->Write(1, $getInstall->no_jaringan);
        $x = 137;
        $y = 63.1;

        $bandwidth = $getInstall->bandwidth;
        $satuan    = $getInstall->satuan;

        $pdf->SetXY($x, $y);
        $pdf->Write(1, $bandwidth);

        // hitung lebar teks bandwidth
        $textWidth = $pdf->GetStringWidth($bandwidth);

        // jarak kecil antar teks (atur sesuai kebutuhan)
        $gap = 1;

        // set posisi satuan tepat setelah bandwidth
        $pdf->SetXY($x + $textWidth + $gap, $y);
        $pdf->Write(1, $satuan);

        $pdf->SetXY(56, 132);
        $pdf->Write(1, $getInstall->vlan);
        $pdf->SetXY(56, 129);
        $pdf->Write(1, $getInstall->nni);
        $writer = new PngWriter();

        // Ambil data user login dan survey
        $namaUser = $getInstall->admin->name ?? 'Unknown';

        $noSpk = $getInstall->no_spk ?? 'N/A';
        $tanggalInstalasi = now()->format('d-m-Y');

        // Format teks QR
        $qrText = "=== Tanda Tangan Digital ===\n" .
            "Nama Penerbit : {$namaUser}\n" .
            "Jabatan        : Admin\n" .
            "Tanggal Instalasi : {$formattedDate}\n" .
            "Nomor SPK      : {$noSpk}\n" .
            "Status Surat   : Disetujui ✔\n";


        // Buat QR code
        $qrCode = new QrCode(
            data: $qrText,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Low,
            size: 300,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,

        );



        // Tulis hasil
        $result = $writer->write($qrCode, null);
        $tempQrPath = storage_path('app/public/temp_qr.png');
        $result->saveToFile($tempQrPath);
        $sanitizedFileName = str_replace(['/', '\\'], '-', $getInstall->no_spk);

        if (file_exists($tempQrPath)) {
            $pdf->Image($tempQrPath, 29.5, 260, 16, 16); // kanan bawah
        }

        return response()->streamDownload(function () use ($pdf, $tempQrPath) {
            $pdf->Output();
            if (file_exists($tempQrPath)) {
                unlink($tempQrPath);
            }
        }, "work_order_instalasi_{$sanitizedFileName}.pdf");
    }
    public function printMaintenancePDF($id)
    {
        $getMaintenance = WorkOrderMaintenance::findOrFail($id);
        $tanggalMaintenance = now()->format('d-m-Y');
        $formattedDate = Carbon::parse($tanggalMaintenance)->translatedFormat('l, d F Y');
        $templatePath = resource_path('pdf/mt.pdf');

        $pdf = new FPDI();
        $pdf->AddPage();
        $pdf->setSourceFile($templatePath);
        $templateId = $pdf->importPage(1);
        $pdf->useTemplate($templateId, 0, 0, 210);
        $pdf->SetFont('Arial', '', 6);

        if ($getMaintenance->onlineBilling->pelanggan && $getMaintenance->onlineBilling->pelanggan->foto) {
            $imagePath = storage_path('app/public/pelanggan/' . $getMaintenance->onlineBilling->pelanggan->foto);
            if (file_exists($imagePath)) {
                list($imgWidth, $imgHeight) = getimagesize($imagePath);
                $boxWidth = 22;
                $boxHeight = 30;
                $widthScale = $boxWidth / $imgWidth;
                $heightScale = $boxHeight / $imgHeight;
                $scale = min($widthScale, $heightScale);
                $finalWidth = $imgWidth * $scale;
                $finalHeight = $imgHeight * $scale;
                $x = 164 + ($boxWidth - $finalWidth) / 2;
                $y = 5 + ($boxHeight - $finalHeight) / 2;
                $pdf->Image($imagePath, $x, $y, $finalWidth, $finalHeight);
            }
        }

        // Teks isi template
        $pdf->SetXY(40, 32.7);
        $pdf->Write(0,  $getMaintenance->no_spk);
        $pdf->SetXY(40, 43.5);
        $pdf->Write(0, $getMaintenance->onlineBilling->pelanggan->nama_pelanggan);
        $pdf->SetXY(40, 51);
        $pdf->MultiCell(65, 3, $getMaintenance->onlineBilling->pelanggan->alamat, 0, 'L');
        $pdf->SetXY(40, 60.9);
        $pdf->Write(1, $getMaintenance->onlineBilling->layanan);
        $pdf->SetXY(40, 69.7);
        $pdf->Write(1, $getMaintenance->onlineBilling->media);
        $pdf->SetXY(40, 85.5);
        $pdf->Write(1, $getMaintenance->onlineBilling->nama_site);
        $pdf->SetXY(40, 87.5);
        $pdf->MultiCell(140, 3, $getMaintenance->onlineBilling->alamat_pemasangan, 0, 'L');
        $pdf->SetXY(140, 32.7);
        $pdf->Write(0, $formattedDate);
        $pdf->SetXY(140, 43.5);
        $pdf->Write(0, $getMaintenance->onlineBilling->nama_pic);
        $pdf->SetXY(140, 52.5);
        $pdf->Write(0, $getMaintenance->onlineBilling->no_pic);
        $pdf->SetXY(140, 60.9);
        $pdf->Write(1, $getMaintenance->onlineBilling->no_jaringan);
        $pdf->SetXY(140, 69.9);
        $pdf->Write(1, $getMaintenance->onlineBilling->bandwidth);
        $pdf->SetXY(143, 70);
        $pdf->Write(1, $getMaintenance->onlineBilling->satuan);
        $pdf->SetXY(140, 78.5);
        $pdf->Write(1, $getMaintenance->onlineBilling->vlan);

        $writer = new PngWriter();

        // Ambil data user login dan survey
        $user = Auth::user();
        $namaUser = $user->name ?? 'Unknown';
        $noSpk = $getMaintenance->no_spk ?? 'N/A';
        $tanggalMaintenance = now()->format('d-m-Y');

        // Format teks QR
        $qrText = "=== Tanda Tangan Digital ===\n" .
            "Nama Penerbit : {$namaUser}\n" .
            "Jabatan        : PSB\n" .
            "Tanggal Maintenance : {$formattedDate}\n" .
            "Nomor SPK      : {$noSpk}\n" .
            "Status Surat   : Disetujui ✔\n";


        // Buat QR code
        $qrCode = new QrCode(
            data: $qrText,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Low,
            size: 300,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,

        );



        // Tulis hasil
        $result = $writer->write($qrCode, null);
        $tempQrPath = storage_path('app/public/temp_qr.png');
        $result->saveToFile($tempQrPath);
        $sanitizedFileName = str_replace(['/', '\\'], '-', $getMaintenance->no_spk);

        if (file_exists($tempQrPath)) {
            $pdf->Image($tempQrPath, 29.5, 266, 16, 16); // kanan bawah
        }

        return response()->streamDownload(function () use ($pdf, $tempQrPath) {
            $pdf->Output();
            if (file_exists($tempQrPath)) {
                unlink($tempQrPath);
            }
        }, "work_order_maintenance_{$sanitizedFileName}.pdf");
    }

    public function printUpgradePDF($id)
    {
        $getUpgrade = WorkOrderUpgrade::findOrFail($id);
        $tanggalUpgrade = now()->format('d-m-Y');
        $formattedDate = Carbon::parse($tanggalUpgrade)->translatedFormat('l, d F Y');
        $templatePath = resource_path('pdf/upgrade.pdf');

        $pdf = new FPDI();
        $pdf->AddPage();
        $pdf->setSourceFile($templatePath);
        $templateId = $pdf->importPage(1);
        $pdf->useTemplate($templateId, 0, 0, 210);
        $pdf->SetFont('Arial', '', 6);

        if ($getUpgrade->onlineBilling->pelanggan && $getUpgrade->onlineBilling->pelanggan->foto) {
            $imagePath = storage_path('app/public/pelanggan/' . $getUpgrade->onlineBilling->pelanggan->foto);
            if (file_exists($imagePath)) {
                list($imgWidth, $imgHeight) = getimagesize($imagePath);
                $boxWidth = 19;
                $boxHeight = 30;
                $widthScale = $boxWidth / $imgWidth;
                $heightScale = $boxHeight / $imgHeight;
                $scale = min($widthScale, $heightScale);
                $finalWidth = $imgWidth * $scale;
                $finalHeight = $imgHeight * $scale;
                $x = 164 + ($boxWidth - $finalWidth) / 2;
                $y = 0 + ($boxHeight - $finalHeight) / 2;
                $pdf->Image($imagePath, $x, $y, $finalWidth, $finalHeight);
            }
        }

        // Teks isi template
        $pdf->SetXY(30, 36);
        $pdf->Write(0,  $getUpgrade->no_spk);
        $pdf->SetXY(29.3, 42.5);
        $pdf->Write(0, $getUpgrade->onlineBilling->pelanggan->nama_pelanggan);
        $pdf->SetXY(29.3, 48.2);
        $pdf->MultiCell(90, 3, $getUpgrade->onlineBilling->pelanggan->alamat, 0, 'L');
        $pdf->SetXY(29.3, 56.2);
        $pdf->Write(1, $getUpgrade->onlineBilling->layanan);
        $pdf->SetXY(29.5, 63);
        $pdf->Write(1, $getUpgrade->onlineBilling->media);
        $pdf->SetXY(29.3, 71.5);
        $pdf->Write(1, $getUpgrade->onlineBilling->nama_site);
        $pdf->SetXY(29.3, 74.8);
        $pdf->MultiCell(150, 3, $getUpgrade->onlineBilling->alamat_pemasangan, 0, 'L');
        $pdf->SetXY(137, 36.2);
        $pdf->Write(0, $formattedDate);
        $pdf->SetXY(137, 42.2);
        $pdf->Write(0, $getUpgrade->onlineBilling->nama_pic);
        $pdf->SetXY(137, 45.4);
        $pdf->Write(0, $getUpgrade->onlineBilling->no_pic);

        $pdf->SetXY(137.2, 56.4);
        $pdf->Write(1, $getUpgrade->onlineBilling->no_jaringan);
        $x = 137;
        $y = 63.1;

        $bandwidth = $getUpgrade->bandwidth_baru;
        $satuan    = $getUpgrade->satuan;

        $pdf->SetXY($x, $y);
        $pdf->Write(1, $bandwidth);

        // hitung lebar teks bandwidth
        $textWidth = $pdf->GetStringWidth($bandwidth);

        // jarak kecil antar teks (atur sesuai kebutuhan)
        $gap = 1;

        // set posisi satuan tepat setelah bandwidth
        $pdf->SetXY($x + $textWidth + $gap, $y);
        $pdf->Write(1, $satuan);

        $pdf->SetXY(56, 132);
        $pdf->Write(1, $getUpgrade->onlineBilling->vlan);
        $pdf->SetXY(56, 129);
        $pdf->Write(1, $getUpgrade->onlineBilling->nni);
        $writer = new PngWriter();

        // Ambil data user login dan survey
        $namaUser = $getUpgrade->admin->name ?? 'Unknown';

        $noSpk = $getUpgrade->no_spk ?? 'N/A';
        $tanggalUpgrade = now()->format('d-m-Y');

        // Format teks QR
        $qrText = "=== Tanda Tangan Digital ===\n" .
            "Nama Penerbit : {$namaUser}\n" .
            "Jabatan        : Admin\n" .
            "Tanggal Upgrade : {$formattedDate}\n" .
            "Nomor SPK      : {$noSpk}\n" .
            "Status Surat   : Disetujui ✔\n";


        // Buat QR code
        $qrCode = new QrCode(
            data: $qrText,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Low,
            size: 300,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,

        );



        // Tulis hasil
        $result = $writer->write($qrCode, null);
        $tempQrPath = storage_path('app/public/temp_qr.png');
        $result->saveToFile($tempQrPath);
        $sanitizedFileName = str_replace(['/', '\\'], '-', $getUpgrade->no_spk);

        if (file_exists($tempQrPath)) {
            $pdf->Image($tempQrPath, 29.5, 260, 16, 16); // kanan bawah
        }

        return response()->streamDownload(function () use ($pdf, $tempQrPath) {
            $pdf->Output();
            if (file_exists($tempQrPath)) {
                unlink($tempQrPath);
            }
        }, "work_order_upgrade_{$sanitizedFileName}.pdf");
    }

    public function printDowngradePDF($id)
    {
        $getDowngrade = WorkOrderDowngrade::findOrFail($id);
        $tanggalDowngrade = now()->format('d-m-Y');
        $formattedDate = Carbon::parse($tanggalDowngrade)->translatedFormat('l, d F Y');
        $templatePath = resource_path('pdf/downgrade.pdf');

        $pdf = new FPDI();
        $pdf->AddPage();
        $pdf->setSourceFile($templatePath);
        $templateId = $pdf->importPage(1);
        $pdf->useTemplate($templateId, 0, 0, 210);
        $pdf->SetFont('Arial', '', 6);

        if ($getDowngrade->onlineBilling->pelanggan && $getDowngrade->onlineBilling->pelanggan->foto) {
            $imagePath = storage_path('app/public/pelanggan/' . $getDowngrade->onlineBilling->pelanggan->foto);
            if (file_exists($imagePath)) {
                list($imgWidth, $imgHeight) = getimagesize($imagePath);
                $boxWidth = 19;
                $boxHeight = 30;
                $widthScale = $boxWidth / $imgWidth;
                $heightScale = $boxHeight / $imgHeight;
                $scale = min($widthScale, $heightScale);
                $finalWidth = $imgWidth * $scale;
                $finalHeight = $imgHeight * $scale;
                $x = 164 + ($boxWidth - $finalWidth) / 2;
                $y = 0 + ($boxHeight - $finalHeight) / 2;
                $pdf->Image($imagePath, $x, $y, $finalWidth, $finalHeight);
            }
        }

        // Teks isi template
        $pdf->SetXY(30, 36);
        $pdf->Write(0,  $getDowngrade->no_spk);
        $pdf->SetXY(29.3, 42.5);
        $pdf->Write(0, $getDowngrade->onlineBilling->pelanggan->nama_pelanggan);
        $pdf->SetXY(29.3, 48.2);
        $pdf->MultiCell(90, 3, $getDowngrade->onlineBilling->pelanggan->alamat, 0, 'L');
        $pdf->SetXY(29.3, 56.2);
        $pdf->Write(1, $getDowngrade->onlineBilling->layanan);
        $pdf->SetXY(29.5, 63);
        $pdf->Write(1, $getDowngrade->onlineBilling->media);
        $pdf->SetXY(29.3, 71.5);
        $pdf->Write(1, $getDowngrade->onlineBilling->nama_site);
        $pdf->SetXY(29.3, 74.8);
        $pdf->MultiCell(150, 3, $getDowngrade->onlineBilling->alamat_pemasangan, 0, 'L');
        $pdf->SetXY(137, 36.2);
        $pdf->Write(0, $formattedDate);
        $pdf->SetXY(137, 42.2);
        $pdf->Write(0, $getDowngrade->onlineBilling->nama_pic);
        $pdf->SetXY(137, 45.4);
        $pdf->Write(0, $getDowngrade->onlineBilling->no_pic);

        $pdf->SetXY(137.2, 56.4);
        $pdf->Write(1, $getDowngrade->onlineBilling->no_jaringan);
        $x = 137;
        $y = 63.1;

        $bandwidth = $getDowngrade->bandwidth_baru;
        $satuan    = $getDowngrade->satuan;

        $pdf->SetXY($x, $y);
        $pdf->Write(1, $bandwidth);

        // hitung lebar teks bandwidth
        $textWidth = $pdf->GetStringWidth($bandwidth);

        // jarak kecil antar teks (atur sesuai kebutuhan)
        $gap = 1;

        // set posisi satuan tepat setelah bandwidth
        $pdf->SetXY($x + $textWidth + $gap, $y);
        $pdf->Write(1, $satuan);

        $pdf->SetXY(56, 132);
        $pdf->Write(1, $getDowngrade->onlineBilling->vlan);
        $pdf->SetXY(56, 129);
        $pdf->Write(1, $getDowngrade->onlineBilling->nni);
        $writer = new PngWriter();

        // Ambil data user login dan survey
        $namaUser = $getDowngrade->admin->name ?? 'Unknown';

        $noSpk = $getDowngrade->no_spk ?? 'N/A';
        $tanggalDowngrade = now()->format('d-m-Y');

        // Format teks QR
        $qrText = "=== Tanda Tangan Digital ===\n" .
            "Nama Penerbit : {$namaUser}\n" .
            "Jabatan        : Admin\n" .
            "Tanggal Downgrade : {$formattedDate}\n" .
            "Nomor SPK      : {$noSpk}\n" .
            "Status Surat   : Disetujui ✔\n";


        // Buat QR code
        $qrCode = new QrCode(
            data: $qrText,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Low,
            size: 300,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,

        );



        // Tulis hasil
        $result = $writer->write($qrCode, null);
        $tempQrPath = storage_path('app/public/temp_qr.png');
        $result->saveToFile($tempQrPath);
        $sanitizedFileName = str_replace(['/', '\\'], '-', $getDowngrade->no_spk);

        if (file_exists($tempQrPath)) {
            $pdf->Image($tempQrPath, 29.5, 260, 16, 16); // kanan bawah
        }

        return response()->streamDownload(function () use ($pdf, $tempQrPath) {
            $pdf->Output();
            if (file_exists($tempQrPath)) {
                unlink($tempQrPath);
            }
        }, "work_order_downgrade_{$sanitizedFileName}.pdf");
    }

    public function printGantiVendorPDF($id)
    {
        $getGantivendor = WorkOrderGantiVendor::findOrFail($id);
        $tanggalGantiVendor = now()->format('d-m-Y');
        $formattedDate = Carbon::parse($tanggalGantiVendor)->translatedFormat('l, d F Y');
        $templatePath = resource_path('pdf/gantivendor.pdf');

        $pdf = new FPDI();
        $pdf->AddPage();
        $pdf->setSourceFile($templatePath);
        $templateId = $pdf->importPage(1);
        $pdf->useTemplate($templateId, 0, 0, 210);
        $pdf->SetFont('Arial', '', 6);

        if ($getGantivendor->onlineBilling->pelanggan && $getGantivendor->onlineBilling->pelanggan->foto) {
            $imagePath = storage_path('app/public/pelanggan/' . $getGantivendor->onlineBilling->pelanggan->foto);
            if (file_exists($imagePath)) {
                list($imgWidth, $imgHeight) = getimagesize($imagePath);
                $boxWidth = 19;
                $boxHeight = 30;
                $widthScale = $boxWidth / $imgWidth;
                $heightScale = $boxHeight / $imgHeight;
                $scale = min($widthScale, $heightScale);
                $finalWidth = $imgWidth * $scale;
                $finalHeight = $imgHeight * $scale;
                $x = 164 + ($boxWidth - $finalWidth) / 2;
                $y = 0 + ($boxHeight - $finalHeight) / 2;
                $pdf->Image($imagePath, $x, $y, $finalWidth, $finalHeight);
            }
        }

        // Teks isi template
        $pdf->SetXY(30, 36);
        $pdf->Write(0,  $getGantivendor->no_spk);
        $pdf->SetXY(29.3, 42.5);
        $pdf->Write(0, $getGantivendor->onlineBilling->pelanggan->nama_pelanggan);
        $pdf->SetXY(29.3, 48.2);
        $pdf->MultiCell(90, 3, $getGantivendor->onlineBilling->pelanggan->alamat, 0, 'L');
        $pdf->SetXY(29.3, 56.2);
        $pdf->Write(1, $getGantivendor->onlineBilling->layanan);
        $pdf->SetXY(29.5, 63);
        $pdf->Write(1, $getGantivendor->onlineBilling->media);
        $pdf->SetXY(29.3, 71.5);
        $pdf->Write(1, $getGantivendor->onlineBilling->nama_site);
        $pdf->SetXY(29.3, 74.8);
        $pdf->MultiCell(150, 3, $getGantivendor->onlineBilling->alamat_pemasangan, 0, 'L');
        $pdf->SetXY(137, 36.2);
        $pdf->Write(0, $formattedDate);
        $pdf->SetXY(137, 42.2);
        $pdf->Write(0, $getGantivendor->onlineBilling->nama_pic);
        $pdf->SetXY(137, 45.4);
        $pdf->Write(0, $getGantivendor->onlineBilling->no_pic);

        $pdf->SetXY(137.2, 56.4);
        $pdf->Write(1, $getGantivendor->onlineBilling->no_jaringan);
        $x = 137;
        $y = 63.1;

        $bandwidth = $getGantivendor->onlineBilling->bandwidth;
        $satuan    = $getGantivendor->onlineBilling->satuan;

        $pdf->SetXY($x, $y);
        $pdf->Write(1, $bandwidth);

        // hitung lebar teks bandwidth
        $textWidth = $pdf->GetStringWidth($bandwidth);

        // jarak kecil antar teks (atur sesuai kebutuhan)
        $gap = 1;

        // set posisi satuan tepat setelah bandwidth
        $pdf->SetXY($x + $textWidth + $gap, $y);
        $pdf->Write(1, $satuan);

        $pdf->SetXY(56, 132);
        $pdf->Write(1, $getGantivendor->onlineBilling->vlan);
        $pdf->SetXY(56, 129);
        $pdf->Write(1, $getGantivendor->onlineBilling->nni);
        $writer = new PngWriter();

        // Ambil data user login dan survey
        $namaUser = $getGantivendor->approvedBy->name ?? 'Unknown';

        $noSpk = $getGantivendor->no_spk ?? 'N/A';
        $tanggalGantiVendor = now()->format('d-m-Y');

        // Format teks QR
        $qrText = "=== Tanda Tangan Digital ===\n" .
            "Nama Penerbit : {$namaUser}\n" .
            "Jabatan        : Admin\n" .
            "Tanggal Ganti Layanan : {$formattedDate}\n" .
            "Nomor SPK      : {$noSpk}\n" .
            "Status Surat   : Disetujui ✔\n";


        // Buat QR code
        $qrCode = new QrCode(
            data: $qrText,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Low,
            size: 300,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,

        );



        // Tulis hasil
        $result = $writer->write($qrCode, null);
        $tempQrPath = storage_path('app/public/temp_qr.png');
        $result->saveToFile($tempQrPath);
        $sanitizedFileName = str_replace(['/', '\\'], '-', $getGantivendor->no_spk);

        if (file_exists($tempQrPath)) {
            $pdf->Image($tempQrPath, 29.5, 260, 16, 16); // kanan bawah
        }

        return response()->streamDownload(function () use ($pdf, $tempQrPath) {
            $pdf->Output();
            if (file_exists($tempQrPath)) {
                unlink($tempQrPath);
            }
        }, "work_order_gantivendor_{$sanitizedFileName}.pdf");
    }
    public function printDismantlePDF($id)
    {
        $getDismantle = WorkOrderDismantle::findOrFail($id);
        $tanggalDismantle = now()->format('d-m-Y');
        $formattedDate = Carbon::parse($tanggalDismantle)->translatedFormat('l, d F Y');
        $templatePath = resource_path('pdf/dismantle.pdf');

        $pdf = new FPDI();
        $pdf->AddPage();
        $pdf->setSourceFile($templatePath);
        $templateId = $pdf->importPage(1);
        $pdf->useTemplate($templateId, 0, 0, 210);
        $pdf->SetFont('Arial', '', 6);

        if ($getDismantle->onlineBilling->pelanggan && $getDismantle->onlineBilling->pelanggan->foto) {
            $imagePath = storage_path('app/public/pelanggan/' . $getDismantle->onlineBilling->pelanggan->foto);
            if (file_exists($imagePath)) {
                list($imgWidth, $imgHeight) = getimagesize($imagePath);
                $boxWidth = 19;
                $boxHeight = 30;
                $widthScale = $boxWidth / $imgWidth;
                $heightScale = $boxHeight / $imgHeight;
                $scale = min($widthScale, $heightScale);
                $finalWidth = $imgWidth * $scale;
                $finalHeight = $imgHeight * $scale;
                $x = 164 + ($boxWidth - $finalWidth) / 2;
                $y = 0 + ($boxHeight - $finalHeight) / 2;
                $pdf->Image($imagePath, $x, $y, $finalWidth, $finalHeight);
            }
        }


        // Teks isi template
        $pdf->SetXY(30, 36);
        $pdf->Write(0,  $getDismantle->no_spk);
        $pdf->SetXY(29.3, 42.5);
        $pdf->Write(0, $getDismantle->onlineBilling->pelanggan->nama_pelanggan);
        $pdf->SetXY(29.3, 48.2);
        $pdf->MultiCell(90, 3, $getDismantle->onlineBilling->pelanggan->alamat, 0, 'L');
        $pdf->SetXY(29.3, 56.2);
        $pdf->Write(1, $getDismantle->onlineBilling->layanan);
        $pdf->SetXY(29.5, 63);
        $pdf->Write(1, $getDismantle->onlineBilling->media);
        $pdf->SetXY(29.3, 71.5);
        $pdf->Write(1, $getDismantle->onlineBilling->nama_site);
        $pdf->SetXY(29.3, 74.8);
        $pdf->MultiCell(150, 3, $getDismantle->onlineBilling->alamat_pemasangan, 0, 'L');
        $pdf->SetXY(137, 36.2);
        $pdf->Write(0, $formattedDate);
        $pdf->SetXY(137, 42.2);
        $pdf->Write(0, $getDismantle->onlineBilling->nama_pic);
        $pdf->SetXY(137, 45.4);
        $pdf->Write(0, $getDismantle->onlineBilling->no_pic);

        $pdf->SetXY(137.2, 56.4);
        $pdf->Write(1, $getDismantle->onlineBilling->no_jaringan);
        $x = 137;
        $y = 63.1;

        $bandwidth = $getDismantle->onlineBilling->bandwidth;
        $satuan    = $getDismantle->onlineBilling->satuan;

        $pdf->SetXY($x, $y);
        $pdf->Write(1, $bandwidth);

        // hitung lebar teks bandwidth
        $textWidth = $pdf->GetStringWidth($bandwidth);

        // jarak kecil antar teks (atur sesuai kebutuhan)
        $gap = 1;

        // set posisi satuan tepat setelah bandwidth
        $pdf->SetXY($x + $textWidth + $gap, $y);
        $pdf->Write(1, $satuan);

        $pdf->SetXY(56, 132);
        $pdf->Write(1, $getDismantle->onlineBilling->vlan);
        $pdf->SetXY(56, 129);
        $pdf->Write(1, $getDismantle->onlineBilling->nni);
        $writer = new PngWriter();

        // Ambil data user login dan survey
        $namaUser = $getDismantle->admin->name ?? 'Unknown';

        $noSpk = $getDismantle->no_spk ?? 'N/A';
        $tanggalDismantle = now()->format('d-m-Y');

        // Format teks QR
        $qrText = "=== Tanda Tangan Digital ===\n" .
            "Nama Penerbit : {$namaUser}\n" .
            "Jabatan        : Admin\n" .
            "Tanggal Dismantle : {$formattedDate}\n" .
            "Nomor SPK      : {$noSpk}\n" .
            "Status Surat   : Disetujui ✔\n";


        // Buat QR code
        $qrCode = new QrCode(
            data: $qrText,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Low,
            size: 300,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,

        );



        // Tulis hasil
        $result = $writer->write($qrCode, null);
        $tempQrPath = storage_path('app/public/temp_qr.png');
        $result->saveToFile($tempQrPath);
        $sanitizedFileName = str_replace(['/', '\\'], '-', $getDismantle->no_spk);

        if (file_exists($tempQrPath)) {
            $pdf->Image($tempQrPath, 29.5, 260, 16, 16); // kanan bawah
        }

        return response()->streamDownload(function () use ($pdf, $tempQrPath) {
            $pdf->Output();
            if (file_exists($tempQrPath)) {
                unlink($tempQrPath);
            }
        }, "work_order_dismantle_{$sanitizedFileName}.pdf");
    }

    public function printRelokasiPDF($id)
    {
        $getRelokasi = WorkOrderRelokasi::findOrFail($id);
        $tanggalRelokasi = now()->format('d-m-Y');
        $formattedDate = Carbon::parse($tanggalRelokasi)->translatedFormat('l, d F Y');
        $templatePath = resource_path('pdf/relokasi.pdf');

        $pdf = new FPDI();
        $pdf->AddPage();
        $pdf->setSourceFile($templatePath);
        $templateId = $pdf->importPage(1);
        $pdf->useTemplate($templateId, 0, 0, 210);
        $pdf->SetFont('Arial', '', 6);

        if ($getRelokasi->onlineBilling->pelanggan && $getRelokasi->onlineBilling->pelanggan->foto) {
            $imagePath = storage_path('app/public/pelanggan/' . $getRelokasi->onlineBilling->pelanggan->foto);
            if (file_exists($imagePath)) {
                list($imgWidth, $imgHeight) = getimagesize($imagePath);
                $boxWidth = 19;
                $boxHeight = 30;
                $widthScale = $boxWidth / $imgWidth;
                $heightScale = $boxHeight / $imgHeight;
                $scale = min($widthScale, $heightScale);
                $finalWidth = $imgWidth * $scale;
                $finalHeight = $imgHeight * $scale;
                $x = 164 + ($boxWidth - $finalWidth) / 2;
                $y = 0 + ($boxHeight - $finalHeight) / 2;
                $pdf->Image($imagePath, $x, $y, $finalWidth, $finalHeight);
            }
        }


        // Teks isi template
        $pdf->SetXY(30, 36);
        $pdf->Write(0,  $getRelokasi->no_spk);
        $pdf->SetXY(29.3, 42.5);
        $pdf->Write(0, $getRelokasi->onlineBilling->pelanggan->nama_pelanggan);
        $pdf->SetXY(29.3, 48.2);
        $pdf->MultiCell(90, 3, $getRelokasi->onlineBilling->pelanggan->alamat, 0, 'L');
        $pdf->SetXY(29.3, 56.2);
        $pdf->Write(1, $getRelokasi->onlineBilling->layanan);
        $pdf->SetXY(29.5, 63);
        $pdf->Write(1, $getRelokasi->onlineBilling->media);
        $pdf->SetXY(29.3, 71.5);
        $pdf->Write(1, $getRelokasi->onlineBilling->nama_site);
        $pdf->SetXY(29.3, 74.8);
        $pdf->MultiCell(150, 3, $getRelokasi->onlineBilling->alamat_pemasangan, 0, 'L');
        $pdf->SetXY(137, 36.2);
        $pdf->Write(0, $formattedDate);
        $pdf->SetXY(137, 42.2);
        $pdf->Write(0, $getRelokasi->onlineBilling->nama_pic);
        $pdf->SetXY(137, 45.4);
        $pdf->Write(0, $getRelokasi->onlineBilling->no_pic);

        $pdf->SetXY(137.2, 56.4);
        $pdf->Write(1, $getRelokasi->onlineBilling->no_jaringan);
        $x = 137;
        $y = 63.1;

        $bandwidth = $getRelokasi->onlineBilling->bandwidth;
        $satuan    = $getRelokasi->onlineBilling->satuan;

        $pdf->SetXY($x, $y);
        $pdf->Write(1, $bandwidth);

        // hitung lebar teks bandwidth
        $textWidth = $pdf->GetStringWidth($bandwidth);

        // jarak kecil antar teks (atur sesuai kebutuhan)
        $gap = 1;

        // set posisi satuan tepat setelah bandwidth
        $pdf->SetXY($x + $textWidth + $gap, $y);
        $pdf->Write(1, $satuan);

        $pdf->SetXY(56, 132);
        $pdf->Write(1, $getRelokasi->onlineBilling->vlan);
        $pdf->SetXY(56, 129);
        $pdf->Write(1, $getRelokasi->onlineBilling->nni);
        $writer = new PngWriter();

        // Ambil data user login dan survey
        $namaUser = $getRelokasi->admin->name ?? 'Unknown';

        $noSpk = $getRelokasi->no_spk ?? 'N/A';
        $tanggalRelokasi = now()->format('d-m-Y');

        // Format teks QR
        $qrText = "=== Tanda Tangan Digital ===\n" .
            "Nama Penerbit : {$namaUser}\n" .
            "Jabatan        : Admin\n" .
            "Tanggal Relokasi : {$formattedDate}\n" .
            "Nomor SPK      : {$noSpk}\n" .
            "Status Surat   : Disetujui ✔\n";


        // Buat QR code
        $qrCode = new QrCode(
            data: $qrText,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Low,
            size: 300,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,

        );



        // Tulis hasil
        $result = $writer->write($qrCode, null);
        $tempQrPath = storage_path('app/public/temp_qr.png');
        $result->saveToFile($tempQrPath);
        $sanitizedFileName = str_replace(['/', '\\'], '-', $getRelokasi->no_spk);

        if (file_exists($tempQrPath)) {
            $pdf->Image($tempQrPath, 29.5, 260, 16, 16); // kanan bawah
        }

        return response()->streamDownload(function () use ($pdf, $tempQrPath) {
            $pdf->Output();
            if (file_exists($tempQrPath)) {
                unlink($tempQrPath);
            }
        }, "work_order_relokasi_{$sanitizedFileName}.pdf");
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
        return redirect()->route('psb.upgrade_show', $id)
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
            // Dapatkan semua admin (atau role yang sesuai)
            // Dapatkan semua admin (atau role yang sesuai)
            $adminUsers = User::where('is_role', 1)->get(); // 1 adalah role untuk admin

            // Buat notifikasi "Survey Completed" untuk setiap admin
            foreach ($adminUsers as $admin) {
                // Cek role pengguna
                if ($admin->is_role == 1) { // Role PSB
                    $url = route('admin.upgrade_show', ['id' => $getUpgrade->id]) . '#upgrade'; // Tambahkan #no_spk untuk PSB
                } else if ($admin->is_role == 5) { // Role Admin
                    $url = route('psb.upgrade_show', ['id' => $getUpgrade->id]) . '#upgrade'; // Tambahkan #no_spk untuk Admin
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
            return redirect()->route('psb.upgrade', $id)->with('success', 'Upgrade berhasil diselesaikan.');
        }

        return redirect()->route('psb.upgrade_show', $id)->with('success', 'Progress berhasil ditambahkan.');
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
        return redirect()->route('psb.downgrade_show', $id)
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
                } else if ($admin->is_role == 5) { // Role Admin
                    $url = route('psb.downgrade_show', ['id' => $getDowngrade->id]) . '#downgrade'; // Tambahkan #no_spk untuk Admin
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
            return redirect()->route('psb.downgrade', $id)->with('success', 'Downgrade berhasil diselesaikan.');
        }

        return redirect()->route('psb.downgrade_show', $id)->with('success', 'Progress berhasil ditambahkan.');
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
        return redirect()->route('psb.dismantle_show', $id)
            ->with('success', 'Dismantle telah disetujui.');
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
            'foto.*' => 'nullable|image|max:10240',
        ]);

        // Menyimpan progress baru
        $progress = new DismantleProgress();
        $progress->work_order_dismantle_id = $id;
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
                DismantleProgressPhoto::create([
                    'dismantle_progress_id' => $progress->id,
                    'file_path' => $fileName
                ]);
            }
        }

        // Redirect ke view upgrade atau detail upgrade berdasarkan aksi
        if ($request->action === 'complete') {
            return redirect()->route('psb.dismantle_show', $id)->with('success', 'Progress berhasil diselesaikan.');
        }

        return redirect()->route('psb.dismantle_show', $id)->with('success', 'Progress berhasil ditambahkan.');
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
            'foto.*' => 'nullable|image|max:10240',
        ]);

        // Menyimpan progress baru
        $progress = new RelokasiProgress();
        $progress->work_order_relokasi_id = $id;
        $progress->keterangan = $request->keterangan;

        // Ambil data upgrade
        $getRelokasi = WorkOrderRelokasi::findOrFail($id);

        // Set status default atau complete sesuai tombol yang ditekan
        if ($request->has('action') && $request->action === 'complete') {
            $progress->status = 'Completed'; // Ubah status progress jadi Completed

            // Ubah status upgrade menjadi Completed
            $getRelokasi->status = 'Completed';
            $getRelokasi->save();

            // Perbarui status di tabel statuses
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

            $adminUsers = User::where('is_role', 1)->get(); // 1 adalah role untuk admin

            // Buat notifikasi "Survey Completed" untuk setiap admin
            foreach ($adminUsers as $admin) {
                // Cek role pengguna
                if ($admin->is_role == 1) { // Role PSB
                    $url = route('admin.relokasi_show', ['id' => $getRelokasi->id]) . '#no_spk'; // Tambahkan #no_spk untuk PSB
                } else if ($admin->is_role == 5) { // Role Admin
                    $url = route('psb.relokasi_show', ['id' => $getRelokasi->id]) . '#no_spk'; // Tambahkan #no_spk untuk Admin
                }

                // Buat notifikasi
                Notification::create([
                    'user_id' => $admin->id,
                    'message' => 'WO Relokasi telah diselesaikan dengan No Order: ' . $getRelokasi->no_spk,
                    'url' => $url, // URL dengan hash #no_spk
                ]);
            }
        } else {
            $progress->status = 'On Progress'; // Default status progress jika belum complete

            // // Update status di tabel WorkOrderUpgrade jika belum Completed
            // if ($getRelokasi->status !== 'Completed') {
            //     $getRelokasi->status = 'On Progress';
            //     $getRelokasi->save();
            // }

            // // Perbarui status di tabel statuses
            // $status = Status::where('work_orderable_id', $getRelokasi->id)
            //     ->where('process', 'Relokasi')
            //     ->first();
            // if ($status) {
            //     $status->status = 'On Progress';
            //     $status->save();
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

        // Redirect ke view upgrade atau detail upgrade berdasarkan aksi
        if ($request->action === 'complete') {
            return redirect()->route('psb.relokasi', $id)->with('success', 'Relokasi berhasil diselesaikan.');
        }

        return redirect()->route('psb.relokasi.show', $id)->with('success', 'Progress berhasil ditambahkan.');
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
        return redirect()->route('psb.relokasi.show', $id)->with('success', 'Relokasi telah disetujui.');
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
        return redirect()->route('psb.relokasi.show', $id)->with('error', 'Relokasi telah ditolak.');
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
            'foto.*' => 'nullable|image|max:10240',
        ]);

        // Menyimpan progress baru
        $progress = new GantiVendorProgress();
        $progress->work_order_ganti_vendor_id = $id;
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
                GantiVendorProgressPhoto::create([
                    'ganti_vendor_progress_id' => $progress->id,
                    'file_path' => $fileName
                ]);
            }
        }

        // Redirect ke view upgrade atau detail upgrade berdasarkan aksi
        if ($request->action === 'complete') {
            return redirect()->route('psb.gantivendor.show', $id)->with('success', 'Progress berhasil diselesaikan.');
        }

        return redirect()->route('psb.gantivendor.show', $id)->with('success', 'Progress berhasil ditambahkan.');
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
        return redirect()->route('psb.maintenance_show', $id)->with('success', 'Maintenance telah disetujui.');
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
        return redirect()->route('psb.maintenance_show', $id)->with('error', 'Maintenance telah ditolak.');
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
                MaintenanceProgressPhoto::create([
                    'maintenance_progress_id' => $progress->id,
                    'file_path' => $fileName
                ]);
            }
        }

        // Redirect ke view upgrade atau detail upgrade berdasarkan aksi
        if ($request->action === 'complete') {
            return redirect()->route('psb.maintenance_show', $id)->with('success', 'Progress berhasil diselesaikan.');
        }

        return redirect()->route('psb.maintenance_show', $id)->with('success', 'Progress berhasil ditambahkan.');
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
            'no_penerima' => 'required|string|max:20',
            'keterangan' => 'nullable|string',
            'non_stock' => 'nullable|string',
            'kebutuhan' => 'nullable|string',

            'cart' => 'nullable|array',

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
                'message' => 'Request barang baru diajukan oleh PSB: ' . Auth::user()->name,
                'url' => $url, // URL dengan hash #request
            ]);
        }


        return redirect()->route('psb.request_barang')->with('success', 'Request barang berhasil diajukan.');
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
            'kebutuhan' => 'nullable|string',

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

        return redirect()->route('psb.request_barang')->with('success', 'Request barang berhasil diperbarui.');
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
