<?php

use App\Livewire\Users;
use GuzzleHttp\Middleware;
use App\Livewire\Chat\Chat;
use App\Livewire\Chat\Index;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GaController;
use App\Http\Controllers\NaController;
use App\Http\Controllers\NocController;
use App\Http\Controllers\PsbController;
use App\Http\Middleware\SessionTimeout;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\HelpdeskController;
use App\Http\Controllers\DashboardController;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

//halaman welcome
Route::get('/', [HomeController::class, 'index']);

//halaman login
Route::get('login', [AuthController::class, 'login']);
Route::post('login_post', [AuthController::class, 'login_post']);

//halaman register
Route::get('register', [AuthController::class, 'register']);
Route::post('register_post', [AuthController::class, 'register_post']);
Route::get('verify/{token}', [AuthController::class, 'verify']);


//halaman logout
Route::get('logout', [AuthController::class, 'logout']);

//halaman forgot password
Route::get('forgot', [AuthController::class, 'forgot']);
Route::post('forgot_post', [AuthController::class, 'forgot_post']);

//halaman reset password
Route::get('reset/{token}', [AuthController::class, 'getReset']);
Route::post('reset_post/{token}', [AuthController::class, 'postReset']);



Route::group(['middleware' => ['superadmin', SessionTimeout::class]], function () {
    Route::get('superadmin/dashboard', [DashboardController::class, 'dashboard'])->name('superadmin.dashboard');
    Route::get('superadmin/userlist', [DashboardController::class, 'userlist'])->name('superadmin.userlist');
    Route::get('superadmin/userlist/{id}', [DashboardController::class, 'unverify'])->name('superadmin.unverify');

    Route::delete('superadmin/delete/{id}', [DashboardController::class, 'destroy'])->name('userlist.destroy');

    Route::get('superadmin/survey', [DashboardController::class, 'survey'])->name('superadmin.survey');
    Route::get('superadmin/survey/{id}', [DashboardController::class, 'show'])->name('superadmin.wo_survey_show');

    Route::get('superadmin/instalasi', [DashboardController::class, 'instalasi'])->name('superadmin.instalasi');
    Route::get('superadmin/instalasi/{id}', [DashboardController::class, 'showinstalasi'])->name('superadmin.wo_instalasi_show');

    Route::get('superadmin/upgrade', [DashboardController::class, 'upgrade'])->name('superadmin.upgrade');
    Route::get('superadmin/upgrade/show/{id}', [DashboardController::class, 'upgradeShow'])->name('superadmin.upgrade_show');

    Route::get('superadmin/downgrade', [DashboardController::class, 'downgrade'])->name('superadmin.downgrade');
    Route::get('superadmin/downgrade/show/{id}', [DashboardController::class, 'downgradeShow'])->name('superadmin.downgrade_show');

    Route::get('superadmin/gantivendor', [DashboardController::class, 'gantivendor'])->name('superadmin.gantivendor');
    Route::get('superadmin/gantivendor/{id}', [DashboardController::class, 'showgantivendor'])->name('superadmin.gantivendor.show');

    Route::get('superadmin/relokasi', [DashboardController::class, 'relokasi'])->name('superadmin.relokasi');
    Route::get('superadmin/relokasi/show/{id}', [DashboardController::class, 'relokasiShow'])->name('superadmin.relokasi_show');

    Route::get('superadmin/dismantle', [DashboardController::class, 'dismantle'])->name('superadmin.dismantle');
    Route::get('superadmin/dismantle/show/{id}', [DashboardController::class, 'dismantleShow'])->name('superadmin.dismantle_show');

    Route::get('superadmin/maintenance', [DashboardController::class, 'maintenance'])->name('superadmin.maintenance');
    Route::get('superadmin/maintenance/show/{id}', [DashboardController::class, 'maintenanceShow'])->name('superadmin.maintenance_show');

    Route::get('superadmin/requestbarang', [DashboardController::class, 'requestbarang'])->name('superadmin.request_barang');
    Route::get('superadmin/requestbarang/{id}', [DashboardController::class, 'showrequest'])->name('superadmin.request_barang.show');

    Route::get('superadmin/OB', [DashboardController::class, 'OB'])->name('superadmin.OB');
    Route::get('superadmin/OB/{id}', [DashboardController::class, 'showOB'])->name('superadmin.OB_show');

    Route::get('superadmin/sitedismantle', [DashboardController::class, 'sitedismantle'])->name('superadmin.sitedismantle');
    Route::get('superadmin/sitedismantle{id}', [DashboardController::class, 'showsitedismantle'])->name('superadmin.showsitedismantle');

    Route::get('superadmin/log', [DashboardController::class, 'logact'])->name('superadmin.log');

    Route::get('superadmin/chat', Index::class)->name('superadmin.chat.index');
    Route::get('superadmin/chat/{query}', Chat::class)->name('superadmin.chat');
    Route::get('superadmin/users', Users::class)->name('superadmin.users');

    Route::post('superadmin/OB/import-online-billing', [DashboardController::class, 'importOB'])->name('import.prosesOB');
});

Route::group(['middleware' => ['admin', SessionTimeout::class]], function () {
    Route::get('admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('admin/instalasi', [AdminController::class, 'instalasi'])->name('admin.instalasi');
    Route::get('admin/instalasi/create', [AdminController::class, 'createinstalasi'])->name('admin.form_instalasi');
    Route::post('admin/instalasi', [AdminController::class, 'storeinstalasi'])->name('admin.form_storeinstalasi');
    Route::get('admin/instalasi/{id}', [AdminController::class, 'showinstalasi'])->name('admin.wo_instalasi_show');
    Route::get('admin/instalasi/edit/{id}', [AdminController::class, 'editinstalasi'])->name('admin.wo_instalasi_edit');
    Route::put('admin/instalasi/{id}', [AdminController::class, 'updateinstalasi'])->name('admin.wo_instalasi_update');
    Route::delete('admin/instalasi/{id}', [AdminController::class, 'destroyinstalasi'])->name('wo_instalasi.destroy');
    Route::patch('/admin/instalasi/{id}/cancel', [AdminController::class, 'cancelInstalasi'])->name('admin.cancel_instalasi');
    Route::post('admin/instalasi/sendBA/{id}', [AdminController::class, 'sendBA'])->name('berita_acara.send');
    Route::post('admin/instalasi/ReceiveBA/{id}', [AdminController::class, 'ReceiveBA'])->name('berita_acara.received');
    Route::get('admin/instalasi/billing/sid/{id}', [AdminController::class, 'sidform'])->name('sid.form');
    Route::post('admin/instalasi/billing/sid/{id}', [AdminController::class, 'storebilling'])->name('online_billing.store');

    // Routes untuk Pelanggan
    Route::get('/admin/pelanggan', [AdminController::class, 'pelanggan'])->name('admin.pelanggan');
    Route::get('/admin/pelanggan/create', [AdminController::class, 'CreatePelanggan'])->name('pelanggan.create');
    Route::post('/admin/pelanggan', [AdminController::class, 'StorePelanggan'])->name('pelanggan.store');
    Route::get('/admin/pelanggan/{id}/edit', [AdminController::class, 'EditPelanggan'])->name('pelanggan.edit');
    Route::put('/admin/pelanggan/{id}', [AdminController::class, 'UpdatePelanggan'])->name('pelanggan.update');
    Route::delete('/admin/pelanggan/{id}', [AdminController::class, 'HapusPelanggan'])->name('pelanggan.hapus');

    // Routes untuk Vendor
    Route::get('/admin/namavendor', [AdminController::class, 'namavendor'])->name('admin.namavendor');
    Route::get('/admin/vendor/create', [AdminController::class, 'CreateVendor'])->name('vendor.create');
    Route::post('/admin/vendor', [AdminController::class, 'StoreVendor'])->name('vendor.store');
    Route::get('/admin/vendor/{id}/edit', [AdminController::class, 'EditVendor'])->name('vendor.edit');
    Route::put('/admin/vendor/{id}', [AdminController::class, 'UpdateVendor'])->name('vendor.update');
    Route::delete('/admin/vendor/{id}', [AdminController::class, 'HapusVendor'])->name('vendor.hapus');

    // Routes untuk Instansi
    Route::get('/admin/instansi', [AdminController::class, 'instansi'])->name('admin.instansi');
    Route::get('/admin/instansi/create', [AdminController::class, 'CreateInstansi'])->name('instansi.create');
    Route::post('/admin/instansi', [AdminController::class, 'StoreInstansi'])->name('instansi.store');
    Route::get('/admin/instansi/{id}/edit', [AdminController::class, 'EditInstansi'])->name('instansi.edit');
    Route::put('/admin/instansi/{id}', [AdminController::class, 'UpdateInstansi'])->name('instansi.update');
    Route::delete('/admin/instansi/{id}', [AdminController::class, 'HapusInstansi'])->name('instansi.hapus');

    // Routes untuk Survey
    Route::get('admin/survey', [AdminController::class, 'survey'])->name('admin.survey');
    Route::get('admin/survey/create', [AdminController::class, 'create'])->name('admin.form_survey');
    Route::post('admin/survey', [AdminController::class, 'store'])->name('admin.form_store');
    Route::get('admin/survey/{id}', [AdminController::class, 'show'])->name('admin.wo_survey_show');
    Route::get('admin/survey/edit/{id}', [AdminController::class, 'edit'])->name('admin.wo_survey_edit');
    Route::put('admin/survey/{id}', [AdminController::class, 'update'])->name('admin.wo_survey_update');
    Route::delete('admin/survey/{id}', [AdminController::class, 'destroy'])->name('wo_survey.destroy');
    Route::patch('/admin/survey/{id}/cancel', [AdminController::class, 'cancelSurvey'])->name('admin.cancel_survey');
    Route::get('admin/survey/{id}/print', [AdminController::class, 'printSurveyPDF'])->name('admin.survey.print');
    Route::patch('notifications/{id}/mark-as-read/admin', [AdminController::class, 'markAsReadAdmin'])->name('notifications.markAsReadAdmin');
    Route::get('admin/survey/{id}/progressinstall', [AdminController::class, 'progressinstall'])->name('admin.survey.progresinstall');
    Route::post('admin/survey/{id}/progressinstall', [AdminController::class, 'storeprogressinstall'])->name('admin.survey.storeprogresinstall');


    Route::get('admin/upgrade', [AdminController::class, 'upgrade'])->name('admin.upgrade');
    Route::get('admin/upgrade/create/{id}', [AdminController::class, 'upgradeCreate'])->name('admin.upgrade_create');
    Route::post('admin/upgrade', [AdminController::class, 'upgradeStore'])->name('admin.upgrade_store');
    Route::get('admin/upgrade/show/{id}', [AdminController::class, 'upgradeShow'])->name('admin.upgrade_show');
    Route::get('admin/upgrade/edit/{id}', [AdminController::class, 'upgradeEdit'])->name('admin.upgrade_edit');
    Route::put('admin/upgrade/{id}', [AdminController::class, 'upgradeUpdate'])->name('admin.upgrade_update');
    Route::delete('admin/upgrade/{id}', [AdminController::class, 'upgradeDestroy'])->name('admin.upgrade_destroy');
    Route::patch('/admin/upgrade/{id}/cancel', [AdminController::class, 'upgradeCancel'])->name('admin.upgrade_cancel');

    Route::get('admin/downgrade', [AdminController::class, 'downgrade'])->name('admin.downgrade');
    Route::get('admin/downgrade/create/{id}', [AdminController::class, 'downgradeCreate'])->name('admin.downgrade_create');
    Route::post('admin/downgrade', [AdminController::class, 'downgradeStore'])->name('admin.downgrade_store');
    Route::get('admin/downgrade/show/{id}', [AdminController::class, 'downgradeShow'])->name('admin.downgrade_show');
    Route::get('admin/downgrade/edit/{id}', [AdminController::class, 'downgradeEdit'])->name('admin.downgrade_edit');
    Route::put('admin/downgrade/{id}', [AdminController::class, 'downgradeUpdate'])->name('admin.downgrade_update');
    Route::delete('admin/downgrade/{id}', [AdminController::class, 'downgradeDestroy'])->name('admin.downgrade_destroy');
    Route::patch('/admin/downgrade/{id}/cancel', [AdminController::class, 'downgradeCancel'])->name('admin.downgrade_cancel');

    Route::get('admin/dismantle', [AdminController::class, 'dismantle'])->name('admin.dismantle');
    Route::get('admin/dismantle/create/{id}', [AdminController::class, 'dismantleCreate'])->name('admin.dismantle_create');
    Route::post('admin/dismantle', [AdminController::class, 'dismantleStore'])->name('admin.dismantle_store');
    Route::get('admin/dismantle/show/{id}', [AdminController::class, 'dismantleShow'])->name('admin.dismantle_show');
    Route::get('admin/dismantle/edit/{id}', [AdminController::class, 'dismantleEdit'])->name('admin.dismantle_edit');
    Route::put('admin/dismantle/{id}', [AdminController::class, 'dismantleUpdate'])->name('admin.dismantle_update');
    Route::delete('admin/dismantle/{id}', [AdminController::class, 'dismantleDestroy'])->name('admin.dismantle_destroy');
    Route::patch('/admin/dismantle/{id}/cancel', [AdminController::class, 'dismantleCancel'])->name('admin.dismantle_cancel');

    Route::get('admin/relokasi', [AdminController::class, 'relokasi'])->name('admin.relokasi');
    Route::get('admin/relokasi/create/{id}', [AdminController::class, 'relokasiCreate'])->name('admin.relokasi_create');
    Route::post('admin/relokasi', [AdminController::class, 'relokasiStore'])->name('admin.relokasi_store');
    Route::get('admin/relokasi/show/{id}', [AdminController::class, 'relokasiShow'])->name('admin.relokasi_show');
    Route::get('admin/relokasi/edit/{id}', [AdminController::class, 'relokasiEdit'])->name('admin.relokasi_edit');
    Route::put('admin/relokasi/{id}', [AdminController::class, 'relokasiUpdate'])->name('admin.relokasi_update');
    Route::delete('admin/relokasi/{id}', [AdminController::class, 'relokasiDestroy'])->name('admin.relokasi_destroy');
    Route::patch('/admin/relokasi/{id}/cancel', [AdminController::class, 'relokasiCancel'])->name('admin.relokasi_cancel');


    Route::get('admin/gantivendor', [AdminController::class, 'gantivendor'])->name('admin.gantivendor');
    Route::get('admin/gantivendor/{id}', [AdminController::class, 'showgantivendor'])->name('admin.gantivendor.show');
    Route::post('admin/gantivendor/{id}/approve', [AdminController::class, 'approvegantivendor'])->name('admin.gantivendor.approve');
    Route::post('admin/gantivendor/{id}/reject', [AdminController::class, 'rejectgantivendor'])->name('admin.gantivendor.reject');
    Route::get('admin/gantivendor/{id}/add-progress-gantivendor', [AdminController::class, 'addProgressGantivendor'])->name('admin_gantivendor_add_progress');
    Route::post('admin/gantivendor/{id}/add-progress-gantivendor', [AdminController::class, 'storeProgressGantivendor'])->name('admin_gantivendor_store_progress');
    Route::get('admin/gantivendor/{id}/inputvendor', [AdminController::class, 'inputvendor'])->name('admin.gantivendor.input');
    Route::post('admin/gantivendor/{id}/inputvendor', [AdminController::class, 'storeinputvendor'])->name('admin.gantivendor.store');

    Route::get('admin/OB', [AdminController::class, 'OB'])->name('admin.OB');
    Route::get('admin/OB/{id}', [AdminController::class, 'showOB'])->name('admin.OB_show');
    Route::get('admin/OB/edit/{id}', [AdminController::class, 'editOB'])->name('admin.OB_edit');
    Route::put('admin/OB/update/{id}', [AdminController::class, 'updateOB'])->name('admin.OB_update');
    Route::get('admin/OB/monitoring/{id}', [AdminController::class, 'showMonitoring'])->name('admin.OB_monitoring');
    Route::post('admin/OB/monitoring/{id}', [AdminController::class, 'updateMonitoring'])->name('admin.OB_updatemonitoring');

    Route::get('admin/sitedismantle', [AdminController::class, 'sitedismantle'])->name('admin.sitedismantle');
    Route::get('admin/sitedismantle{id}', [AdminController::class, 'showsitedismantle'])->name('admin.showsitedismantle');
    Route::post('admin/reaktifasi/{id}/confirm', [AdminController::class, 'reaktifasi'])->name('admin.reaktifasi');

    // Routes untuk admin Request Barang
    Route::get('admin/requestbarang', [AdminController::class, 'requestbarang'])->name('admin.request_barang');
    Route::get('admin/requestbarang/create', [AdminController::class, 'createrequest'])->name('admin.request_barang.create');
    Route::post('admin/requestbarang', [AdminController::class, 'storerequest'])->name('admin.request_barang.store');
    Route::get('admin/requestbarang/{id}', [AdminController::class, 'showrequest'])->name('admin.request_barang.show');
    Route::post('admin/requestbarang/add-to-cart', [AdminController::class, 'addToCart'])->name('admin.request_barang.add_to_cart');
    Route::get('admin/requestbarang/{id}/edit', [AdminController::class, 'editrequest'])->name('admin.request_barang.edit');
    Route::put('admin/requestbarang/{id}/update', [AdminController::class, 'updateRequest'])->name('admin.request_barang.update');


    Route::get('admin/chat', Index::class)->name('admin.chat.index');
    Route::get('admin/chat/{query}', Chat::class)->name('admin.chat');
    Route::get('admin/users', Users::class)->name('admin.users');

    Route::get('admin/work-order-survey/export', [AdminController::class, 'exportWoSurvey'])->name('work-order-survey.export');
    Route::get('admin/work-order-install/export', [AdminController::class, 'exportWoInstall'])->name('work-order-install.export');
    Route::get('admin/work-order-upgrade/export', [AdminController::class, 'exportWoUpgrade'])->name('work-order-upgrade.export');
    Route::get('admin/work-order-downgrade/export', [AdminController::class, 'exportWoDowngrade'])->name('work-order-downgrade.export');
    Route::get('admin/work-order-dismantle/export', [AdminController::class, 'exportWoDismantle'])->name('work-order-dismantle.export');
    Route::get('admin/work-order-relokasi/export', [AdminController::class, 'exportWoRelokasi'])->name('work-order-relokasi.export');
    Route::get('admin/work-order-gantivendor/export', [AdminController::class, 'exportWoGantiVendor'])->name('work-order-gantivendor.export');

    Route::post('admin/OB/import-online-billing', [AdminController::class, 'import'])->name('import.proses');
});

Route::group(['middleware' => ['ga', SessionTimeout::class]], function () {
    Route::get('ga/dashboard', [GaController::class, 'dashboard'])->name('ga.dashboard');

    // Routes untuk Jenis
    Route::get('ga/jenis', [GaController::class, 'jenis'])->name('ga.Jenis');
    Route::get('ga/create-jenis', [GaController::class, 'createJenis'])->name('createJenis');
    Route::post('ga/store-jenis', [GaController::class, 'storeJenis'])->name('storeJenis');
    Route::get('ga/jenis/{id}/edit', [GaController::class, 'EditJenis'])->name('editJenis');
    Route::put('/ga/jenis/{id}', [GaController::class, 'UpdateJenis'])->name('updateJenis');
    Route::delete('/ga/jenis/{id}', [GaController::class, 'HapusJenis'])->name('hapusJenis');
    // Routes untuk Merek
    Route::get('ga/merek', [GaController::class, 'merek'])->name('ga.Merek');
    Route::get('ga/create-merek', [GaController::class, 'createMerek'])->name('createMerek');
    Route::post('ga/store-merek', [GaController::class, 'storeMerek'])->name('storeMerek');
    Route::get('ga/merek/{id}/edit', [GaController::class, 'EditMerek'])->name('editMerek');
    Route::put('ga/merek/{id}', [GaController::class, 'UpdateMerek'])->name('updateMerek');
    Route::delete('ga/merek/{id}', [GaController::class, 'HapusMerek'])->name('hapusMerek');
    // Routes untuk Tipe
    Route::get('ga/tipe', [GaController::class, 'tipe'])->name('ga.Tipe');
    Route::get('ga/create-tipe', [GaController::class, 'createTipe'])->name('createTipe');
    Route::post('ga/store-tipe', [GaController::class, 'storeTipe'])->name('storeTipe');
    Route::get('ga/tipe/{id}/edit', [GaController::class, 'EditTipe'])->name('editTipe');
    Route::put('ga/tipe/{id}', [GaController::class, 'UpdateTipe'])->name('updateTipe');
    Route::delete('ga/tipe/{id}', [GaController::class, 'HapusTipe'])->name('hapusTipe');
    // Routes untuk Stock Barang
    Route::get('ga/stockbarang', [GaController::class, 'stockbarang'])->name('ga.stockbarang');
    Route::get('ga/stockbarang/create', [GaController::class, 'createstockbarang'])->name('ga.stockbarang_create');
    Route::post('ga/stockbarang', [GaController::class, 'storestockbarang'])->name('stockbarang_store');
    Route::get('ga/stockbarang/{id}/edit', [GaController::class, 'editstockbarang'])->name('stockbarang_edit');
    Route::put('ga/stockbarang/{id}', [GaController::class, 'updatestockbarang'])->name('stockbarang_update');
    Route::delete('ga/stockbarang/{id}', [GaController::class, 'hapusstockbarang'])->name('stockbarang_hapus');

    Route::get('ga/instalasi', [GaController::class, 'instalasi'])->name('ga.instalasi');
    Route::get('ga/instalasi/{id}', [GaController::class, 'showinstalasi'])->name('ga.instalasi.show');
    Route::post('ga/instalasi/{id}/approve', [GaController::class, 'approveinstalasi'])->name('ga.instalasi.approve');
    Route::post('ga/instalasi/{id}/reject', [GaController::class, 'rejectinstalasi'])->name('ga.instalasi.reject');
    Route::get('ga/instalasi/{id}/input_barang/create', [GaController::class, 'inputBaranginstalasicreate'])->name('ga.input_barang_instalasi.create');
    Route::post('ga/instalasi/{id}/input_barang', [GaController::class, 'inputBaranginstalasistore'])->name('ga.input_barang_instalasi.store');
    Route::delete('ga/cancel_barang_instalasi/{barangKeluar}', [GAController::class, 'cancelBaranginstalasi'])->name('ga.cancel_barang.instalasi');
    Route::get('ga/instalasi/{id}/createshipped', [GaController::class, 'instalasicreateShipped'])->name('ga.install.create.shipped');
    Route::post('ga/instalasi/{id}/storeshipped', [GaController::class, 'instalasistoreShipped'])->name('ga.install.store.shipped');

    Route::get('ga/maintenance', [GaController::class, 'maintenance'])->name('ga.maintenance');
    Route::get('ga/maintenance/show/{id}', [GaController::class, 'maintenanceShow'])->name('ga.maintenance_show');
    Route::post('ga/maintenance/{id}/approve', [GaController::class, 'approvemaintenance'])->name('ga.maintenance.approve');
    Route::post('ga/maintenance/{id}/reject', [GaController::class, 'rejectmaintenance'])->name('ga.maintenance.reject');
    Route::get('ga/maintenance/{id}/input_barang/create', [GaController::class, 'inputBarangmaintenancecreate'])->name('ga.input_barang_maintenance.create');
    Route::post('ga/maintenance/{id}/input_barang', [GaController::class, 'inputBarangmaintenancestore'])->name('ga.input_barang_maintenance.store');
    Route::delete('ga/cancel_barang_maintenance/{barangKeluar}', [GAController::class, 'cancelBarangmaintenance'])->name('ga.cancel_barang.maintenance');
    Route::get('ga/maintenance/{id}/createshipped', [GaController::class, 'maintenancecreateShipped'])->name('ga.maintenance.create.shipped');
    Route::post('ga/maintenance/{id}/storeshipped', [GaController::class, 'maintenancestoreShipped'])->name('ga.maintenance.store.shipped');

    // Routes untuk Request Barang
    Route::get('ga/requestbarang', [GaController::class, 'requestbarang'])->name('ga.request_barang');
    Route::get('ga/requestbarang/{id}', [GaController::class, 'showrequest'])->name('ga.request_barang.show');
    Route::post('ga/request-barang/{id}/approve', [GaController::class, 'approve'])->name('ga.request_barang.approve');
    Route::post('ga/request-barang/{id}/reject', [GaController::class, 'reject'])->name('ga.request_barang.reject');
    Route::post('ga/request-barang/{id}/completed', [GaController::class, 'requestcompleted'])->name('ga.request_barang.completed');

    Route::get('ga/request_barang/{id}/input_barang/create', [GaController::class, 'inputBarangcreate'])->name('ga.input_barang.create');
    Route::post('ga/request_barang/{id}/input_barang', [GaController::class, 'inputBarangstore'])->name('ga.input_barang.store');
    Route::post('ga/request_barang/{requestBarangId}/update_status/{status}', [GaController::class, 'updateStatus'])->name('ga.update_status');
    Route::delete('ga/cancel_barang/{barangKeluar}', [GAController::class, 'cancelBarang'])->name('ga.cancel_barang');
    Route::patch('notifications/{id}/mark-as-read/ga', [GaController::class, 'markAsReadGa'])->name('notifications.markAsReadGa');
    Route::get('ga/request_barang/{id}/createshipped', [GaController::class, 'requestcreateShipped'])->name('ga.request.create.shipped');
    Route::post('ga/request_barang/{id}/storeshipped', [GaController::class, 'requeststoreShipped'])->name('ga.request.store.shipped');
    Route::get('ga/requestbarang/{id}/print/requestbarang', [GaController::class, 'printSuratRequest'])->name('ga.request_barang.print');


    Route::get('ga/dismantle', [GaController::class, 'dismantle'])->name('ga.dismantle');
    Route::get('ga/dismantle/show/{id}', [GaController::class, 'dismantleShow'])->name('ga.dismantle_show');
    Route::post('ga/dismantle/{id}/approve', [GaController::class, 'approvedismantle'])->name('ga.dismantle.approve');
    Route::get('/ga/dismantle/{id}/add-progress-dismantle', [GaController::class, 'addProgressDismantle'])->name('ga_dismantle_add_progress');
    Route::post('/ga/dismantle/{id}/add-progress-dismantle', [GaController::class, 'storeProgressDismantle'])->name('ga_dismantle_store_progress');
    Route::get('/ga/dismantle/{id}/input_barang_dismantle', [GaController::class, 'inputBarangDismantle'])->name('ga.inputbarang_dismantle');
    Route::post('/ga/store-barang-dismantle/{id}', [GaController::class, 'storeBarangDismantle'])->name('ga_store_barang_dismantle');
    Route::delete('/ga/dismantle/cancel-barang/{id}', [GAController::class, 'cancelBarangDismantle'])->name('ga.cancel_barang_dismantle');
    Route::post('ga/dismantle/{id}/complete', [GAController::class, 'completeDismantle'])->name('dismantle.complete');

    Route::get('ga/relokasi', [GaController::class, 'relokasi'])->name('ga.relokasi');
    Route::get('ga/relokasi/{id}', [GaController::class, 'showrelokasi'])->name('ga.relokasi.show');
    Route::post('ga/relokasi/{id}/approve', [GaController::class, 'approverelokasi'])->name('ga.relokasi.approve');
    Route::post('ga/relokasi/{id}/reject', [GaController::class, 'rejectrelokasi'])->name('ga.relokasi.reject');
    Route::get('ga/relokasi/{id}/input_barang/create', [GaController::class, 'inputBarangrelokasicreate'])->name('ga.input_barang_relokasi.create');
    Route::post('ga/relokasi/{id}/input_barang', [GaController::class, 'inputBarangrelokasistore'])->name('ga.input_barang_relokasi.store');
    Route::delete('ga/cancel_barang_relokasi/{barangKeluar}', [GAController::class, 'cancelBarangrelokasi'])->name('ga.cancel_barang.relokasi');
    Route::get('ga/relokasi/{id}/createshipped', [GaController::class, 'relokasicreateShipped'])->name('ga.relokasi.create.shipped');
    Route::post('ga/relokasi/{id}/storeshipped', [GaController::class, 'relokasistoreShipped'])->name('ga.relokasi.store.shipped');

    Route::get('ga/upgrade', [GaController::class, 'upgrade'])->name('ga.upgrade');
    Route::get('ga/upgrade/{id}', [GaController::class, 'showupgrade'])->name('ga.upgrade.show');
    Route::post('ga/upgrade/{id}/approve', [GaController::class, 'approveupgrade'])->name('ga.upgrade.approve');
    Route::post('ga/upgrade/{id}/reject', [GaController::class, 'rejectupgrade'])->name('ga.upgrade.reject');
    Route::get('ga/upgrade/{id}/input_barang/create', [GaController::class, 'inputBarangupgradecreate'])->name('ga.input_barang_upgrade.create');
    Route::post('ga/upgrade/{id}/input_barang', [GaController::class, 'inputBarangupgradestore'])->name('ga.input_barang_upgrade.store');
    Route::delete('ga/cancel_barang_upgrade/{barangKeluar}', [GAController::class, 'cancelBarangupgrade'])->name('ga.cancel_barang.upgrade');
    Route::get('ga/upgrade/{id}/createshipped', [GaController::class, 'upgradecreateShipped'])->name('ga.upgrade.create.shipped');
    Route::post('ga/upgrade/{id}/storeshipped', [GaController::class, 'upgradestoreShipped'])->name('ga.upgrade.store.shipped');

    Route::get('ga/OB', [GaController::class, 'OB'])->name('ga.OB');
    Route::get('ga/OB/{id}', [GaController::class, 'showOB'])->name('ga.OB_show');

    Route::get('ga/sitedismantle', [GaController::class, 'sitedismantle'])->name('ga.sitedismantle');
    Route::get('ga/sitedismantle{id}', [GaController::class, 'showsitedismantle'])->name('ga.showsitedismantle');

    Route::get('ga/requestbarang/{id}/print/detailrequestbarang', [GaController::class, 'printDetailBarang'])->name('ga.request_barang.printdetailbarang');
    Route::get('ga/instalasi/{id}/print/detailbaranginstalasi', [GaController::class, 'printDetailBarangInstalasi'])->name('ga.instalasi_barang.printdetailbarang');
    Route::get('ga/maintenance/{id}/print/detailbarangmaintenance', [GaController::class, 'printDetailBarangMaintenance'])->name('ga.maintenance_barang.printdetailbarang');
    Route::get('ga/relokasi/{id}/print/detailbarangrelokasi', [GaController::class, 'printDetailBarangRelokasi'])->name('ga.relokasi_barang.printdetailbarang');
    Route::get('ga/upgrade/{id}/print/detailbarangupgrade', [GaController::class, 'printDetailBarangUpgrade'])->name('ga.upgrade_barang.printdetailbarang');

    Route::get('ga/chat', Index::class)->name('ga.chat.index');
    Route::get('ga/chat/{query}', Chat::class)->name('ga.chat');
    Route::get('ga/users', Users::class)->name('ga.users');
});
// Routes untuk Request Barang
Route::group(['middleware' => ['helpdesk', SessionTimeout::class]], function () {
    Route::get('helpdesk/dashboard', [HelpdeskController::class, 'dashboard'])->name('helpdesk.dashboard');
    Route::get('helpdesk/requestbarang', [HelpdeskController::class, 'requestbarang'])->name('hd.request_barang');
    Route::get('helpdesk/requestbarang/create', [HelpdeskController::class, 'createrequest'])->name('hd.request_barang.create');
    Route::post('helpdesk/requestbarang', [HelpdeskController::class, 'storerequest'])->name('hd.request_barang.store');
    Route::get('helpdesk/requestbarang/{id}', [HelpdeskController::class, 'showrequest'])->name('hd.request_barang.show');
    Route::post('helpdesk/requestbarang/add-to-cart', [HelpdeskController::class, 'addToCart'])->name('hd.request_barang.add_to_cart');
    Route::get('helpdesk/requestbarang/{id}/edit', [HelpdeskController::class, 'editrequest'])->name('hd.request_barang.edit');
    Route::put('helpdesk/requestbarang/{id}/update', [HelpdeskController::class, 'updateRequest'])->name('hd.request_barang.update');
    Route::patch('notifications/{id}/mark-as-read/hd', [HelpdeskController::class, 'markAsReadhd'])->name('notifications.markAsReadhd');
    Route::get('helpdesk/requestbarang/{id}/print/requestbarang', [HelpdeskController::class, 'printSuratRequest'])->name('hd.request_barang.print');


    Route::get('helpdesk/maintenance', [HelpdeskController::class, 'maintenance'])->name('hd.maintenance');
    Route::get('helpdesk/maintenance/create/{id}', [HelpdeskController::class, 'maintenanceCreate'])->name('hd.maintenance_create');
    Route::post('helpdesk/maintenance', [HelpdeskController::class, 'maintenanceStore'])->name('hd.maintenance_store');
    Route::get('helpdesk/maintenance/show/{id}', [HelpdeskController::class, 'maintenanceShow'])->name('hd.maintenance_show');
    Route::get('helpdesk/maintenance/edit/{id}', [HelpdeskController::class, 'maintenanceEdit'])->name('hd.maintenance_edit');
    Route::put('helpdesk/maintenance/{id}', [HelpdeskController::class, 'maintenanceUpdate'])->name('hd.maintenance_update');
    Route::delete('helpdesk/maintenance/{id}', [HelpdeskController::class, 'maintenanceDestroy'])->name('hd.maintenance_destroy');
    Route::patch('/helpdesk/maintenance/{id}/cancel', [HelpdeskController::class, 'maintenanceCancel'])->name('hd.maintenance_cancel');

    Route::get('helpdesk/gantivendor', [HelpdeskController::class, 'gantivendor'])->name('hd.gantivendor');
    Route::get('helpdesk/gantivendor/create/{id}', [HelpdeskController::class, 'gantivendorCreate'])->name('hd.gantivendor_create');
    Route::post('helpdesk/gantivendor', [HelpdeskController::class, 'gantivendorStore'])->name('hd.gantivendor_store');
    Route::get('helpdesk/gantivendor/{id}', [HelpdeskController::class, 'gantivendorShow'])->name('hd.gantivendor.show');
    Route::get('helpdesk/gantivendor/edit/{id}', [HelpdeskController::class, 'gantivendorEdit'])->name('hd.gantivendor.edit');
    Route::put('helpdesk/gantivendor/{id}', [HelpdeskController::class, 'gantivendorUpdate'])->name('hd.gantivendor_update');

    Route::get('helpdesk/OB', [HelpdeskController::class, 'OB'])->name('hd.OB');
    Route::get('helpdesk/OB/{id}', [HelpdeskController::class, 'showOB'])->name('hd.OB_show');
    Route::get('helpdesk/sitedismantle', [HelpdeskController::class, 'sitedismantle'])->name('hd.sitedismantle');
    Route::get('helpdesk/sitedismantle{id}', [HelpdeskController::class, 'showsitedismantle'])->name('hd.showsitedismantle');


    Route::get('helpdesk/chat', Index::class)->name('helpdesk.chat.index');
    Route::get('helpdesk/chat/{query}', Chat::class)->name('helpdesk.chat');
    Route::get('helpdesk/users', Users::class)->name('helpdesk.users');
});

Route::group(['middleware' => ['na', SessionTimeout::class]], function () {
    Route::get('na/dashboard', [NaController::class, 'dashboard'])->name('na.dashboard');
    Route::get('na/instalasi', [NaController::class, 'instalasi'])->name('na.instalasi');
    Route::get('na/instalasi/{id}', [NaController::class, 'showinstalasi'])->name('na.instalasi.show');
    Route::patch('na/configure-barang/{id}', [NaController::class, 'configureBarang'])
        ->name('na.configure-barang');
    Route::get('na/install/{id}/add-progress-instalasi', [NaController::class, 'addProgressInstalasi'])->name('na_install_add_progress');
    Route::post('na/install/{id}/add-progress-instalasi', [NaController::class, 'storeProgressInstalasi'])->name('na_install_store_progress');

    Route::get('na/upgrade', [NaController::class, 'upgrade'])->name('na.upgrade');
    Route::get('na/upgrade/show/{id}', [NaController::class, 'upgradeShow'])->name('na.upgrade_show');
    Route::post('na/upgrade/{id}/approve', [NaController::class, 'approveupgrade'])->name('na.upgrade.approve');
    Route::get('na/upgrade/{id}/add-progress-upgrade', [NaController::class, 'addProgressUpgrade'])->name('na_upgrade_add_progress');
    Route::post('na/upgrade/{id}/add-progress-upgrade', [NaController::class, 'storeProgressUpgrade'])->name('na_upgrade_store_progress');

    Route::get('na/maintenance', [NaController::class, 'maintenance'])->name('na.maintenance');
    Route::get('na/maintenance/show/{id}', [NaController::class, 'maintenanceShow'])->name('na.maintenance_show');

    Route::get('na/downgrade', [NaController::class, 'downgrade'])->name('na.downgrade');
    Route::get('na/downgrade/show/{id}', [NaController::class, 'downgradeShow'])->name('na.downgrade_show');
    Route::post('na/downgrade/{id}/approve', [NaController::class, 'approvedowngrade'])->name('na.downgrade.approve');
    Route::get('na/downgrade/{id}/add-progress-downgrade', [NaController::class, 'addProgressDowngrade'])->name('na_downgrade_add_progress');
    Route::post('na/downgrade/{id}/add-progress-downgrade', [NaController::class, 'storeProgressDowngrade'])->name('na_downgrade_store_progress');

    Route::get('na/dismantle', [NaController::class, 'dismantle'])->name('na.dismantle');
    Route::get('na/dismantle/show/{id}', [NaController::class, 'dismantleShow'])->name('na.dismantle_show');
    Route::post('na/dismantle/progress/{id}/disable', [NaController::class, 'storeDisable'])->name('na.dismantle.progress.disable');

    Route::get('na/relokasi', [NaController::class, 'relokasi'])->name('na.relokasi');
    Route::get('na/relokasi/{id}', [NaController::class, 'showrelokasi'])->name('na.relokasi.show');
    Route::get('na/relokasi/{id}/add-progress-relokasi', [NaController::class, 'addProgressRelokasi'])->name('na_relokasi_add_progress');
    Route::post('na/relokasi/{id}/add-progress-relokasi', [NaController::class, 'storeProgressRelokasi'])->name('na_relokasi_store_progress');

    Route::get('na/gantivendor', [NaController::class, 'gantivendor'])->name('na.gantivendor');
    Route::get('na/gantivendor/{id}', [NaController::class, 'showgantivendor'])->name('na.gantivendor.show');
    Route::get('na/gantivendor/{id}/add-progress-gantivendor', [NaController::class, 'addProgressGantivendor'])->name('na_gantivendor_add_progress');
    Route::post('na/gantivendor/{id}/add-progress-gantivendor', [NaController::class, 'storeProgressGantivendor'])->name('na_gantivendor_store_progress');
    Route::get('na/gantivendor/{id}/sidbaru', [NaController::class, 'inputsidbaru'])->name('na.gantivendor.inputsidbaru');
    Route::post('na/gantivendor/{id}/sidbaru', [NaController::class, 'storeinputsidbaru'])->name('na.gantivendor.storesidbaru');
    Route::get('na/OB', [NaController::class, 'OB'])->name('na.OB');
    Route::get('na/OB/{id}', [NaController::class, 'showOB'])->name('na.OB_show');

    Route::get('na/sitedismantle', [NaController::class, 'sitedismantle'])->name('na.sitedismantle');
    Route::get('na/sitedismantle{id}', [NaController::class, 'showsitedismantle'])->name('na.showsitedismantle');

    // Routes untuk Request Barang
    Route::get('na/requestbarang', [NaController::class, 'requestbarang'])->name('na.request_barang');
    Route::get('na/requestbarang/create', [NaController::class, 'createrequest'])->name('na.request_barang.create');
    Route::post('na/requestbarang', [NaController::class, 'storerequest'])->name('na.request_barang.store');
    Route::get('na/requestbarang/{id}', [NaController::class, 'showrequest'])->name('na.request_barang.show');
    Route::post('na/requestbarang/add-to-cart', [NaController::class, 'addToCart'])->name('na.request_barang.add_to_cart');
    Route::get('na/requestbarang/{id}/edit', [NaController::class, 'editrequest'])->name('na.request_barang.edit');
    Route::put('na/requestbarang/{id}/update', [NaController::class, 'updateRequest'])->name('na.request_barang.update');
    Route::patch('notifications/{id}/mark-as-read/na', [NaController::class, 'markAsReadNa'])->name('notifications.markAsReadNa');

    Route::get('na/chat', Index::class)->name('na.chat.index');
    Route::get('na/chat/{query}', Chat::class)->name('na.chat');
    Route::get('na/users', Users::class)->name('na.users');
});


Route::group(['middleware' => ['noc', SessionTimeout::class]], function () {
    Route::get('noc/dashboard', [NocController::class, 'dashboard'])->name('noc.dashboard');
    Route::get('noc/instalasi', [NocController::class, 'instalasi'])->name('noc.instalasi');
    Route::get('noc/instalasi/{id}', [NocController::class, 'showinstalasi'])->name('noc.instalasi.show');
    Route::patch('noc/configure-barang/{id}', [NocController::class, 'configureBarang'])
        ->name('noc.configure-barang');
    Route::get('noc/install/{id}/add-progress-instalasi', [NocController::class, 'addProgressInstalasi'])->name('noc_install_add_progress');
    Route::post('noc/install/{id}/add-progress-instalasi', [NocController::class, 'storeProgressInstalasi'])->name('noc_install_store_progress');

    Route::get('noc/upgrade', [NocController::class, 'upgrade'])->name('noc.upgrade');
    Route::get('noc/upgrade/show/{id}', [NocController::class, 'upgradeShow'])->name('noc.upgrade_show');
    Route::post('noc/upgrade/{id}/approve', [NocController::class, 'approveupgrade'])->name('noc.upgrade.approve');
    Route::get('noc/upgrade/{id}/add-progress-upgrade', [NocController::class, 'addProgressUpgrade'])->name('noc_upgrade_add_progress');
    Route::post('noc/upgrade/{id}/add-progress-upgrade', [NocController::class, 'storeProgressUpgrade'])->name('noc_upgrade_store_progress');

    Route::get('noc/downgrade', [NocController::class, 'downgrade'])->name('noc.downgrade');
    Route::get('noc/downgrade/show/{id}', [NocController::class, 'downgradeShow'])->name('noc.downgrade_show');
    Route::post('noc/downgrade/{id}/approve', [NocController::class, 'approvedowngrade'])->name('noc.downgrade.approve');
    Route::get('noc/downgrade/{id}/add-progress-downgrade', [NocController::class, 'addProgressDowngrade'])->name('noc_downgrade_add_progress');
    Route::post('noc/downgrade/{id}/add-progress-downgrade', [NocController::class, 'storeProgressDowngrade'])->name('noc_downgrade_store_progress');


    Route::get('noc/gantivendor', [NocController::class, 'gantivendor'])->name('noc.gantivendor');
    Route::get('noc/gantivendor/{id}', [NocController::class, 'showgantivendor'])->name('noc.gantivendor.show');
    Route::get('noc/gantivendor/{id}/add-progress-gantivendor', [NocController::class, 'addProgressGantivendor'])->name('noc_gantivendor_add_progress');
    Route::post('noc/gantivendor/{id}/add-progress-gantivendor', [NocController::class, 'storeProgressGantivendor'])->name('noc_gantivendor_store_progress');
    Route::get('noc/gantivendor/{id}/sidbaru', [NocController::class, 'inputsidbaru'])->name('noc.gantivendor.inputsidbaru');
    Route::post('noc/gantivendor/{id}/sidbaru', [NocController::class, 'storeinputsidbaru'])->name('noc.gantivendor.storesidbaru');

    Route::get('noc/relokasi', [NocController::class, 'relokasi'])->name('noc.relokasi');
    Route::get('noc/relokasi/{id}', [NocController::class, 'showrelokasi'])->name('noc.relokasi.show');
    Route::get('noc/relokasi/{id}/add-progress-relokasi', [NocController::class, 'addProgressRelokasi'])->name('noc_relokasi_add_progress');
    Route::post('noc/relokasi/{id}/add-progress-relokasi', [NocController::class, 'storeProgressRelokasi'])->name('noc_relokasi_store_progress');

    Route::get('noc/dismantle', [NocController::class, 'dismantle'])->name('noc.dismantle');
    Route::get('noc/dismantle/show/{id}', [NocController::class, 'dismantleShow'])->name('noc.dismantle_show');
    Route::post('noc/dismantle/progress/{id}/disable', [NocController::class, 'storeDisable'])->name('noc.dismantle.progress.disable');

    Route::get('noc/OB', [NocController::class, 'OB'])->name('noc.OB');
    Route::get('noc/OB/{id}', [NocController::class, 'showOB'])->name('noc.OB_show');
    Route::get('noc/sitedismantle', [NocController::class, 'sitedismantle'])->name('noc.sitedismantle');
    Route::get('noc/sitedismantle{id}', [NocController::class, 'showsitedismantle'])->name('noc.showsitedismantle');

    Route::get('noc/maintenance', [NocController::class, 'maintenance'])->name('noc.maintenance');
    Route::get('noc/maintenance/show/{id}', [NocController::class, 'maintenanceShow'])->name('noc.maintenance_show');
    Route::post('noc/maintenance/{id}/approve', [NocController::class, 'approvemaintenance'])->name('noc.maintenance.approve');
    Route::post('noc/maintenance/{id}/reject', [NocController::class, 'rejectmaintenance'])->name('noc.maintenance.reject');
    Route::get('noc/maintenance/{id}/add-progress-maintenance', [NocController::class, 'addProgressMaintenance'])->name('noc_maintenance_add_progress');
    Route::post('noc/maintenance/{id}/add-progress-maintenance', [NocController::class, 'storeProgressMaintenance'])->name('noc_maintenance_store_progress');


    Route::get('noc/requestbarang', [NocController::class, 'requestbarang'])->name('noc.request_barang');
    Route::get('noc/requestbarang/create', [NocController::class, 'createrequest'])->name('noc.request_barang.create');
    Route::post('noc/requestbarang', [NocController::class, 'storerequest'])->name('noc.request_barang.store');
    Route::get('noc/requestbarang/{id}', [NocController::class, 'showrequest'])->name('noc.request_barang.show');
    Route::post('noc/requestbarang/add-to-cart', [NocController::class, 'addToCart'])->name('noc.request_barang.add_to_cart');
    Route::get('noc/requestbarang/{id}/edit', [NocController::class, 'editrequest'])->name('noc.request_barang.edit');
    Route::put('noc/requestbarang/{id}/update', [NocController::class, 'updateRequest'])->name('noc.request_barang.update');
    Route::patch('notifications/{id}/mark-as-read/noc', [NocController::class, 'markAsReadnoc'])->name('notifications.markAsReadnoc');

    Route::get('noc/chat', Index::class)->name('noc.chat.index');
    Route::get('noc/chat/{query}', Chat::class)->name('noc.chat');
    Route::get('noc/users', Users::class)->name('noc.users');
});

Route::group(['middleware' => ['psb', SessionTimeout::class]], function () {
    Route::get('psb/dashboard', [PsbController::class, 'dashboard'])->name('psb.dashboard');
    Route::get('psb/instalasi', [PsbController::class, 'instalasi'])->name('psb.instalasi');
    Route::get('psb/instalasi/{id}', [PsbController::class, 'showinstalasi'])->name('psb.instalasi.show');

    Route::get('/psb/install/{id}/add-progress-instalasi', [PsbController::class, 'addProgressInstalasi'])->name('psb_install_add_progress');
    Route::post('/psb/install/{id}/add-progress-instalasi', [PsbController::class, 'storeProgressInstalasi'])->name('psb_install_store_progress');

    Route::get('psb/maintenance', [PsbController::class, 'maintenance'])->name('psb.maintenance');
    Route::get('psb/maintenance/show/{id}', [PsbController::class, 'maintenanceShow'])->name('psb.maintenance_show');
    Route::post('psb/maintenance/{id}/approve', [PsbController::class, 'approvemaintenance'])->name('psb.maintenance.approve');
    Route::post('psb/maintenance/{id}/reject', [PsbController::class, 'rejectmaintenance'])->name('psb.maintenance.reject');
    Route::get('psb/maintenance/{id}/add-progress-maintenance', [PsbController::class, 'addProgressMaintenance'])->name('psb_maintenance_add_progress');
    Route::post('psb/maintenance/{id}/add-progress-maintenance', [PsbController::class, 'storeProgressMaintenance'])->name('psb_maintenance_store_progress');

    Route::get('psb/upgrade', [PsbController::class, 'upgrade'])->name('psb.upgrade');
    Route::get('psb/upgrade/show/{id}', [PsbController::class, 'upgradeShow'])->name('psb.upgrade_show');
    Route::post('psb/upgrade/{id}/approve', [PsbController::class, 'approveupgrade'])->name('psb.upgrade.approve');
    Route::get('/psb/upgrade/{id}/add-progress-upgrade', [PsbController::class, 'addProgressUpgrade'])->name('psb_upgrade_add_progress');
    Route::post('/psb/upgrade/{id}/add-progress-upgrade', [PsbController::class, 'storeProgressUpgrade'])->name('psb_upgrade_store_progress');


    Route::get('psb/downgrade', [PsbController::class, 'downgrade'])->name('psb.downgrade');
    Route::get('psb/downgrade/show/{id}', [PsbController::class, 'downgradeShow'])->name('psb.downgrade_show');
    Route::post('psb/downgrade/{id}/approve', [PsbController::class, 'approvedowngrade'])->name('psb.downgrade.approve');
    Route::get('/psb/downgrade/{id}/add-progress-downgrade', [PsbController::class, 'addProgressDowngrade'])->name('psb_downgrade_add_progress');
    Route::post('/psb/downgrade/{id}/add-progress-downgrade', [PsbController::class, 'storeProgressDowngrade'])->name('psb_downgrade_store_progress');

    Route::get('psb/gantivendor', [PsbController::class, 'gantivendor'])->name('psb.gantivendor');
    Route::get('psb/gantivendor/{id}', [PsbController::class, 'showgantivendor'])->name('psb.gantivendor.show');
    Route::get('psb/gantivendor/{id}/add-progress-gantivendor', [PsbController::class, 'addProgressGantivendor'])->name('psb_gantivendor_add_progress');
    Route::post('psb/gantivendor/{id}/add-progress-gantivendor', [PsbController::class, 'storeProgressGantivendor'])->name('psb_gantivendor_store_progress');

    Route::get('psb/relokasi', [PsbController::class, 'relokasi'])->name('psb.relokasi');
    Route::get('psb/relokasi/{id}', [PsbController::class, 'showrelokasi'])->name('psb.relokasi.show');
    Route::post('psb/relokasi/{id}/approve', [PsbController::class, 'approverelokasi'])->name('psb.relokasi.approve');
    Route::post('psb/relokasi/{id}/reject', [PsbController::class, 'rejectrelokasi'])->name('psb.relokasi.reject');
    Route::get('psb/relokasi/{id}/add-progress-relokasi', [PsbController::class, 'addProgressRelokasi'])->name('psb_relokasi_add_progress');
    Route::post('psb/relokasi/{id}/add-progress-relokasi', [PsbController::class, 'storeProgressRelokasi'])->name('psb_relokasi_store_progress');

    Route::get('psb/dismantle', [PsbController::class, 'dismantle'])->name('psb.dismantle');
    Route::get('psb/dismantle/show/{id}', [PsbController::class, 'dismantleShow'])->name('psb.dismantle_show');
    Route::post('psb/dismantle/{id}/approve', [PsbController::class, 'approvedismantle'])->name('psb.dismantle.approve');
    Route::get('/psb/dismantle/{id}/add-progress-dismantle', [PsbController::class, 'addProgressDismantle'])->name('psb_dismantle_add_progress');
    Route::post('/psb/dismantle/{id}/add-progress-dismantle', [PsbController::class, 'storeProgressDismantle'])->name('psb_dismantle_store_progress');

    Route::get('psb/OB', [PsbController::class, 'OB'])->name('psb.OB');
    Route::get('psb/OB/{id}', [PsbController::class, 'showOB'])->name('psb.OB_show');
    Route::get('psb/sitedismantle', [PsbController::class, 'sitedismantle'])->name('psb.sitedismantle');
    Route::get('psb/sitedismantle{id}', [PsbController::class, 'showsitedismantle'])->name('psb.showsitedismantle');

    Route::get('psb/requestbarang', [PsbController::class, 'requestbarang'])->name('psb.request_barang');
    Route::get('psb/requestbarang/create', [PsbController::class, 'createrequest'])->name('psb.request_barang.create');
    Route::post('psb/requestbarang', [PsbController::class, 'storerequest'])->name('psb.request_barang.store');
    Route::get('psb/requestbarang/{id}', [PsbController::class, 'showrequest'])->name('psb.request_barang.show');
    Route::post('psb/requestbarang/add-to-cart', [PsbController::class, 'addToCart'])->name('psb.request_barang.add_to_cart');
    Route::get('psb/requestbarang/{id}/edit', [PsbController::class, 'editrequest'])->name('psb.request_barang.edit');
    Route::put('psb/requestbarang/{id}/update', [PsbController::class, 'updateRequest'])->name('psb.request_barang.update');

    Route::get('psb/survey', [PsbController::class, 'survey'])->name('psb.survey');
    Route::get('/psb/survey/{id}', [PsbController::class, 'show'])->name('psb.survey_show');
    Route::post('psb/survey/{id}/approve', [PsbController::class, 'approvesurvey'])->name('psb.survey.approve');
    Route::get('/psb/survey/{id}/add-progress-survey', [PsbController::class, 'addProgressSurvey'])->name('psb_survey_add_progress');
    Route::post('/psb/survey/{id}/add-progress-survey', [PsbController::class, 'storeProgressSurvey'])->name('psb_survey_store_progress');

    Route::get('psb/survey/{id}/{progressId}/edit', [PsbController::class, 'editProgress'])->name('psb_survey_edit');

    // Route untuk update progress survey
    Route::put('psb/survey/{id}/{progressId}', [PsbController::class, 'updateProgress'])->name('psb_survey_update');

    // Route untuk hapus progress survey
    Route::delete('psb/survey/{id}/{progressId}', [PsbController::class, 'deleteProgress'])->name('psb_survey_delete');
    Route::patch('notifications/{id}/mark-as-read/psb', [psbController::class, 'markAsReadPsb'])->name('notifications.markAsReadPsb');


    Route::get('psb/survey/{id}/print/survey', [PsbController::class, 'printSurveyPDF'])->name('psb.survey.print');
    Route::get('psb/instalasi/{id}/print/instalasi', [PsbController::class, 'printInstalasiPDF'])->name('psb.instalasi.print');
    Route::get('psb/maintenance/{id}/print/maintenance', [PsbController::class, 'printMaintenancePDF'])->name('psb.maintenance.print');
    Route::get('psb/upgrade/{id}/print/upgrade', [PsbController::class, 'printUpgradePDF'])->name('psb.upgrade.print');
    Route::get('psb/downgrade/{id}/print/downgrade', [PsbController::class, 'printDowngradePDF'])->name('psb.downgrade.print');
    Route::get('psb/gantivendor/{id}/print/gantivendor', [PsbController::class, 'printGantiVendorPDF'])->name('psb.gantivendor.print');
    Route::get('psb/dismantle/{id}/print/dismantle', [PsbController::class, 'printDismantlePDF'])->name('psb.dismantle.print');
    Route::get('psb/relokasi/{id}/print/relokasi', [PsbController::class, 'printRelokasiPDF'])->name('psb.relokasi.print');


    Route::get('psb/chat', Index::class)->name('psb.chat.index');
    Route::get('psb/chat/{query}', Chat::class)->name('psb.chat');
    Route::get('psb/users', Users::class)->name('psb.users');
});
