<?php
// ১. সেশন এবং ডাটাবেজ কানেকশন (সবার আগে)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../../config/database.php';

// ২. লগইন ও সিকিউরিটি চেক (কোনো HTML আউটপুট হওয়ার আগেই)
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] != 'admin' && $_SESSION['user_role'] != 'accounts')) {
    header("Location: ../auth/login.php");
    exit(); // এটি খুব গুরুত্বপূর্ণ
}

// ৩. ডাটা কুয়েরি
$today = date('Y-m-d');
$income_res = mysqli_query($conn, "SELECT SUM(amount) as total FROM hospital_accounts WHERE type='income' AND date='$today'");
$today_income = mysqli_fetch_assoc($income_res)['total'] ?? 0;

$expense_res = mysqli_query($conn, "SELECT SUM(amount) as total FROM hospital_accounts WHERE type='expense' AND date='$today'");
$today_expense = mysqli_fetch_assoc($expense_res)['total'] ?? 0;

// ৪. এখন ভিজ্যুয়াল হেডার ইনক্লুড করুন
include_once '../../includes/header.php';
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h3 class="fw-bold text-navy">হিসাবরক্ষণ ড্যাশবোর্ড</h3>
            <p class="text-muted">আজকের তারিখ: <?php echo date('d M, Y'); ?></p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card bg-success text-white p-4 rounded-4 shadow border-0 text-center">
                <h6>আজকের মোট আয়</h6>
                <h2 class="fw-bold">৳ <?php echo number_format($today_income, 2); ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger text-white p-4 rounded-4 shadow border-0 text-center">
                <h6>আজকের মোট ব্যয়</h6>
                <h2 class="fw-bold">৳ <?php echo number_format($today_expense, 2); ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-white p-4 rounded-4 shadow border-0 text-center">
                <a href="patient-billing.php" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-sm">
                    <i class="fas fa-plus-circle me-2"></i>নতুন মানি রিসিট
                </a>
            </div>
        </div>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>