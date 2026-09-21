<?php
require_once "connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);
$auth->checkRole(['admin', 'petugas']);

// Ambil data jamaah
$stmt = $db->query("SELECT * FROM jamaah ORDER BY id DESC");
$jamaahList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung statistik singkat jamaah
$totalJamaah = count($jamaahList);
$totalLaki   = 0;
$totalPerempuan = 0;

foreach ($jamaahList as $j) {
    // Menyesuaikan dengan nama kolom database: 'jenis_kelamin'
    $jk = strtoupper($j['jenis_kelamin'] ?? '');
    if ($jk === 'L' || $jk === 'LAKI-LAKI') {
        $totalLaki++;
    } elseif ($jk === 'P' || $jk === 'PEREMPUAN') {
        $totalPerempuan++;
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

    .stat-card {
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05) !important;
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

    .table-card {
        border-radius: 20px;
        border: 1px solid #e2e8f0;
    }

    .table th {
        color: #475569;
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 0.6px;
        text-transform: uppercase;
        border-bottom: 2px solid #f1f5f9 !important;
        padding-top: 1rem;
        padding-bottom: 1rem;
    }

    .badge-gender-m {
        background-color: #e0f2fe;
        color: #0369a1;
        border: 1px solid #bae6fd;
    }

    .badge-gender-f {
        background-color: #fce7f3;
        color: #be185d;
        border: 1px solid #fbcfe8;
    }

    .action-btn-edit {
        background-color: #f0fdf4;
        color: var(--secondary-emerald);
        border: 1px solid #bbf7d0;
        border-radius: 10px;
        transition: all 0.2s ease;
    }

    .action-btn-edit:hover {
        background-color: var(--secondary-emerald);
        color: #ffffff;
    }

    .action-btn-delete {
        background-color: #fef2f2;
        color: #ef4444;
        border: 1px solid #fecaca;
        border-radius: 10px;
        transition: all 0.2s ease;
    }

    .action-btn-delete:hover {
        background-color: #ef4444;
        color: #ffffff;
    }
</style>

<div class="main-wrapper">
    <?php include "components/topbar.php"; ?>

    <div class="content-body p-4">
        <!-- Header Judul & Tombol Tambah -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div class="d-flex align-items-center">
                <div class="icon-header-box text-white me-3 shadow-sm">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <h3 class="fw-extrabold text-dark mb-0">Data Jamaah</h3>
                    <p class="mb-0 text-muted small">Kelola dan pantau profil jamaah Haji dan Umroh</p>
                </div>
            </div>
    
        </div>

        <!-- Stat Cards Summary -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card stat-card bg-white border-0 shadow-sm p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold">Total Jamaah</span>
                            <h3 class="fw-bold text-dark mb-0 mt-1"><?= $totalJamaah; ?> <span class="fs-6 fw-normal text-muted">Pax</span></h3>
                        </div>
                        <div class="rounded-circle p-3 d-flex align-items-center justify-content-center" style="background-color: #ecfdf5; color: var(--secondary-emerald); width: 48px; height: 48px;">
                            <i class="fas fa-user-friends fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card bg-white border-0 shadow-sm p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold">Jamaah Laki-Laki</span>
                            <h3 class="fw-bold text-dark mb-0 mt-1"><?= $totalLaki; ?> <span class="fs-6 fw-normal text-muted">Pax</span></h3>
                        </div>
                        <div class="rounded-circle p-3 d-flex align-items-center justify-content-center" style="background-color: #e0f2fe; color: #0284c7; width: 48px; height: 48px;">
                            <i class="fas fa-mars fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card bg-white border-0 shadow-sm p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold">Jamaah Perempuan</span>
                            <h3 class="fw-bold text-dark mb-0 mt-1"><?= $totalPerempuan; ?> <span class="fs-6 fw-normal text-muted">Pax</span></h3>
                        </div>
                        <div class="rounded-circle p-3 d-flex align-items-center justify-content-center" style="background-color: #fce7f3; color: #db2777; width: 48px; height: 48px;">
                            <i class="fas fa-venus fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table Card -->
        <div class="card table-card border-0 shadow-sm bg-white">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold text-dark mb-0">Daftar Jamaah Terdaftar</h5>
                        <p class="text-muted small mb-0">Seluruh data identitas jamaah terdaftar dalam sistem</p>
                    </div>
                    <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-semibold"><?= $totalJamaah; ?> Jamaah</span>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle border-0 mb-0">
                        <thead>
                            <tr>
                                <th class="border-0 pb-3" style="width: 50px;">NO</th>
                                <th class="border-0 pb-3">NIK</th>
                                <th class="border-0 pb-3">NAMA LENGKAP</th>
                                <th class="border-0 pb-3">JENIS KELAMIN</th>
                                <th class="border-0 pb-3">NO HP / WHATSAPP</th>
                                <th class="border-0 pb-3">ALAMAT</th>
                                <th class="border-0 pb-3 text-center" style="width: 110px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($jamaahList)): ?>
                                <?php $no = 1; foreach ($jamaahList as $row): ?>
                                    <tr class="border-top">
                                        <td class="py-3 fw-bold text-secondary"><?= $no++; ?></td>
                                        <td class="py-3 font-monospace fw-semibold text-dark"><?= htmlspecialchars($row['nik']); ?></td>
                                        <td class="py-3">
                                            <!-- Disesuaikan ke 'nama_lengkap' -->
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama_lengkap'] ?? '-'); ?></div>
                                        </td>
                                        <td class="py-3">
                                            <?php 
                                                // Disesuaikan ke 'jenis_kelamin'
                                                $jkVal = strtoupper($row['jenis_kelamin'] ?? '');
                                                if ($jkVal === 'L' || $jkVal === 'LAKI-LAKI'): 
                                            ?>
                                                <span class="badge rounded-pill px-3 py-2 badge-gender-m fw-semibold">
                                                    <i class="fas fa-mars me-1"></i> Laki-Laki
                                                </span>
                                            <?php elseif ($jkVal === 'P' || $jkVal === 'PEREMPUAN'): ?>
                                                <span class="badge rounded-pill px-3 py-2 badge-gender-f fw-semibold">
                                                    <i class="fas fa-venus me-1"></i> Perempuan
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-light text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 fw-semibold text-secondary">
                                            <i class="fab fa-whatsapp text-success me-1"></i> <?= htmlspecialchars($row['no_hp'] ?? '-'); ?>
                                        </td>
                                        <td class="py-3 text-muted small" style="max-width: 250px;">
                                            <?= htmlspecialchars($row['alamat'] ?? '-'); ?>
                                        </td>
                                        <td class="py-3 text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="proses_hapus_jamaah.php?id=<?= $row['id']; ?>" class="btn btn-sm action-btn-delete px-2.5 py-1.5" onclick="return confirm('Yakin ingin menghapus data jamaah ini?')" title="Hapus Data">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr class="border-top">
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <div class="my-3">
                                            <i class="fas fa-users-slash fa-3x text-light mb-3" style="color: #cbd5e1 !important;"></i>
                                            <p class="mb-0 fw-semibold text-secondary">Belum ada data jamaah terdaftar.</p>
                                        </div>
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