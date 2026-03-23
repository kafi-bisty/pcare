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
        .main-logo { height: 50px !important; width: 50px !important; border-radius: 50%  border: 2px solid var(--secondary-cyan); }
        .top-header { background: linear-gradient(90deg, var(--primary-navy) 0%, #11022c 100%); color: red; font-size: 15px; }
        .emergency-fixed { color: var(--secondary-cyan) !important; font-weight: 800; animation: pulse 2s infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
        .navbar { background: white !important; border-bottom: 1px solid #eee; }
        
/* ==========================================================================
   আধুনিক মুভিং নোটিশ (Premium Style)
   ========================================================================== */
.notice-container {
    background: #aedae7; /* সাদা ব্যাকগ্রাউন্ড */
    height: 45px;
    border-bottom: 2px solid var(--light-bg);
    overflow: hidden;
    position: relative;
    z-index: 1000;
}

/* নোটিশ লেবেল (লাল গ্রেডিয়েন্ট) */
.notice-label {
    background: linear-gradient(45deg, #04411d, #aa0808);
    color: white;
    padding: 0 25px;
    font-weight: 800;
    height: 100%;
    display: flex;
    align-items: center;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 1px;
    z-index: 10;
    white-space: nowrap;
    /* আধুনিক শেপ */
    clip-path: polygon(0 0, 85% 0, 100% 50%, 85% 100%, 0 100%);
    box-shadow: 5px 0 15px rgba(255, 71, 87, 0.3);
}

/* স্ক্রলিং টেক্সট কন্টেইনার */
.scrolling-text-container {
    flex: 1;
    overflow: hidden;
    white-space: nowrap;
    background: #f8f9ff; /* হালকা নীলাভ আভা */
    height: 100%;
    display: flex;
    align-items: center;
}

/* মেইন মুভিং টেক্সট */
.scrolling-text {
    display: inline-block;
    padding-left: 100%; /* লেখাটি ডান দিক থেকে শুরু হবে */
    font-weight: 700;
    color: var(--primary-navy);
    font-size: 25px;
    animation: marquee-modern 25s linear infinite; /* গতি পরিবর্তন করতে ২৫সে ব্যবহার করুন */
}

/* মাউস বা টাচ করলে লেখা থেমে যাবে */
.scrolling-text-container:hover .scrolling-text {
    animation-play-state: paused;
    color: var(--secondary-cyan); /* মাউস নিলে রঙ বদলাবে */
}

/* এনিমেশন লজিক */
@keyframes marquee-modern {
    0% { transform: translateX(0); }
    100% { transform: translateX(-100%); }
}

/* মোবাইল ডিভাইসের জন্য ছোট এডজাস্টমেন্ট */
@media (max-width: 768px) {
    .notice-container { height: 38px; }
    .notice-label { padding: 0 15px; font-size: 11px; }
    .scrolling-text { font-size: 12px; animation-duration: 15s; }
}

/* ফিক্সড হেডারের জন্য কন্টেন্ট অ্যাডজাস্টমেন্ট */
.header-spacer {
    height: 155px; /* পিসিতে হেডারের মোট উচ্চতা (Top Header + Nav + Notice) */
    width: 100%;
    display: block;
}

/* মোবাইলে যখন টপ হেডার হাইড হবে, তখন স্পেসার ছোট হবে */
@media (max-width: 991px) {
    .header-spacer {
        height: 110px; /* মোবাইলে হেডারের উচ্চতা কম থাকে */
    }
}

/* নিশ্চিত করুন ড্যাশবোর্ডের কন্টেন্ট যেন হেডারের নিচে না যায় */
.container-fluid, .container {
    position: relative;
    z-index: 1;
}

/* হেডার যেন সবসময় সবার উপরে থাকে */
.master-header {
    z-index: 2000 !important;
}





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
                <img src="<?php echo BASE_URL; ?>assets/images/logo.png" alt="Logo" class="main-logo me-2" >
                <div>
                    <span class="d-block fw-bold lh-1 text-navy">পেশেন্ট কেয়ার</span>
                    <span class="small text-uppercase d-none d-sm-block text-cyan" style="font-size: 0.9rem;">হাসপাতাল এন্ড ডায়াগনস্টিক সেন্টার</span>
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

    
<!-- ৩. আধুনিক ও ডাইনামিক মুভিং নোটিশ বার -->
<!-- ৩. আধুনিক ও ডাইনামিক মুভিং নোটিশ বার -->
<div class="notice-container d-flex align-items-center shadow-sm">
    <!-- লাল এনিমেটেড ব্যাজ -->
    <div class="notice-label shadow-sm">
        <i class="fas fa-bullhorn me-2"></i><?php echo $lang['notice_title'] ?? 'নোটিশ'; ?>
    </div>
    
    <div class="scrolling-text-container">
        <div class="scrolling-text">
            <?php 
                $n_res = mysqli_query($conn, "SELECT notice_text FROM site_notices WHERE id = 1");
                $n_row = mysqli_fetch_assoc($n_res);
                $notice = $n_row['notice_text'] ?? 'পেশেন্ট কেয়ার হাসপাতালে আপনাকে স্বাগতম! অভিজ্ঞ ডাক্তারদের মাধ্যমে আধুনিক ও উন্নত চিকিৎসা সেবা প্রদান করা হচ্ছে।';
                echo $notice;
            ?>
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