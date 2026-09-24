<?php
/*
    frontend/pages/pilih-layanan.php
    -----------------------------------
    Halaman pilih paket + konfirmasi pendaftaran, tampilannya
    tetap di frontend (header/footer ARSHA), TIDAK masuk ke
    tampilan admin backend. Yang ke backend cuma proses simpan
    datanya saja (form ini submit ke backend/proses_pendaftaran.php).
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../database/connection.php';

$db = (new Database())->getConnection();

// Wajib login & harus role jamaah. Kalau belum, balik ke halaman login frontend dulu.
$isLoggedIn = isset($_SESSION['login']) && $_SESSION['login'] === true;
$role       = $_SESSION['role'] ?? '';

if (!$isLoggedIn) {
    header("Location: login.php?redirect=" . urlencode('pilih-layanan.php' . (isset($_GET['open']) ? '?open=' . (int) $_GET['open'] : '')));
    exit;
}
if ($role !== 'jamaah') {
    header("Location: ../../index.php");
    exit;
}

$listPaket    = [];
$errorMessage = "";

try {
    $stmtPaket = $db->query("SELECT * FROM paket ORDER BY id DESC");
    $listPaket = $stmtPaket->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = "Gagal mengambil data layanan.";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Pilihan Layanan Ibadah - Kemenhaj Panel</title>

  <link href="../template/assets/img/favicon.png" rel="icon">

  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Jost:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

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

    .card-layanan { border-radius: 20px; border: 1px solid #e2e8f0; transition: all 0.3s ease; }
    .card-layanan:hover { transform: translateY(-5px); box-shadow: 0 12px 25px rgba(0,0,0,0.08) !important; border-color: #047857; }
    .btn-gold { background: linear-gradient(135deg, #d97706 0%, #b45309 100%); color: #fff; font-weight: 700; border: none; }
    .btn-gold:hover { opacity: 0.9; color: #fff; }
    .icon-header-box { width: 52px; height: 52px; border-radius: 14px; background: linear-gradient(135deg, #064e3b 0%, #047857 100%); display: flex; align-items: center; justify-content: center; font-size: 1.35rem; color: #fff; }
  </style>
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
      <div class="d-flex align-items-center gap-2" style="margin-left:20px;">
        <span style="color:#fff; font-size:14px;"><i class="bi bi-person-circle me-1"></i><?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></span>
      </div>
    </div>
  </header>

  <main class="main">
    <section class="section" style="padding-top: 140px; padding-bottom: 100px; background:#f8fafc;">
      <div class="container">

        <?php
            $openId = (isset($_GET['open']) && ctype_digit((string) $_GET['open'])) ? (int) $_GET['open'] : 0;

            $paketDipilih = null;
            if ($openId > 0) {
                foreach ($listPaket as $p) {
                    if ((int) $p['id'] === $openId) {
                        $paketDipilih = $p;
                        break;
                    }
                }
            }
        ?>

        <?php if ($openId > 0 && $paketDipilih) { ?>

          <!-- Sudah pilih 1 paket dari halaman detail -> langsung tampilkan konfirmasinya saja -->
          <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">

              <div class="text-center mb-4">
                <div class="icon-header-box mx-auto mb-3"><i class="fas fa-paper-plane"></i></div>
                <h3 class="fw-bold mb-1" style="color:#064e3b;">Konfirmasi Pendaftaran</h3>
                <p class="text-muted small">Periksa kembali program yang kamu pilih sebelum lanjut</p>
              </div>

              <div class="p-4 p-md-5" style="background:#fff; border-radius:20px; box-shadow:0 15px 35px rgba(0,0,0,0.08);">

                <div class="p-3 bg-light rounded-3 mb-3">
                  <div class="small text-muted">Program Pilihan:</div>
                  <div class="fw-bold text-dark fs-5"><?php echo htmlspecialchars($paketDipilih['nama_paket']); ?></div>
                  <div class="fw-bold mt-1" style="color:#047857;">
                    Rp <?php echo number_format($paketDipilih['harga'], 0, ',', '.'); ?>
                  </div>
                </div>

                <form action="../../backend/proses_pendaftaran.php" method="POST">
                  <input type="hidden" name="pilih_paket_id" value="<?php echo $paketDipilih['id']; ?>">
                  <input type="hidden" name="metode_pembayaran" value="Bayar Tunai / Cash">

                  <div class="mb-3 text-start">
                    <label class="form-label fw-bold text-secondary small">METODE PEMBAYARAN</label>
                    <div class="p-2 border rounded-3 bg-light d-flex align-items-center gap-2 text-dark">
                      <i class="fas fa-money-bill-wave text-success"></i>
                      <span>Bayar Tunai / Cash (Di Kantor)</span>
                    </div>
                  </div>

                  <div class="mb-4 text-start">
                    <label class="form-label fw-bold text-secondary small">OPSI PEMBAYARAN</label>
                    <select name="opsi_bayar" class="form-select py-2 rounded-3" required>
                      <option value="" selected disabled hidden>-- Pilih Opsi Pembayaran --</option>
                      <option value="Lunas">Pelunasan Langsung (Lunas)</option>
                      <option value="DP">Uang Muka / DP</option>
                    </select>
                  </div>

                  <div class="d-flex gap-2">
                    <a href="paket-detail.php?id=<?php echo $paketDipilih['id']; ?>" class="btn btn-light rounded-3 px-4 flex-fill">Batal</a>
                    <button type="submit" class="btn btn-gold rounded-3 px-4 flex-fill">Lanjutkan Pendaftaran</button>
                  </div>
                </form>

              </div>

              <div class="text-center mt-3">
                <a href="pilih-layanan.php" class="small text-decoration-none" style="color:#064e3b;">Lihat semua paket lain</a>
              </div>

            </div>
          </div>

        <?php } else { ?>

        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
          <div class="d-flex align-items-center">
            <div class="icon-header-box me-3">
              <i class="fas fa-kaaba"></i>
            </div>
            <div>
              <h3 class="fw-bold mb-0" style="color:#064e3b;">Pilihan Layanan Ibadah</h3>
              <p class="mb-0 text-muted small">Pilih program perjalanan Haji &amp; Umroh yang sesuai dengan kebutuhan Anda</p>
            </div>
          </div>

          <a href="riwayat.php" class="btn btn-outline-secondary px-4 py-2 rounded-3 d-inline-flex align-items-center gap-2">
            <i class="fas fa-history"></i>
            <span>Lihat Riwayat Saya</span>
          </a>
        </div>

        <?php if (!empty($errorMessage)) { ?>
          <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php } ?>

        <div class="row g-4">
          <?php if (empty($listPaket)) { ?>
            <div class="col-12 text-center py-5">
              <i class="fas fa-box-open fa-3x text-muted mb-3 d-block opacity-50"></i>
              <h5 class="text-muted fw-bold">Belum ada program ibadah yang tersedia saat ini.</h5>
            </div>
          <?php } else { ?>
            <?php foreach ($listPaket as $p) { ?>
              <?php
                  $pId     = $p['id'];
                  $pNama   = $p['nama_paket'] ?? 'Layanan Travel';
                  $pJenis  = $p['jenis'] ?? 'Umroh/Haji';
                  $pHarga  = $p['harga'] ?? 0;
                  $pDurasi = $p['durasi'] ?? '-';
                  $pDesc   = $p['deskripsi'] ?? ($p['fasilitas'] ?? 'Fasilitas akomodasi, konsumsi, dan pembimbing ibadah terjamin.');
              ?>
              <div class="col-md-6 col-lg-4" id="layanan-<?php echo $pId; ?>">
                <div class="card card-layanan h-100 border-0 shadow-sm bg-white p-4 d-flex flex-column justify-content-between">
                  <div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                      <span class="badge px-3 py-2 rounded-pill fw-semibold" style="background-color:#d1fae5; color:#047857; font-size:12px;">
                        <i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($pJenis); ?>
                      </span>
                      <span class="small text-muted fw-bold">
                        <i class="far fa-clock me-1 text-warning"></i><?php echo htmlspecialchars($pDurasi); ?> Hari
                      </span>
                    </div>

                    <h5 class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($pNama); ?></h5>
                    <p class="text-muted small mb-4" style="line-height:1.6; min-height:48px;">
                      <?php echo htmlspecialchars(substr($pDesc, 0, 100)) . (strlen($pDesc) > 100 ? '...' : ''); ?>
                    </p>
                  </div>

                  <div class="pt-3 border-top">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                      <span class="text-muted small">Biaya Per Seseorang</span>
                      <h5 class="fw-bold mb-0" style="color:#047857;">
                        Rp <?php echo number_format($pHarga, 0, ',', '.'); ?>
                      </h5>
                    </div>

                    <a href="pilih-layanan.php?open=<?php echo $pId; ?>" class="btn btn-gold w-100 py-2 rounded-3 d-flex align-items-center justify-content-center gap-2">
                      <i class="fas fa-paper-plane"></i>
                      <span>Daftar Sekarang</span>
                    </a>
                  </div>
                </div>
              </div>
            <?php } ?>
          <?php } ?>
        </div>

        <?php } ?>

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