<?php
require_once "connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);
$auth->checkRole(['admin', 'petugas']);

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: tabel_pendaftaran.php");
    exit();
}

$stmtPendaftaran = $db->prepare("SELECT * FROM pendaftaran WHERE id = :id");
$stmtPendaftaran->execute([':id' => $id]);
$data = $stmtPendaftaran->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    header("Location: tabel_pendaftaran.php");
    exit();
}

$jamaahList = $db->query("SELECT id, nik, nama_lengkap FROM jamaah ORDER BY nama_lengkap ASC")->fetchAll(PDO::FETCH_ASSOC);
$paketList = $db->query("SELECT id, nama_paket, jenis, harga FROM paket ORDER BY nama_paket ASC")->fetchAll(PDO::FETCH_ASSOC);
$keberangkatanList = $db->query("SELECT k.id, k.tanggal_berangkat, p.nama_paket 
                                 FROM keberangkatan k 
                                 JOIN paket p ON k.paket_id = p.id 
                                 ORDER BY k.tanggal_berangkat ASC")->fetchAll(PDO::FETCH_ASSOC);

// Tanggal minimal untuk date picker (hari ini) supaya tanggal yang sudah lewat tidak bisa dipilih
$todayDate = date('Y-m-d');

include "components/header.php";
include "components/sidebar.php";
?>

<div class="main-wrapper">
    <?php include "components/topbar.php"; ?>

    <div class="content-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <div class="p-3 rounded-4 d-flex align-items-center justify-content-center me-3 shadow-sm" style="background-color: #1a4d36; color: #ffffff; width: 50px; height: 50px;">
                    <i class="fas fa-edit fa-lg"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0" style="color: #2c2825;">Edit Pendaftaran</h3>
                    <p class="mb-0 small text-muted">Ubah data transaksi pendaftaran porsi</p>
                </div>
            </div>
            <a href="tabel_pendaftaran.php" class="btn btn-outline-secondary px-4 fw-semibold" style="border-radius: 10px;">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <!-- Full Width Card Container -->
        <div class="card border-0 shadow-sm rounded-4 bg-white w-100">
            <div class="card-body p-4 p-md-5">
                <form action="proses_update_pendaftaran.php" method="POST">
                    <input type="hidden" name="id" value="<?= $data['id']; ?>">

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-bold text-dark small">PILIH JAMAAH <span class="text-danger"></span></label>
                            <select name="jamaah_id" class="form-select rounded-3 py-2" disabled>
                                <?php foreach ($jamaahList as $j): ?>
                                    <option value="<?= $j['id']; ?>" <?= $data['jamaah_id'] == $j['id'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($j['nama_lengkap']); ?> (NIK: <?= htmlspecialchars($j['nik']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <!-- Field dikunci: value asli tetap dikirim lewat hidden input -->
                            <input type="hidden" name="jamaah_id" value="<?= $data['jamaah_id']; ?>">
                        </div>

                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-bold text-dark small">PILIH PAKET <span class="text-danger"></span></label>
                            <select name="paket_id" class="form-select rounded-3 py-2" disabled>
                                <?php foreach ($paketList as $p): ?>
                                    <option value="<?= $p['id']; ?>" <?= $data['paket_id'] == $p['id'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($p['nama_paket']); ?> - [<?= $p['jenis']; ?>] (Rp <?= number_format($p['harga'], 0, ',', '.'); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" name="paket_id" value="<?= $data['paket_id']; ?>">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark small">JADWAL KEBERANGKATAN</label>
                        <select name="keberangkatan_id" class="form-select rounded-3 py-2" disabled>
                            <option value="">-- Belum Dijadwalkan --</option>
                            <?php foreach ($keberangkatanList as $kb): ?>
                                <option value="<?= $kb['id']; ?>" <?= $data['keberangkatan_id'] == $kb['id'] ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($kb['nama_paket']); ?> - <?= date('d M Y', strtotime($kb['tanggal_berangkat'])); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="keberangkatan_id" value="<?= $data['keberangkatan_id']; ?>">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-bold text-dark small">TANGGAL DAFTAR <span class="text-danger"></span></label>
                            <input type="date" class="form-control rounded-3 py-2" value="<?= $data['tgl_daftar']; ?>" min="<?= $todayDate; ?>" disabled>
                            <!-- Field dikunci: value asli tetap dikirim lewat hidden input -->
                            <input type="hidden" name="tgl_daftar" value="<?= $data['tgl_daftar']; ?>">
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-bold text-dark small">STATUS PENDAFTARAN <span class="text-danger"></span></label>
                            <select name="status" class="form-select rounded-3 py-2" required>
                                <?php 
                                    // Daftar status disesuaikan dengan alur aplikasi
                                    $statuses = ['Menunggu', 'Berangkat', 'Proses', 'Pulang', 'Selesai'];
                                    foreach ($statuses as $st):
                                ?>
                                    <option value="<?= $st; ?>" <?= strcasecmp($data['status'], $st) === 0 ? 'selected' : ''; ?>><?= $st; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4" style="border-color: #f0eae1;">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="tabel_pendaftaran.php" class="btn btn-light px-4 py-2 rounded-3">Batal</a>
                        <button type="submit" class="btn px-4 py-2 fw-bold text-white shadow-sm rounded-3" style="background-color: #d4a359;">
                            <i class="fas fa-save me-1"></i> Perbarui Pendaftaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php 
include "components/footer.php";
include "components/bottom.php"; 
?>