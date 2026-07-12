<?php
/**
 * ১. সেশন এবং লজিক কন্ট্রোল
 */
session_start();
include_once '../../config/database.php';
include_once '../../config/constants.php';
include_once '../../config/functions.php';

// লগইন চেক
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'manager', 'accounts'])) {
    header("Location: ../auth/staff-login.php");
    exit;
}

$user_role = $_SESSION['user_role']; 
$today = date('Y-m-d');

/**
 * ২. ডাইনামিক ডাটা সংগ্রহ
 */
$doctor_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM doctors"))['total'];
$patient_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM patients"))['total'];
$reception_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM receptionists"))['total'];
$total_staff = $doctor_count + $reception_count;
$income_res = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as total FROM hospital_accounts WHERE type='income' AND date='$today'"));
$today_income = $income_res['total'] ?? 0;
$expense_res = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as total FROM hospital_accounts WHERE type='expense' AND date='$today'"));
$today_expense = $expense_res['total'] ?? 0;
$active_admissions = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM admissions WHERE status='admitted'"))['total'];
$new_messages = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM contact_messages WHERE status = 'unread'"))['total'];
$recent_logs = mysqli_query($conn, "SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 6");

include_once '../../includes/header.php';
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ড্যাশবোর্ড | প্যানেল</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --navy: #0A2647; --cyan: #2AA7E5; --dark-navy: #06182c; }
        body { background-color: #f1f5f9; font-family: 'Segoe UI', sans-serif; margin: 0; }
        
        /* সাইডবার সেটিংস - এটি হেডারের নিচে থাকবে */
        .sidebar { 
            height: calc(100vh - 155px); 
            width: 260px; 
            position: fixed; 
            top: 155px; 
            left: 0; 
            background: var(--navy); 
            color: white; 
            z-index: 1000; 
            overflow-y: auto;
            transition: 0.3s;
        }
        
        .sidebar-menu a { color: rgba(255,255,255,0.7); text-decoration: none; padding: 12px 20px; display: block; border-left: 4px solid transparent; transition: 0.3s; font-size: 14px; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: rgba(255,255,255,0.1); color: white; border-left-color: var(--cyan); }
        
        /* মেইন কন্টেন্ট এলাকা - সাইডবারের ডানে থাকবে */
        .main-wrapper { margin-left: 260px; min-height: 80vh; display: flex; flex-direction: column; }
        .main-content { padding: 30px; flex: 1; }

        .stat-card { border: none; border-radius: 20px; transition: 0.3s; color: white; height: 110px; display: flex; flex-direction: column; justify-content: center; position: relative; overflow: hidden; padding: 20px; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .bg-grad-navy { background: linear-gradient(135deg, var(--navy) 0%, #1e4a7a 100%); }
        .bg-grad-cyan { background: linear-gradient(135deg, #2AA7E5) 0%, #17a2b8 100%); }
        .bg-grad-green { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .bg-grad-red { background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%); }

        .tool-box { background: #fff; border-radius: 24px; padding: 25px; text-align: center; text-decoration: none; display: block; border: 1px solid #e2e8f0; transition: 0.3s; }
        .tool-box:hover { background: var(--navy); border-color: var(--navy); transform: translateY(-8px); }
        .tool-box:hover h6, .tool-box:hover small { color: #fff !important; }
        .tool-icon { font-size: 2.2rem; margin-bottom: 12px; transition: 0.3s; color: var(--navy); }
        .tool-box:hover .tool-icon { color: var(--cyan) !important; }

        /* মোবাইলের জন্য রেসপনসিভ */
        @media (max-width: 992px) { 
            .sidebar { margin-left: -260px; } 
            .main-wrapper { margin-left: 0; } 
        }
    </style>
</head>
<body>

<!-- সাইডবার শুরু -->
<nav class="sidebar no-print">
    <div class="py-3 text-center border-bottom border-secondary border-opacity-25 mb-2">
        <small class="text-info x-small text-uppercase fw-bold"><?php echo $user_role; ?> Portal</small>
    </div>
    <div class="sidebar-menu">
        <a href="dashboard.php" class="active"><i class="fas fa-th-large me-2"></i>ড্যাশবোর্ড</a>
        <a href="manage-accounts.php"><i class="fas fa-wallet me-2"></i>আয়-ব্যয় হিসাব</a>
        <a href="admission-manager.php"><i class="fas fa-bed me-2"></i>পেশেন্ট এডমিশন</a>
        <a href="lab-billing.php"><i class="fas fa-flask me-2"></i>ল্যাব বিলিং</a>
        <a href="patient-billing.php"><i class="fas fa-ticket-alt me-2"></i>মানি রিসিট</a>

        <?php if(in_array($user_role, ['admin', 'manager'])): ?>
            <div class="px-3 mt-3 mb-1 small text-muted text-uppercase" style="font-size: 10px;">Management</div>
            <a href="manage-all-staff.php"><i class="fas fa-users-cog me-2"></i>স্টাফ ম্যানেজার</a>
            <a href="manage-lab-tests.php"><i class="fas fa-vial me-2"></i>টেস্ট ম্যানেজমেন্ট</a>
        <?php endif; ?>

        <a href="../auth/logout.php" class="text-danger mt-4"><i class="fas fa-power-off me-2"></i>লগআউট</a>
    </div>
</nav>
<!-- সাইডবার শেষ -->

<!-- মেইন র‍্যাপার শুরু (কন্টেন্ট + ফুটার) -->
<div class="main-wrapper">
    
    <div class="main-content">
        <!-- টপবার -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-navy mb-0">সিস্টেম ওভারভিউ</h3>
                <p class="text-muted small">স্বাগতম, <strong><?php echo $_SESSION['user_name']; ?></strong>!</p>
            </div>
            <div class="badge bg-white text-navy p-2 px-3 shadow-sm rounded-pill border">
                <i class="far fa-calendar-alt me-2"></i>আজ: <?php echo date('d M, Y'); ?>
            </div>
        </div>

        <!-- পরিসংখ্যান কার্ডস -->
        <div class="row g-3 mb-5">
            <div class="col-md-3">
                <div class="card stat-card bg-grad-green p-3">
                    <small class="opacity-75">আজকের আয়</small>
                    <h3 class="fw-bold mb-0">৳<?php echo number_format($today_income); ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-grad-red p-3">
                    <small class="opacity-75">আজকের ব্যয়</small>
                    <h3 class="fw-bold mb-0">৳<?php echo number_format($today_expense); ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-grad-navy p-3">
                    <small class="opacity-75">ভর্তি রোগী</small>
                    <h3 class="fw-bold mb-0"><?php echo $active_admissions; ?> জন</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-grad-cyan p-3">
                    <small class="opacity-75">মোট স্টাফ</small>
                    <h3 class="fw-bold mb-0"><?php echo $total_staff; ?> জন</h3>
                </div>
            </div>
        </div>

        <!-- টুলস গ্রিড -->
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <a href="manage-accounts.php" class="tool-box shadow-sm text-decoration-none">
                    <i class="fas fa-calculator tool-icon"></i>
                    <h6 class="fw-bold text-navy">Accounts</h6>
                </a>
            </div>
            <div class="col-md-3">
                <a href="patient-billing.php" class="tool-box shadow-sm text-decoration-none">
                    <i class="fas fa-ticket-alt tool-icon text-success"></i>
                    <h6 class="fw-bold text-navy">Billing</h6>
                </a>
            </div>
            <div class="col-md-3">
                <a href="manage-all-staff.php" class="tool-box shadow-sm text-decoration-none">
                    <i class="fas fa-users-cog tool-icon text-warning"></i>
                    <h6 class="fw-bold text-navy">Staffs</h6>
                </a>
            </div>
            <div class="col-md-3">
                <a href="messages.php" class="tool-box shadow-sm text-decoration-none">
                    <i class="fas fa-envelope-open-text tool-icon text-info"></i>
                    <h6 class="fw-bold text-navy">Inbox</h6>
                </a>
            </div>
        </div>

        <!-- প্রশাসনিক ফুটার (Small) -->
        <div class="alert bg-white shadow-sm border py-2 rounded-4 d-flex justify-content-between align-items-center mb-0">
            <small class="text-muted">© <?php echo date('Y'); ?> পেশেন্ট কেয়ার হাসপাতাল। সর্বস্বত্ব সংরক্ষিত।</small>
            <span class="badge bg-light text-navy border fw-normal">ভার্সন ৩.০</span>
        </div>

    </div> <!-- main-content শেষ -->

    <!-- ★ মেইন পাবলিক ফুটার (Large) ★ -->
    <?php include_once '../../includes/footer.php'; ?>

</div> <!-- main-wrapper শেষ -->

</body>
</html>