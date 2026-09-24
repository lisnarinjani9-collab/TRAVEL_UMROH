<?php
/*
    frontend/pages/register.php
    -----------------------------
    Halaman registrasi jamaah, tampilannya dibuat SAMA seperti
    web depan (header ARSHA, nuansa hijau-emas, footer) - bukan
    kotak polos kayak backend. Proses simpan data tetap ke
    database yang sama dengan backend.
*/

require_once __DIR__ . '/../database/connection.php';

$db = (new Database())->getConnection();

$error   = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nik           = trim($_POST['nik']);
    $nama_lengkap  = trim($_POST['nama_lengkap']);
    $jenis_kelamin = trim($_POST['jenis_kelamin']);
    $no_hp         = trim($_POST['no_hp']);
    $alamat        = trim($_POST['alamat']);
    $username      = trim($_POST['username']);
    $password      = trim($_POST['password']);

    if (empty($nik) || empty($nama_lengkap) || empty($jenis_kelamin) || empty($username) || empty($password)) {
        $error = "NIK, Nama Lengkap, Jenis Kelamin, Username, dan Password wajib diisi!";
    } else {
        try {
            $checkUser = $db->prepare("SELECT id FROM user WHERE username = ?");
            $checkUser->execute([$username]);

            $checkNik = $db->prepare("SELECT id FROM jamaah WHERE nik = ?");
            $checkNik->execute([$nik]);

            if ($checkUser->rowCount() > 0) {
                $error = "Username sudah digunakan, silakan pilih username lain!";
            } else if ($checkNik->rowCount() > 0) {
                $error = "NIK sudah terdaftar! Silakan gunakan NIK lain atau langsung login.";
            } else {
                $db->beginTransaction();

                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $stmtUser = $db->prepare("INSERT INTO user (username, password, role) VALUES (?, ?, 'jamaah')");
                $stmtUser->execute([$username, $hashedPassword]);
                $userId = $db->lastInsertId();

                $stmtJamaah = $db->prepare("INSERT INTO jamaah (user_id, nik, nama_lengkap, jenis_kelamin, no_hp, alamat) VALUES (?, ?, ?, ?, ?, ?)");
                $stmtJamaah->execute([$userId, $nik, $nama_lengkap, $jenis_kelamin, $no_hp, $alamat]);
                $jamaahId = $db->lastInsertId();

                $updateUser = $db->prepare("UPDATE user SET jamaah_id = ? WHERE id = ?");
                $updateUser->execute([$jamaahId, $userId]);

                $db->commit();
                $success = "Registrasi berhasil! Mengalihkan ke halaman login...";
            }
        } catch (PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $error = "Gagal mendaftar: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Registrasi Jamaah - Kemenhaj Panel</title>

  <link href="../template/assets/img/favicon.png" rel="icon">

  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Jost:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <link href="../template/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../template/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">

  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

  <link href="../template/assets/css/main.css" rel="stylesheet">

  <style>
    #header { background: linear-gradient(135deg, #064e3b 0%, #022c22 100%) !important; }
    #header .sitename { color: #ffffff !important; }
    #header .navmenu ul li a { color: #e5e7eb !important; }
    #header .navmenu ul li a:hover { color: #d4af37 !important; }
    #footer { background: linear-gradient(135deg, #022c22 0%, #064e3b 100%) !important; color: #e5e7eb !important; }
    #footer .sitename { color: #ffffff !important; }
    #footer .copyright { background: #022c22 !important; color: #e5e7eb !important; }
  </style>

  <?php if ($success) { ?>
    <meta http-equiv="refresh" content="2;url=login.php">
  <?php } ?>
</head>

<body class="index-page">

  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">
      <a href="../../index.php" class="logo d-flex align-items-center me-auto">
        <h1 class="sitename">Arsha</h1>
      </a>
      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="../../index.php">Home</a></li>
          <li><a href="../../index.php#about">Tentang</a></li>
          <li><a href="../../index.php#services">Data Paket</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>
    </div>
  </header>

  <main class="main">
    <section class="section" style="padding-top: 140px; padding-bottom: 100px; background:#f8fafc;">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-6 col-md-8">

            <div class="text-center mb-4">
              <i class="fas fa-user-plus fa-3x mb-3" style="color:#064e3b;"></i>
              <h2 style="color:#064e3b;">Registrasi Jamaah</h2>
              <p class="text-muted">Isi data lengkap Anda untuk mendaftar layanan travel</p>
            </div>

            <div class="p-4 p-md-5" style="background:#fff; border-radius:20px; box-shadow:0 15px 35px rgba(0,0,0,0.08);">

              <?php if ($error) { ?>
                <div class="alert alert-danger py-2 small"><?php echo $error; ?></div>
              <?php } ?>

              <?php if ($success) { ?>
                <div class="alert alert-success py-3 small fw-bold text-center">
                  <i class="fas fa-check-circle fa-2x mb-2 d-block"></i>
                  <?php echo $success; ?>
                </div>
              <?php } ?>

              <?php if (!$success) { ?>
              <form method="POST">
                <div class="mb-3 text-start">
                  <label class="form-label small fw-bold">NIK (Nomor Induk Kependudukan)</label>
                  <input type="text" name="nik" class="form-control" placeholder="Masukkan 16 digit NIK" required>
                </div>
                <div class="mb-3 text-start">
                  <label class="form-label small fw-bold">Nama Lengkap</label>
                  <input type="text" name="nama_lengkap" class="form-control" placeholder="Masukkan nama lengkap" required>
                </div>
                <div class="mb-3 text-start">
                  <label class="form-label small fw-bold">Jenis Kelamin</label>
                  <select name="jenis_kelamin" class="form-select" required>
                    <option value="">-- Pilih Jenis Kelamin --</option>
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                  </select>
                </div>
                <div class="mb-3 text-start">
                  <label class="form-label small fw-bold">No. Telepon / WhatsApp</label>
                  <input type="text" name="no_hp" class="form-control" placeholder="Contoh: 08123456789">
                </div>
                <div class="mb-3 text-start">
                  <label class="form-label small fw-bold">Alamat Lengkap</label>
                  <textarea name="alamat" class="form-control" rows="2" placeholder="Masukkan alamat domisili"></textarea>
                </div>
                <div class="mb-3 text-start">
                  <label class="form-label small fw-bold">Username</label>
                  <input type="text" name="username" class="form-control" placeholder="Buat username" required>
                </div>
                <div class="mb-4 text-start">
                  <label class="form-label small fw-bold">Password</label>
                  <input type="password" name="password" class="form-control" placeholder="Buat password" required>
                </div>
                <button type="submit" class="w-100 py-2 rounded-3 mb-3" style="background:linear-gradient(135deg, #d97706 0%, #b45309 100%); color:#fff; font-weight:700; border:none;">DAFTAR SEKARANG</button>
              </form>
              <?php } ?>

              <div class="text-center mt-2 pt-3 border-top">
                <p class="small text-muted mb-0">Sudah punya akun? <a href="login.php" class="fw-bold text-decoration-none" style="color:#064e3b;">Masuk di sini</a></p>
              </div>

            </div>

          </div>
        </div>
      </div>
    </section>
  </main>

  <footer id="footer" class="footer">
    <div class="container footer-top">
      <div class="row gy-4">
        <div class="col-lg-4 col-md-6 footer-about">
          <a href="../../index.php" class="d-flex align-items-center">
            <span class="sitename">Arsha</span>
          </a>
        </div>
      </div>
      <div class="container copyright text-center mt-4">
        <p>&copy; <span>Copyright</span> <strong class="px-1 sitename">Arsha</strong> <span>All Rights Reserved</span></p>
      </div>
    </div>
  </footer>

  <script src="../template/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../template/assets/js/main.js"></script>

</body>
</html>