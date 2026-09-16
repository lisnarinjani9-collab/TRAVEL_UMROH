<?php
require_once "connection.php";
require_once "classes/Auth.php";
require_once "classes/Paket.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);
$auth->checkRole(['admin', 'petugas']);

// Ambil ID dari URL
$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: tabel_paket.php");
    exit;
}

$paketObj = new Paket($db);
$paket = $paketObj->getById($id);

if (!$paket) {
    header("Location: tabel_paket.php");
    exit;
}

include "components/header.php";
include "components/sidebar.php";
?>

<div class="main-wrapper">
    <?php include "components/topbar.php"; ?>

    <div class="content-body">
        <!-- Header Judul -->
        <div class="d-flex align-items-center mb-4">
            <div class="p-3 rounded-3 d-flex align-items-center justify-content-center me-3" style="background-color: #d4a359; color: #ffffff; width: 48px; height: 48px;">
                <i class="fas fa-edit fa-lg"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0" style="color: #2c2825;">Edit Paket Travel</h3>
                <p class="mb-0 small" style="color: #78716c;">Perbarui data paket perjalanan Haji atau Umroh.</p>
            </div>
        </div>

        <!-- Form Card -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center fw-bold mb-3" style="color: #1a4d36;">
                    <i class="fas fa-kaaba me-2"></i> Informasi Paket
                </div>
                <hr class="mt-0 mb-4" style="border-color: #e7e5e4;">

                <form action="proses_update_paket.php" method="POST">
                    <input type="hidden" name="id" value="<?= $paket['id']; ?>">

                    <!-- Nama Paket -->
                    <div class="mb-3">
                        <label for="nama_paket" class="form-label small fw-semibold" style="color: #44403c;">Nama Paket</label>
                        <input type="text" class="form-control form-control-lg fs-6" id="nama_paket" name="nama_paket" value="<?= htmlspecialchars($paket['nama_paket'] ?? $paket['nama'] ?? ''); ?>" required>
                    </div>

                    <!-- Jenis Paket -->
                    <div class="mb-3">
                        <label for="jenis_paket" class="form-label small fw-semibold" style="color: #44403c;">Jenis Paket</label>
                        <?php $selectedJenis = $paket['jenis_paket'] ?? $paket['jenis'] ?? ''; ?>
                        <select class="form-select form-select-lg fs-6" id="jenis_paket" name="jenis_paket" style="color: #44403c;" required>
                            <option value="Haji" <?= $selectedJenis == 'Haji' ? 'selected' : ''; ?>>Haji</option>
                            <option value="Umroh" <?= $selectedJenis == 'Umroh' ? 'selected' : ''; ?>>Umroh</option>
                        </select>
                    </div>

                    <!-- Harga Paket -->
                    <div class="mb-3">
                        <label for="harga_display" class="form-label small fw-semibold" style="color: #44403c;">Harga Paket</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text fw-bold fs-6" style="background-color: #1a4d36; color: #ffffff; border: none;">Rp</span>
                            
                            <input 
                                type="text" 
                                class="form-control fs-6" 
                                id="harga_display" 
                                inputmode="numeric" 
                                placeholder="Contoh: 25.000.000" 
                                value="<?= isset($paket['harga']) ? number_format($paket['harga'], 0, ',', '.') : ''; ?>"
                                required>
                                
                            <input 
                                type="hidden" 
                                id="harga" 
                                name="harga" 
                                value="<?= htmlspecialchars($paket['harga'] ?? ''); ?>">
                        </div>
                    </div>

                    <!-- Durasi Perjalanan (TAMBAHAN BARU) -->
                    <div class="mb-3">
                        <label for="durasi" class="form-label small fw-semibold" style="color: #44403c;">Durasi Perjalanan</label>
                        <div class="input-group input-group-lg">
                            <input 
                                type="number" 
                                class="form-control fs-6" 
                                id="durasi" 
                                name="durasi" 
                                min="1" 
                                placeholder="Contoh: 9" 
                                value="<?= htmlspecialchars($paket['durasi'] ?? ''); ?>"
                                required>
                            <span class="input-group-text bg-light text-muted fw-semibold fs-6">Hari</span>
                        </div>
                    </div>

                    <!-- Kuota Jamaah -->
                    <div class="mb-3">
                        <label for="kuota" class="form-label small fw-semibold" style="color: #44403c;">Kuota Jamaah</label>
                        <input 
                            type="number" 
                            class="form-control form-control-lg fs-6" 
                            id="kuota" 
                            name="kuota" 
                            min="1" 
                            oninput="if(this.value < 1) this.value = 1;" 
                            placeholder="Contoh: 45" 
                            value="<?= htmlspecialchars($paket['kuota'] ?? ''); ?>"
                            required>
                    </div>

                    <!-- Deskripsi Paket -->
                    <div class="mb-4">
                        <label for="deskripsi" class="form-label small fw-semibold" style="color: #44403c;">Deskripsi Paket</label>
                        <textarea class="form-control fs-6" id="deskripsi" name="deskripsi" rows="4"><?= htmlspecialchars($paket['deskripsi'] ?? ''); ?></textarea>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="d-flex justify-content-end gap-2 pt-2">
                        <a href="tabel_paket.php" class="btn border btn-md px-4 fw-medium" style="background-color: #f5f5f4; color: #57534e;">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-md px-4 fw-bold shadow-sm" style="background-color: #d4a359; color: #ffffff; border: none;">
                            <i class="fas fa-save me-1"></i> Update Paket
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const hargaDisplay = document.getElementById('harga_display');
    const hargaReal = document.getElementById('harga');

    if (hargaDisplay && hargaReal) {
        hargaDisplay.addEventListener('input', function (e) {
            let value = this.value.replace(/\D/g, '');
            hargaReal.value = value;
            this.value = value ? new Intl.NumberFormat('id-ID').format(value) : '';
        });
    }
});
</script>

<?php 
include "components/footer.php";
include "components/bottom.php"; 
?>