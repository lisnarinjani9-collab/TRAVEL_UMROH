
<?php
require_once "database/connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);

$auth->checkRole(['jamaah']);

$listPaket = [];
$errorMessage = "";

try {
    $stmtPaket = $db->query("SELECT * FROM paket ORDER BY id DESC");
    $listPaket = $stmtPaket->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = "Gagal mengambil data layanan: " . $e->getMessage();
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

    .card-layanan {
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }

    .card-layanan:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 25px rgba(0,0,0,0.08) !important;
        border-color: var(--secondary-emerald);
    }

    .btn-gold {
        background: linear-gradient(135deg, #d4af37 0%, #b8860b 100%);
        color: #ffffff;
        font-weight: 700;
        border: none;
        transition: all 0.3s ease;
    }

    .btn-gold:hover {
        background: linear-gradient(135deg, #c29d26 0%, #996515 100%);
        color: #ffffff;
    }
</style>

<div class="main-wrapper">
    <?php include "components/topbar.php"; ?>

    <div class="content-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div class="d-flex align-items-center">
                <div class="icon-header-box text-white me-3 shadow-sm">
                    <i class="fas fa-kaaba"></i>
                </div>
                <div>
                    <h3 class="fw-extrabold text-dark mb-0">Pilihan Layanan Ibadah</h3>
                    <p class="mb-0 text-muted small">Pilih program perjalanan Haji & Umroh yang sesuai dengan kebutuhan Anda</p>
                </div>
            </div>

            <a href="riwayat_pendaftaran.php" class="btn btn-outline-secondary px-4 py-2.5 rounded-3 d-inline-flex align-items-center gap-2">
                <i class="fas fa-history"></i>
                <span>Lihat Riwayat Saya</span>
            </a>
        </div>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4 alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($errorMessage); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <?php if (empty($listPaket)): ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-box-open fa-3x text-muted mb-3 d-block opacity-50"></i>
                    <h5 class="text-muted fw-bold">Belum ada program ibadah yang tersedia saat ini.</h5>
                </div>
            <?php else: ?>
                <?php foreach ($listPaket as $p): ?>
                    <?php 
                        $pId     = $p['id'];
                        $pNama   = $p['nama_paket'] ?? 'Layanan Travel';
                        $pJenis  = $p['jenis'] ?? $p['tipe'] ?? 'Umroh/Haji';
                        $pHarga  = $p['harga'] ?? 0;
                        $pDurasi = $p['durasi'] ?? $p['durasi_hari'] ?? '-';
                        $pDesc   = $p['deskripsi'] ?? $p['fasilitas'] ?? 'Fasilitas akomodasi, konsumsi, dan pembimbing ibadah terjamin.';
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card card-layanan h-100 border-0 shadow-sm bg-white p-4 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <span class="badge px-3 py-2 rounded-pill fw-semibold" style="background-color: #d1fae5; color: #047857; font-size: 12px;">
                                        <i class="fas fa-tag me-1"></i><?= htmlspecialchars($pJenis); ?>
                                    </span>
                                    <span class="small text-muted fw-bold">
                                        <i class="far fa-clock me-1 text-warning"></i><?= htmlspecialchars($pDurasi); ?> Hari
                                    </span>
                                </div>

                                <h5 class="fw-bold text-dark mb-2"><?= htmlspecialchars($pNama); ?></h5>
                                <p class="text-muted small mb-4" style="line-height: 1.6; min-height: 48px;">
                                    <?= htmlspecialchars(substr($pDesc, 0, 100)); ?><?= strlen($pDesc) > 100 ? '...' : ''; ?>
                                </p>
                            </div>

                            <div class="pt-3 border-top">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <span class="text-muted small">Biaya Per Seseorang</span>
                                    <h5 class="fw-extrabold mb-0" style="color: var(--secondary-emerald);">
                                        Rp <?= number_format($pHarga, 0, ',', '.'); ?>
                                    </h5>
                                </div>

                                <button type="button" class="btn btn-gold w-100 py-2.5 rounded-3 d-flex align-items-center justify-content-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalBayar<?= $pId; ?>">
                                    <i class="fas fa-paper-plane"></i>
                                    <span>Daftar Sekarang</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Pemilihan Pembayaran -->
                    <div class="modal fade" id="modalBayar<?= $pId; ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow rounded-4">
                                <div class="modal-header border-bottom-0 pb-0">
                                    <h5 class="modal-title fw-bold text-dark">Konfirmasi Pendaftaran</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="proses_pendaftaran.php" method="POST">
                                    <div class="modal-body py-4">
                                        <input type="hidden" name="pilih_paket_id" value="<?= $pId; ?>">
                                        <input type="hidden" name="metode_pembayaran" value="Bayar Tunai / Cash">
                                        
                                        <div class="p-3 bg-light rounded-3 mb-3">
                                            <div class="small text-muted">Program Pilihan:</div>
                                            <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($pNama); ?></div>
                                            <div class="fw-extrabold mt-1" style="color: var(--secondary-emerald);">
                                                Rp <?= number_format($pHarga, 0, ',', '.'); ?>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold text-secondary small">METODE PEMBAYARAN</label>
                                            <div class="p-2.5 border rounded-3 bg-light d-flex align-items-center gap-2 text-dark font-weight-bold">
                                                <i class="fas fa-money-bill-wave text-success"></i>
                                                <span>Bayar Tunai / Cash (Di Kantor)</span>
                                            </div>
                                        </div>

<div class="mb-2">
    <label class="form-label fw-bold text-secondary small">OPSI PEMBAYARAN</label>
    <select name="opsi_bayar" class="form-select py-2.5 rounded-3" required>
        <option value="" selected disabled hidden>-- Pilih Opsi Pembayaran --</option>
        <option value="Lunas">Pelunasan Langsung (Lunas)</option>
        <option value="DP">Uang Muka / DP</option>
    </select>
</div>
                                    </div>  
                                    <div class="modal-footer border-top-0 pt-0">
                                        <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-gold rounded-3 px-4">Lanjutkan Pendaftaran</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
include "components/footer.php";
include "components/bottom.php"; 

?>