<?php
require_once "connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);

$auth->checkRole(['jamaah']);

$userRole = $_SESSION['role'] ?? '';
$userId   = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
$jamaahId = $_SESSION['jamaah_id'] ?? null;

$jadwalList = [];
$errorMessage = "";
$successMessage = "";

try {
    // Query mengambil data keberangkatan berdasarkan jamaah yang login
    $query = "SELECT k.*, 
                     p.tgl_daftar,
                     COALESCE(j.nama_lengkap, 'Jamaah') AS nama_jamaah, 
                     pk.nama_paket,
                     pk.jenis AS jenis_layanan,
                     pk.durasi,
                     pk.deskripsi
              FROM pendaftaran p
              INNER JOIN paket pk ON p.paket_id = pk.id
              LEFT JOIN keberangkatan k ON p.keberangkatan_id = k.id
              LEFT JOIN jamaah j ON p.jamaah_id = j.id";

    $params = [];

    if (!empty($jamaahId)) {
        $query .= " WHERE p.jamaah_id = :jamaah_id";
        $params[':jamaah_id'] = $jamaahId;
    } elseif (!empty($userId)) {
        $query .= " WHERE j.user_id = :user_id";
        $params[':user_id'] = $userId;
    }

    $query .= " ORDER BY k.tanggal_berangkat ASC, p.id DESC";

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $jadwalList = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Fallback query jika relasi jadwal belum terikat
    try {
        $queryFallback = "SELECT k.*, 
                                 pk.nama_paket,
                                 pk.jenis AS jenis_layanan,
                                 pk.durasi,
                                 pk.deskripsi
                          FROM keberangkatan k
                          INNER JOIN paket pk ON k.paket_id = pk.id
                          ORDER BY k.tanggal_berangkat ASC";

        $stmt = $db->prepare($queryFallback);
        $stmt->execute();
        $jadwalList = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    .table th {
        font-size: 0.825rem;
        font-weight: 700;
        color: #334155;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .btn-action-yellow {
        background-color: #fef3c7;
        color: #d97706;
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: all 0.2s ease;
        border: none;
    }

    .btn-action-yellow:hover {
        background-color: #fde68a;
        color: #b45309;
    }

    .modal-content {
        border-radius: 20px;
        border: none;
    }

    .modal-header-icon {
        color: #d97706;
        font-size: 1.25rem;
    }
</style>

<div class="main-wrapper">
    <?php include "components/topbar.php"; ?>

    <div class="content-body p-4">
        <!-- Header Halaman -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div class="d-flex align-items-center">
                <div class="icon-header-box text-white me-3 shadow-sm">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div>
                    <h3 class="fw-extrabold text-dark mb-0">Jadwal Keberangkatan</h3>
                    <p class="mb-0 text-muted small">Pantau detail tanggal keberangkatan, penerbangan, dan rincian perjalanan Anda</p>
                </div>
            </div>
        </div>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4 alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($errorMessage); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Table Card Container -->
        <div class="card table-card border-0 shadow-sm bg-white">
            <div class="card-body p-4 p-md-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">Informasi Penerbangan & Transportasi</h5>
                        <p class="text-muted small mb-0">Jadwal resmi penerbangan dan kepulangan ibadah Anda</p>
                    </div>
                    <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-semibold">
                        <?= count($jadwalList); ?> Agenda
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-borderless align-middle mb-0">
                        <thead>
                            <tr class="border-bottom">
                                <th class="pb-3 ps-2" style="width: 50px;">NO</th>
                                <th class="pb-3">PROGRAM & LAYANAN</th>
                                <th class="pb-3">MASKAPAI / EMBARKASI</th>
                                <th class="pb-3">TGL KEBERANGKATAN</th>
                                <th class="pb-3">TGL KEPULANGAN</th>
                                <th class="pb-3 text-center">STATUS</th>
                                <th class="pb-3 text-center" style="width: 100px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($jadwalList) > 0): ?>
                                <?php $no = 1; foreach ($jadwalList as $index => $row): ?>
                                <?php 
                                    $tglBerangkat = $row['tanggal_berangkat'] ?? null;
                                    $tglPulang    = $row['tgl_kepulangan'] ?? null;
                                    $today        = date('Y-m-d');

                                    if ($tglBerangkat && $tglBerangkat > $today) {
                                        $statusBadge = '<span class="badge rounded-pill px-3 py-2" style="background-color: #e0f2fe; color: #0284c7; font-size: 11px;"><i class="fas fa-plane-departure me-1"></i> Mendatang</span>';
                                    } elseif ($tglBerangkat && $tglBerangkat <= $today && ($tglPulang >= $today || !$tglPulang)) {
                                        $statusBadge = '<span class="badge rounded-pill px-3 py-2" style="background-color: #d1fae5; color: #047857; font-size: 11px;"><i class="fas fa-sync-alt me-1"></i> Berlangsung</span>';
                                    } else {
                                        $statusBadge = '<span class="badge rounded-pill px-3 py-2" style="background-color: #f1f5f9; color: #64748b; font-size: 11px;"><i class="fas fa-check-circle me-1"></i> Selesai</span>';
                                    }

                                    $modalId = "detailModalJadwal" . $index;
                                ?>
                                <tr class="border-bottom">
                                    <td class="ps-2">
                                        <div class="d-flex align-items-center justify-content-center rounded-3 fw-bold text-muted" 
                                             style="width: 32px; height: 32px; background-color: #f1f5f9; font-size: 13px;">
                                            <?= $no++; ?>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="fw-bold text-dark" style="font-size: 14px;"><?= htmlspecialchars($row['nama_paket'] ?? 'Paket Belum Ditentukan'); ?></div>
                                        <div class="text-muted small" style="font-size: 12px;">
                                            <span class="badge px-2 py-1 mt-1" style="background-color: #f1f5f9; color: #475569;">
                                                <?= htmlspecialchars($row['jenis_layanan'] ?? 'Haji/Umroh'); ?>
                                            </span>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="fw-semibold text-dark" style="font-size: 13px;">
                                            <i class="fas fa-plane me-1 text-primary"></i> <?= htmlspecialchars($row['maskapai'] ?? '-'); ?>
                                        </div>
                                        <div class="text-muted small" style="font-size: 12px;">
                                            Embarkasi: <?= htmlspecialchars($row['embarkasi'] ?? '-'); ?>
                                        </div>
                                    </td>

                                    <td class="fw-semibold text-success" style="font-size: 13px;">
                                        <i class="far fa-calendar-alt me-1 text-muted"></i>
                                        <?= $tglBerangkat ? date('d M Y', strtotime($tglBerangkat)) : '-'; ?>
                                    </td>

                                    <td class="fw-semibold text-secondary" style="font-size: 13px;">
                                        <i class="far fa-calendar-check me-1 text-muted"></i>
                                        <?= $tglPulang ? date('d M Y', strtotime($tglPulang)) : '-'; ?>
                                    </td>

                                    <td class="text-center">
                                        <?= $statusBadge; ?>
                                    </td>

                                    <td class="text-center">
                                        <button type="button" class="btn-action-yellow" data-bs-toggle="modal" data-bs-target="#<?= $modalId; ?>" title="Lihat Detail Jadwal">
                                            <i class="fas fa-eye" style="font-size: 12px;"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Modal Detail Jadwal -->
                                <div class="modal fade" id="<?= $modalId; ?>" tabindex="-1" aria-labelledby="<?= $modalId; ?>Label" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content p-3 shadow">
                                            <div class="modal-header border-0 pb-0">
                                                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="<?= $modalId; ?>Label">
                                                    <i class="fas fa-plane-departure modal-header-icon"></i> Detail Jadwal Keberangkatan
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body pt-4">
                                                <div class="row g-3 mb-4">
                                                    <div class="col-6">
                                                        <span class="text-muted small d-block mb-1">Nama Paket</span>
                                                        <h6 class="fw-bold text-dark mb-0"><?= htmlspecialchars($row['nama_paket'] ?? '-'); ?></h6>
                                                    </div>
                                                    <div class="col-6">
                                                        <span class="text-muted small d-block mb-1">Maskapai</span>
                                                        <h6 class="fw-bold text-primary mb-0"><?= htmlspecialchars($row['maskapai'] ?? '-'); ?></h6>
                                                    </div>
                                                </div>

                                                <div class="row g-3 mb-4">
                                                    <div class="col-6">
                                                        <span class="text-muted small d-block mb-1">Tanggal Berangkat</span>
                                                        <span class="fw-bold text-success">
                                                            <i class="far fa-calendar-alt me-1"></i> <?= $tglBerangkat ? date('d M Y', strtotime($tglBerangkat)) : '-'; ?>
                                                        </span>
                                                    </div>
                                                    <div class="col-6">
                                                        <span class="text-muted small d-block mb-1">Tanggal Kepulangan</span>
                                                        <span class="fw-bold text-secondary">
                                                            <i class="far fa-calendar-check me-1"></i> <?= $tglPulang ? date('d M Y', strtotime($tglPulang)) : '-'; ?>
                                                        </span>
                                                    </div>
                                                </div>

                                                <div class="row g-3 mb-4">
                                                    <div class="col-6">
                                                        <span class="text-muted small d-block mb-1">Embarkasi</span>
                                                        <span class="fw-semibold text-dark"><?= htmlspecialchars($row['embarkasi'] ?? '-'); ?></span>
                                                    </div>
                                                    <div class="col-6">
                                                        <span class="text-muted small d-block mb-1">Kuota Penerbangan</span>
                                                        <span class="fw-semibold text-dark"><?= htmlspecialchars($row['kuota_penerbangan'] ?? '-'); ?> Orang</span>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <span class="text-muted small d-block mb-1">Catatan / Keterangan</span>
                                                    <div class="p-3 bg-light rounded-3 text-secondary small" style="min-height: 70px;">
                                                        <?= !empty($row['keterangan']) ? nl2br(htmlspecialchars($row['keterangan'])) : 'Tidak ada catatan khusus untuk jadwal ini.'; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0 pt-0 justify-content-end">
                                                <button type="button" class="btn btn-secondary px-4 py-2 rounded-3 fw-semibold" data-bs-dismiss="modal">Tutup</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- End Modal -->

                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fas fa-calendar-times fa-2x mb-3 d-block opacity-25"></i>
                                        Belum ada jadwal keberangkatan yang tersedia.
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