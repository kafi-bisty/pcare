<?php
session_start();
include_once '../../config/database.php';
include_once '../../config/constants.php';
include_once '../../config/functions.php';

// ১. লগইন চেক
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/staff-login.php");
    exit;
}

$user_role = $_SESSION['user_role']; 
$today = date('Y-m-d');

// ২. উন্নত পরিসংখ্যান ডাটা আনা
// স্টাফ ও পেশেন্ট সংখ্যা
$doctor_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM doctors"))['total'];
$patient_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM patients"))['total'];
$reception_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM receptionists"))['total'];
$total_staff = $doctor_count + $reception_count;

// আজকের আর্থিক হিসাব
$income_res = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as total FROM hospital_accounts WHERE type='income' AND date='$today'"));
$today_income = $income_res['total'] ?? 0;

$expense_res = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as total FROM hospital_accounts WHERE type='expense' AND date='$today'"));
$today_expense = $expense_res['total'] ?? 0;

// ভর্তি থাকা রোগী (IPD)
$active_admissions = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM admissions WHERE status='admitted'"))['total'];

// নতুন মেসেজ
$new_messages = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM contact_messages WHERE status = 'unread'"))['total'];

// ৩. সাম্প্রতিক ৫টি অ্যাক্টিভিটি লগ আনা
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
        body { background-color: #f1f5f9; font-family: 'Segoe UI', sans-serif; }
        
        /* সাইডবার স্টাইল */
        .sidebar { height: 100vh; width: 260px; position: fixed; background: var(--navy); color: white; transition: 0.3s; z-index: 1000; }
        .sidebar-logo { width: 50px; height: 50px; border-radius: 50%; border: 2px solid var(--cyan); background: #fff; padding: 2px; }
        .sidebar-menu a { color: rgba(255,255,255,0.7); text-decoration: none; padding: 12px 20px; display: block; border-left: 4px solid transparent; transition: 0.3s; font-size: 14px; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: rgba(255,255,255,0.1); color: white; border-left-color: var(--cyan); }
        
        /* কন্টেন্ট স্টাইল */
        .main-content { margin-left: 260px; padding: 30px; }
        .stat-card { border: none; border-radius: 20px; transition: 0.3s; position: relative; overflow: hidden; color: white; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        
        .bg-grad-navy { background: linear-gradient(135deg, var(--navy) 0%, #1e4a7a 100%); }
        .bg-grad-cyan { background: linear-gradient(135deg, #2AA7E5 0%, #17a2b8 100%); }
        .bg-grad-green { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .bg-grad-red { background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%); }

        .tool-box { background: #fff; border-radius: 24px; padding: 25px; text-align: center; text-decoration: none; display: block; border: 1px solid #e2e8f0; transition: 0.3s; height: 100%; }
        .tool-box:hover { background: var(--navy); border-color: var(--navy); transform: translateY(-8px); }
        .tool-box:hover h6, .tool-box:hover small { color: #fff !important; }
        .tool-icon { font-size: 2.5rem; margin-bottom: 15px; transition: 0.3s; }
        .tool-box:hover .tool-icon { transform: scale(1.1); color: var(--cyan) !important; }

        @media (max-width: 992px) { .sidebar { margin-left: -260px; } .main-content { margin-left: 0; } }
    </style>
</head>
<body>

<div class="sidebar shadow no-print">
    <div class="text-center py-4 border-bottom border-secondary border-opacity-25 mb-3">
        <img src="<?php echo BASE_URL; ?>assets/images/logo.png" alt="Logo" class="sidebar-logo mb-2">
        <h5 class="fw-bold mb-0 text-white">Patient Care</h5>
        <small class="text-info x-small text-uppercase"><?php echo $user_role; ?> Portal</small>
    </div>
    
    <div class="sidebar-menu">
        <a href="dashboard.php" class="active"><i class="fas fa-th-large me-2"></i>ড্যাশবোর্ড</a>
        <a href="manage-accounts.php"><i class="fas fa-wallet me-2"></i>আয়-ব্যয় হিসাব</a>
        <a href="admission-manager.php"><i class="fas fa-bed me-2"></i>পেশেন্ট এডমিশন</a>
        <a href="lab-billing.php"><i class="fas fa-flask me-2"></i>ল্যাব বিলিং</a>
        <a href="patient-billing.php"><i class="fas fa-ticket-alt me-2"></i>মানি রিসিট</a>

        <?php if($user_role == 'admin'): ?>
            <div class="px-3 mt-4 mb-2 small text-muted text-uppercase">Admin Control</div>
            <a href="manage-all-staff.php"><i class="fas fa-users-cog me-2"></i>স্টাফ ম্যানেজার</a>
            <a href="manage-lab-tests.php"><i class="fas fa-vial me-2"></i>টেস্ট ম্যানেজমেন্ট</a>
            <a href="manage-gallery.php"><i class="fas fa-images me-2"></i>গ্যালারি ম্যানেজ</a>
        <?php endif; ?>

        <a href="../auth/logout.php" class="text-danger mt-5"><i class="fas fa-power-off me-2"></i>লগআউট</a>
    </div>
</div>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-navy">সিস্টেম ওভারভিউ</h3>
        <div class="badge bg-white text-navy p-2 px-3 shadow-sm rounded-pill border">
            <i class="far fa-calendar-alt me-2"></i>আজ: <?php echo date('d M, Y'); ?>
        </div>
    </div>

    <!-- পরিসংখ্যান কার্ডস -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card bg-grad-navy p-3">
                <small class="opacity-75">আজকের আয় (Income)</small>
                <h3 class="fw-bold mb-0">৳ <?php echo number_format($today_income); ?></h3>
                <i class="fas fa-chart-line position-absolute bottom-0 end-0 m-3 opacity-25 fa-2x"></i>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-grad-red p-3">
                <small class="opacity-75">আজকের ব্যয় (Expense)</small>
                <h3 class="fw-bold mb-0">৳ <?php echo number_format($today_expense); ?></h3>
                <i class="fas fa-shopping-cart position-absolute bottom-0 end-0 m-3 opacity-25 fa-2x"></i>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-grad-cyan p-3">
                <small class="opacity-75">ভর্তি থাকা রোগী (IPD)</small>
                <h3 class="fw-bold mb-0"><?php echo $active_admissions; ?> জন</h3>
                <i class="fas fa-user-injured position-absolute bottom-0 end-0 m-3 opacity-25 fa-2x"></i>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-grad-green p-3">
                <small class="opacity-75">নতুন মেসেজ (Inbox)</small>
                <h3 class="fw-bold mb-0"><?php echo $new_messages; ?> টি</h3>
                <i class="fas fa-envelope position-absolute bottom-0 end-0 m-3 opacity-25 fa-2x"></i>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- সাম্প্রতিক লগস -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-navy mb-0">অ্যাক্টিভিটি লগ (Activity Logs)</h5>
                    <a href="activity-logs.php" class="small text-decoration-none">সব দেখুন</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <tbody>
                            <?php while($log = mysqli_fetch_assoc($recent_logs)): ?>
                            <tr>
                                <td class="py-3 border-bottom border-light">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 bg-light rounded-circle p-2 me-3">
                                            <i class="fas fa-bolt text-warning"></i>
                                        </div>
                                        <div>
                                            <p class="mb-0 small fw-bold text-dark"><?php echo $log['user_name']; ?></p>
                                            <small class="text-muted"><?php echo $log['details']; ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end small text-muted"><?php echo date('h:i A', strtotime($log['created_at'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- শর্টকাট নেভিগেশন -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <h5 class="fw-bold text-navy mb-4">কুইক সার্ভিস</h5>
                <div class="d-grid gap-3">
                    <a href="patient-billing.php" class="btn btn-outline-primary py-3 rounded-pill fw-bold"><i class="fas fa-ticket-alt me-2"></i>মানি রিসিট কাটুন</a>
                    <a href="lab-billing.php" class="btn btn-outline-info py-3 rounded-pill fw-bold"><i class="fas fa-flask me-2"></i>নতুন ল্যাব বিলিং</a>
                    <a href="admission-manager.php" class="btn btn-outline-dark py-3 rounded-pill fw-bold"><i class="fas fa-bed me-2"></i>পেশেন্ট ভর্তি করুন</a>
                </div>
                <div class="mt-4 p-3 bg-light rounded-4 text-center">
                    <small class="text-muted d-block mb-1">সিস্টেম আইপি: <?php echo $_SERVER['REMOTE_ADDR']; ?></small>
                    <span class="badge bg-success rounded-pill px-3">Secure Connection</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ম্যানেজমেন্ট গ্রিড -->
    <div class="mt-5">
        <h4 class="fw-bold text-navy mb-4">ম্যানেজমেন্ট কন্ট্রোল</h4>
        <div class="row g-4">
            <div class="col-md-4 col-lg-3">
                <a href="manage-accounts.php" class="tool-box">
                    <i class="fas fa-calculator tool-icon text-primary"></i>
                    <h6 class="fw-bold text-navy mb-1">Accounts</h6>
                    <small class="text-muted">আয় ও ব্যয়ের বিবরণ</small>
                </a>
            </div>
            <div class="col-md-4 col-lg-3">
                <a href="manage-all-staff.php" class="tool-box">
                    <i class="fas fa-user-md tool-icon text-warning"></i>
                    <h6 class="fw-bold text-navy mb-1">Staffs</h6>
                    <small class="text-muted">ডাক্তার ও স্টাফ লিস্ট</small>
                </a>
            </div>
            <div class="col-md-4 col-lg-3">
                <a href="manage-lab-tests.php" class="tool-box">
                    <i class="fas fa-vial tool-icon text-danger"></i>
                    <h6 class="fw-bold text-navy mb-1">Lab Tests</h6>
                    <small class="text-muted">টেস্টের তালিকা ও মূল্য</small>
                </a>
            </div>
            <div class="col-md-4 col-lg-3">
                <a href="messages.php" class="tool-box">
                    <i class="fas fa-envelope-open-text tool-icon text-info"></i>
                    <h6 class="fw-bold text-navy mb-1">Inbox</h6>
                    <small class="text-muted">পেশেন্টদের মেসেজ</small>
                </a>
            </div>
        </div>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>
</body>
</html>