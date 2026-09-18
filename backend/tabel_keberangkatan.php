<?php
require_once "connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);
$auth->checkRole(['admin', 'petugas']);

$userRole = $_SESSION['role'] ?? '';

$jadwal = [];
$errorMessage = "";

try {
    // Kueri Utama dengan pengurutan berdasarkan ID jika nama kolom tanggal bervariasi
    $query = "SELECT jk.*, 
                     pk.nama_paket,
                     COALESCE(pk.jenis, pk.tipe, 'Haji/Umroh') AS jenis_layanan
              FROM jadwal_keberangkatan jk
              LEFT JOIN paket pk ON jk.paket_id = pk.id
              ORDER BY jk.id DESC";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $jadwal = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Kueri Fallback jika nama tabel menggunakan 'keberangkatan'
    try {
        $queryFallback = "SELECT jk.*, 
                                 pk.nama_paket,
                                 'Haji/Umroh' AS jenis_layanan
                          FROM keberangkatan jk
                          LEFT JOIN paket pk ON jk.paket_id = pk.id
                          ORDER BY jk.id DESC";
        $stmt = $db->prepare($queryFallback);
        $stmt->execute();
        $jadwal = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $ex) {
        $errorMessage = "Terjadi kesalahan saat mengambil jadwal keberangkatan: " . $ex->getMessage();
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
</style>

<div class="main-wrapper">
    <?php include "components/topbar.php"; ?>

    <div class="content-body p-4">
        <!-- Header Halaman -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <div class="icon-header-box text-white me-3 shadow-sm">
                    <i class="fas fa-plane-departure"></i>
                </div>
                <div>
                    <h3 class="fw-extrabold text-dark mb-0">Jadwal Keberangkatan</h3>
                    <p class="mb-0 text-muted small">Kelola tanggal keberangkatan, maskapai, dan kuota penerbangan jamaah</p>
                </div>
            </div>
            
            <a href="form_tambah_jadwal.php" class="btn btn-gold px-4 py-2.5 shadow-sm d-flex align-items-center gap-2">
                <i class="fas fa-plus"></i> Tambah Jadwal
            </a>
        </div>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4">
                <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>

        <!-- Table Card Container -->
        <div class="card table-card border-0 shadow-sm bg-white">
            <div class="card-body p-4 p-md-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">Daftar Jadwal Penerbangan</h5>
                        <p class="text-muted small mb-0">Informasi tanggal estimasi keberangkatan dan kepulangan jamaah</p>
                    </div>
                    <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-semibold">
                        <?= count($jadwal); ?> Jadwal Terdaftar
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-borderless align-middle mb-0">
                        <thead>
                            <tr class="border-bottom">
                                <th class="pb-3 ps-2" style="width: 50px;">NO</th>
                                <th class="pb-3">NAMA PAKET & JENIS</th>
                                <th class="pb-3">TGL KEBERANGKATAN</th>
                                <th class="pb-3">TGL KEPULANGAN</th>
                                <th class="pb-3">MASKAPAI / EMBARKASI</th>
                                <th class="pb-3 text-center">STATUS</th>
                                <th class="pb-3 text-center" style="width: 120px;">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($jadwal) > 0): ?>
                                <?php $no = 1; foreach ($jadwal as $row): ?>
                                <?php 
                                    // Pengecekan variasi nama kolom keberangkatan
                                    $tglVal = $row['tgl_keberangkatan'] ?? $row['tanggal_keberangkatan'] ?? $row['tanggal'] ?? null;
                                    $tglBerangkat = $tglVal ? strtotime($tglVal) : null;
                                    $today = strtotime('today');
                                    
                                    if ($tglBerangkat && $tglBerangkat >= $today) {
                                        $statusBadge = '<span class="badge rounded-pill px-3 py-2" style="background-color: #e0f2fe; color: #0284c7; font-size: 11px;"><i class="fas fa-clock me-1"></i> Mendatang</span>';
                                    } else {
                                        $statusBadge = '<span class="badge rounded-pill px-3 py-2" style="background-color: #d1fae5; color: #047857; font-size: 11px;"><i class="fas fa-check-circle me-1"></i> Selesai</span>';
                                    }

                                    // Pengecekan variasi nama kolom kepulangan
                                    $tglPulangVal = $row['tgl_kepulangan'] ?? $row['tanggal_kepulangan'] ?? null;
                                ?>
                                <tr class="border-bottom">
                                    <td class="ps-2">
                                        <div class="d-flex align-items-center justify-content-center rounded-3 fw-bold text-muted" 
                                             style="width: 32px; height: 32px; background-color: #f1f5f9; font-size: 13px;">
                                            <?= $no++; ?>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="fw-bold text-dark" style="font-size: 14px;"><?= htmlspecialchars($row['nama_paket'] ?? 'Paket Umum'); ?></div>
                                        <div class="text-muted small" style="font-size: 12px;">
                                            <span class="badge px-2 py-1" style="background-color: #f1f5f9; color: #475569;">
                                                <?= htmlspecialchars($row['jenis_layanan']); ?>
                                            </span>
                                        </div>
                                    </td>

                                    <td class="fw-bold" style="color: var(--secondary-emerald); font-size: 13px;">
                                        <i class="far fa-calendar-alt me-1 text-muted"></i>
                                        <?= $tglVal ? date('d M Y', strtotime($tglVal)) : '-'; ?>
                                    </td>

                                    <td class="text-secondary small fw-medium" style="font-size: 13px;">
                                        <?= !empty($tglPulangVal) ? date('d M Y', strtotime($tglPulangVal)) : '<span class="text-muted fst-italic">-</span>'; ?>
                                    </td>

                                    <td>
                                        <div class="fw-semibold text-dark" style="font-size: 13px;">
                                            <i class="fas fa-plane me-1" style="color: var(--accent-gold);"></i>
                                            <?= htmlspecialchars($row['maskapai'] ?? 'Saudia Airlines'); ?>
                                        </div>
                                        <div class="text-muted small" style="font-size: 11px;">
                                            Embarkasi: <?= htmlspecialchars($row['embarkasi'] ?? 'Jakarta (CGK)'); ?>
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        <?= $statusBadge; ?>
                                    </td>

                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="form_update_jadwal.php?id=<?= $row['id']; ?>" class="btn btn-sm d-flex align-items-center justify-content-center rounded-3 border-0" style="background-color: #e0f2fe; color: #0284c7; width: 34px; height: 34px;" title="Edit">
                                                <i class="fas fa-edit" style="font-size: 13px;"></i>
                                            </a>
                                            <?php if ($userRole === 'admin'): ?>
                                            <a href="hapus_jadwal.php?id=<?= $row['id']; ?>" class="btn btn-sm d-flex align-items-center justify-content-center rounded-3 border-0" style="background-color: #fee2e2; color: #ef4444; width: 34px; height: 34px;" onclick="return confirm('Yakin ingin menghapus jadwal ini?');" title="Hapus">
                                                <i class="fas fa-trash-alt" style="font-size: 13px;"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fas fa-calendar-times fa-2x mb-3 d-block opacity-25"></i>
                                        Belum ada jadwal keberangkatan yang ditambahkan.
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