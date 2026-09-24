
<?php
require_once "database/connection.php";
require_once "classes/Auth.php";
require_once "classes/Paket.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);
$auth->checkRole(['admin', 'petugas']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paketObj = new Paket($db);
    $id         = (int)$_POST['id'];
    $nama_paket = htmlspecialchars($_POST['nama_paket']);
    $jenis      = htmlspecialchars($_POST['jenis']);
    $harga      = (float)$_POST['harga'];
    $kuota      = (int)$_POST['kuota'];
    $deskripsi  = htmlspecialchars($_POST['deskripsi']);

    if ($paketObj->update($id, $nama_paket, $jenis, $harga, $kuota, $deskripsi)) {
        echo "<script>alert('Data paket berhasil diperbarui!'); window.location.href='tabel_paket.php';</script>";
    } else {
        echo "<script>alert('Data paket gagal diperbarui!'); window.history.back();</script>";
    }
}
?>