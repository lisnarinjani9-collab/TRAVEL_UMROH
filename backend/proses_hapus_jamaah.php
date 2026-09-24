
<?php
require_once "database/connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);
$auth->checkRole(['admin', 'petugas']);

$id = $_GET['id'] ?? null;
// Parameter untuk mengecek apakah user sudah menekan tombol "Yakin"
$confirm = $_GET['confirm'] ?? null; 

if ($id) {
    try {
        // 1. Ambil user_id dari tabel jamaah
        $stmtGet = $db->prepare("SELECT user_id FROM jamaah WHERE id = :id");
        $stmtGet->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtGet->execute();
        $dataJamaah = $stmtGet->fetch(PDO::FETCH_ASSOC);

        if (!$dataJamaah) {
            header("Location: tabel_jamaah.php");
            exit();
        }

        $user_id = $dataJamaah['user_id'];

        // 2. Cek apakah jamaah ini memiliki transaksi (misalnya di tabel pendaftaran)
        $stmtCheck = $db->prepare("SELECT COUNT(*) FROM pendaftaran WHERE jamaah_id = :id");
        $stmtCheck->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtCheck->execute();
        $jumlahTransaksi = $stmtCheck->fetchColumn();

        // 3. Jika ada transaksi dan user belum konfirmasi, tampilkan UI peringatan
        if ($jumlahTransaksi > 0 && $confirm !== 'yes') {
            echo "<!DOCTYPE html>
            <html lang='id'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Konfirmasi Hapus Transaksi</title>
                <style>
                    body { font-family: Arial, sans-serif; background-color: #f3f4f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
                    .card { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); text-align: center; max-width: 450px; }
                    h3 { color: #dc2626; margin-top: 0; font-size: 24px; }
                    p { color: #4b5563; margin-bottom: 25px; line-height: 1.6; font-size: 16px; }
                    .btn-group { display: flex; justify-content: center; gap: 15px; }
                    .btn { padding: 10px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; color: #fff; transition: background-color 0.2s; }
                    .btn-cancel { background-color: #6b7280; }
                    .btn-cancel:hover { background-color: #4b5563; }
                    .btn-confirm { background-color: #ef4444; }
                    .btn-confirm:hover { background-color: #dc2626; }
                </style>
            </head>
            <body>
                <div class='card'>
                    <h3>Peringatan!</h3>
                    <p>Jamaah ini memiliki riwayat transaksi pendaftaran/pembayaran. Apakah Anda yakin ingin menghapus data jamaah beserta <b>seluruh data transaksinya</b> secara permanen?</p>
                    <div class='btn-group'>
                        <a href='tabel_jamaah.php' class='btn btn-cancel'>Batalkan</a>
                        <a href='proses_hapus_jamaah.php?id={$id}&confirm=yes' class='btn btn-confirm'>Yakin</a>
                    </div>
                </div>
            </body>
            </html>";
            // Hentikan proses PHP di sini, tunggu user klik tombol
            exit();
        }

        // 4. Jika tidak ada transaksi ATAU user sudah klik "Yakin", eksekusi penghapusan
        $db->beginTransaction();

        // Hapus data anak (pembayaran)
        $stmtPembayaran = $db->prepare("DELETE FROM pembayaran WHERE pendaftaran_id IN (SELECT id FROM pendaftaran WHERE jamaah_id = :id)");
        $stmtPembayaran->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtPembayaran->execute();

        // Hapus data anak (pendaftaran)
        $stmtPendaftaran = $db->prepare("DELETE FROM pendaftaran WHERE jamaah_id = :id");
        $stmtPendaftaran->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtPendaftaran->execute();

        // Hapus data jamaah
        $stmtJamaah = $db->prepare("DELETE FROM jamaah WHERE id = :id");
        $stmtJamaah->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtJamaah->execute();

        // Hapus data user
        $stmtUser = $db->prepare("DELETE FROM user WHERE id = :user_id");
        $stmtUser->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmtUser->execute();

        $db->commit();

        header("Location: tabel_jamaah.php?status=deleted");
        exit();

    } catch (PDOException $e) {
        $db->rollBack();
        $errorMessage = addslashes($e->getMessage()); 
        echo "<script>alert('Gagal: $errorMessage'); window.location.href='tabel_jamaah.php';</script>";
        exit();
    }
} else {
    header("Location: tabel_jamaah.php");
    exit();
}
?>