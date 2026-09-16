<?php
require_once "connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);
$auth->checkRole(['admin', 'petugas']);

$userRole = $_SESSION['role'] ?? '';

$pembayaran = [];
$errorMessage = "";

try {
    // Query yang diperbaiki (Menghapus GROUP BY agar aman dari aturan ONLY_FULL_GROUP_BY)
    $query = "SELECT pb.*, 
                     j.nama_lengkap, 
                     j.nik,
                     pk.nama_paket
              FROM pembayaran pb
              LEFT JOIN jamaah j ON pb.jamaah_id = j.id
              LEFT JOIN pendaftaran p ON p.jamaah_id = j.id
              LEFT JOIN paket pk ON p.paket_id = pk.id
              ORDER BY pb.id DESC";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $pembayaran = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = "Terjadi kesalahan saat mengambil data pembayaran: " . $e->getMessage();
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

    .table thead th {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        border-bottom: 2px solid #f1f5f9;
    }

    .badge-status {
        font-size: 0.75rem;
        padding: 0.35em 0.8em;
        border-radius: 20px;
        font-weight: 600;
    }

    .badge-valid { background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .badge-pending { background-color: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
    .badge-ditolak { background-color: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }

    .btn-action-edit {
        background-color: #e0f2fe;
        color: #0284c7;
        border-radius: 8px;
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-action-delete {
        background-color: #fee2e2;
        color: #ef4444;
        border-radius: 8px;
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
</style>

<div class="main-wrapper">
    <?php include "components/topbar.php"; ?>

    <div class="content-body p-4">
        <!-- Header Halaman -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <div class="icon-header-box text-white me-3 shadow-sm">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div>
                    <h3 class="fw-extrabold text-dark mb-0">Data Pembayaran</h3>
                    <p class="mb-0 text-muted small">Kelola riwayat transaksi dan pembayaran tagihan jamaah</p>
                </div>
            </div>
            
            <a href="form_tambah_pembayaran.php" class="btn btn-gold px-4 py-2.5 d-flex align-items-center gap-2 shadow-sm">
                <i class="fas fa-plus"></i> Catat Pembayaran
            </a>
        </div>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4">
                <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>

        <!-- Table Card Container Full Width -->
        <div class="card table-card border-0 shadow-sm bg-white">
            <div class="card-body p-4 p-md-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold text-dark mb-0">Daftar Transaksi Pembayaran</h5>
                        <p class="text-muted small mb-0">Riwayat angsuran dan pelunasan porsi Haji & Umroh</p>
                    </div>
                    <span class="badge bg-light text-secondary rounded-pill px-3 py-2 fw-semibold">
                        <?= count($pembayaran); ?> Transaksi
                    </span>
                </div>

                <div class="table-responsive mt-3">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3" style="width: 50px;">NO</th>
                                <th>JAMAAH & PAKET</th>
                                <th>TGL BAYAR</th>
                                <th>NOMINAL</th>
                                <th>BUKTI</th>
                                <th>STATUS</th>
                                <th class="text-center" style="width: 120px;">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($pembayaran) > 0): ?>
                                <?php $no = 1; foreach ($pembayaran as $row): ?>
                                <tr>
                                    <td class="ps-3 fw-bold text-muted"><?= $no++; ?></td>
                                    <td>
                                        <div class="fw-bold text-dark" style="font-size: 0.925rem;"><?= htmlspecialchars($row['nama_lengkap'] ?? 'Jamaah Tidak Ditemukan'); ?></div>
                                        <div class="text-muted small">NIK: <?= htmlspecialchars($row['nik'] ?? '-'); ?> | <?= htmlspecialchars($row['nama_paket'] ?? 'Paket N/A'); ?></div>
                                    </td>
                                    <td class="text-secondary small fw-medium">
                                        <?= !empty($row['tanggal_bayar']) ? date('d M Y', strtotime($row['tanggal_bayar'])) : '-'; ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold" style="color: var(--primary-emerald); font-size: 0.95rem;">
                                            Rp <?= number_format($row['nominal'] ?? 0, 0, ',', '.'); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (!empty($row['bukti_transfer'])): ?>
                                            <a href="uploads/<?= htmlspecialchars($row['bukti_transfer']); ?>" target="_blank" class="btn btn-sm btn-light border small text-primary">
                                                <i class="fas fa-image me-1"></i> Lihat
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted fst-italic small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                            $status = strtolower($row['status'] ?? 'pending');
                                            $badgeClass = 'badge-pending';
                                            if ($status === 'valid') $badgeClass = 'badge-valid';
                                            elseif ($status === 'ditolak') $badgeClass = 'badge-ditolak';
                                        ?>
                                        <span class="badge badge-status <?= $badgeClass; ?>">
                                            <?= ucfirst($status); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="form_update_pembayaran.php?id=<?= $row['id']; ?>" class="btn-action-edit" title="Edit">
                                                <i class="fas fa-edit" style="font-size: 12px;"></i>
                                            </a>
                                            <?php if ($userRole === 'admin'): ?>
                                            <a href="hapus_pembayaran.php?id=<?= $row['id']; ?>" class="btn-action-delete" onclick="return confirm('Yakin ingin menghapus data pembayaran ini?');" title="Hapus">
                                                <i class="fas fa-trash-alt" style="font-size: 12px;"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fas fa-inbox fa-2x mb-3 d-block opacity-50"></i>
                                        Belum ada data pembayaran.
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