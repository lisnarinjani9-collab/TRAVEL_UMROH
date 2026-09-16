<?php
require_once "connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);
$auth->checkRole(['admin', 'petugas']);

$userRole = $_SESSION['role'] ?? '';

$laporanList = [];
$errorMessage = "";

try {
    // Mengambil data gabungan (JOIN) dari tabel pendaftaran, jamaah, paket, dan pembayaran
$query = "SELECT 
                p.id AS id_pendaftaran,
                p.tgl_daftar,
                j.nama_lengkap AS nama_jamaah,
                j.nik AS no_identitas,
                pkt.nama_paket,
                pkt.jenis AS jenis_paket,
                pkt.harga,
                COALESCE(SUM(pm.jumlah_bayar), 0) AS total_bayar,
                CASE 
                    WHEN COALESCE(SUM(pm.jumlah_bayar), 0) >= pkt.harga THEN 'Lunas'
                    WHEN COALESCE(SUM(pm.jumlah_bayar), 0) > 0 THEN 'Cicilan'
                    ELSE 'Belum Bayar'
                END AS status_pembayaran
              FROM pendaftaran p
              LEFT JOIN jamaah j ON p.id_jamaah = j.id
              LEFT JOIN paket pkt ON p.id_paket = pkt.id
              LEFT JOIN pembayaran pm ON p.id = pm.id_pendaftaran
              GROUP BY p.id
              ORDER BY p.id DESC";
              
    $stmt = $db->prepare($query);
    $stmt->execute();
    $laporanList = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = "Terjadi kesalahan saat mengambil data laporan: " . $e->getMessage();
}

// Hitung Statistik Laporan
$totalPendaftaran = count($laporanList);
$totalLunas = 0;
$totalPendapatan = 0;

foreach ($laporanList as $lap) {
    if ($lap['status_pembayaran'] === 'Lunas') {
        $totalLunas++;
    }
    $totalPendapatan += $lap['total_bayar'];
}

include "components/header.php";
include "components/sidebar.php";
?>

<!-- Import Google Fonts & Icons -->
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

    /* Stat Cards Modern */
    .stat-card-laporan {
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card-laporan:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.06) !important;
    }

    .stat-card-laporan::before {
        content: '';
        position: absolute;
        top: 0; left: 0; width: 100%; height: 4px;
    }
    .stat-card-laporan.total::before { background: var(--primary-emerald); }
    .stat-card-laporan.lunas::before { background: #10b981; }
    .stat-card-laporan.omset::before { background: var(--accent-gold); }

    .icon-box-p {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
    }

    /* Styling Table Modern */
    .custom-table-card {
        border-radius: 20px;
        border: 1px solid #e2e8f0;
    }

    .table-modern thead th {
        background-color: #f8fafc;
        color: #64748b;
        font-weight: 700;
        font-size: 0.75rem;
        letter-spacing: 0.7px;
        text-transform: uppercase;
        border-bottom: 2px solid #e2e8f0;
        padding-top: 1rem;
        padding-bottom: 1rem;
    }

    .table-modern tbody tr {
        transition: all 0.2s ease;
    }

    .table-modern tbody tr:hover {
        background-color: rgba(241, 245, 249, 0.5);
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

    .action-btn {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        border: none;
    }

    .action-btn-view {
        background-color: #fef3c7;
        color: #d97706;
    }
    .action-btn-view:hover {
        background-color: #d97706;
        color: #ffffff;
    }

    @media print {
        .sidebar, .topbar, .btn-gold, .action-btn, .no-print {
            display: none !important;
        }
        .main-wrapper {
            margin: 0 !important;
            padding: 0 !important;
        }
    }
</style>

<div class="main-wrapper">
    <?php include "components/topbar.php"; ?>

    <div class="content-body p-4">
        <!-- Header Halaman Modern -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
            <div class="d-flex align-items-center">
                <div class="icon-box-p text-white me-3 shadow-sm" style="background: linear-gradient(135deg, #064e3b 0%, #047857 100%);">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <div>
                    <h3 class="fw-extrabold text-dark mb-0">Generate Laporan</h3>
                    <p class="mb-0 text-muted small">Laporan rekapitulasi pendaftaran dan transaksi pembayaran jamaah</p>
                </div>
            </div>
            
            <!-- Tombol Print Laporan -->
            <button onclick="window.print()" class="btn btn-gold px-4 py-2.5 shadow-sm d-flex align-items-center gap-2">
                <i class="fas fa-print"></i> Cetak Laporan
            </button>
        </div>

        <!-- Ringkasan Stats Modern -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card stat-card-laporan total shadow-sm p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold d-block mb-1">Total Pendaftaran</span>
                            <h3 class="fw-bold text-dark mb-0"><?= $totalPendaftaran; ?></h3>
                        </div>
                        <div class="icon-box-p text-emerald" style="background-color: rgba(6, 78, 59, 0.1); color: var(--primary-emerald);">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card-laporan lunas shadow-sm p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold d-block mb-1">Pendaftaran Lunas</span>
                            <h3 class="fw-bold text-dark mb-0"><?= $totalLunas; ?></h3>
                        </div>
                        <div class="icon-box-p bg-success bg-opacity-10 text-success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card-laporan omset shadow-sm p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold d-block mb-1">Total Pembayaran Masuk</span>
                            <h3 class="fw-bold text-dark mb-0" style="font-size: 1.35rem;">Rp <?= number_format($totalPendapatan, 0, ',', '.'); ?></h3>
                        </div>
                        <div class="icon-box-p bg-warning bg-opacity-10 text-warning">
                            <i class="fas fa-wallet"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center gap-2">
                <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>

        <!-- Table Card Container Modern -->
        <div class="card custom-table-card border-0 shadow-sm bg-white w-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">Daftar Transaksi Jamaah</h5>
                        <p class="mb-0 small text-muted">Rekapitulasi data gabungan jamaah, paket, dan status pembayaran</p>
                    </div>
                    <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-semibold">
                        <?= count($laporanList); ?> Data Laporan
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-modern align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3" style="width: 60px;">NO</th>
                                <th>INFORMASI JAMAAH</th>
                                <th>PAKET TERPILIH</th>
                                <th>TGL DAFTAR</th>
                                <th>TOTAL HARGA</th>
                                <th>TOTAL DIBAYAR</th>
                                <th>STATUS BAYAR</th>
                                <th class="text-center no-print" style="width: 90px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($laporanList) > 0): ?>
                                <?php $no = 1; foreach ($laporanList as $row): ?>
                                <?php 
                                    $sBayar = $row['status_pembayaran'];
                                    $badgeClass = 'bg-danger bg-opacity-10 text-danger';
                                    if ($sBayar === 'Lunas') {
                                        $badgeClass = 'bg-success bg-opacity-10 text-success';
                                    } elseif ($sBayar === 'Cicilan') {
                                        $badgeClass = 'bg-warning bg-opacity-10 text-warning';
                                    }
                                ?>
                                <tr class="border-bottom">
                                    <td class="ps-3">
                                        <span class="fw-bold text-secondary small">
                                            <?= sprintf("%02d", $no++); ?>
                                        </span>
                                    </td>

                                    <!-- Nama & Identitas Jamaah -->
                                    <td class="py-3">
                                        <div class="fw-bold text-dark fs-6 mb-1"><?= htmlspecialchars($row['nama_jamaah'] ?? 'Jamaah Tidak Ditemukan'); ?></div>
                                        <div class="text-muted small">
                                            <i class="far fa-id-card me-1 opacity-50"></i>NIK/ID: <?= htmlspecialchars($row['no_identitas'] ?? '-'); ?>
                                        </div>
                                    </td>

                                    <!-- Paket Terpilih -->
                                    <td>
                                        <div class="fw-semibold text-dark small"><?= htmlspecialchars($row['nama_paket'] ?? '-'); ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($row['jenis_paket'] ?? ''); ?></small>
                                    </td>

                                    <!-- Tgl Daftar -->
                                    <td>
                                        <div class="fw-semibold text-secondary small">
                                            <i class="far fa-calendar-alt me-1 text-muted"></i>
                                            <?= !empty($row['tgl_daftar']) ? date('d/m/Y', strtotime($row['tgl_daftar'])) : '-'; ?>
                                        </div>
                                    </td>

                                    <!-- Harga Paket -->
                                    <td>
                                        <span class="fw-semibold text-dark small">
                                            Rp <?= number_format($row['harga'] ?? 0, 0, ',', '.'); ?>
                                        </span>
                                    </td>

                                    <!-- Total Dibayar -->
                                    <td>
                                        <span class="fw-bold" style="color: var(--primary-emerald); font-size: 14px;">
                                            Rp <?= number_format($row['total_bayar'] ?? 0, 0, ',', '.'); ?>
                                        </span>
                                    </td>

                                    <!-- Status Pembayaran -->
                                    <td>
                                        <span class="badge rounded-pill px-3 py-2 <?= $badgeClass; ?>" style="font-size: 11px; font-weight: 600;">
                                            <?= $sBayar; ?>
                                        </span>
                                    </td>

                                    <!-- Action -->
                                    <td class="text-center no-print">
                                        <div class="d-flex justify-content-center">
                                            <!-- Tombol Detail Modal -->
                                            <button type="button" class="action-btn action-btn-view" data-bs-toggle="modal" data-bs-target="#modalDetail<?= $row['id_pendaftaran']; ?>" title="Lihat Detail Laporan">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal Detail Laporan -->
                                <div class="modal fade" id="modalDetail<?= $row['id_pendaftaran']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content border-0 shadow-lg rounded-4">
                                            <div class="modal-header border-bottom-0 pb-0">
                                                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                                                    <i class="fas fa-file-alt text-warning"></i> Detail Rincian Laporan
                                                </h5>
                                            </div>
                                            <div class="modal-body p-4">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="text-muted small fw-semibold d-block mb-1">Nama Jamaah</label>
                                                        <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($row['nama_jamaah'] ?? '-'); ?></div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="text-muted small fw-semibold d-block mb-1">No Identitas / NIK</label>
                                                        <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($row['no_identitas'] ?? '-'); ?></div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="text-muted small fw-semibold d-block mb-1">Paket Travel</label>
                                                        <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama_paket'] ?? '-'); ?></div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="text-muted small fw-semibold d-block mb-1">Tanggal Pendaftaran</label>
                                                        <div class="fw-bold text-dark"><?= !empty($row['tgl_daftar']) ? date('d F Y', strtotime($row['tgl_daftar'])) : '-'; ?></div>
                                                    </div>
                                                    <div class="col-12"><hr class="my-2"></div>
                                                    <div class="col-md-4">
                                                        <label class="text-muted small fw-semibold d-block mb-1">Biaya Paket</label>
                                                        <div class="fw-bold text-dark fs-6">Rp <?= number_format($row['harga'] ?? 0, 0, ',', '.'); ?></div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="text-muted small fw-semibold d-block mb-1">Jumlah Terbayar</label>
                                                        <div class="fw-bold text-success fs-6">Rp <?= number_format($row['total_bayar'] ?? 0, 0, ',', '.'); ?></div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="text-muted small fw-semibold d-block mb-1">Status Pembayaran</label>
                                                        <div>
                                                            <span class="badge rounded-pill px-3 py-2 <?= $badgeClass; ?>" style="font-size: 11px; font-weight: 600;">
                                                                <?= $sBayar; ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-top-0 pt-0">
                                                <button type="button" class="btn btn-secondary px-4 rounded-3" data-bs-dismiss="modal">Tutup</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fas fa-folder-open fa-3x mb-3 text-light-gray d-block"></i>
                                        Belum ada data transaksi/laporan yang tersedia.
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