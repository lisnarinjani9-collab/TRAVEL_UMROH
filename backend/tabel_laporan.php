<?php
require_once "connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);
$auth->checkRole(['admin', 'petugas']);

$laporanData = [];
$totalPendaftaran = 0;
$pendaftaranLunas = 0;
$totalPembayaranMasuk = 0;
$errorMessage = "";

try {
    // 1. Ambil data gabungan laporan tanpa membuat tabel laporan baru
    $queryLaporan = "SELECT 
                        pd.id AS pendaftaran_id,
                        j.nama_lengkap AS nama_jamaah,
                        j.nik,
                        pk.nama_paket,
                        pk.harga AS total_harga,
                        pd.created_at AS tgl_daftar,
                        COALESCE(
                            (SELECT SUM(COALESCE(pm.jumlah_bayar, pm.nominal, pm.bayar, pm.jumlah, 0)) 
                             FROM pembayaran pm 
                             WHERE pm.pendaftaran_id = pd.id), 0
                        ) AS total_dibayar,
                        pd.status_pembayaran
                     FROM pendaftaran pd
                     LEFT JOIN jamaah j ON  pd.jamaah_id = j.id
                     LEFT JOIN paket pk ON pd.paket_id = pk.id
                     ORDER BY pd.id DESC";

    $stmt = $db->prepare($queryLaporan);
    $stmt->execute();
    $laporanData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Hitung Card Ringkasan Laporan
    $totalPendaftaran = count($laporanData);
    
    foreach ($laporanData as $row) {
        $totalPembayaranMasuk += (float)$row['total_dibayar'];
        
        $status = strtolower($row['status_pembayaran'] ?? '');
        $totalHarga = (float)($row['total_harga'] ?? 0);
        $totalDibayar = (float)($row['total_dibayar'] ?? 0);
        
        if ($status === 'lunas' || ($totalHarga > 0 && $totalDibayar >= $totalHarga)) {
            $pendaftaranLunas++;
        }
    }

} catch (PDOException $e) {
    // Fallback Query sederhanakan jika struktur tabel pembayaran berbeda
    try {
        $querySimple = "SELECT 
                            pd.id AS pendaftaran_id,
                            COALESCE(j.nama_lengkap, 'Jamaah') AS nama_jamaah,
                            COALESCE(j.nik, '-') AS nik,
                            COALESCE(pk.nama_paket, 'Paket Travel') AS nama_paket,
                            COALESCE(pk.harga, 0) AS total_harga,
                            COALESCE(pd.created_at, pd.tanggal_daftar, CURRENT_DATE) AS tgl_daftar,
                            COALESCE(pd.total_bayar, pd.jumlah_bayar, 0) AS total_dibayar,
                            COALESCE(pd.status_pembayaran, pd.status, 'Pending') AS status_pembayaran
                        FROM pendaftaran pd
                        LEFT JOIN jamaah j ON pd.jamaah_id = j.id
                        LEFT JOIN paket pk ON pd.paket_id = pk.id
                        ORDER BY pd.id DESC";

        $stmt = $db->prepare($querySimple);
        $stmt->execute();
        $laporanData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalPendaftaran = count($laporanData);
        foreach ($laporanData as $row) {
            $totalPembayaranMasuk += (float)$row['total_dibayar'];
            if (strtolower($row['status_pembayaran']) === 'lunas') {
                $pendaftaranLunas++;
            }
        }
    } catch (PDOException $ex) {
        $errorMessage = "Terjadi kesalahan saat mengambil data laporan: " . $ex->getMessage();
    }
}

include "components/header.php";
include "components/sidebar.php";
?>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --primary-emerald: #064e3b;
        --secondary-emerald: #047857;
        --accent-gold: #d97706;
        --bg-modern: #f8fafc;
    }

    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background-color: var(--bg-modern);
    }

    .icon-header-box {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: linear-gradient(135deg, #064e3b 0%, #047857 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
    }

    .table-card {
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        width: 100%;
    }

    .btn-gold {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        color: #ffffff !important;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-gold:hover {
        opacity: 0.95;
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(217, 119, 6, 0.3);
    }

    .table th {
        font-size: 0.825rem;
        font-weight: 700;
        color: #334155;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .card-stat {
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        transition: transform 0.2s ease;
    }

    .card-stat:hover {
        transform: translateY(-3px);
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
</style>

<div class="main-wrapper">
    <?php include "components/topbar.php"; ?>

    <div class="content-body p-4">
        <!-- Header Halaman -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <div class="icon-header-box text-white me-3 shadow-sm">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <div>
                    <h3 class="fw-extrabold text-dark mb-0">Generate Laporan</h3>
                    <p class="mb-0 text-muted small">Laporan rekapitulasi pendaftaran dan transaksi pembayaran jamaah</p>
                </div>
            </div>
            
            <button onclick="window.print()" class="btn btn-gold px-4 py-2.5 shadow-sm d-flex align-items-center gap-2">
                <i class="fas fa-print"></i> Cetak Laporan
            </button>
        </div>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4">
                <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>

        <!-- Stat Cards Container -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card card-stat border-0 shadow-sm bg-white p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small fw-semibold mb-1">Total Pendaftaran</p>
                            <h3 class="fw-bold text-dark mb-0"><?= number_format($totalPendaftaran); ?></h3>
                        </div>
                        <div class="stat-icon bg-light text-primary">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-stat border-0 shadow-sm bg-white p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small fw-semibold mb-1">Pendaftaran Lunas</p>
                            <h3 class="fw-bold text-success mb-0"><?= number_format($pendaftaranLunas); ?></h3>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-stat border-0 shadow-sm bg-white p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small fw-semibold mb-1">Total Pembayaran Masuk</p>
                            <h3 class="fw-bold text-dark mb-0">Rp <?= number_format($totalPembayaranMasuk, 0, ',', '.'); ?></h3>
                        </div>
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="fas fa-wallet"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Card Container -->
        <div class="card table-card border-0 shadow-sm bg-white">
            <div class="card-body p-4 p-md-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">Daftar Transaksi Jamaah</h5>
                        <p class="text-muted small mb-0">Rekapitulasi data gabungan jamaah, paket, dan status pembayaran</p>
                    </div>
                    <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-semibold">
                        <?= count($laporanData); ?> Data Laporan
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-borderless align-middle mb-0">
                        <thead>
                            <tr class="border-bottom">
                                <th class="pb-3 ps-2" style="width: 50px;">NO</th>
                                <th class="pb-3">INFORMASI JAMAAH</th>
                                <th class="pb-3">PAKET TERPILIH</th>
                                <th class="pb-3">TGL DAFTAR</th>
                                <th class="pb-3">TOTAL HARGA</th>
                                <th class="pb-3">TOTAL DIBAYAR</th>
                                <th class="pb-3 text-center">STATUS BAYAR</th>
                                <th class="pb-3 text-center" style="width: 100px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($laporanData) > 0): ?>
                                <?php $no = 1; foreach ($laporanData as $row): ?>
                                <?php 
                                    $harga = (float)($row['total_harga'] ?? 0);
                                    $dibayar = (float)($row['total_dibayar'] ?? 0);
                                    $statusRaw = strtolower($row['status_pembayaran'] ?? '');

                                    if ($statusRaw === 'lunas' || ($harga > 0 && $dibayar >= $harga)) {
                                        $badgeStatus = '<span class="badge rounded-pill px-3 py-1 bg-success bg-opacity-10 text-success fw-semibold"><i class="fas fa-check-circle me-1"></i> Lunas</span>';
                                    } elseif ($dibayar > 0) {
                                        $badgeStatus = '<span class="badge rounded-pill px-3 py-1 bg-warning bg-opacity-10 text-warning fw-semibold"><i class="fas fa-clock me-1"></i> Cicilan</span>';
                                    } else {
                                        $badgeStatus = '<span class="badge rounded-pill px-3 py-1 bg-danger bg-opacity-10 text-danger fw-semibold"><i class="fas fa-times-circle me-1"></i> Belum Bayar</span>';
                                    }
                                ?>
                                <tr class="border-bottom">
                                    <td class="ps-2">
                                        <div class="d-flex align-items-center justify-content-center rounded-3 fw-bold text-muted" 
                                             style="width: 32px; height: 32px; background-color: #f1f5f9; font-size: 13px;">
                                            <?= $no++; ?>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="fw-bold text-dark" style="font-size: 14px;"><?= htmlspecialchars($row['nama_jamaah'] ?? '-'); ?></div>
                                        <div class="text-muted small" style="font-size: 12px;">NIK: <?= htmlspecialchars($row['nik'] ?? '-'); ?></div>
                                    </td>

                                    <td class="fw-semibold text-secondary" style="font-size: 13px;">
                                        <?= htmlspecialchars($row['nama_paket'] ?? 'Paket Umum'); ?>
                                    </td>

                                    <td class="small text-muted" style="font-size: 13px;">
                                        <?= !empty($row['tgl_daftar']) ? date('d M Y', strtotime($row['tgl_daftar'])) : '-'; ?>
                                    </td>

                                    <td class="fw-bold text-dark" style="font-size: 13px;">
                                        Rp <?= number_format($harga, 0, ',', '.'); ?>
                                    </td>

                                    <td class="fw-bold text-success" style="font-size: 13px;">
                                        Rp <?= number_format($dibayar, 0, ',', '.'); ?>
                                    </td>

                                    <td class="text-center">
                                        <?= $badgeStatus; ?>
                                    </td>

                                    <td class="text-center">
                                        <a href="detail_pembayaran.php?id=<?= $row['pendaftaran_id']; ?>" 
                                           class="btn btn-sm d-inline-flex align-items-center justify-content-center rounded-3 border-0" 
                                           style="background-color: #e0f2fe; color: #0284c7; width: 34px; height: 34px;" 
                                           title="Detail Pembayaran">
                                            <i class="fas fa-eye" style="font-size: 13px;"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fas fa-folder-open fa-3x mb-3 text-secondary opacity-50"></i>
                                        <p class="mb-0">Belum ada data transaksi/laporan yang tersedia.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
include "components/footer.php";
include "components/bottom.php"; 
?>