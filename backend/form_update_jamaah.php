<?php
require_once "connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);
$auth->checkRole(['admin', 'petugas']);

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: tabel_jamaah.php");
    exit();
}

$stmt = $db->prepare("SELECT * FROM jamaah WHERE id = :id LIMIT 1");
$stmt->bindParam(':id', $id);
$stmt->execute();
$jamaah = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$jamaah) {
    echo "<script>alert('Data jamaah tidak ditemukan!'); window.location.href='tabel_jamaah.php';</script>";
    exit();
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

    .form-card {
        border-radius: 20px;
        border: 1px solid #e2e8f0;
    }

    .form-control, .form-select {
        border-radius: 12px;
        border: 1px solid #cbd5e1;
        padding: 0.65rem 0.9rem;
        font-size: 0.925rem;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--secondary-emerald);
        box-shadow: 0 0 0 4px rgba(4, 120, 87, 0.1);
    }

    .btn-gold {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        color: #ffffff !important;
        border: none;
        border-radius: 12px;
        font-weight: 600;
    }

    .btn-cancel {
        background-color: #f1f5f9;
        color: #475569;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        font-weight: 600;
    }
</style>

<div class="main-wrapper">
    <?php include "components/topbar.php"; ?>

    <div class="content-body p-4">
        <div class="d-flex align-items-center mb-4">
            <div class="icon-header-box text-white me-3 shadow-sm">
                <i class="fas fa-user-edit"></i>
            </div>
            <div>
                <h3 class="fw-extrabold text-dark mb-0">Edit Data Jamaah</h3>
                <p class="mb-0 text-muted small">Perbarui data identitas jamaah Haji dan Umroh</p>
            </div>
        </div>

        <div class="card form-card border-0 shadow-sm bg-white">
            <div class="card-body p-4">
                <form action="proses_update_jamaah.php" method="POST">
                    <input type="hidden" name="id" value="<?= $jamaah['id']; ?>">

                    <div class="row g-3">
                        <div class="col-md-6 mb-2">
                            <label for="nik" class="form-label fw-semibold">NIK <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nik" name="nik" maxlength="16"
                                value="<?= htmlspecialchars($jamaah['nik']); ?>" required>
                        </div>

                        <div class="col-md-6 mb-2">
                            <label for="nama_lengkap" class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap"
                                value="<?= htmlspecialchars($jamaah['nama_lengkap']); ?>" required>
                        </div>

                        <div class="col-md-6 mb-2">
                            <label for="jenis_kelamin" class="form-label fw-semibold">Jenis Kelamin <span class="text-danger">*</span></label>
                            <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                                <option value="L" <?= ($jamaah['jenis_kelamin'] === 'L') ? 'selected' : ''; ?>>Laki-Laki</option>
                                <option value="P" <?= ($jamaah['jenis_kelamin'] === 'P') ? 'selected' : ''; ?>>Perempuan</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-2">
                            <label for="no_hp" class="form-label fw-semibold">No HP / WhatsApp <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="no_hp" name="no_hp"
                                value="<?= htmlspecialchars($jamaah['no_hp']); ?>" required>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="alamat" class="form-label fw-semibold">Alamat Lengkap</label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="3"><?= htmlspecialchars($jamaah['alamat']); ?></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="tabel_jamaah.php" class="btn btn-cancel px-4 py-2 d-flex align-items-center gap-2">
                            <i class="fas fa-arrow-left"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-gold px-4 py-2 d-flex align-items-center gap-2 shadow-sm">
                            <i class="fas fa-save"></i> Simpan Perubahan
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