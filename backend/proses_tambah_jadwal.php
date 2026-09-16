<?php
require_once "connection.php";
require_once "classes/Auth.php";
// Opsi 1: Jika menggunakan class Jadwal terpisah
// require_once "classes/Jadwal.php";

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

    // 1. Validasi Input Wajib Tidak Boleh Kosong
    if (empty($paket_id) || empty($tanggal_berangkat) || empty($maskapai) || empty($embarkasi) || empty($kuota)) {
        echo "<script>
            alert('Paket Travel, Tanggal Keberangkatan, Maskapai, Embarkasi, dan Kuota wajib diisi!');
            window.history.back();
        </script>";
        exit();
    }

    // 2. Validasi Kuota Minimum
    if ($kuota < 1) {
        echo "<script>
            alert('Kuota Penerbangan minimal 1 Pax!');
            window.history.back();
        </script>";
        exit();
    }

    // 3. Simpan ke Database
    try {
        // PERBAIKAN: Menggunakan kolom 'kuota_penerbangan' dan 'status'
        $query = "INSERT INTO keberangkatan (paket_id, tanggal_berangkat, tgl_kepulangan, maskapai, embarkasi, kuota_penerbangan, keterangan) 
                  VALUES (:paket_id, :tanggal_berangkat, :tgl_kepulangan, :maskapai, :embarkasi, :kuota, :keterangan, 'Mendatang')";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':paket_id', $paket_id);
        $stmt->bindParam(':tanggal_berangkat', $tanggal_berangkat);
        
        // Handle tanggal kepulangan NULL jika kosong
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