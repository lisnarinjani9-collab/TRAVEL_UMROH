<?php
require_once "connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);
$auth->checkRole(['admin', 'petugas']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paket_id          = intval($_POST['paket_id'] ?? 0);
    $tanggal_berangkat = trim($_POST['tanggal_berangkat'] ?? '');
    $tgl_kepulangan    = trim($_POST['tgl_kepulangan'] ?? '');
    $maskapai          = trim($_POST['maskapai'] ?? '');
    $embarkasi         = trim($_POST['embarkasi'] ?? '');
    $kuota             = intval($_POST['kuota'] ?? 0);
    $keterangan        = trim($_POST['keterangan'] ?? '');

    if (empty($paket_id) || empty($tanggal_berangkat) || empty($maskapai) || empty($embarkasi) || empty($kuota)) {
        echo "<script>
            alert('Paket Travel, Tanggal Keberangkatan, Maskapai, Embarkasi, dan Kuota wajib diisi!');
            window.history.back();
        </script>";
        exit();
    }

    if ($kuota < 1) {
        echo "<script>
            alert('Kuota Penerbangan minimal 1 Pax!');
            window.history.back();
        </script>";
        exit();
    }

    try {
        // PERBAIKAN: Menggunakan nama tabel jadwal_keberangkatan & nama kolom tgl_keberangkatan
        $query = "INSERT INTO jadwal_keberangkatan (paket_id, tgl_keberangkatan, tgl_kepulangan, maskapai, embarkasi, kuota_penerbangan, keterangan) 
                  VALUES (:paket_id, :tanggal_berangkat, :tgl_kepulangan, :maskapai, :embarkasi, :kuota, :keterangan)";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':paket_id', $paket_id);
        $stmt->bindParam(':tanggal_berangkat', $tanggal_berangkat);
        
        if (empty($tgl_kepulangan)) {
            $stmt->bindValue(':tgl_kepulangan', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(':tgl_kepulangan', $tgl_kepulangan);
        }

        $stmt->bindParam(':maskapai', $maskapai);
        $stmt->bindParam(':embarkasi', $embarkasi);
        $stmt->bindParam(':kuota', $kuota);
        $stmt->bindParam(':keterangan', $keterangan);

        if ($stmt->execute()) {
            header("Location: tabel_keberangkatan.php?status=success");
            exit();
        } else {
            echo "<script>alert('Gagal menambah jadwal keberangkatan!'); window.history.back();</script>";
            exit();
        }
    } catch (PDOException $e) {
        echo "<script>alert('Terjadi kesalahan database: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
        exit();
    }

} else {
    header("Location: tabel_keberangkatan.php");
    exit();
}
?>