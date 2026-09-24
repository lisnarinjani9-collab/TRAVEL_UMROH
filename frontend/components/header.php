
<?php
// Path dasar, disesuaikan tergantung header.php ini dipanggil dari mana:
// - index.php (root)      -> $baseRoot = '', $basePages = 'frontend/pages/'
// - frontend/pages/*.php  -> $baseRoot = '../../', $basePages = ''
// Kalau file pemanggilnya lupa nge-set, default-nya dianggap dipanggil dari root.
$baseRoot   = $baseRoot ?? '';
$basePages  = $basePages ?? 'frontend/pages/';
$activePage = $activePage ?? 'home'; // 'home' | 'tentang' | 'paket' | 'paket-anda'

// Kalau baseRoot kosong berarti kita LAGI di index.php -> menu Home/Tentang/Data Paket
// bisa langsung loncat ke anchor (#hero dst). Kalau nggak, harus balik dulu ke index.php.
$hrefHome     = ($baseRoot === '') ? '#hero'     : $baseRoot . 'index.php#hero';
$hrefTentang  = ($baseRoot === '') ? '#about'    : $baseRoot . 'index.php#about';
$hrefPaket    = ($baseRoot === '') ? '#services' : $baseRoot . 'index.php#services';

// Pastikan session sudah berjalan supaya status login (yang di-set oleh backend/login.php)
// bisa terbaca di halaman frontend ini.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// proses logout langsung di sini (tanpa file logout.php terpisah)
if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header("Location: " . $baseRoot . "index.php");
    exit;
}

$isLoggedIn = isset($_SESSION['login']) && $_SESSION['login'] === true;
$displayName = $_SESSION['username'] ?? '';
$userRole    = $_SESSION['role'] ?? '';

// Kalau jamaah sudah pernah pilih/daftar paket, munculin menu "Paket Anda"
// di header yang ngarah ke riwayat pendaftaran dia.
$hasPendaftaran = false;
if ($isLoggedIn && $userRole === 'jamaah') {
    require_once __DIR__ . '/../database/connection.php';
    try {
        $db = (new Database())->getConnection();
        $stmtCek = $db->prepare(
            "SELECT COUNT(*) FROM pendaftaran p
             LEFT JOIN jamaah j ON p.jamaah_id = j.id
             WHERE j.user_id = :user_id"
        );
        $stmtCek->execute([':user_id' => $_SESSION['user_id'] ?? 0]);
        $hasPendaftaran = ((int) $stmtCek->fetchColumn()) > 0;
    } catch (PDOException $e) {
        $hasPendaftaran = false;
    }
}
?>
<header id="header" class="header d-flex align-items-center fixed-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">

      <a href="<?php echo $baseRoot; ?>index.php" class="logo d-flex align-items-center me-auto">
        <!-- Uncomment the line below if you also wish to use an image logo -->
        <!-- <img src="assets/img/logo.webp" alt=""> -->
        <h1 class="sitename">TRAVEL HAJI & UMROH</h1>
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="<?php echo $hrefHome; ?>" class="<?php echo $activePage === 'home' ? 'active' : ''; ?>">Home</a></li>
          <li><a href="<?php echo $hrefTentang; ?>" class="<?php echo $activePage === 'tentang' ? 'active' : ''; ?>">Tentang </a></li>
          <li><a href="<?php echo $hrefPaket; ?>" class="<?php echo $activePage === 'paket' ? 'active' : ''; ?>">Data Paket</a></li>
          <?php if ($hasPendaftaran) { ?>
            <li><a href="<?php echo $basePages; ?>riwayat.php" class="<?php echo $activePage === 'paket-anda' ? 'active' : ''; ?>">Paket Anda</a></li>
          <?php } ?>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

      <?php if ($isLoggedIn): ?>
        <div class="d-flex align-items-center gap-2 auth-area">
          <a href="backend/index.php" class="btn-register d-flex align-items-center gap-1 text-decoration-none">
            <i class="bi bi-person-circle"></i>
            <span><?= htmlspecialchars($displayName) ?></span>
          </a>
          <a class="btn-register" href="<?php echo $baseRoot; ?>index.php?logout=1" onclick="return confirm('Yakin ingin logout?');">Logout</a>
        </div>
      <?php else: ?>
        <div class="d-flex align-items-center gap-2 auth-area">
          <a class="btn-register" href="<?php echo $basePages; ?>register.php">Register</a>
          <a class="btn-register" href="<?php echo $basePages; ?>login.php">Login</a>
        </div>
      <?php endif; ?>

    </div>
</header>

<style>
  .auth-area { margin-left: 20px; }
  .btn-register {
    background: transparent;
    color: #fff;
    border: 2px solid rgba(255,255,255,0.6);
    padding: 8px 22px;
    border-radius: 50px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    transition: 0.3s;
    white-space: nowrap;
  }
  .btn-register:hover {
    background: rgba(255,255,255,0.15);
    border-color: #fff;
    color: #fff;
  }
  /* Menu yang lagi aktif dikasih warna emas, samain sama warna hover */
  #header .navmenu .active,
  #header .navmenu .active:focus {
    color: #d4af37 !important;
  }
</style>

  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">

      <a href="index.html" class="logo d-flex align-items-center me-auto">
        <!-- Uncomment the line below if you also wish to use an image logo -->
        <!-- <img src="assets/img/logo.webp" alt=""> -->
        <h1 class="sitename">Arsha</h1>
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="#hero" class="active">Home</a></li>
          <li><a href="#about">Tentang</a></li>
          <li><a href="#services">Nama Paket</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

      <a class="btn-getstarted" href="#about">Get Started</a>

    </div>
  </header>
