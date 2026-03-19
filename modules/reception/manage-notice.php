<?php
// ১. ডাটাবেজ এবং সেশন চেক
include_once '../../config/database.php';
include_once '../../config/constants.php';
include_once '../../config/functions.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// ২. রিসেপশন লগইন চেক
if (!isset($_SESSION['reception_id']) || $_SESSION['user_role'] != 'reception') {
    header("Location: ../auth/staff-login.php");
    exit;
}

// ৩. নোটিশ আপডেট বা সেভ লজিক
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_notice'])) {
    $new_notice = mysqli_real_escape_string($conn, $_POST['notice_text']);
    
    // চেক করা ১ নম্বর আইডি আছে কি না
    $check_query = mysqli_query($conn, "SELECT id FROM site_notices WHERE id = 1");
    
    if (mysqli_num_rows($check_query) > 0) {
        // যদি থাকে তবে আপডেট করো
        $sql = "UPDATE site_notices SET notice_text = '$new_notice' WHERE id = 1";
    } else {
        // না থাকলে নতুন ইনসার্ট করো
        $sql = "INSERT INTO site_notices (id, notice_text) VALUES (1, '$new_notice')";
    }
    
    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "নোটিশটি সফলভাবে আপডেট করা হয়েছে!";
        header("Location: dashboard.php");
        exit;
    } else {
        $_SESSION['error'] = "ডাটাবেজ এরর: " . mysqli_error($conn);
    }
}

// ৪. বর্তমান নোটিশ ডাটাবেজ থেকে আনা
$notice_res = mysqli_query($conn, "SELECT notice_text FROM site_notices WHERE id = 1");
$notice_data = mysqli_fetch_assoc($notice_res);
$current_text = ($notice_data) ? $notice_data['notice_text'] : "";

include_once '../../includes/header.php';
?>

<div class="container py-5" style="min-height: 80vh;">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold text-navy"><i class="fas fa-bullhorn me-2 text-danger"></i>নোটিশ বোর্ড ম্যানেজমেন্ট</h3>
                <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill btn-sm px-4">ড্যাশবোর্ড</a>
            </div>

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header p-4 text-white text-center" style="background: linear-gradient(135deg, var(--primary-navy), var(--secondary-cyan)); border:none;">
                    <h5 class="mb-0 fw-bold">ওয়েবসাইটের নোটিশ পরিবর্তন করুন</h5>
                </div>
                
                <div class="card-body p-4 p-md-5 bg-white">
                    <form action="" method="POST">
                        <div class="mb-4">
                            <label class="form-label fw-bold text-navy small">নোটিশ টেক্সট লিখুন:</label>
                            <textarea name="notice_text" class="form-control rounded-4 p-3 shadow-sm border-light bg-light" rows="6" required><?php echo $current_text; ?></textarea>
                            <div class="form-text mt-3 text-muted">
                                <i class="fas fa-info-circle me-1 text-info"></i> এখানে যা লিখবেন তা পুরো ওয়েবসাইটের ওপরের নোটিশ বারে দেখা যাবে।
                            </div>
                        </div>

                        <div class="text-end border-top pt-4">
                            <button type="submit" name="update_notice" class="btn btn-primary btn-lg rounded-pill px-5 shadow fw-bold">
                                <i class="fas fa-save me-2"></i> আপডেট এবং সেভ করুন
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.text-navy { color: var(--primary-navy); }
textarea.form-control:focus { background-color: #fff !important; border-color: var(--secondary-cyan) !important; box-shadow: 0 0 15px rgba(42, 167, 229, 0.1) !important; }
</style>

<?php include_once '../../includes/footer.php'; ?>