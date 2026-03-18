<?php
// ১. ডাটাবেজ এবং সেশন সবার আগে (Redirect এরর এড়াতে)
include_once '../../config/database.php';
include_once '../../config/constants.php';
include_once '../../config/functions.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// ২. রিসেপশন লগইন চেক
if (!isset($_SESSION['reception_id']) || $_SESSION['user_role'] != 'reception') {
    header("Location: ../auth/staff-login.php");
    exit;
}

// ৩. নোটিশ আপডেট লজিক
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_notice'])) {
    $new_notice = mysqli_real_escape_string($conn, $_POST['notice_text']);
    
    // ডাটাবেজে আপডেট করা (আইডি ১ এর জন্য)
    $update_query = "UPDATE site_notices SET notice_text = '$new_notice' WHERE id = 1";
    
    if (mysqli_query($conn, $update_query)) {
        $_SESSION['success'] = "নোটিশটি সফলভাবে আপডেট করা হয়েছে!";
        // বাটন চাপলে ড্যাশবোর্ডে ফিরে যাবে
        header("Location: dashboard.php");
        exit;
    } else {
        $_SESSION['error'] = "দুঃখিত, নোটিশ আপডেট করা যায়নি।";
    }
}

// ৪. বর্তমান নোটিশ ডাটাবেজ থেকে আনা
$notice_res = mysqli_query($conn, "SELECT notice_text FROM site_notices WHERE id = 1");
$notice_data = mysqli_fetch_assoc($notice_res);

include_once '../../includes/header.php';
?>

<div class="container py-5" style="min-height: 80vh;">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- হেডার এবং ব্যাক বাটন -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold text-navy"><i class="fas fa-bullhorn me-2 text-danger"></i>নোটিশ বোর্ড ম্যানেজমেন্ট</h3>
                <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill btn-sm px-4 shadow-sm">
                    <i class="fas fa-arrow-left me-1"></i> ড্যাশবোর্ড
                </a>
            </div>

            <!-- নোটিশ এডিট কার্ড -->
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header p-4 text-white text-center" style="background: linear-gradient(135deg, var(--primary-navy), var(--secondary-cyan)); border:none;">
                    <h5 class="mb-0 fw-bold">ওয়েবসাইটের নোটিশ পরিবর্তন করুন</h5>
                    <p class="small mb-0 opacity-75">এখানে যা লিখবেন তা সবার উপরে স্ক্রল করবে</p>
                </div>
                
                <div class="card-body p-4 p-md-5 bg-white">
                    <form action="" method="POST">
                        <div class="mb-4">
                            <label class="form-label fw-bold text-navy small">বর্তমান নোটিশ টেক্সট:</label>
                            <textarea name="notice_text" class="form-control rounded-4 p-3 shadow-sm border-light bg-light" rows="6" placeholder="আপনার নোটিশটি এখানে লিখুন..." required><?php echo $notice_data['notice_text']; ?></textarea>
                            <div class="form-text mt-3 text-muted">
                                <i class="fas fa-info-circle me-1 text-info"></i> 
                                <b>টিপস:</b> জরুরি ফোন নম্বর বা নতুন সেবার ঘোষণা এখানে দিতে পারেন।
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