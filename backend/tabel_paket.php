
<?php
require_once "database/connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);
$auth->checkRole(['admin', 'petugas']);

$userRole = $_SESSION['role'] ?? '';

$paketList = [];
$errorMessage = "";

try {
    $query = "SELECT * FROM paket ORDER BY id DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $paketList = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = "Terjadi kesalahan saat mengambil data paket: " . $e->getMessage();
}

// Hitung total paket
$totalPaket = count($paketList);
$totalHaji = 0;
$totalUmroh = 0;

foreach ($paketList as $p) {
    $jenis = strtolower($p['jenis'] ?? $p['jenis_paket'] ?? '');
    if (strpos($jenis, 'haji') !== false) {
        $totalHaji++;
    } elseif (strpos($jenis, 'umroh') !== false) {
        $totalUmroh++;
    }
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
    .stat-card-paket {
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card-paket:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.06) !important;
    }

    .stat-card-paket::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
    }

    .stat-card-paket.total::before {
        background: var(--primary-emerald);
    }

    .stat-card-paket.haji::before {
        background: var(--accent-gold);
    }

    .stat-card-paket.umroh::before {
        background: #0284c7;
    }

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

    .action-btn-edit {
        background-color: #e0f2fe;
        color: #0284c7;
    }

    .action-btn-edit:hover {
        background-color: #0284c7;
        color: #ffffff;
    }

    .action-btn-delete {
        background-color: #fee2e2;
        color: #ef4444;
    }

    .action-btn-delete:hover {
        background-color: #ef4444;
        color: #ffffff;
    }
</style>

<div class="main-wrapper">
    <?php include "components/topbar.php"; ?>

    <div class="content-body p-4">
        <!-- Header Halaman Modern -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
            <div class="d-flex align-items-center">
                <div class="icon-box-p text-white me-3 shadow-sm" style="background: linear-gradient(135deg, #064e3b 0%, #047857 100%);">
                    <i class="fas fa-kaaba"></i>
                </div>
                <div>
                    <h3 class="fw-extrabold text-dark mb-0">Paket Travel</h3>
                    <p class="mb-0 text-muted small">Kelola katalog dan harga paket perjalanan Haji & Umroh</p>
                </div>
            </div>

            <!-- PERBAIKAN 1: Izinkan Petugas dan Admin melihat tombol Tambah -->
            <?php if (in_array($userRole, ['admin', 'petugas'])): ?>
                <a href="form_tambah_paket.php" class="btn btn-gold px-4 py-2.5 shadow-sm d-flex align-items-center gap-2">
                    <i class="fas fa-plus"></i> Add Paket Baru
                </a>
            <?php endif; ?>
        </div>

        <!-- Ringkasan Stats Modern -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card stat-card-paket total shadow-sm p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold d-block mb-1">Total Paket Available</span>
                            <h3 class="fw-bold text-dark mb-0"><?= $totalPaket; ?></h3>
                        </div>
                        <div class="icon-box-p text-emerald" style="background-color: rgba(6, 78, 59, 0.1); color: var(--primary-emerald);">
                            <i class="fas fa-box"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card-paket haji shadow-sm p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold d-block mb-1">Paket Haji</span>
                            <h3 class="fw-bold text-dark mb-0"><?= $totalHaji; ?></h3>
                        </div>
                        <div class="icon-box-p bg-warning bg-opacity-10 text-warning">
                            <i class="fas fa-kaaba"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card-paket umroh shadow-sm p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold d-block mb-1">Paket Umroh</span>
                            <h3 class="fw-bold text-dark mb-0"><?= $totalUmroh; ?></h3>
                        </div>
                        <div class="icon-box-p bg-info bg-opacity-10 text-info">
                            <i class="fas fa-plane-departure"></i>
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
                        <h5 class="fw-bold text-dark mb-1">Daftar Paket Travel</h5>
                        <p class="mb-0 small text-muted">Katalog paket aktif yang siap didaftarkan oleh jamaah</p>
                    </div>
                    <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-semibold">
                        <?= count($paketList); ?> Total Paket
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-modern align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3" style="width: 60px;">NO</th>
                                <th>INFORMASI PAKET</th>
                                <th>JENIS</th>
                                <th>HARGA / PAX</th>
                                <th>DURASI</th>
                                <th>KUOTA</th>
                                <th class="text-center" style="width: 140px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($paketList) > 0): ?>
                                <?php $no = 1;
                                foreach ($paketList as $row): ?>
                                    <?php
                                    $jenisText = $row['jenis'] ?? $row['jenis_paket'] ?? 'Umroh';
                                    $isHaji = (strpos(strtolower($jenisText), 'haji') !== false);

                                    $durasiVal = !empty($row['durasi']) ? $row['durasi'] : '-';
                                    if (is_numeric($durasiVal)) {
                                        $durasiVal .= ' Hari';
                                    }
                                    ?>
                                    <tr class="border-bottom">
                                        <td class="ps-3">
                                            <span class="fw-bold text-secondary small">
                                                <?= sprintf("%02d", $no++); ?>
                                            </span>
                                        </td>

                                        <!-- Nama & Deskripsi Paket -->
                                        <td class="py-3">
                                            <div class="fw-bold text-dark fs-6 mb-1">
                                                <?= htmlspecialchars($row['nama_paket']); ?></div>
                                            <div class="text-muted small text-truncate" style="max-width: 380px;">
                                                <i class="fas fa-align-left me-1 opacity-50"></i><?= htmlspecialchars($row['deskripsi'] ?? $row['fasilitas'] ?? '-'); ?>
                                            </div>
                                        </td>

                                        <!-- Jenis -->
                                        <td>
                                            <?php if ($isHaji): ?>
                                                <span class="badge rounded-pill px-3 py-2" style="background-color: #fef3c7; color: #d97706; font-size: 11px; font-weight: 600;">
                                                    <i class="fas fa-kaaba me-1"></i> Haji
                                                </span>
                                            <?php else: ?>
                                                <span class="badge rounded-pill px-3 py-2" style="background-color: #e0f2fe; color: #0284c7; font-size: 11px; font-weight: 600;">
                                                    <i class="fas fa-plane-departure me-1"></i> Umroh
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Harga -->
                                        <td>
                                            <span class="fw-bold" style="color: var(--accent-gold) !important; font-size: 15px;">
                                                Rp <?= number_format($row['harga'] ?? 0, 0, ',', '.'); ?>
                                            </span>
                                        </td>

                                        <!-- Durasi Paket -->
                                        <td>
                                            <div class="fw-semibold text-secondary small">
                                                <i class="far fa-clock me-1 text-muted"></i>
                                                <?= htmlspecialchars($durasiVal); ?>
                                            </div>
                                        </td>

                                        <!-- Kuota -->
                                        <td>
                                            <span class="badge bg-light text-dark border px-2.5 py-1.5 rounded-2 fw-medium">
                                                <i class="fas fa-users me-1 text-muted"></i><?= htmlspecialchars($row['kuota'] ?? '0'); ?> Jamaah
                                            </span>
                                        </td>

                                        <!-- Action -->
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <!-- Tombol Mata (Detail Modal) -->
                                                <button type="button" class="action-btn action-btn-view" data-bs-toggle="modal" data-bs-target="#modalDetail<?= $row['id']; ?>" title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </button>

                                                <!-- Tombol Edit -->
                                                <a href="form_update_paket.php?id=<?= $row['id']; ?>" class="action-btn action-btn-edit" title="Edit Paket">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                <!-- PERBAIKAN 2: Izinkan Petugas dan Admin melihat tombol Hapus -->
                                                <?php if (in_array($userRole, ['admin', 'petugas'])): ?>
                                                    <a href="delete_paket.php?id=<?= $row['id']; ?>" class="action-btn action-btn-delete" onclick="return confirm('Yakin ingin menghapus paket ini?');" title="Hapus Paket">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Modal Detail Paket -->
                                    <div class="modal fade" id="modalDetail<?= $row['id']; ?>" tabindex="-1" aria-labelledby="modalDetailLabel<?= $row['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content border-0 shadow-lg rounded-4">
                                                <div class="modal-header border-bottom-0 pb-0">
                                                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="modalDetailLabel<?= $row['id']; ?>">
                                                        <i class="fas fa-info-circle text-warning"></i> Detail Paket Travel
                                                    </h5>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="text-muted small fw-semibold d-block mb-1">Nama Paket</label>
                                                            <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($row['nama_paket']); ?></div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="text-muted small fw-semibold d-block mb-1">Jenis Paket</label>
                                                            <div>
                                                                <?php if ($isHaji): ?>
                                                                    <span class="badge rounded-pill px-3 py-2" style="background-color: #fef3c7; color: #d97706; font-size: 11px; font-weight: 600;">
                                                                        <i class="fas fa-kaaba me-1"></i> Haji
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span class="badge rounded-pill px-3 py-2" style="background-color: #e0f2fe; color: #0284c7; font-size: 11px; font-weight: 600;">
                                                                        <i class="fas fa-plane-departure me-1"></i> Umroh
                                                                    </span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="text-muted small fw-semibold d-block mb-1">Harga / Pax</label>
                                                            <div class="fw-bold text-success fs-5">Rp <?= number_format($row['harga'] ?? 0, 0, ',', '.'); ?></div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="text-muted small fw-semibold d-block mb-1">Durasi</label>
                                                            <div class="fw-bold text-dark"><i class="far fa-clock me-1 text-muted"></i><?= htmlspecialchars($durasiVal); ?></div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="text-muted small fw-semibold d-block mb-1">Kuota Tersedia</label>
                                                            <div class="fw-bold text-dark"><i class="fas fa-users me-1 text-muted"></i><?= htmlspecialchars($row['kuota'] ?? '0'); ?> Jamaah</div>
                                                        </div>
                                                        <div class="col-12">
                                                            <hr class="my-2">
                                                            <label class="text-muted small fw-semibold d-block mb-1">Deskripsi & Fasilitas Paket</label>
                                                            <div class="bg-light p-3 rounded-3 text-dark small" style="white-space: pre-line; line-height: 1.6;">
                                                                <?= htmlspecialchars($row['deskripsi'] ?? $row['fasilitas'] ?? 'Tidak ada deskripsi detail.'); ?>
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
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fas fa-box-open fa-3x mb-3 text-light-gray d-block"></i>
                                        Belum ada data paket travel yang ditambahkan.
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