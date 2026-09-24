<?php
/*
    frontend/pages/paket-detail.php
    ---------------------------------
    Halaman detail 1 paket Haji/Umroh, tampilannya SAMA seperti
    web depan (header ARSHA, nuansa hijau-emas, footer) - bukan
    halaman backend. Dibuka dari tombol "Lihat Paket" di halaman
    Data Paket (index.php#services).
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['login']) && $_SESSION['login'] === true;

require_once __DIR__ . '/../database/connection.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (!$isLoggedIn) {
    // Belum login -> suruh login dulu, nanti abis login balik lagi ke paket ini.
    header("Location: login.php?redirect=" . urlencode('paket-detail.php?id=' . $id));
    exit;
}

$db = (new Database())->getConnection();

$paket = null;
if ($id > 0) {
    $stmt = $db->prepare("SELECT * FROM paket WHERE id = ?");
    $stmt->execute([$id]);
    $paket = $stmt->fetch();
}

if ($paket) {
    $isHaji     = ($paket['jenis'] == 'Haji');
    $icon       = $isHaji ? 'fa-kaaba' : 'fa-plane-departure';
    $iconBg     = $isHaji ? 'linear-gradient(135deg, #064e3b 0%, #047857 100%)' : 'linear-gradient(135deg, #0284c7 0%, #0369a1 100%)';
    $badgeBg    = $isHaji ? '#fef3c7' : '#e0f2fe';
    $badgeColor = $isHaji ? '#d97706' : '#0284c7';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title><?php echo $paket ? htmlspecialchars($paket['nama_paket']) : 'Paket Tidak Ditemukan'; ?> - Kemenhaj Panel</title>

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
    .detail-icon-box {
      width: 80px; height: 80px; border-radius: 20px;
      display: flex; align-items: center; justify-content: center;
      font-size: 2rem; color: #fff; margin-bottom: 20px;
    }
    .info-box {
      background: #f8fafc; border-radius: 14px; padding: 16px 20px;
      text-align: center; height: 100%;
    }
    .info-box .label { font-size: 0.8rem; color: #64748b; margin-bottom: 4px; }
    .info-box .value { font-weight: 700; color: #064e3b; font-size: 1.05rem; }
  </style>
</head>

<body class="index-page">

<?php $baseRoot = '../../'; $basePages = ''; include '../components/header.php' ?>

  <main class="main">
    <section class="section" style="padding-top: 140px; padding-bottom: 100px; background:#f8fafc;">
      <div class="container">

        <?php if (!$paket) { ?>

          <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
              <i class="fas fa-circle-exclamation fa-3x mb-3" style="color:#d97706;"></i>
              <h3 style="color:#064e3b;">Paket tidak ditemukan</h3>
              <p class="text-muted">Paket yang kamu cari sudah tidak tersedia atau link-nya salah.</p>
              <a href="../../index.php#services" class="btn mt-2" style="background:linear-gradient(135deg, #d97706 0%, #b45309 100%); color:#fff; font-weight:700;">
                &larr; Kembali ke Data Paket
              </a>
            </div>
          </div>

        <?php } else { ?>



          <div class="row justify-content-center">
            <div class="col-lg-8">
              <div class="p-4 p-md-5" style="background:#fff; border-radius:20px; box-shadow:0 15px 35px rgba(0,0,0,0.08);">

                <div class="detail-icon-box" style="background:<?php echo $iconBg; ?>;">
                  <i class="fas <?php echo $icon; ?>"></i>
                </div>

                <span style="display:inline-block; padding:4px 14px; border-radius:999px; font-size:0.8rem; font-weight:700; background:<?php echo $badgeBg; ?>; color:<?php echo $badgeColor; ?>;">
                  <?php echo htmlspecialchars($paket['jenis']); ?>
                </span>

                <h2 class="fw-bold mt-3 mb-3" style="color:#064e3b;">
                  <?php echo htmlspecialchars($paket['nama_paket']); ?>
                </h2>

                <div class="row g-3 mb-4">
                  <div class="col-4">
                    <div class="info-box">
                      <div class="label">Durasi</div>
                      <div class="value"><?php echo htmlspecialchars($paket['durasi']); ?> hari</div>
                    </div>
                  </div>
                  <div class="col-4">
                    <div class="info-box">
                      <div class="label">Jenis</div>
                      <div class="value"><?php echo htmlspecialchars($paket['jenis']); ?></div>
                    </div>
                  </div>
                  <div class="col-4">
                    <div class="info-box">
                      <div class="label">Harga</div>
                      <div class="value">Rp <?php echo number_format($paket['harga'], 0, ',', '.'); ?></div>
                    </div>
                  </div>
                </div>

                <?php if (!empty($paket['deskripsi'])) { ?>
                  <h6 class="fw-bold" style="color:#064e3b;">Deskripsi Paket</h6>
                  <p class="text-muted"><?php echo nl2br(htmlspecialchars($paket['deskripsi'])); ?></p>
                <?php } else { ?>
                  <h6 class="fw-bold" style="color:#064e3b;">Deskripsi Paket</h6>
                  <p class="text-muted">
                    Paket <?php echo htmlspecialchars($paket['jenis']); ?> "<?php echo htmlspecialchars($paket['nama_paket']); ?>"
                    dengan durasi perjalanan <?php echo htmlspecialchars($paket['durasi']); ?> hari.
                    Hubungi admin untuk info fasilitas, jadwal keberangkatan, dan syarat pendaftaran lebih lengkap.
                  </p>
                <?php } ?>

                <?php if (!empty($paket['fasilitas'])) { ?>
                  <h6 class="fw-bold mt-3" style="color:#064e3b;">Fasilitas</h6>
                  <p class="text-muted"><?php echo nl2br(htmlspecialchars($paket['fasilitas'])); ?></p>
                <?php } ?>

                <div class="d-flex flex-wrap gap-2 mt-4 pt-3 border-top">
                  <button type="button" class="btn px-4 py-2" style="background:linear-gradient(135deg, #d97706 0%, #b45309 100%); color:#fff; font-weight:700;" data-bs-toggle="modal" data-bs-target="#modalKonfirmasiPendaftaran">
                    Pilih Paket
                  </button>
                  <a href="../../index.php#services" class="btn px-4 py-2" style="background:#e2e8f0; color:#064e3b; font-weight:700;">
                    Lihat Paket Lain
                  </a>
                </div>

              </div>
            </div>
          </div>

          <!-- Modal Konfirmasi Pendaftaran -->
          <div class="modal fade" id="modalKonfirmasiPendaftaran" tabindex="-1" aria-labelledby="modalKonfirmasiPendaftaranLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content" style="border-radius:20px; border:none;">
                <div class="modal-header border-0 pb-0">
                  <h5 class="modal-title fw-bold" id="modalKonfirmasiPendaftaranLabel" style="color:#064e3b;">Konfirmasi Pendaftaran</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-2">

                  <div class="p-3 bg-light rounded-3 mb-3">
                    <div class="small text-muted">Program Pilihan:</div>
                    <div class="fw-bold text-dark fs-5"><?php echo htmlspecialchars($paket['nama_paket']); ?></div>
                    <div class="fw-bold mt-1" style="color:#047857;">
                      Rp <?php echo number_format($paket['harga'], 0, ',', '.'); ?>
                    </div>
                  </div>

                  <form action="../../backend/proses_pendaftaran.php" method="POST">
                    <input type="hidden" name="pilih_paket_id" value="<?php echo $paket['id']; ?>">
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
                      <button type="button" class="btn btn-light rounded-3 px-4 flex-fill" data-bs-dismiss="modal">Batal</button>
                      <button type="submit" class="btn rounded-3 px-4 flex-fill" style="background:linear-gradient(135deg, #d97706 0%, #b45309 100%); color:#fff; font-weight:700;">Lanjutkan Pendaftaran</button>
                    </div>
                  </form>

                </div>
              </div>
            </div>
          </div>

        <?php } ?>

      </div>
    </section>
  </main>

  <footer id="footer" class="footer">
    <div class="container footer-top">
      <div class="row gy-4">b  
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