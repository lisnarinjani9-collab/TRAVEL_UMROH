<?php
require_once "connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);

$auth->checkRole(['jamaah']);

$userId   = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
$jamaahId = $_SESSION['jamaah_id'] ?? null;

$successMessage = "";
$errorMessage = "";

// Mengambil Data Profil Jamaah disesuaikan persis dengan struktur tabel MySQL
$jamaahData = [];
try {
    if (!empty($jamaahId)) {
        $stmt = $db->prepare("SELECT j.*, u.username 
                              FROM jamaah j 
                              LEFT JOIN user u ON (u.jamaah_id = j.id OR j.user_id = u.id) 
                              WHERE j.id = :id");
        $stmt->execute([':id' => $jamaahId]);
        $jamaahData = $stmt->fetch(PDO::FETCH_ASSOC);
    } elseif (!empty($userId)) {
        $stmt = $db->prepare("SELECT j.*, u.username 
                              FROM jamaah j 
                              LEFT JOIN user u ON (u.jamaah_id = j.id OR j.user_id = u.id) 
                              WHERE u.id = :user_id OR j.user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        $jamaahData = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Jika data jamaah belum ada di database, buat template array kosong
    if (!$jamaahData) {
        $jamaahData = [
            'id'            => $jamaahId,
            'nama_lengkap'  => $_SESSION['username'] ?? '',
            'no_hp'         => '',
            'nik'           => '',
            'jenis_kelamin' => 'L',
            'alamat'        => '',
            'username'      => $_SESSION['username'] ?? 'jamaah'
        ];
    }
} catch (PDOException $e) {
    $errorMessage = "Gagal mengambil data profil: " . $e->getMessage();
}

// Fitur Update Profil Jamaah (HANYA NO HP DAN ALAMAT YANG DIPERBARUI)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $noHp   = trim($_POST['no_hp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');

    try {
        if (!empty($jamaahData['id'])) {
            // Update data nomor HP dan alamat saja
            $updateSql = "UPDATE jamaah SET 
                            no_hp = :hp, 
                            alamat = :alamat 
                          WHERE id = :id";
            $stmtUpdate = $db->prepare($updateSql);
            $stmtUpdate->execute([
                ':hp'     => $noHp,
                ':alamat' => $alamat,
                ':id'     => $jamaahData['id']
            ]);
        } else {
            // Insert baru jika record jamaah belum ada
            $insertSql = "INSERT INTO jamaah (user_id, nik, nama_lengkap, jenis_kelamin, alamat, no_hp) 
                          VALUES (:user_id, :nik, :nama, :jk, :alamat, :hp)";
            $stmtInsert = $db->prepare($insertSql);
            $stmtInsert->execute([
                ':user_id' => $userId,
                ':nik'     => $jamaahData['nik'] ?? '',
                ':nama'    => $jamaahData['nama_lengkap'] ?? ($_SESSION['username'] ?? ''),
                ':jk'      => $jamaahData['jenis_kelamin'] ?? 'L',
                ':alamat'  => $alamat,
                ':hp'      => $noHp
            ]);
            $newJamaahId = $db->lastInsertId();
            $jamaahData['id'] = $newJamaahId;
            $_SESSION['jamaah_id'] = $newJamaahId;

            // Sync ID ke tabel user jika ada kolom jamaah_id
            $stmtSync = $db->prepare("UPDATE user SET jamaah_id = :jid WHERE id = :uid");
            $stmtSync->execute([':jid' => $newJamaahId, ':uid' => $userId]);
        }

        $successMessage = "Profil berhasil diperbarui!";
        
        // Refresh variabel lokal
        $jamaahData['no_hp']  = $noHp;
        $jamaahData['alamat'] = $alamat;

    } catch (PDOException $e) {
        $errorMessage = "Gagal memperbarui profil: " . $e->getMessage();
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

    .profile-card {
        border-radius: 20px;
        border: 1px solid #e2e8f0;
    }

    .avatar-box {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        background: linear-gradient(135deg, #064e3b 0%, #047857 100%);
        color: #ffffff;
        font-size: 2.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px auto;
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

    .form-control:focus, .form-select:focus {
        border-color: var(--secondary-emerald);
        box-shadow: 0 0 0 0.25rem rgba(4, 120, 87, 0.15);
    }

    .form-control[readonly], .form-control[disabled], .form-select[disabled] {
        background-color: #f1f5f9 !important;
        cursor: not-allowed;
        color: #64748b;
    }
</style>

<div class="main-wrapper">
    <?php include "components/topbar.php"; ?>

    <div class="content-body p-4">
        <!-- Header Halaman -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div class="d-flex align-items-center">
                <div class="icon-header-box text-white me-3 shadow-sm">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div>
                    <h3 class="fw-extrabold text-dark mb-0">Profil Saya</h3>
                    <p class="mb-0 text-muted small">Kelola informasi data diri dan akun jamaah Anda</p>
                </div>
            </div>
        </div>

        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4 alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($successMessage); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4 alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($errorMessage); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Card Informasi Ringkas Akun -->
            <div class="col-lg-4">
                <div class="card profile-card border-0 shadow-sm bg-white p-4 text-center">
                    <div class="avatar-box shadow-sm">
                        <i class="fas fa-user"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($jamaahData['nama_lengkap'] ?: ($jamaahData['username'] ?? 'Jamaah')); ?></h5>
                    <p class="text-muted small mb-3">@<?= htmlspecialchars($jamaahData['username'] ?? $_SESSION['username'] ?? 'jamaah'); ?></p>
                    
                    <span class="badge rounded-pill px-3 py-2 align-self-center mb-3" style="background-color: #d1fae5; color: #047857; font-size: 12px;">
                        <i class="fas fa-shield-alt me-1"></i> Akun Jamaah Terverifikasi
                    </span>

                    <hr class="my-3">

                    <div class="text-start">
                        <div class="mb-2">
                            <span class="text-muted small d-block">Nomor Telepon / WA:</span>
                            <span class="fw-semibold text-dark"><?= htmlspecialchars($jamaahData['no_hp'] ?: '-'); ?></span>
                        </div>
                        <div class="mb-2">
                            <span class="text-muted small d-block">NIK / No. KTP:</span>
                            <span class="fw-semibold text-dark"><?= htmlspecialchars($jamaahData['nik'] ?: '-'); ?></span>
                        </div>
                        <div class="mb-2">
                            <span class="text-muted small d-block">Jenis Kelamin:</span>
                            <span class="fw-semibold text-dark"><?= ($jamaahData['jenis_kelamin'] ?? 'L') === 'L' ? 'Laki-Laki' : 'Perempuan'; ?></span>
                        </div>
                        <div class="mb-0">
                            <span class="text-muted small d-block">Alamat:</span>
                            <span class="fw-semibold text-dark"><?= htmlspecialchars($jamaahData['alamat'] ?: '-'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Edit Profil -->
            <div class="col-lg-8">
                <div class="card profile-card border-0 shadow-sm bg-white p-4 p-md-5">
                    <h5 class="fw-bold text-dark mb-1">Edit Data Diri</h5>
                    <p class="text-muted small mb-4">Hanya Nomor HP dan Alamat yang dapat Anda ubah secara langsung.</p>

                    <form action="" method="POST">
                        <div class="row g-3">
                            <!-- READONLY: Nama Lengkap -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary small">Nama Lengkap (Sesuai KTP)</label>
                                <input type="text" class="form-control py-2.5 rounded-3 bg-light" value="<?= htmlspecialchars($jamaahData['nama_lengkap'] ?? ''); ?>">
                            </div>

                            <!-- EDITABLE: Nomor HP -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary small">Nomor HP / WhatsApp <span class="text-danger">*</span></label>
                                <input type="text" name="no_hp" class="form-control py-2.5 rounded-3" value="<?= htmlspecialchars($jamaahData['no_hp'] ?? ''); ?>" placeholder="08xxxxxxxxxx" required>
                            </div>

                            <!-- READONLY: NIK -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary small">NIK / Nomor KTP</label>
                                <input type="text" class="form-control py-2.5 rounded-3 bg-light" value="<?= htmlspecialchars($jamaahData['nik'] ?? ''); ?>">
                            </div>

                            <!-- READONLY: Jenis Kelamin -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary small">Jenis Kelamin</label>
                                <select class="form-select py-2.5 rounded-3 bg-light" disabled>
                                    <option value="L" <?= ($jamaahData['jenis_kelamin'] ?? 'L') === 'L' ? 'selected' : ''; ?>>Laki-Laki</option>
                                    <option value="P" <?= ($jamaahData['jenis_kelamin'] ?? '') === 'P' ? 'selected' : ''; ?>>Perempuan</option>
                                </select>
                            </div>

                            <!-- EDITABLE: Alamat -->
                            <div class="col-12">
                                <label class="form-label fw-semibold text-secondary small">Alamat Lengkap <span class="text-danger">*</span></label>
                                <textarea name="alamat" class="form-control rounded-3" rows="3" placeholder="Masukkan alamat domisili..." required><?= htmlspecialchars($jamaahData['alamat'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" name="update_profile" class="btn btn-gold px-4 py-2.5 rounded-3 shadow-sm d-inline-flex align-items-center gap-2">
                                <i class="fas fa-save"></i>
                                <span>Simpan Perubahan</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
include "components/footer.php";
include "components/bottom.php"; 
?>