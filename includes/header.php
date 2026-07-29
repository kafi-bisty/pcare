<?php
/**
 * ১. সেশন এবং পাথ সেটআপ (সবার আগে)
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// পাথ নির্ধারণ
$root = dirname(__DIR__) . DIRECTORY_SEPARATOR;

// ২. ডাটাবেজ এবং কনস্ট্যান্ট লোড করা
require_once $root . 'config/constants.php'; 
require_once $root . 'config/database.php'; 
require_once $root . 'config/functions.php';

// ৩. ডাটাবেজ থেকে গ্লোবাল সেটিংস আনা
$settings_data = [];
if (isset($conn)) {
    mysqli_set_charset($conn, "utf8mb4");
    $s_res = mysqli_query($conn, "SELECT * FROM settings");
    if ($s_res) {
        while($s_row = mysqli_fetch_assoc($s_res)) {
            $settings_data[$s_row['setting_key']] = $s_row['setting_value'];
        }
    }
}

// ৪. ল্যাঙ্গুয়েজ ফাইল লোড
if(file_exists($root . 'config/lang_bn.php')) require_once $root . 'config/lang_bn.php';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $settings_data['hospital_name'] ?? 'পেশেন্ট কেয়ার হাসপাতাল'; ?></title>
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>assets/images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">

    <style>
        :root { --primary-navy: #0A2647; --secondary-cyan: #2AA7E5; }
        .master-header { position: fixed; top: 0; left: 0; width: 100%; z-index: 2000; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .main-logo { height: 50px !important; width: 50px !important; border-radius: 50%; border: 2px solid var(--secondary-cyan); }
        .top-header { background: var(--primary-navy); color: #fff; font-size: 14px; }
        .emergency-fixed { color: var(--secondary-cyan) !important; font-weight: 800; animation: pulse 2s infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
        .navbar { background: white !important; border-bottom: 1px solid #eee; }
        
        /* আপডেট করা নোটিশ বার স্টাইল */
        .notice-container { 
            background: #e8f5ff; 
            height: 40px; 
            overflow: hidden; 
            display: flex; 
            align-items: center; 
            position: relative;
            border-bottom: 1px solid #d1e8ff;
        }

        .notice-label { 
            background: #c62828; 
            color: white; 
            padding: 0 35px 0 15px; 
            font-weight: bold; 
            height: 100%; 
            display: flex; 
            align-items: center; 
            font-size: 14px; 
            z-index: 20; 
            clip-path: polygon(0 0, 85% 0, 100% 100%, 0% 100%); 
            position: relative;
        }

        .scrolling-text-container {
            flex: 1;
            overflow: hidden;
            white-space: nowrap;
        }

        .scrolling-text { 
            display: inline-block; 
            padding-left: 100%; 
            font-weight: 600; 
            color: #0A2647; 
            font-size: 15px; 
            animation: marquee-modern 25s linear infinite; 
        }

        @keyframes marquee-modern { 
            0% { transform: translateX(0); } 
            100% { transform: translateX(-100%); } 
        }

        .header-spacer { height: 145px; }
        @media (max-width: 991px) { .header-spacer { height: 100px; } }
    </style>
</head>
<body>

<div class="master-header">
    <!-- ১. টপ হেডার -->
    <div class="top-header py-2 d-none d-md-block">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="small fw-bold">
                <i class="fas fa-map-marker-alt text-info me-1"></i> <?php echo $settings_data['hospital_address'] ?? 'বরগুনা'; ?> 
                <span class="ms-3 ps-3 border-start border-secondary border-opacity-50">
                    <i class="far fa-clock text-info"></i> <span id="navClock">00:00:00 AM</span>
                </span>
            </div>
            <div class="small fw-bold">
                <a href="tel:<?php echo $settings_data['emergency_contact'] ?? ''; ?>" class="emergency-fixed text-decoration-none">
                    <i class="fas fa-phone-alt"></i> জরুরি: <?php echo $settings_data['emergency_contact'] ?? ''; ?>
                </a>
            </div>
        </div>
    </div>

    <!-- ২. নেভিগেশন বার -->
    <nav class="navbar navbar-expand-lg py-1">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?php echo BASE_URL; ?>index.php">
                <img src="<?php echo BASE_URL; ?>assets/images/logo.png" alt="Logo" class="main-logo me-2">
                <div>
                    <span class="d-block fw-bold lh-1 text-navy fs-4"><?php echo $settings_data['hospital_name'] ?? 'পেশেন্ট কেয়ার '; ?></span>
                    <span class="small text-uppercase d-none d-sm-block text-cyan" style="font-size: .90rem;"> এন্ড ডায়াগনস্টিক সেন্টার</span>
                </div>
            </a>
            <button class="navbar-toggler border-0" data-bs-toggle="collapse" data-bs-target="#navbarMain"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>index.php">হোম</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>modules/public/doctors.php">ডাক্তারবৃন্দ</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>#services">সেবাসমূহ</a></li>মে
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>#contact">যোগাযোগ</a></li>
                    
                    <?php if(isset($_SESSION['user_role'])): ?>
                        <li class="nav-item dropdown ms-lg-3">
                            <a class="nav-link dropdown-toggle fw-bold text-primary" href="#" id="userDrop" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i> <?php echo $_SESSION['user_name']; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-2">
                                <?php 
                                    $role = $_SESSION['user_role'];
                                    $dashboard_link = (in_array($role, ['admin', 'manager', 'accounts'])) ? "modules/admin/dashboard.php" : "modules/$role/dashboard.php";
                                ?>
                                <li><a class="dropdown-item fw-bold" href="<?php echo BASE_URL . $dashboard_link; ?>">ড্যাশবোর্ড</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger fw-bold" href="<?php echo BASE_URL; ?>modules/auth/logout.php">লগআউট</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-lg-3"><a class="nav-link text-navy fw-bold" href="<?php echo BASE_URL; ?>modules/public/patient-login.php">লগইন</a></li>
                        <li class="nav-item ms-lg-2"><a class="nav-link btn btn-primary text-white rounded-pill px-4 shadow-sm" href="<?php echo BASE_URL; ?>modules/public/patient-register.php" style="background: var(--primary-navy); border:none;">রেজিস্ট্রেশন</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ৩. আপডেট করা নোটিশ বার -->
    <div class="notice-container shadow-sm">
        <div class="notice-label">
            <i class="fas fa-bullhorn me-2"></i> নোটিশ
        </div>
        <div class="scrolling-text-container">
            <div class="scrolling-text">
                <?php echo $settings_data['notice_text'] ?? 'পেশেন্ট কেয়ার হাসপাতালে আপনাকে স্বাগতম! উন্নত সেবাই আমাদের অঙ্গীকার।'; ?>
            </div>
        </div>
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
</body>
</html>