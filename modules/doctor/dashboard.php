<?php
include_once '../../includes/header.php';

// ১. ডাক্তার লগইন চেক
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'doctor') {
    echo "<script>window.location.href='../auth/staff-login.php';</script>";
    exit;
}

$doctor_id = $_SESSION['user_id'];
$today = date('Y-m-d');
$day_name = date('l');

// ২. ডাটাবেজ থেকে তথ্য আনা
// ২.১ ডাক্তারের প্রোফাইল তথ্য
$doctor = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM doctors WHERE id = '$doctor_id'"));

// ২.২ পরিসংখ্যান
$today_approved = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(id) as total FROM appointments WHERE doctor_id = '$doctor_id' AND appointment_date = '$today' AND status = 'approved'"))['total'];
$today_completed = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(id) as total FROM appointments WHERE doctor_id = '$doctor_id' AND appointment_date = '$today' AND status = 'completed'"))['total'];
$total_history = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(id) as total FROM appointments WHERE doctor_id = '$doctor_id' AND status = 'completed'"))['total'];

// ২.৩ লাইভ সিরিয়াল তথ্য
$sched_res = mysqli_query($conn, "SELECT current_serial, max_patients FROM doctor_schedules WHERE doctor_id = '$doctor_id' AND day_of_week = '$day_name' AND is_available = 1");
$sched_data = mysqli_fetch_assoc($sched_res);
$live_serial = $sched_data['current_serial'] ?? 0;
$max_patients = $sched_data['max_patients'] ?? 0;

// ২.৪ লেটেস্ট স্টাফ নোটিশ আনা (এরর ফ্রি লজিক)
$announcement_query = mysqli_query($conn, "SELECT * FROM staff_announcements ORDER BY id DESC LIMIT 1");
$announcement = mysqli_fetch_assoc($announcement_query);
$alert_type = 'primary';
if ($announcement) {
    $alert_type = ($announcement['priority'] == 'info') ? 'primary' : (($announcement['priority'] == 'warning') ? 'warning' : 'danger');
}
?>

<div class="container-fluid bg-light py-5" style="min-height: 90vh;">
    <div class="container">
        
        <!-- ১. ডাক্তার প্রোফাইল ও লাইভ সিরিয়াল কার্ড -->
        <div class="row mb-5">
            <div class="col-md-12">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-body p-0">
                        <div class="row g-0">
                            <!-- প্রোফাইল অংশ -->
                            <div class="col-md-3 p-4 text-center text-white d-flex flex-column align-items-center justify-content-center" style="background: linear-gradient(135deg, var(--primary-navy) 0%, #1a4a7a 100%);">
                                <img src="../../assets/images/doctors/<?php echo $doctor['image']; ?>" class="rounded-circle border border-4 border-white shadow mb-3" width="130" height="130" style="object-fit:cover;">
                                <h4 class="fw-bold mb-0 text-white">ডা. <?php echo $doctor['name']; ?></h4>
                                <p class="small opacity-75 mb-0 text-white"><?php echo $doctor['specialization']; ?></p>
                                <span class="badge bg-info mt-2 px-3 rounded-pill text-dark">ID: #<?php echo $doctor_id; ?></span>
                            </div>
                            
                            <!-- লাইভ স্ট্যাটাস অংশ -->
                            <div class="col-md-9 p-4 p-md-5 bg-white">
                                <div class="row align-items-center">
                                    <div class="col-md-7">
                                        <h3 class="fw-bold text-navy">আসসালামু আলাইকুম, আজ আপনার ডিউটি</h3>
                                        <p class="text-muted"><i class="fas fa-hospital-alt me-2"></i>চেম্বার: <?php echo $doctor['chamber_no'] ? $doctor['chamber_no'] : 'রুম সেট নেই'; ?> | <i class="fas fa-calendar-check me-2"></i>তারিখ: <?php echo date('d M, Y (l)'); ?></p>
                                        <hr class="my-4 opacity-10">
                                        <div class="d-flex gap-3">
                                            <a href="today-patients.php" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold"><i class="fas fa-play me-2"></i>রোগী দেখা শুরু করুন</a>
                                        </div>
                                    </div>
                                    <div class="col-md-5 text-center mt-4 mt-md-0 border-start">
                                        <div class="p-4 rounded-4" style="background-color: #fff5f5; border: 2px dashed #ff4757;">
                                            <h6 class="text-uppercase fw-bold text-muted small mb-2">লাইভ সিরিয়াল চলছে</h6>
                                            <h1 class="display-3 fw-bold text-danger mb-0">#<?php echo $live_serial; ?></h1>
                                            <p class="small text-muted mt-2 fw-bold">আজকের লিমিট: <?php echo $max_patients; ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ২. অভ্যন্তরীণ স্টাফ নোটিশ বক্স -->
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

        <!-- ৩. পরিসংখ্যান বক্স গ্রিড -->
        <div class="row g-4 mb-5 text-center">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-4 rounded-4 bg-white border-start border-warning border-5 h-100">
                    <h2 class="fw-bold text-navy mb-0"><?php echo $today_approved; ?></h2>
                    <p class="text-muted small mb-0 fw-bold uppercase">অপেক্ষমাণ (আজ)</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-4 rounded-4 bg-white border-start border-success border-5 h-100">
                    <h2 class="fw-bold text-navy mb-0"><?php echo $today_completed; ?></h2>
                    <p class="text-muted small mb-0 fw-bold uppercase">দেখা হয়েছে (আজ)</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-4 rounded-4 bg-white border-start border-primary border-5 h-100">
                    <h2 class="fw-bold text-navy mb-0"><?php echo $total_history; ?></h2>
                    <p class="text-muted small mb-0 fw-bold uppercase">মোট রোগী রেকর্ড</p>
                </div>
            </div>
        </div>

        <!-- ৪. ম্যানেজমেন্ট টুলস মেনু -->
        <h4 class="fw-bold mb-4 text-navy">ম্যানেজমেন্ট টুলস</h4>
        <div class="row g-4">
            <div class="col-md-4 col-lg-3">
                <a href="today-patients.php" class="quick-tool shadow-sm">
                    <i class="fas fa-list-ol text-primary"></i>
                    <h6>আজকের সিরিয়াল</h6>
                    <small>রোগী দেখুন ও নেক্সট করুন</small>
                </a>
            </div>
            <div class="col-md-4 col-lg-3">
                <a href="patient-history.php" class="quick-tool shadow-sm">
                    <i class="fas fa-file-medical-alt text-success"></i>
                    <h6>রোগীর ইতিহাস</h6>
                    <small>পুরানো দেখা রোগীর তথ্য</small>
                </a>
            </div>
            <div class="col-md-4 col-lg-3">
                <a href="manage-items.php" class="quick-tool shadow-sm">
                    <i class="fas fa-capsules text-danger"></i>
                    <h6>Drug & Test Manager</h6>
                    <small>নতুন ওষুধ ও ল্যাব টেস্ট যোগ</small>
                </a>
            </div>
            <div class="col-md-4 col-lg-3">
                <a href="profile-settings.php" class="quick-tool shadow-sm">
                    <i class="fas fa-user-cog text-warning"></i>
                    <h6>প্রোফাইল সেটিংস</h6>
                    <small>ফি ও চেম্বার তথ্য আপডেট</small>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.text-navy { color: var(--primary-navy); }
.quick-tool {
    background: #fff; padding: 30px 20px; border-radius: 24px;
    display: block; text-align: center; text-decoration: none;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border: 1px solid rgba(0,0,0,0.02); height: 100%;
}
.quick-tool i { font-size: 2.5rem; margin-bottom: 15px; display: block; }
.quick-tool h6 { color: var(--primary-navy); font-weight: 700; margin-bottom: 8px; }
.quick-tool small { color: #888; font-size: 0.8rem; }
.quick-tool:hover {
    transform: translateY(-12px); background-color: var(--primary-navy);
    box-shadow: 0 15px 35px rgba(10, 38, 71, 0.2) !important;
}
.quick-tool:hover h6, .quick-tool:hover small, .quick-tool:hover i { color: #fff !important; }
</style>

<?php include_once '../../includes/footer.php'; ?>