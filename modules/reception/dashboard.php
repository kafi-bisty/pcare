<?php
include_once '../../includes/header.php';

// ১. রিসেপশন লগইন চেক
if (!isset($_SESSION['reception_id']) || $_SESSION['user_role'] != 'reception') {
    echo "<script>window.location.href='../auth/staff-login.php';</script>";
    exit;
}

// ২. ডাটাবেজ থেকে রিয়েল-টাইম পরিসংখ্যান আনা
$today = date('Y-m-d');
$pending_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(id) as total FROM appointments WHERE status = 'pending'"))['total'];
$approved_today = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(id) as total FROM appointments WHERE status = 'approved' AND appointment_date = '$today'"))['total'];
$doctor_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(id) as total FROM doctors"))['total'];
$total_patients = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(id) as total FROM patients"))['total'];

// ৩. স্টাফ নোটিশ আনা (মালিকের পক্ষ থেকে সবশেষ নোটিশটি আনবে)
$announcement_query = mysqli_query($conn, "SELECT * FROM staff_announcements ORDER BY id DESC LIMIT 1");
$announcement = mysqli_fetch_assoc($announcement_query);

$alert_type = 'primary'; // ডিফল্ট
if ($announcement) {
    $alert_type = ($announcement['priority'] == 'info') ? 'primary' : (($announcement['priority'] == 'warning') ? 'warning' : 'danger');
}
?>

<div class="container-fluid bg-light py-5" style="min-height: 90vh;">
    <div class="container">
        
        <!-- ১. স্বাগতম ব্যানার -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden" style="background: linear-gradient(135deg, var(--primary-navy) 0%, #1a4a7a 100%);">
                    <div class="card-body p-4 p-md-5">
                        <div class="row align-items-center">
                            <div class="col-md-8 text-white">
                                <h2 class="fw-bold mb-2 text-white">আসসালামু আলাইকুম, <?php echo $_SESSION['user_name']; ?>!</h2>
                                <p class="opacity-75 mb-0 fs-5 text-white">পেশেন্ট কেয়ার হাসপাতালের রিসেপশন কন্ট্রোল প্যানেলে আপনাকে স্বাগতম। আজ আপনার ডিউটি সময় শুরু হয়েছে।</p>
                                <div class="mt-3">
                                    <span class="badge bg-info py-2 px-3 rounded-pill"><i class="far fa-calendar-alt me-2"></i>আজ: <?php echo date('d M, Y'); ?></span>
                                    <span class="badge bg-success py-2 px-3 rounded-pill ms-2">স্ট্যাটাস: অনলাইন</span>
                                </div>
                            </div>
                            <div class="col-md-4 text-end d-none d-md-block">
                                <i class="fas fa-hospital-user fa-6x text-white opacity-25"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ২. অভ্যন্তরীণ স্টাফ নোটিশ বক্স (এরর ফ্রি লজিক) -->
        <?php if ($announcement): ?>
            <div class="alert alert-<?php echo $alert_type; ?> border-0 shadow-sm rounded-4 p-4 mb-5 d-flex align-items-center">
                <div class="icon-box me-3">
                    <i class="fas <?php echo ($alert_type == 'danger') ? 'fa-exclamation-triangle' : 'fa-info-circle'; ?> fa-2x"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1"><?php echo $announcement['title']; ?></h5>
                    <p class="mb-0 small text-dark"><?php echo $announcement['message']; ?></p>
                    <small class="opacity-50 mt-1 d-block text-dark">আপডেট সময়: <?php echo date('d M, h:i A', strtotime($announcement['updated_at'])); ?></small>
                </div>
            </div>
        <?php endif; ?>

        <!-- ৩. পরিসংখ্যান কার্ডস -->
        <div class="row g-4 mb-5 text-center">
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm p-3 rounded-4 bg-white border-start border-warning border-5 h-100">
                    <h6 class="text-muted small fw-bold text-uppercase">পেন্ডিং অনুরোধ</h6>
                    <h3 class="fw-bold text-navy mb-0"><?php echo $pending_count; ?></h3>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm p-3 rounded-4 bg-white border-start border-success border-5 h-100">
                    <h6 class="text-muted small fw-bold text-uppercase">আজকের সিরিয়াল</h6>
                    <h3 class="fw-bold text-navy mb-0"><?php echo $approved_today; ?></h3>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm p-3 rounded-4 bg-white border-start border-primary border-5 h-100">
                    <h6 class="text-muted small fw-bold text-uppercase">মোট ডাক্তার</h6>
                    <h3 class="fw-bold text-navy mb-0"><?php echo $doctor_count; ?></h3>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm p-3 rounded-4 bg-white border-start border-info border-5 h-100">
                    <h6 class="text-muted small fw-bold text-uppercase">মোট রোগী</h6>
                    <h3 class="fw-bold text-navy mb-0"><?php echo $total_patients; ?></h3>
                </div>
            </div>
        </div>

        <!-- ৪. কুইক অ্যাকশন মেনু -->
        <h4 class="fw-bold mb-4 text-navy"><i class="fas fa-th-large me-2"></i>ম্যানেজমেন্ট মেনু</h4>
        <div class="row g-4">
            <div class="col-md-4 col-lg-3">
                <a href="pending-appointments.php" class="quick-btn shadow-sm">
                    <div class="icon-box bg-warning bg-opacity-10 text-warning"><i class="fas fa-calendar-check"></i></div>
                    <h6>Approve Appointments</h6>
                    <small>রোগীদের রিকোয়েস্ট দেখুন</small>
                </a>
            </div>
            <div class="col-md-4 col-lg-3">
                <a href="manage-notice.php" class="quick-btn shadow-sm">
                    <div class="icon-box bg-danger bg-opacity-10 text-danger"><i class="fas fa-bullhorn"></i></div>
                    <h6>Manage Notice</h6>
                    <small>হোমপেজের নোটিশ পরিবর্তন</small>
                </a>
            </div>
            <div class="col-md-4 col-lg-3">
                <a href="manage-doctors.php" class="quick-btn shadow-sm">
                    <div class="icon-box bg-primary bg-opacity-10 text-primary"><i class="fas fa-user-md"></i></div>
                    <h6>Manage Doctors</h6>
                    <small>ডাক্তার প্রোফাইল ও রুম আপডেট</small>
                </a>
            </div>
            <div class="col-md-4 col-lg-3">
                <a href="search-patient.php" class="quick-btn shadow-sm">
                    <div class="icon-box bg-success bg-opacity-10 text-success"><i class="fas fa-search-plus"></i></div>
                    <h6>Search Patient</h6>
                    <small>ফোন নম্বর দিয়ে রোগী খুঁজুন</small>
                </a>
            </div>
            <div class="col-md-4 col-lg-3">
                <a href="add-new-patient.php" class="quick-btn shadow-sm">
                    <div class="icon-box bg-danger bg-opacity-10 text-danger"><i class="fas fa-user-plus"></i></div>
                    <h6>Direct Registration</h6>
                    <small>নতুন রোগী সরাসরি রেজিস্টার</small>
                </a>
            </div>
            <div class="col-md-4 col-lg-3">
                <a href="appointment-reports.php" class="quick-btn shadow-sm">
                    <div class="icon-box bg-info bg-opacity-10 text-info"><i class="fas fa-file-invoice"></i></div>
                    <h6>Appointment Reports</h6>
                    <small>তারিখ অনুযায়ী রিপোর্ট বের করুন</small>
                </a>
            </div>
            <div class="col-md-4 col-lg-3">
                <a href="manage-tips.php" class="quick-btn shadow-sm">
                    <div class="icon-box bg-info bg-opacity-10 text-info"><i class="fas fa-lightbulb"></i></div>
                    <h6>Health Tips</h6>
                    <small>হোমপেজের পরামর্শ আপডেট করুন</small>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.text-navy { color: var(--primary-navy); }
.quick-btn {
    background: #fff; padding: 30px 20px; border-radius: 24px;
    display: block; text-align: center; text-decoration: none;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border: 1px solid rgba(0,0,0,0.02); height: 100%;
}
.quick-btn .icon-box {
    width: 65px; height: 65px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 20px; font-size: 1.8rem; transition: 0.3s;
}
.quick-btn h6 { color: var(--primary-navy); font-weight: 700; margin-bottom: 8px; }
.quick-btn small { color: #888; font-size: 0.8rem; }
.quick-btn:hover {
    transform: translateY(-12px); background: var(--primary-navy);
    box-shadow: 0 15px 35px rgba(10, 38, 71, 0.2) !important;
}
.quick-btn:hover h6, .quick-btn:hover small { color: #fff; }
.quick-btn:hover .icon-box { background: rgba(255,255,255,0.2) !important; color: #fff !important; }
</style>

<?php include_once '../../includes/footer.php'; ?>