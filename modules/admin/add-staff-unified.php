<?php
// ১. কনফিগ এবং সেশন সবার আগে
include_once '../../config/database.php';
include_once '../../config/constants.php';
include_once '../../config/functions.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// এডমিন চেক
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/staff-login.php");
    exit;
}

// ২. ফর্ম সাবমিট লজিক
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_member'])) {
    $role = $_POST['role'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $nid = mysqli_real_escape_string($conn, $_POST['nid']);
    $joining_date = mysqli_real_escape_string($conn, $_POST['joining_date']);

    // --- ৩. ডুপ্লিকেট চেক (ইউনিক ইউজারনেম) ---
    $check_admin = mysqli_query($conn, "SELECT id FROM admins WHERE username = '$username'");
    $check_doctor = mysqli_query($conn, "SELECT id FROM doctors WHERE username = '$username'");
    $check_reception = mysqli_query($conn, "SELECT id FROM receptionists WHERE username = '$username'");

    if (mysqli_num_rows($check_admin) > 0 || mysqli_num_rows($check_doctor) > 0 || mysqli_num_rows($check_reception) > 0) {
        $_SESSION['error'] = "দুঃখিত! '$username' ইউজারনেমটি ইতিমধ্যে ব্যবহৃত হয়েছে।";
        header("Location: add-staff-unified.php");
        exit;
    }

    // --- ৪. রোল অনুযায়ী কুয়েরি তৈরি ---
    $query = "";

    // এডমিন, ম্যানেজার এবং হিসাবরক্ষক একই টেবিলে যাবে (রোল আলাদা হবে)
    if ($role == 'admin' || $role == 'manager' || $role == 'accounts') {
        $query = "INSERT INTO admins (name, username, password, nid, joining_date, role) VALUES ('$name', '$username', '$password', '$nid', '$joining_date', '$role')";
    } 
    // ডাক্তারদের জন্য
    elseif ($role == 'doctor') {
        $spec = mysqli_real_escape_string($conn, $_POST['specialization'] ?? '');
        $qual = mysqli_real_escape_string($conn, $_POST['qualification'] ?? '');
        $fee = mysqli_real_escape_string($conn, $_POST['fee'] ?? 0);
        $query = "INSERT INTO doctors (name, username, password, nid, joining_date, specialization, qualification, fee, status) VALUES ('$name', '$username', '$password', '$nid', '$joining_date', '$spec', '$qual', '$fee', 'active')";
    } 
    // রিসেপশনিস্টদের জন্য
    elseif ($role == 'reception') {
        $phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
        $query = "INSERT INTO receptionists (name, username, password, nid, joining_date, phone, status) VALUES ('$name', '$username', '$password', '$nid', '$joining_date', '$phone', 'active')";
    }

    // --- ৫. এক্সিকিউশন ---
    if (!empty($query) && mysqli_query($conn, $query)) {
        log_activity($conn, "ADD", "নতুন $role ($name) যোগ করা হয়েছে।");
        $_SESSION['success'] = "সফলভাবে $role একাউন্ট তৈরি হয়েছে!";
        header("Location: manage-all-staff.php");
        exit;
    } else {
        $_SESSION['error'] = "ত্রুটি: সঠিক তথ্য প্রদান করুন বা ডাটাবেজ চেক করুন।";
        header("Location: add-staff-unified.php");
        exit;
    }
}

include_once '../../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header p-4 text-white text-center" style="background: linear-gradient(135deg, var(--primary-navy), var(--secondary-cyan)); border:none;">
                    <h3 class="fw-bold mb-0">নতুন স্টাফ একাউন্ট তৈরি</h3>
                    <p class="small opacity-75 mb-0">হাসপাতালের সব রোলের জন্য একটি ফরম</p>
                </div>
                
                <div class="card-body p-4 p-md-5 bg-white">
                    <?php if(isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger rounded-4 py-2 small mb-4">
                            <i class="fas fa-exclamation-triangle me-2"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="row g-4">
                            <!-- পদবি সিলেকশন (সব রোল সহ) -->
                            <div class="col-md-12">
                                <label class="form-label fw-bold text-navy small">পদবি (Role) নির্বাচন করুন</label>
                                <select name="role" id="roleSelect" class="form-select border-primary shadow-none" required>
                                    <option value="">নির্বাচন করুন...</option>
                                    <option value="admin">ওনার / এডমিন (Admin)</option>
                                    <option value="manager">ম্যানেজার (Manager)</option>
                                    <option value="accounts">হিসাবরক্ষক (Accounts)</option>
                                    <option value="doctor">ডাক্তার (Doctor)</option>
                                    <option value="reception">রিসেপশনিস্ট (Receptionist)</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold">পুরো নাম</label>
                                <input type="text" name="name" class="form-control rounded-3 shadow-none" placeholder="নাম লিখুন" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">এনআইডি (NID)</label>
                                <input type="text" name="nid" class="form-control rounded-3 shadow-none" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">ইউজারনেম (Login ID)</label>
                                <input type="text" name="username" class="form-control rounded-3 shadow-none" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">পাসওয়ার্ড সেট করুন</label>
                                <input type="password" name="password" class="form-control rounded-3 shadow-none" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">যোগদানের তারিখ</label>
                                <input type="date" name="joining_date" class="form-control rounded-3 shadow-none" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>

                            <!-- স্পেশাল ইনপুট (ডাক্তার বা রিসেপশন হলে দেখাবে) -->
                            <div class="col-md-12 d-none" id="receptionField">
                                <label class="form-label small fw-bold text-primary">মোবাইল নম্বর (রিসেপশন)</label>
                                <input type="text" name="phone" class="form-control rounded-3 shadow-none">
                            </div>

                            <div id="doctorFields" class="row g-4 d-none mt-1">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-primary">বিভাগ</label>
                                    <input type="text" name="specialization" class="form-control rounded-3 shadow-none">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-primary">যোগ্যতা</label>
                                    <input type="text" name="qualification" class="form-control rounded-3 shadow-none">
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-5 pt-3 border-top">
                            <button type="submit" name="add_member" class="btn btn-primary btn-lg rounded-pill px-5 shadow fw-bold">একাউন্ট তৈরি করুন</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('roleSelect').addEventListener('change', function() {
    var role = this.value;
    document.getElementById('doctorFields').classList.toggle('d-none', role !== 'doctor');
    document.getElementById('receptionField').classList.toggle('d-none', role !== 'reception');
});
</script>

<?php include_once '../../includes/footer.php'; ?>