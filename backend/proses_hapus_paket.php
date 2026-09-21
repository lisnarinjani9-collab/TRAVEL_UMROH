<?php
require_once "connection.php";
require_once "classes/Auth.php";
require_once "classes/Paket.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);
$auth->checkRole(['admin', 'petugas']);

// Pastikan ada parameter ID di URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
    $paketObj = new Paket($db);

    if ($paketObj->delete($id)) {
        header("Location: tabel_paket.php?status=deleted");
        exit();
    } else {
        header("Location: tabel_paket.php?status=error");
        exit();
    }
} else {
    header("Location: tabel_paket.php");
    exit();
}