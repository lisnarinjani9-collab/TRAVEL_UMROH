
<?php
// ambil koneksi database (file yang sama dipakai backend)
require_once __DIR__ . '/../database/connection.php';

$db = (new Database())->getConnection();

// ambil semua data dari tabel paket
$sql = $db->query("SELECT * FROM paket ORDER BY id DESC");
$daftarPaket = $sql->fetchAll();
?>

<style>
  /* Kartu paket + animasi saat di-hover */
  .paket-card {
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    background: #ffffff;
    height: 100%;
    padding: 28px;
    display: flex;
    flex-direction: column;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
  }
  .paket-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 16px 30px rgba(6, 78, 59, 0.15);
  }
  .paket-card:hover .paket-icon-box {
    transform: scale(1.1);
  }
  .paket-icon-box {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    color: #ffffff;
    margin-bottom: 16px;
    transition: transform 0.25s ease;
  }
  .paket-btn {
    display: block;
    text-align: center;
    padding: 10px 0;
    border-radius: 12px;
    color: #ffffff !important;
    font-weight: 600;
    text-decoration: none;
    transition: opacity 0.2s ease, transform 0.2s ease;
  }
  .paket-btn:hover {
    opacity: 0.9;
    transform: translateY(-2px);
  }
</style>

    <section id="services" class="services section" style="background:#f8fafc;">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2 style="color:#064e3b;">Data Paket</h2>
        <p>Pilihan paket Haji &amp; Umroh yang tersedia saat ini</p>
      </div><!-- End Section Title -->

      <div class="container">

        <?php if (count($daftarPaket) == 0) { ?>

          <!-- kalau tabel paket masih kosong -->
          <p class="text-center">Belum ada paket yang tersedia.</p>

        <?php } else { ?>

          <!-- tampilkan setiap paket jadi 1 kartu -->
          <div class="row g-4">
            <?php foreach ($daftarPaket as $paket) { ?>

              <?php
                  $isHaji = ($paket['jenis'] == 'Haji');
                  $icon        = $isHaji ? 'fa-kaaba' : 'fa-plane-departure';
                  $iconBg      = $isHaji ? 'linear-gradient(135deg, #064e3b 0%, #047857 100%)' : 'linear-gradient(135deg, #0284c7 0%, #0369a1 100%)';
                  $badgeBg     = $isHaji ? '#fef3c7' : '#e0f2fe';
                  $badgeColor  = $isHaji ? '#d97706' : '#0284c7';
              ?>

              <div class="col-md-6 col-lg-3" data-aos="fade-up">
                <div class="paket-card shadow-sm">

                  <div class="paket-icon-box" style="background:<?php echo $iconBg; ?>;">
                    <i class="fas <?php echo $icon; ?>"></i>
                  </div>

                  <span style="display:inline-block; padding:4px 14px; border-radius:999px; font-size:0.8rem; font-weight:700; background:<?php echo $badgeBg; ?>; color:<?php echo $badgeColor; ?>;">
                    <?php echo $paket['jenis']; ?>
                  </span>

                  <h5 class="fw-bold mt-3 mb-1" style="color:#064e3b;">
                    <?php echo $paket['nama_paket']; ?>
                  </h5>

                  <p class="text-muted small mb-3">
                    <?php echo $paket['durasi']; ?> hari
                  </p>

                  <div class="fw-bold fs-5 mb-3" style="color:#d97706;">
                    Rp <?php echo number_format($paket['harga'], 0, ',', '.'); ?>
                  </div>

                  <a href="frontend/pages/paket-detail.php?id=<?php echo $paket['id']; ?>" class="paket-btn" style="margin-top:auto; background:linear-gradient(135deg, #d97706 0%, #b45309 100%);">
                    Lihat Paket
                  </a>

                </div>
              </div>

            <?php } ?>
          </div>

        <?php } ?>

      </div>