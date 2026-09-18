<?php
require_once "connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);

// Hanya Admin dan Petugas
$auth->checkRole(['admin', 'petugas']);

$listPembayaran = [];
$errorMessage = "";

try {
    // Query yang disesuaikan persis dengan struktur database kamu
    $query = "SELECT 
                pd.id AS pendaftaran_id,
                pd.tgl_daftar,
                p.id AS pembayaran_id,
                p.tanggal_bayar,
                COALESCE(p.nominal, pk.harga, 0) AS nominal,
                COALESCE(p.status, pd.status, 'Pending') AS status_bayar,
                p.bukti_transfer,
                j.nama_lengkap AS nama_jamaah,
                pk.nama_paket
              FROM pendaftaran pd
              LEFT JOIN pembayaran p ON p.jamaah_id = pd.jamaah_id
              LEFT JOIN jamaah j ON pd.jamaah_id = j.id
              LEFT JOIN paket pk ON pd.paket_id = pk.id
              ORDER BY pd.id DESC";

    $stmt = $db->query($query);
    $listPembayaran = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $errorMessage = "Gagal mengambil data pembayaran: " . $e->getMessage();
}

include "components/header.php";
include "components/sidebar.php";
?>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --primary-emerald: #064e3b;
        --secondary-emerald: #047857;
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
    }
    .btn-gold {
        background: linear-gradient(135deg, #d4af37 0%, #b8860b 100%);
        color: #ffffff;
        font-weight: 700;
        border: none;
    }
</style>

<div class="main-wrapper">
    <?php include "components/topbar.php"; ?>

    <div class="content-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div class="d-flex align-items-center">
                <div class="icon-header-box text-white me-3 shadow-sm">
                    <i class="fas fa-wallet"></i>
                </div>
                <div>
                    <h3 class="fw-extrabold text-dark mb-0">Data Pembayaran</h3>
                    <p class="mb-0 text-muted small">Kelola riwayat transaksi dan pembayaran tagihan jamaah</p>
                </div>
            </div>

            <a href="tambah_pembayaran.php" class="btn btn-gold px-4 py-2.5 rounded-3 shadow-sm d-inline-flex align-items-center gap-2">
                <i class="fas fa-plus-circle"></i>
                <span>Catat Pembayaran</span>
            </a>
        </div>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4 alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($errorMessage); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card table-card border-0 shadow-sm bg-white">
            <div class="card-body p-4 p-md-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">Daftar Transaksi Pembayaran</h5>
                        <p class="text-muted small mb-0">Riwayat angsuran dan pelunasan porsi Haji & Umroh</p>
                    </div>
                    <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-semibold">
                        <?= count($listPembayaran); ?> Transaksi
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-borderless align-middle mb-0">
                        <thead>
                            <tr class="border-bottom">
                                <th class="pb-3 ps-2" style="width: 50px;">NO</th>
                                <th class="pb-3">JAMAAH & PAKET</th>
                                <th class="pb-3">TGL BAYAR / DAFTAR</th>
                                <th class="pb-3 text-end">NOMINAL</th>
                                <th class="pb-3 text-center">BUKTI</th>
                                <th class="pb-3 text-center">STATUS</th>
                                <th class="pb-3 text-center" style="width: 100px;">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($listPembayaran) > 0): ?>
                                <?php $no = 1; foreach ($listPembayaran as $row): ?>
                                <?php 
                                    $st = strtolower($row['status_bayar']);
                                    if ($st === 'lunas' || $st === 'valid' || $st === 'disetujui') {
                                        $badge = '<span class="badge rounded-pill px-3 py-2" style="background-color: #d1fae5; color: #047857;"><i class="fas fa-check-circle me-1"></i> Valid / Lunas</span>';
                                    } elseif ($st === 'ditolak') {
                                        $badge = '<span class="badge rounded-pill px-3 py-2" style="background-color: #fee2e2; color: #dc2626;"><i class="fas fa-times-circle me-1"></i> Ditolak</span>';
                                    } else {
                                        $badge = '<span class="badge rounded-pill px-3 py-2" style="background-color: #e0f2fe; color: #0284c7;"><i class="fas fa-clock me-1"></i> Pending</span>';
                                    }

                                    $tglTampil = !empty($row['tanggal_bayar']) ? $row['tanggal_bayar'] : $row['tgl_daftar'];
                                    $targetId = !empty($row['pembayaran_id']) ? $row['pembayaran_id'] : $row['pendaftaran_id'];
                                ?>
                                <tr class="border-bottom">
                                    <td class="ps-2 fw-bold text-muted"><?= $no++; ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama_jamaah'] ?? 'Jamaah'); ?></div>
                                        <div class="small text-muted"><?= htmlspecialchars($row['nama_paket'] ?? '-'); ?></div>
                                    </td>
                                    <td class="small text-secondary">
                                        <?= !empty($tglTampil) ? date('d M Y', strtotime($tglTampil)) : '-'; ?>
                                    </td>
                                    <td class="text-end fw-bold" style="color: var(--secondary-emerald);">
                                        Rp <?= number_format($row['nominal'], 0, ',', '.'); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($row['bukti_transfer'])): ?>
                                            <a href="uploads/<?= htmlspecialchars($row['bukti_transfer']); ?>" target="_blank" class="btn btn-sm btn-outline-info rounded-3">Lihat</a>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center"><?= $badge; ?></td>
                                    <td class="text-center">
                                        <a href="detail_pembayaran.php?id=<?= $targetId; ?>" class="btn btn-sm btn-light border rounded-3" title="Kelola">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fas fa-folder-open fa-2x mb-3 d-block opacity-25"></i>
                                        Belum ada data pendaftaran/pembayaran.
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