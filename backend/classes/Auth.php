<?php
session_start();

class Auth {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($username, $password) {
        $stmt = $this->conn->prepare("SELECT * FROM user WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch();

        if ($user) {
            // Cek password hash atau fallback plain-text (untuk data dummy awal)
            if (password_verify($password, $user['password']) || $password === $user['password']) {
                // TAMBAHKAN BARIS INI: Set indikator bahwa user sudah berhasil login
                $_SESSION['login']    = true; 
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role']     = $user['role'];
                return true;
            }
        }
        return false;
    }

    public function checkRole($allowed_roles = []) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 1. Cek apakah user sudah login
        if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
            header("Location: login.php");
            exit();
        }

        // 2. Ambil role dari session
        $user_role = $_SESSION['role'] ?? '';

        // 3. Cek apakah role user ada dalam daftar role yang diperbolehkan
        if (!in_array($user_role, $allowed_roles)) {
            // PERBAIKAN REDIRECT: Lempar kembali ke login.php, BUKAN ke halaman yang sedang diakses
            echo "<script>
                    alert('Akses Ditolak! Anda tidak memiliki hak akses.');
                    window.location.href = 'login.php';
                </script>";
            exit();
        }
    }

    public function logout() {
        session_start();
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit;
    }
}
?>