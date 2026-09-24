
<?php
require_once "database/connection.php";
require_once "classes/Auth.php";
require_once "classes/Paket.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);

// Izinkan admin, petugas, DAN jamaah untuk akses index.php
$auth->checkRole(['admin', 'petugas', 'jamaah']);

$role = $_SESSION['role'] ?? '';
$user_id = $_SESSION['user_id'] ?? null;

if ($role === 'jamaah') {
    // 1. Ambil jamaah_id dari session, jika tidak ada cari berdasarkan user_id login
    $jamaah_id = $_SESSION['jamaah_id'] ?? null;

    if (!$jamaah_id && $user_id) {
        $stmtGetJamaah = $db->prepare("SELECT id FROM jamaah WHERE user_id = ?");
        $stmtGetJamaah->execute([$user_id]);
        $resJamaah = $stmtGetJamaah->fetch();
        $jamaah_id = $resJamaah['id'] ?? null;
    }

    // Inisialisasi nilai default agar tidak undefined
    $jadwalKeberangkatan = '0';
    $namaPaketDiikuti   = 'Belum Terdaftar';
    $totalPendaftaran    = 0;
    $totalPembayaran     = 0;

    if ($jamaah_id) {
        // Query Total Pendaftaran Jamaah
        $stmtPendaftaran = $db->prepare("SELECT COUNT(*) as total FROM pendaftaran WHERE jamaah_id = ?");
        $stmtPendaftaran->execute([$jamaah_id]);
        $totalPendaftaran = $stmtPendaftaran->fetch()['total'] ?? 0;

        // Query Total Pembayaran Jamaah
        $stmtPembayaran = $db->prepare("SELECT COUNT(*) as total FROM pembayaran WHERE jamaah_id = ?");
        $stmtPembayaran->execute([$jamaah_id]);
        $totalPembayaran = $stmtPembayaran->fetch()['total'] ?? 0;

        // Query Jadwal Keberangkatan Terdekat (Aktif)
        $stmtJadwal = $db->prepare("
            SELECT k.tanggal_berangkat, pkt.nama_paket 
            FROM pendaftaran p 
            LEFT JOIN keberangkatan k ON p.keberangkatan_id = k.id
            JOIN paket pkt ON p.paket_id = pkt.id 
            WHERE p.jamaah_id = ? AND p.status IN ('Berangkat', 'Proses', 'Menunggu') 
            ORDER BY k.tanggal_berangkat ASC LIMIT 1
        ");
        $stmtJadwal->execute([$jamaah_id]);
        $jadwalData = $stmtJadwal->fetch();

        if (!empty($jadwalData['tanggal_berangkat'])) {
            $jadwalKeberangkatan = date('d M Y', strtotime($jadwalData['tanggal_berangkat']));
        }
        if (!empty($jadwalData['nama_paket'])) {
            $namaPaketDiikuti = $jadwalData['nama_paket'];
        }

        // Database Anomali

        // Query Jadwal Keberangkatan Terdekat
        // $stmtJadwal = $db->prepare("
        //     SELECT k.tanggal_berangkat, pkt.nama_paket 
        //     FROM pendaftaran p 
        //     LEFT JOIN keberangkatan k ON p.keberangkatan_id = k.id
        //     JOIN paket pkt ON p.paket_id = pkt.id 
        //     WHERE p.jamaah_id = ? AND p.status IN ('Berangkat', 'Proses', 'Menunggu') 
        //     ORDER BY k.tanggal_berangkat ASC LIMIT 1
        // ");
        // $stmtJadwal->execute([$jamaah_id]);
        // $jadwalData = $stmtJadwal->fetch();

        // $jadwalKeberangkatan = !empty($jadwalData['tanggal_berangkat']) ? date('d M Y', strtotime($jadwalData['tanggal_berangkat'])) : 'Belum Ada';
        // $namaPaketDiikuti = $jadwalData['nama_paket'] ?? 'Belum Terdaftar';
    } else {
        $totalPendaftaran = 0;
        $totalPembayaran = 0;
        $jadwalKeberangkatan = '0';
        $namaPaketDiikuti = 'Belum Terdaftar';
    }
} else {
    // Data khusus admin & petugas
    $paketObj = new Paket($db);
    $totalHaji  = $paketObj->countByJenis('Haji');
    $totalUmroh = $paketObj->countByJenis('Umroh');

    $stmtJamaah = $db->query("SELECT COUNT(*) as total FROM jamaah");
    $totalJamaah = $stmtJamaah->fetch()['total'] ?? 0;

    $stmtPendaftaran = $db->query("SELECT COUNT(*) as total FROM pendaftaran");
    $totalPendaftaran = $stmtPendaftaran->fetch()['total'] ?? 0;
}

include "components/header.php";
include "components/sidebar.php";
?>

<!-- Import Google Fonts & FontAwesome -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --primary-emerald: #064e3b;
        --secondary-emerald: #047857;
        --accent-gold: #d97706;
        --light-gold: #fef3c7;
        --bg-modern: #f8fafc;
    }

    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background-color: var(--bg-modern);
    }

    .dashboard-hero-premium {
        background: linear-gradient(135deg, #022c22 0%, #064e3b 60%, #047857 100%);
        border-radius: 24px;
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(217, 119, 6, 0.2);
        box-shadow: 0 20px 30px -10px rgba(6, 78, 59, 0.25);
    }

    .hero-glow {
        position: absolute;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(217, 119, 6, 0.25) 0%, rgba(255,255,255,0) 70%);
        top: -100px;
        right: -50px;
        pointer-events: none;
    }

    .hero-pattern {
        position: absolute;
        right: -10px;
        bottom: -35px;
        opacity: 0.08;
        font-size: 16rem;
        color: #ffffff;
        pointer-events: none;
        transform: rotate(-10deg);
    }

    .gold-badge {
        background: linear-gradient(90deg, rgba(217, 119, 6, 0.2) 0%, rgba(245, 158, 11, 0.2) 100%);
        border: 1px solid #f59e0b;
        color: #fef08a;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .stat-card-modern {
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .stat-card-modern::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: transparent;
        transition: background 0.3s ease;
    }

    .stat-card-modern.haji-card::before { background: #d97706; }
    .stat-card-modern.umroh-card::before { background: #0284c7; }
    .stat-card-modern.jamaah-card::before { background: #059669; }
    .stat-card-modern.pendaftaran-card::before { background: #7c3aed; }

    .stat-card-modern:hover {
        transform: translateY(-6px);
        box-shadow: 0 16px 30px rgba(0, 0, 0, 0.07) !important;
    }

    .icon-box-modern {
        width: 58px;
        height: 58px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .quick-card-interactive {
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        transition: all 0.3s ease;
        text-decoration: none !important;
        display: block;
    }

    .quick-card-interactive:hover {
        transform: translateY(-4px);
        border-color: #cbd5e1;
        box-shadow: 0 12px 25px rgba(0,0,0,0.06);
    }

    .quick-card-interactive:hover .action-arrow {
        transform: translateX(5px);
        background-color: var(--primary-emerald) !important;
        color: #ffffff !important;
    }

    .action-arrow {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: #f1f5f9;
        color: #475569;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }
</style>

<div class="main-wrapper">
    <?php include "components/topbar.php"; ?>

    <div class="content-body p-4">
        
        <!-- Hero Banner Islami Premium -->
        <div class="dashboard-hero-premium p-4 p-md-5 mb-4 text-white">
            <div class="hero-glow"></div>
            <i class="fas fa-kaaba hero-pattern"></i>
            <div class="row align-items-center position-relative" style="z-index: 2;">
                <div class="col-lg-8">
                    <span class="badge gold-badge px-3 py-2 rounded-pill mb-3">
                        <i class="fas fa-crown text-warning me-1"></i> Dashboard <?= ucfirst($role); ?>
                    </span>
                    <h2 class="fw-extrabold mb-2 display-6">Selamat Datang, <?= htmlspecialchars($_SESSION['username'] ?? 'Pengguna'); ?>! 👋</h2>
                    <p class="mb-0 text-light opacity-90 fs-6 style-italic fw-normal">
                        "Labbaikallahumma Labbaik" — Selamat datang di Layanan Informasi Haji & Umroh Kemenhaj Panel.
                    </p>
                </div>
            </div>
        </div>

        <!-- Header Ringkasan -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold text-dark mb-1">Ringkasan Layanan</h4>
                <p class="text-muted small mb-0">Statistik dan data operasional terbaru</p>
            </div>
            <span class="badge bg-white text-emerald border shadow-sm px-3 py-2 rounded-pill d-flex align-items-center gap-2">
                <span class="spinner-grow spinner-grow-sm text-success" role="status"></span>
                <span class="fw-semibold text-dark">Sistem Live</span>
            </span>
        </div>

        <!-- Grid Cards Statistik Modern -->
        <div class="row g-4 mb-4">
            <?php if ($role === 'jamaah'): ?>
                <!-- Tampilan Card Khusus Jamaah (3 Card) -->
                <div class="col-12 col-md-4">
                    <div class="card stat-card-modern haji-card shadow-sm p-3 h-100">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted fw-semibold small d-block mb-1">Jadwal Keberangkatan</span>
                                <h2 class="fw-bold text-dark mb-0 fs-3"><?= $jadwalKeberangkatan; ?></h2>
                            </div>
                            <div class="icon-box-modern bg-warning bg-opacity-10 text-warning">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-top d-flex align-items-center justify-content-between text-muted small">
                            <span><i class="fas fa-plane text-warning me-1"></i> Program</span>
                            <span class="fw-semibold text-dark text-truncate" style="max-width: 140px;"><?= htmlspecialchars($namaPaketDiikuti); ?></span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="card stat-card-modern pendaftaran-card shadow-sm p-3 h-100">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted fw-semibold small d-block mb-1">Pendaftaran Saya</span>
                                <h2 class="fw-bold text-dark mb-0 display-6"><?= number_format($totalPendaftaran); ?></h2>
                            </div>
                            <div class="icon-box-modern bg-primary bg-opacity-10 text-primary">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-top d-flex align-items-center justify-content-between text-muted small">
                            <span><i class="fas fa-info-circle text-primary me-1"></i> Riwayat</span>
                            <span class="fw-semibold text-dark">Pendaftaran</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="card stat-card-modern jamaah-card shadow-sm p-3 h-100">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted fw-semibold small d-block mb-1">Riwayat Pembayaran</span>
                                <h2 class="fw-bold text-dark mb-0 display-6"><?= number_format($totalPembayaran); ?></h2>
                            </div>
                            <div class="icon-box-modern bg-success bg-opacity-10 text-success">
                                <i class="fas fa-wallet"></i>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-top d-flex align-items-center justify-content-between text-muted small">
                            <span><i class="fas fa-check-circle text-success me-1"></i> Lunas/DP</span>
                            <span class="fw-semibold text-dark">Transaksi</span>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <!-- Tampilan Card Khusus Admin & Petugas -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card stat-card-modern haji-card shadow-sm p-3 h-100">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted fw-semibold small d-block mb-1">Paket Haji</span>
                                <h2 class="fw-bold text-dark mb-0 display-6"><?= number_format($totalHaji); ?></h2>
                            </div>
                            <div class="icon-box-modern bg-warning bg-opacity-10 text-warning">
                                <i class="fas fa-kaaba"></i>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-top d-flex align-items-center justify-content-between text-muted small">
                            <span><i class="fas fa-check-circle text-warning me-1"></i> Layanan Aktif</span>
                            <span class="fw-semibold text-dark">Haji Program</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card stat-card-modern umroh-card shadow-sm p-3 h-100">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted fw-semibold small d-block mb-1">Paket Umroh</span>
                                <h2 class="fw-bold text-dark mb-0 display-6"><?= number_format($totalUmroh); ?></h2>
                            </div>
                            <div class="icon-box-modern bg-info bg-opacity-10 text-info">
                                <i class="fas fa-plane-departure"></i>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-top d-flex align-items-center justify-content-between text-muted small">
                            <span><i class="fas fa-check-circle text-info me-1"></i> Layanan Aktif</span>
                            <span class="fw-semibold text-dark">Umrah Travel</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card stat-card-modern jamaah-card shadow-sm p-3 h-100">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted fw-semibold small d-block mb-1">Total Jamaah</span>
                                <h2 class="fw-bold text-dark mb-0 display-6"><?= number_format($totalJamaah); ?></h2>
                            </div>
                            <div class="icon-box-modern bg-success bg-opacity-10 text-success">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-top d-flex align-items-center justify-content-between text-muted small">
                            <span><i class="fas fa-user-check text-success me-1"></i> Terverifikasi</span>
                            <span class="fw-semibold text-dark">Data Jamaah</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card stat-card-modern pendaftaran-card shadow-sm p-3 h-100">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted fw-semibold small d-block mb-1">Total Pendaftaran</span>
                                <h2 class="fw-bold text-dark mb-0 display-6"><?= number_format($totalPendaftaran); ?></h2>
                            </div>
                            <div class="icon-box-modern bg-primary bg-opacity-10 text-primary">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-top d-flex align-items-center justify-content-between text-muted small">
                            <span><i class="fas fa-sync text-primary me-1"></i> Transaksi</span>
                            <span class="fw-semibold text-dark">Registrasi</span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Menu Akses Cepat Modern -->
        <div class="mb-3">
            <h5 class="fw-bold text-dark mb-1">Akses Pintas</h5>
            <p class="text-muted small mb-0">Menu interaktif untuk mempermudah navigasi</p>
        </div>

        <div class="row g-3">
            <?php if ($role === 'jamaah'): ?>
                <div class="col-md-4">
                    <a href="form_pendaftaran_jamaah.php" class="quick-card-interactive p-3 shadow-sm">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="icon-box-modern text-white" style="background-color: var(--primary-emerald);">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">Daftar Haji/Umroh</h6>
                                    <span class="text-muted small">Pengajuan porsi baru</span>
                                </div>
                            </div>
                            <div class="action-arrow"><i class="fas fa-arrow-right"></i></div>
                        </div>
                    </a>
                </div>

                <div class="col-md-4">
                    <a href="riwayat_pendaftaran.php" class="quick-card-interactive p-3 shadow-sm">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="icon-box-modern text-dark" style="background-color: var(--light-gold); color: var(--accent-gold) !important;">
                                    <i class="fas fa-history"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">Status Pendaftaran</h6>
                                    <span class="text-muted small">Cek progres pendaftaran</span>
                                </div>
                            </div>
                            <div class="action-arrow"><i class="fas fa-arrow-right"></i></div>
                        </div>
                    </a>
                </div>

                <div class="col-md-4">
                    <a href="profile_jamaah.php" class="quick-card-interactive p-3 shadow-sm">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="icon-box-modern bg-info text-white">
                                    <i class="fas fa-user-cog"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">Profil Saya</h6>
                                    <span class="text-muted small">Atur akun & biodata</span>
                                </div>
                            </div>
                            <div class="action-arrow"><i class="fas fa-arrow-right"></i></div>
                        </div>
                    </a>
                </div>
            <?php else: ?>
                <!-- Quick Actions Admin / Petugas -->
                <div class="col-md-4">
                    <a href="form_tambah_paket.php" class="quick-card-interactive p-3 shadow-sm">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="icon-box-modern text-white" style="background-color: var(--primary-emerald);">
                                    <i class="fas fa-plus"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">Buat Paket Baru</h6>
                                    <span class="text-muted small">Kelola program Haji & Umroh</span>
                                </div>
                            </div>
                            <div class="action-arrow"><i class="fas fa-arrow-right"></i></div>
                        </div>
                    </a>
                </div>

                <div class="col-md-4">
                    <a href="tabel_jamaah.php" class="quick-card-interactive p-3 shadow-sm">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="icon-box-modern text-dark" style="background-color: var(--light-gold); color: var(--accent-gold) !important;">
                                    <i class="fas fa-id-card"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">Kelola Data Jamaah</h6>
                                    <span class="text-muted small">Kelola porsi & dokumen</span>
                                </div>
                            </div>
                            <div class="action-arrow"><i class="fas fa-arrow-right"></i></div>
                        </div>
                    </a>
                </div>

                <div class="col-md-4">
                    <a href="tabel_laporan.php" class="quick-card-interactive p-3 shadow-sm">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="icon-box-modern bg-info text-white">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">Cetak Laporan</h6>
                                    <span class="text-muted small">Rekapitulasi & transaksi</span>
                                </div>
                            </div>
                            <div class="action-arrow"><i class="fas fa-arrow-right"></i></div>
                        </div>
                    </a>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php 
include "components/footer.php";
?>