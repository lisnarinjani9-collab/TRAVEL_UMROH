<?php
require_once "connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);
$auth->checkRole(['admin', 'petugas']);

$id = intval($_GET['id'] ?? 0);

// Ambil data Pendaftaran berdasarkan ID (JOIN Jamaah & Paket untuk tampilan text)
$stmtPendaftaran = $db->prepare("
    SELECT p.*, j.nama_lengkap, j.nik, pk.nama_paket, pk.jenis, pk.harga 
    FROM pendaftaran p
    JOIN jamaah j ON p.jamaah_id = j.id
    JOIN paket pk ON p.paket_id = pk.id
    WHERE p.id = :id
");
$stmtPendaftaran->execute([':id' => $id]);
$pendaftaran = $stmtPendaftaran->fetch(PDO::FETCH_ASSOC);

if (!$pendaftaran) {
    echo "<script>alert('Data pendaftaran tidak ditemukan!'); window.location='tabel_pendaftaran.php';</script>";
    exit();
}

// Ambil list Keberangkatan saja
$keberangkatanList = $db->query("SELECT id, tanggal_berangkat, keterangan FROM keberangkatan ORDER BY tanggal_berangkat ASC")->fetchAll(PDO::FETCH_ASSOC);

include "components/header.php";
include "components/sidebar.php";
?>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<div class="main-wrapper">
    <?php include "components/topbar.php"; ?>

    <div class="content-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <div class="icon-header-box text-white me-3 shadow-sm">
                    <i class="fas fa-edit"></i>
                </div>
                <div>
                    <h3 class="fw-extrabold text-dark mb-0">Edit Pendaftaran</h3>
                    <p class="mb-0 text-muted small">Ubah data transaksi pendaftaran porsi</p>
                </div>
            </div>
            <a href="tabel_pendaftaran.php" class="btn btn-cancel px-4 py-2 d-flex align-items-center gap-2 shadow-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card form-card border-0 shadow-sm bg-white">
                    <div class="card-body p-4 p-md-5">
                        <form action="proses_update_pendaftaran.php" method="POST">
                            <input type="hidden" name="id" value="<?= $pendaftaran['id']; ?>">
                            
                            <!-- Hidden input agar nilai tetap terkirim ke backend -->
                            <input type="hidden" name="jamaah_id" value="<?= $pendaftaran['jamaah_id']; ?>">
                            <input type="hidden" name="paket_id" value="<?= $pendaftaran['paket_id']; ?>">
                            <input type="hidden" name="tgl_daftar" value="<?= $pendaftaran['tgl_daftar']; ?>">

                            <div class="row g-3">
                                <!-- Jamaah (Disabled) -->
                                <div class="col-md-6 mb-2">
                                    <label for="jamaah_id" class="form-label">Jamaah Terdaftar</label>
                                    <select class="form-select bg-light" disabled>
                                        <option selected>
                                            <?= htmlspecialchars($pendaftaran['nama_lengkap']); ?> (NIK: <?= htmlspecialchars($pendaftaran['nik']); ?>)
                                        </option>
                                    </select>
                                </div>

                                <!-- Paket (Disabled) -->
                                <div class="col-md-6 mb-2">
                                    <label for="paket_id" class="form-label">Paket Layanan</label>
                                    <select class="form-select bg-light" disabled>
                                        <option selected>
                                            <?= htmlspecialchars($pendaftaran['nama_paket']); ?> - [<?= htmlspecialchars($pendaftaran['jenis']); ?>] - Rp <?= number_format($pendaftaran['harga'], 0, ',', '.'); ?>
                                        </option>
                                    </select>
                                </div>

                                <!-- Jadwal Keberangkatan (BISA DI-UPDATE) -->
                                <div class="col-md-12 mb-2">
                                    <label for="keberangkatan_id" class="form-label">Jadwal Keberangkatan</label>
                                    <select name="keberangkatan_id" id="keberangkatan_id" class="form-select">
                                        <option value="">-- Belum Dijadwalkan --</option>
                                        <?php foreach ($keberangkatanList as $kb): ?>
                                            <option value="<?= $kb['id']; ?>" <?= ($kb['id'] == $pendaftaran['keberangkatan_id']) ? 'selected' : ''; ?>>
                                                <?= date('d M Y', strtotime($kb['tanggal_berangkat'])); ?> 
                                                <?= !empty($kb['keterangan']) ? '('.htmlspecialchars($kb['keterangan']).')' : ''; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Tanggal Daftar (Disabled) -->
                                <div class="col-md-6 mb-2">
                                    <label for="tgl_daftar" class="form-label">Tanggal Daftar</label>
                                    <input type="date" class="form-control bg-light" value="<?= htmlspecialchars($pendaftaran['tgl_daftar']); ?>" disabled>
                                </div>

                                <!-- Status Pendaftaran (BISA DI-UPDATE) -->
                                <div class="col-md-6 mb-2">
                                    <label for="status" class="form-label">Status Pendaftaran <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-select" required>
                                        <option value="Menunggu" <?= ($pendaftaran['status'] === 'Menunggu') ? 'selected' : ''; ?>>Menunggu</option>
                                        <option value="Proses" <?= ($pendaftaran['status'] === 'Proses') ? 'selected' : ''; ?>>Proses</option>
                                        <option value="Berangkat" <?= ($pendaftaran['status'] === 'Berangkat') ? 'selected' : ''; ?>>Berangkat</option>
                                        <option value="Pulang" <?= ($pendaftaran['status'] === 'Pulang') ? 'selected' : ''; ?>>Pulang</option>
                                        <option value="Selesai" <?= ($pendaftaran['status'] === 'Selesai') ? 'selected' : ''; ?>>Selesai</option>
                                    </select>
                                </div>
                            </div>

                            <hr class="my-4" style="border-color: #f1f5f9;">

                            <div class="d-flex justify-content-end gap-2">
                                <a href="tabel_pendaftaran.php" class="btn btn-light px-4 py-2">Batal</a>
                                <button type="submit" class="btn btn-warning text-white px-4 py-2">
                                    <i class="fas fa-save me-1"></i> Perbarui Pendaftaran
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
include "components/footer.php";
include "components/bottom.php"; 
?>