<?php
require_once "connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);
$auth->checkRole(['admin', 'petugas']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pendaftaran_id = intval($_POST['pendaftaran_id'] ?? 0);
    $nominal        = floatval($_POST['nominal'] ?? 0);
    $tanggal_bayar  = trim($_POST['tanggal_bayar'] ?? '');
    $status         = trim($_POST['status'] ?? 'Valid');

    // 1. Validasi Input Utama
    if ($pendaftaran_id <= 0 || empty($tanggal_bayar)) {
        echo "<script>
            alert('Jamaah/Pendaftaran dan Tanggal Bayar wajib dipilih!');
            window.history.back();
        </script>";
        exit();
    }

    // 2. Validasi Nominal Tidak Boleh Kurang Dari 1
    if ($nominal <= 0) {
        echo "<script>
            alert('Nominal pembayaran harus lebih dari 0!');
            window.history.back();
        </script>";
        exit();
    }

    // 3. Penanganan Upload File Bukti Transfer
    $bukti_transfer = '';
    if (isset($_FILES['bukti_transfer']) && $_FILES['bukti_transfer']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath   = $_FILES['bukti_transfer']['tmp_name'];
        $fileName      = $_FILES['bukti_transfer']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
        if (!in_array($fileExtension, $allowedExtensions)) {
            echo "<script>
                alert('Format file bukti transfer harus berupa JPG, PNG, atau PDF!');
                window.history.back();
            </script>";
            exit();
        }

        $newFileName   = 'tf_' . time() . '_' . rand(100, 999) . '.' . $fileExtension;
        $uploadFileDir = 'uploads/bukti_transfer/';

        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0777, true);
        }

        $dest_path = $uploadFileDir . $newFileName;
        if (!move_uploaded_file($fileTmpPath, $dest_path)) {
            echo "<script>
                alert('Gagal mengunggah file bukti transfer!');
                window.history.back();
            </script>";
            exit();
        }
        $bukti_transfer = $newFileName;
    }

    // 4. Insert Data ke Database
    try {
        $query = "INSERT INTO pembayaran (pendaftaran_id, tanggal_bayar, nominal, bukti_transfer, status) 
                  VALUES (:pendaftaran_id, :tanggal_bayar, :nominal, :bukti_transfer, :status)";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':pendaftaran_id', $pendaftaran_id, PDO::PARAM_INT);
        $stmt->bindParam(':tanggal_bayar', $tanggal_bayar);
        $stmt->bindParam(':nominal', $nominal);
        $stmt->bindParam(':bukti_transfer', $bukti_transfer);
        $stmt->bindParam(':status', $status);

        if ($stmt->execute()) {
            header("Location: tabel_pembayaran.php?status=success");
            exit();
        } else {
            echo "<script>alert('Gagal menambah data pembayaran!'); window.history.back();</script>";
            exit();
        }
    } catch (PDOException $e) {
        echo "<script>alert('Error Database: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
        exit();
    }
} else {
    header("Location: tabel_pembayaran.php");
    exit();
}
?>