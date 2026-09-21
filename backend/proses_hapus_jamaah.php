<?php
require_once "connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);
$auth->checkRole(['admin', 'petugas']);

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        // Mulai transaksi database
        $db->beginTransaction();

        // 1. Hapus akun pengguna di tabel 'user' yang terhubung dengan jamaah_id
        $stmtUser = $db->prepare("DELETE FROM user WHERE jamaah_id = :jamaah_id");
        $stmtUser->bindParam(':jamaah_id', $id, PDO::PARAM_INT);
        $stmtUser->execute();

        // 2. Hapus data jamaah di tabel 'jamaah'
        $stmtJamaah = $db->prepare("DELETE FROM jamaah WHERE id = :id");
        $stmtJamaah->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtJamaah->execute();

        // Eksekusi perubahan ke database
        $db->commit();

        header("Location: tabel_jamaah.php?status=deleted");
        exit();

    } catch (PDOException $e) {
        // Batalkan seluruh proses jika terjadi kesalahan
        $db->rollBack();
        echo "<script>alert('Gagal menghapus data jamaah dan akun!'); window.location.href='tabel_jamaah.php';</script>";
        exit();
    }
} else {
    header("Location: tabel_jamaah.php");
    exit();
}
?>