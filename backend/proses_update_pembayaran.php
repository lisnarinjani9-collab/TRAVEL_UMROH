<?php
require_once "connection.php";
require_once "classes/Auth.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);
$auth->checkRole(['admin', 'petugas', 'jamaah']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pembayaranId = $_POST['pembayaran_id'] ?? null;
    $pendaftaranId = $_POST['pendaftaran_id'] ?? null;
    $sisaPembayaran = $_POST['sisa_pembayaran'] ?? 0;
    $statusInput = $_POST['status'] ?? 'pending';

// Pemetaan status dari form ke Enum Database (sesuaikan dengan nilai di database)
    $statusPembayaran = 'pending';
    if ($statusInput === 'lunas' || $statusInput === 'cicil') {
        $statusPembayaran = 'valid';
    } else if ($statusInput === 'ditolak') {
        $statusPembayaran = 'ditolak';
    }

    try {
        $db->beginTransaction();

        // 1. Cek atau Update pada tabel Pembayaran
        if (!empty($pembayaranId)) {
            // Update jika data pembayaran sudah ada
            $queryUpdate = "UPDATE pembayaran SET sisa_pembayaran = :sisa, status = :status WHERE id = :id";
            $stmt = $db->prepare($queryUpdate);
            $stmt->execute([
                ':sisa' => $sisaPembayaran,
                ':status' => $statusPembayaran,
                ':id' => $pembayaranId
            ]);
        } else if (!empty($pendaftaranId)) {
            // Ambil jamaah_id, keberangkatan_id, dan paket_id dari tabel pendaftaran
            $stmtGetPendaftaran = $db->prepare("SELECT jamaah_id, keberangkatan_id, paket_id FROM pendaftaran WHERE id = :pendaftaran_id LIMIT 1");
            $stmtGetPendaftaran->execute([':pendaftaran_id' => $pendaftaranId]);
            $dataPendaftaran = $stmtGetPendaftaran->fetch(PDO::FETCH_ASSOC);

            if ($dataPendaftaran && isset($dataPendaftaran['jamaah_id'])) {
                $keberangkatanId = $dataPendaftaran['keberangkatan_id'];

                // Jika keberangkatan_id di pendaftaran kosong/null, cari keberangkatan_id default berdasarkan paket_id
                if (empty($keberangkatanId) && !empty($dataPendaftaran['paket_id'])) {
                    $stmtGetKeberangkatan = $db->prepare("SELECT id FROM keberangkatan WHERE paket_id = :paket_id LIMIT 1");
                    $stmtGetKeberangkatan->execute([':paket_id' => $dataPendaftaran['paket_id']]);
                    $dataKeberangkatan = $stmtGetKeberangkatan->fetch(PDO::FETCH_ASSOC);
                    
                    if ($dataKeberangkatan) {
                        $keberangkatanId = $dataKeberangkatan['id'];
                    }
                }

                // Validasi agar keberangkatan_id tidak null saat disimpan
                if (empty($keberangkatanId)) {
                    throw new Exception("Data keberangkatan untuk pendaftaran ini belum ditentukan. Harap set jadwal keberangkatan terlebih dahulu.");
                }

                // Insert dengan menyertakan keberangkatan_id dan jamaah_id
                $queryInsert = "INSERT INTO pembayaran (jamaah_id, keberangkatan_id, nominal, sisa_pembayaran, status, tanggal_bayar) 
                                VALUES (:jamaah_id, :keberangkatan_id, 0, :sisa, :status, NOW())";
                $stmt = $db->prepare($queryInsert);
                $stmt->execute([
                    ':jamaah_id' => $dataPendaftaran['jamaah_id'],
                    ':keberangkatan_id' => $keberangkatanId,
                    ':sisa' => $sisaPembayaran,
                    ':status' => $statusPembayaran
                ]);
            }
        }

        // 2. Jika Pembayaran Full / Lunas / Sisa = 0, perbarui status pendaftaran jamaah
        if ($statusInput === 'lunas' || (float)$sisaPembayaran == 0) {
            if (!empty($pendaftaranId)) {
                $queryPendaftaran = "UPDATE pendaftaran SET status = 'Diproses' WHERE id = :pendaftaran_id";
                $stmtPendaftaran = $db->prepare($queryPendaftaran);
                $stmtPendaftaran->execute([':pendaftaran_id' => $pendaftaranId]);
            }
        }

        $db->commit();
        header("Location: riwayat_pembayaran.php?status=success");
        exit;

    } catch (Exception $e) {
        $db->rollBack();
        echo "Gagal memperbarui data: " . $e->getMessage();
    }
} else {
    header("Location: riwayat_pembayaran.php");
    exit;
}