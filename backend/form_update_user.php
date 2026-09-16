<?php
require_once "connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);
// Izinkan admin & petugas
$auth->checkRole(['admin', 'petugas']);

$currentUserRole = $_SESSION['role'] ?? '';

// Ambil ID dari URL
$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: tabel_user.php");
    exit;
}

// Query ambil data user
$stmt = $db->prepare("SELECT * FROM user WHERE id = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: tabel_user.php");
    exit;
}

// Keamanan Tambahan: Jika Petugas mencoba mengedit user Admin via URL langsung, tolak
if ($currentUserRole === 'petugas' && $user['role'] === 'admin') {
    echo "<script>alert('Anda tidak memiliki akses untuk mengedit akun Admin!'); window.location.href='tabel_user.php';</script>";
    exit;
}

include "components/header.php";
include "components/sidebar.php";
?>

<div class="main-wrapper">
    <?php include "components/topbar.php"; ?>

    <div class="content-body p-4">
        <!-- Header Judul -->
        <div class="d-flex align-items-center mb-4">
            <div class="p-3 rounded-3 d-flex align-items-center justify-content-center me-3" style="background-color: #d4a359; color: #ffffff; width: 48px; height: 48px;">
                <i class="fas fa-key fa-lg"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0" style="color: #2c2825;">
                    <?= $currentUserRole === 'petugas' ? 'Ubah Password Petugas' : 'Edit Data User'; ?>
                </h3>
                <p class="mb-0 small" style="color: #78716c;">
                    <?= $currentUserRole === 'petugas' ? 'Silakan masukkan password baru untuk akun ini.' : 'Perbarui informasi dan hak akses pengguna sistem.'; ?>
                </p>
            </div>
        </div>

        <!-- Form Card -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center fw-bold mb-3" style="color: #1a4d36;">
                    <i class="fas fa-user me-2"></i> Informasi User
                </div>
                <hr class="mt-0 mb-4" style="border-color: #e7e5e4;">

                <form action="proses_update_user.php" method="POST">
                    <input type="hidden" name="id" value="<?= $user['id']; ?>">

                    <!-- Username: Jika Petugas, set Readonly/Disabled -->
                    <div class="mb-3">
                        <label for="username" class="form-label small fw-semibold" style="color: #44403c;">Username</label>
                        <input type="text" class="form-control form-control-lg fs-6" id="username" name="username" value="<?= htmlspecialchars($user['username'] ?? ''); ?>" <?= $currentUserRole === 'petugas' ? 'readonly' : 'required'; ?>>
                        <?php if ($currentUserRole === 'petugas'): ?>
                            <small class="text-muted">Petugas tidak dapat mengubah username.</small>
                        <?php endif; ?>
                    </div>

                    <!-- Password Baru -->
                    <div class="mb-3">
                        <label for="password" class="form-label small fw-semibold" style="color: #44403c;">Password Baru <span class="text-muted fw-normal">(Kosongkan jika tidak ingin diubah)</span></label>
                        <input type="password" class="form-control form-control-lg fs-6" id="password" name="password" placeholder="Masukkan password baru...">
                    </div>

                    <!-- Role / Hak Akses: Jika Petugas, dikunci tetap 'petugas' -->
                    <div class="mb-4">
                        <label for="role" class="form-label small fw-semibold" style="color: #44403c;">Role / Hak Akses</label>
                        <?php if ($currentUserRole === 'petugas'): ?>
                            <input type="text" class="form-control form-control-lg fs-6" value="Petugas" readonly>
                            <input type="hidden" name="role" value="petugas">
                        <?php else: ?>
                            <?php $selectedRole = $user['role'] ?? ''; ?>
                            <select class="form-select form-select-lg fs-6" id="role" name="role" style="color: #44403c;" required>
                                <option value="admin" <?= $selectedRole == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                <option value="petugas" <?= $selectedRole == 'petugas' ? 'selected' : ''; ?>>Petugas</option>
                            </select>
                        <?php endif; ?>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="d-flex justify-content-end gap-2 pt-2">
                        <a href="tabel_user.php" class="btn border btn-md px-4 fw-medium" style="background-color: #f5f5f4; color: #57534e;">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-md px-4 fw-bold shadow-sm" style="background-color: #d4a359; color: #ffffff; border: none;">
                            <i class="fas fa-save me-1"></i> Simpan Perubahan
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