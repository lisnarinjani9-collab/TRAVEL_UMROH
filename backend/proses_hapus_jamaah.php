<?php
require_once "connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);
$auth->checkRole(['admin', 'petugas']);

$id = $_GET['id'] ?? null;

if ($id) {
    $query = "DELETE FROM jamaah WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        header("Location: tabel_jamaah.php?status=deleted");
        exit();
    } else {
        echo "<script>alert('Gagal menghapus data jamaah!'); window.location.href='tabel_jamaah.php';</script>";
        exit();
    }
} else {
    header("Location: tabel_jamaah.php");
    exit();
}
?>