<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$root = dirname(__DIR__) . DIRECTORY_SEPARATOR;
require_once $root . 'config/constants.php'; 
require_once $root . 'config/database.php';
require_once $root . 'config/functions.php';

if(file_exists($root . 'config/lang_bn.php')) require_once $root . 'config/lang_bn.php';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>পেশেন্ট কেয়ার হাসপাতাল এন্ড ডায়াগনস্টিক সেন্টার</title>
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>assets/images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">

    <style>
        :root { --primary-navy: #0A2647; --secondary-cyan: #2AA7E5; }
        .master-header { position: fixed; top: 0; left: 0; width: 100%; z-index: 2000; background: white; }
        .main-logo { height: 40px !important; width: 40px !important; border-radius: 50% !important; object-fit: cover; border: 2px solid var(--secondary-cyan); }
        .top-header { background: linear-gradient(90deg, var(--primary-navy) 0%, #1a4a7a 100%); color: white; font-size: 13px; }
        .emergency-fixed { color: var(--secondary-cyan) !important; font-weight: 800; animation: pulse 2s infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
        .navbar { background: white !important; border-bottom: 1px solid #eee; }
        .notice-container { background: #E5F2FF; height: 35px; border-bottom: 1px solid var(--secondary-cyan); overflow: hidden; }
        .notice-label { background: #ff4757; color: white; padding: 0 15px; font-weight: bold; height: 100%; display: flex; align-items: center; font-size: 12px; }
        .header-spacer { height: 145px; }
        marquee { font-weight: 700; color: var(--primary-navy); line-height: 35px; }
    </style>
</head>
<body>

<div class="master-header">
    <div class="top-header py-2 d-none d-md-block">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="small fw-bold"><i class="fas fa-map-marker-alt text-info"></i> কলেজ রোড, বরগুনা 
                <span class="ms-3 ps-3 border-start border-secondary border-opacity-50"><i class="far fa-clock text-info"></i> <span id="navClock">00:00:00 AM</span></span>
            </div>
            <div class="small fw-bold">
                <a href="tel:+09617558899" class="emergency-fixed text-decoration-none"><i class="fas fa-phone-alt"></i> জরুরি: +09617558899</a>
            </div>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg py-1">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?php echo BASE_URL; ?>index.php">
                <img src="<?php echo BASE_URL; ?>assets/images/logo.png" alt="Logo" class="main-logo me-2">
                <div>
                    <span class="d-block fw-bold lh-1 text-navy">পেশেন্ট কেয়ার</span>
                    <span class="small text-uppercase d-none d-sm-block text-cyan" style="font-size: 0.6rem;">হাসপাতাল এন্ড ডায়াগনস্টিক সেন্টার</span>
                </div>
            </a>
            <button class="navbar-toggler border-0" data-bs-toggle="collapse" data-bs-target="#navbarMain"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>index.php">হোম</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>modules/public/doctors.php">ডাক্তারবৃন্দ</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>#services">সেবাসমূহ</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>#contact">যোগাযোগ</a></li>
                    <?php if(isset($_SESSION['user_role'])): ?>
                        <li class="nav-item dropdown ms-lg-3">
                            <a class="nav-link dropdown-toggle" href="#" id="userDrop" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle"></i> <?php echo ($_SESSION['user_role'] == 'admin') ? "Super Admin" : $_SESSION['user_name']; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-2">
                                <li><a class="dropdown-item fw-bold" href="<?php echo BASE_URL; ?>modules/<?php echo $_SESSION['user_role']; ?>/dashboard.php">ড্যাশবোর্ড</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger fw-bold" href="<?php echo BASE_URL; ?>modules/auth/logout.php">লগআউট</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-lg-3"><a class="nav-link text-navy" href="<?php echo BASE_URL; ?>modules/public/patient-login.php">লগইন</a></li>
                        <li class="nav-item ms-lg-2"><a class="nav-link btn btn-primary text-white rounded-pill px-4" href="<?php echo BASE_URL; ?>modules/public/patient-register.php" style="background: var(--primary-navy);">রেজিস্ট্রেশন</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="notice-container d-flex align-items-center">
        <div class="notice-label shadow-sm">নোটিশ</div>
        <marquee behavior="scroll" direction="left" onmouseover="this.stop();" onmouseout="this.start();">
            <?php 
                $n_res = mysqli_query($conn, "SELECT notice_text FROM site_notices WHERE id = 1");
                $n_row = mysqli_fetch_assoc($n_res);
                echo $n_row['notice_text'] ?? 'পেশেন্ট কেয়ার হাসপাতালে স্বাগতম!';
            ?>
        </marquee>
    </div>
</div>

<div class="header-spacer"></div>

<script>
function updateHeaderClock() {
    const now = new Date();
    let h = now.getHours(); let m = now.getMinutes(); let s = now.getSeconds();
    const ampm = h >= 12 ? 'PM' : 'AM'; h = h % 12 || 12;
    m = m < 10 ? '0'+m : m; s = s < 10 ? '0'+s : s;
    if(document.getElementById('navClock')) document.getElementById('navClock').innerText = h + ":" + m + ":" + s + " " + ampm;
}
setInterval(updateHeaderClock, 1000); updateHeaderClock();
</script>