<?php
require_once "connection.php";
require_once "classes/Auth.php";
require_once "classes/Pendaftaran.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);
$auth->checkRole(['admin', 'petugas']);

if (isset($_GET['id'])) {
    $pendaftaranObj = new Pendaftaran($db);
    $id = (int)$_GET['id'];

    if ($pendaftaranObj->delete($id)) {
        echo "<script>alert('Data pendaftaran berhasil dihapus!'); window.location.href='tabel_pendaftaran.php';</script>";
    } else {
        echo "<script>alert('Data pendaftaran gagal dihapus!'); window.location.href='tabel_pendaftaran.php';</script>";
    }
}
?>