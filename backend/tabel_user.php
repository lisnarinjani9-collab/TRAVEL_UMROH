<?php
require_once "connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);

// Izinkan admin dan petugas masuk
$auth->checkRole(['admin', 'petugas']);

$userRole = $_SESSION['role'] ?? '';

// Jika yang login adalah Petugas, arahkan ke halaman profil
if ($userRole === 'petugas') {
    header("Location: profile_petugas.php");
    exit();
}

// Ambil seluruh data user yang ber-role 'petugas'
$stmt = $db->prepare("SELECT * FROM user WHERE role = 'petugas' ORDER BY id DESC");
$stmt->execute();
$petugasList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung total petugas
$totalPetugas = count($petugasList);

include "components/header.php";
include "components/sidebar.php";
?>

<div class="main-wrapper">
    <?php include "components/topbar.php"; ?>

    <div class="content-body p-4">
        
        <!-- Header Judul & Tombol Tambah -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="d-flex align-items-center">
                <div class="p-3 rounded-3 d-flex align-items-center justify-content-center me-3" style="background-color: #1b4d3e; color: #ffffff; width: 48px; height: 48px;">
                    <i class="fas fa-user-shield fa-lg"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0" style="color: #2c2825;">Kelola Petugas</h3>
                    <p class="mb-0 small" style="color: #78716c;">Daftar seluruh akun petugas yang terdaftar dalam sistem.</p>
                </div>
            </div>

            <!-- Tombol Tambah Petugas Aksen Oranye -->
            <a href="form_tambah_user.php" class="btn btn-md px-4 py-2 fw-bold shadow-sm" style="background-color: #d97706; color: #ffffff; border: none; border-radius: 8px;">
                <i class="fas fa-plus me-1"></i> Add Petugas Baru
            </a>
        </div>



        <!-- Card Tabel Data Petugas -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                
                <!-- Judul Tabel & Badge Total -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold mb-1" style="color: #2c2825;">Daftar Petugas</h5>
                        <p class="mb-0 small text-muted">Akun petugas aktif yang memiliki hak akses sistem.</p>
                    </div>
                    <span class="badge px-3 py-2 fw-normal" style="background-color: #f3f4f6; color: #4b5563; font-size: 12px; border-radius: 6px;">
                        <?= $totalPetugas; ?> Total Petugas
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background-color: #f8fafc; color: #64748b; font-size: 11px; letter-spacing: 0.5px; text-transform: uppercase; border-bottom: 2px solid #e2e8f0;">
                            <tr>
                                <th class="py-3 px-3" width="8%">NO</th>
                                <th class="py-3 px-3">INFORMASI PETUGAS</th>
                                <th class="py-3 px-3" width="20%">ROLE</th>
                                <th class="py-3 px-3 text-center" width="12%">AKSI</th>
                            </tr>
                        </thead>
                        <tbody style="border-top: none;">
                            <?php if (!empty($petugasList)): ?>
                                <?php $no = 1; foreach ($petugasList as $petugas): ?>
                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                        <td class="px-3 fw-medium" style="color: #64748b; font-size: 13px;">
                                            <?= sprintf("%02d", $no++); ?>
                                        </td>
                                        <td class="px-3">
                                            <div class="fw-bold" style="color: #1e293b; font-size: 14px;">
                                                <?= htmlspecialchars($petugas['username']); ?>
                                            </div>
                                        </td>
                                        <td class="px-3">
                                            <span class="badge px-3 py-1 fw-semibold" style="background-color: #e6f4ea; color: #166534; font-size: 12px; border-radius: 12px;">
                                                Petugas
                                            </span>
                                        </td>
                                        <td class="px-3 text-center">
                                            <!-- Action Button Hapus bergaya Icon Box -->
                                            <a href="process/proses_hapus_user.php?id=<?= $petugas['id']; ?>" 
                                            class="btn btn-sm p-2 d-inline-flex align-items-center justify-content-center" 
                                            style="background-color: #fef2f2; color: #dc2626; border-radius: 8px; width: 34px; height: 34px; border: none;"
                                            title="Hapus Petugas"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus petugas ini?');">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted" style="font-size: 13px;">
                                        Belum ada data petugas yang terdaftar.
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