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

$user_role = $_SESSION['user_role']; // বর্তমান ইউজারের পদবি (admin, manager, accounts)

// ২. পরিসংখ্যান ডাটা আনা
$doctor_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM doctors"))['total'];
$patient_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM patients"))['total'];
$reception_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM receptionists"))['total'];
$total_staff = $doctor_count + $reception_count;
$new_messages = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM contact_messages WHERE status = 'unread'"))['total'];

// ৩. সাম্প্রতিক ৫টি অ্যাক্টিভিটি লগ আনা
$recent_logs = mysqli_query($conn, "SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>ড্যাশবোর্ড | <?php echo ucfirst($user_role); ?> প্যানেল</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --navy: #0A2647; --cyan: #2AA7E5; }
        body { background-color: #F8FAFC; font-family: 'Segoe UI', sans-serif; }
        
        .sidebar { height: 100vh; width: 260px; position: fixed; background: var(--navy); color: white; padding-top: 10px; z-index: 1000; }
        .sidebar-logo { width: 60px; height: 60px; border-radius: 50%; border: 2px solid var(--cyan); background: #fff; object-fit: cover; }
        .sidebar a { color: rgba(255,255,255,0.7); text-decoration: none; padding: 12px 20px; display: block; border-left: 4px solid transparent; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.1); color: white; border-left: 4px solid var(--cyan); }
        
        .main-content { margin-left: 260px; padding: 30px; min-height: 100vh; }
        .card-stat { border: none; border-radius: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .btn-quick { transition: 0.3s; border: 1px solid #eee !important; border-radius: 24px; }
        .btn-quick:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(10, 38, 71, 0.1) !important; background-color: var(--navy) !important; color: #fff !important; }
        .btn-quick:hover i, .btn-quick:hover h6, .btn-quick:hover small { color: #fff !important; }
    </style>
</head>
<body>

   
<!-- সাইডবার শুরু -->
<div class="sidebar shadow">
    <!-- সাইডবার হেডার (লোগো ও নাম) -->
    <div class="text-center py-4 border-bottom border-secondary border-opacity-25 mb-3">
        <img src="<?php echo BASE_URL; ?>assets/images/logo.png" alt="Logo" class="sidebar-logo mb-2">
        <h5 class="fw-bold mb-0 text-white">পেশেন্ট কেয়ার</h5>
        <small class="text-info x-small text-uppercase"><?php echo $user_role; ?> Portal</small>
    </div>
    
    <!-- ২. এইখানে আপনার দেওয়া মেনু ফিল্টারিং কোডটি বসবে -->
    <div class="sidebar-menu">
        <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt me-2"></i>ড্যাশবোর্ড</a>
        
        <a href="manage-accounts.php"><i class="fas fa-calculator me-2"></i>আয়-ব্যয় হিসাব</a>

        <!-- মালিক (Admin) ছাড়া অন্য কারো জন্য এই লিঙ্কগুলো হাইড থাকবে -->
        <?php if($_SESSION['user_role'] == 'admin'): ?>
            <a href="manage-all-staff.php"><i class="fas fa-users-cog me-2"></i>স্টাফ ম্যানেজার</a>
            <a href="activity-logs.php"><i class="fas fa-history me-2"></i>অ্যাক্টিভিটি লগ</a>
            <a href="manage-staff-announcement.php"><i class="fas fa-bullhorn me-2"></i>বিশেষ নোটিশ</a>
            <a href="manage-items.php"><i class="fas fa-pills me-2"></i>ওষুধ ও টেস্ট</a>
            <a href="manage-gallery.php"><i class="fas fa-images me-2"></i>গ্যালারি ম্যানেজ</a>
        <?php endif; ?>
        
        <!-- ম্যানেজার (Manager) যদি বিশেষ কিছু দেখতে পারে (ঐচ্ছিক) -->
        <?php if($_SESSION['user_role'] == 'manager'): ?>
            <a href="messages.php"><i class="fas fa-envelope me-2"></i>ইনবক্স দেখুন</a>
        <?php endif; ?>

        <a href="../auth/logout.php" class="text-danger mt-4"><i class="fas fa-sign-out-alt me-2"></i>লগআউট</a>
    </div>
</div>
<!-- সাইডবার শেষ -->
























    <!-- ২. মেইন কন্টেন্ট -->
    <div class="main-content">
        <h3 class="fw-bold text-navy mb-4">স্বাগতম, আপনার ড্যাশবোর্ড</h3>
        
        <!-- পরিসংখ্যান কার্ডসমূহ -->
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card card-stat p-4 bg-white border-start border-primary border-5">
                    <h6 class="text-muted small fw-bold">মোট স্টাফ</h6>
                    <h2 class="fw-bold text-navy mb-0"><?php echo $total_staff; ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stat p-4 bg-white border-start border-success border-5">
                    <h6 class="text-muted small fw-bold">মোট রোগী</h6>
                    <h2 class="fw-bold text-success mb-0"><?php echo $patient_count; ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stat p-4 bg-white border-start border-info border-5">
                    <h6 class="text-muted small fw-bold">নতুন মেসেজ</h6>
                    <h2 class="fw-bold text-info mb-0"><?php echo $new_messages; ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stat p-4 bg-dark text-white shadow">
                    <h6 class="small fw-bold opacity-75">সিস্টেম স্ট্যাটাস</h6>
                    <h4 class="fw-bold mb-0 text-success">অনলাইন</h4>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- সাম্প্রতিক কার্যক্রম (Activity Log) - শুধুমাত্র এডমিন ও ম্যানেজার দেখবে -->
            <?php if(in_array($user_role, ['admin', 'manager'])): ?>
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-bold mb-0 text-navy"><i class="fas fa-history me-2"></i>সাম্প্রতিক কার্যক্রম</h5>
                    </div>
                    <div class="table-responsive p-3">
                        <table class="table table-sm table-hover small">
                            <tbody>
                                <?php while($log = mysqli_fetch_assoc($recent_logs)): ?>
                                    <tr>
                                        <td class="py-2 border-0">
                                            <strong><?php echo $log['user_name']; ?></strong> 
                                            <span class="text-muted small"><?php echo $log['details']; ?></span>
                                            <div class="text-end x-small opacity-50"><?php echo date('h:i A', strtotime($log['created_at'])); ?></div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 p-5 h-100 text-center">
                    <i class="fas fa-chart-line fa-4x text-light mb-3"></i>
                    <h5 class="text-muted">হিসাবরক্ষক প্যানেলে স্বাগতম</h5>
                    <p class="small text-muted">আপনার জন্য নির্ধারিত টুলসগুলো নিচে দেওয়া হলো।</p>
                </div>
            </div>
            <?php endif; ?>

            <!-- কুইক লিঙ্ক (ডান পাশে) -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                    <h5 class="fw-bold mb-4 text-navy">কুইক লিঙ্ক</h5>
                    <div class="d-grid gap-2">
                        <?php if($user_role == 'admin'): ?>
                            <a href="add-staff-unified.php" class="btn btn-primary rounded-pill py-2 shadow-sm border-0" style="background:var(--navy)">
                                <i class="fas fa-plus-circle me-1"></i> নতুন স্টাফ যোগ করুন
                            </a>
                        <?php endif; ?>
                        
                        <a href="manage-accounts.php" class="btn btn-light rounded-pill py-2 border">
                            <i class="fas fa-calculator me-1 text-primary"></i> আয়-ব্যয় হিসাব দেখুন
                        </a>

                        <?php if(in_array($user_role, ['admin', 'manager'])): ?>
                            <a href="manage-staff-announcement.php" class="btn btn-light rounded-pill py-2 border">
                                <i class="fas fa-bullhorn me-1 text-warning"></i> স্টাফ নোটিশ আপডেট
                            </a>
                            <a href="manage-gallery.php" class="btn btn-light rounded-pill py-2 border">
                                <i class="fas fa-images me-1 text-info"></i> গ্যালারি ম্যানেজ
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ম্যানেজমেন্ট টুলস গ্রিড (নিচে) -->
        <div class="mt-5 pt-2">
            <h4 class="fw-bold mb-4 text-navy">ম্যানেজমেন্ট টুলস</h4>
            <div class="row g-3">
                
                <!-- ১. হিসাব বাটন (Admin, Manager, Accounts সবাই দেখবে) -->
                <div class="col-md-4 col-lg-3">
                    <a href="manage-accounts.php" class="btn btn-white w-100 py-4 shadow-sm btn-quick bg-white text-decoration-none">
                        <i class="fas fa-calculator text-primary fa-2x mb-2 d-block"></i>
                        <h6 class="fw-bold mb-0 text-navy">Hospital Accounts</h6>
                        <small class="text-muted small">আয় ও ব্যয়ের হিসাব</small>
                    </a>
                </div>

                <!-- ২. স্টাফ ম্যানেজার (Admin ও Manager দেখবে) -->
                <?php if(in_array($user_role, ['admin', 'manager'])): ?>
                <div class="col-md-4 col-lg-3">
                    <a href="manage-all-staff.php" class="btn btn-white w-100 py-4 shadow-sm btn-quick bg-white text-decoration-none">
                        <i class="fas fa-users-cog text-warning fa-2x mb-2 d-block"></i>
                        <h6 class="fw-bold mb-0 text-navy">Staff Manager</h6>
                        <small class="text-muted small">মেম্বার তালিকা ও এডিট</small>
                    </a>
                </div>
                <?php endif; ?>

                <!-- ৩. ইনবক্স (Admin ও Manager দেখবে) -->
                <?php if(in_array($user_role, ['admin', 'manager'])): ?>
                <div class="col-md-4 col-lg-3">
                    <a href="messages.php" class="btn btn-white w-100 py-4 shadow-sm btn-quick bg-white text-decoration-none">
                        <i class="fas fa-envelope-open-text text-info fa-2x mb-2 d-block"></i>
                        <h6 class="fw-bold mb-0 text-navy">Patient Inbox</h6>
                        <small class="text-muted small">রোগীদের বার্তা দেখুন</small>
                    </a>
                </div>
                <?php endif; ?>

                <!-- ৪. নতুন মেম্বার যোগ (শুধুমাত্র Admin দেখবে) -->
                <?php if($user_role == 'admin'): ?>
                <div class="col-md-4 col-lg-3">
                    <a href="add-staff-unified.php" class="btn btn-white w-100 py-4 shadow-sm btn-quick bg-white text-decoration-none">
                        <i class="fas fa-user-plus text-success fa-2x mb-2 d-block"></i>
                        <h6 class="fw-bold mb-0 text-navy">Add New Member</h6>
                        <small class="text-muted small">নতুন স্টাফ নিয়োগ</small>
                    </a>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>