<?php
require_once "connection.php";
require_once "classes/Auth.php";
require_once "classes/Paket.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);
$auth->checkRole(['admin', 'petugas']);

if (isset($_GET['id'])) {
    $paketObj = new Paket($db);
    $id = (int)$_GET['id'];

    if ($paketObj->delete($id)) {
        echo "<script>alert('Data paket berhasil dihapus!'); window.location.href='tabel_paket.php';</script>";
    } else {
        echo "<script>alert('Data paket gagal dihapus!'); window.location.href='tabel_paket.php';</script>";
    }
}
?>