<?php
// ১. কনফিগ এবং সেশন চেক (হেডারের আগে)
include_once '../../config/database.php';
include_once '../../config/constants.php';
include_once '../../config/functions.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['reception_id']) || $_SESSION['user_role'] != 'reception') {
    header("Location: ../auth/staff-login.php");
    exit;
}

// ২. রোগী রেজিস্ট্রেশন লজিক
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register_patient'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $age = mysqli_real_escape_string($conn, $_POST['age']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    
    // ফোন নম্বরকে ডিফল্ট পাসওয়ার্ড হিসেবে সেট করা (যাতে রোগী পরে লগইন করতে পারে)
    $password = password_hash($phone, PASSWORD_DEFAULT);

    // জন্ম তারিখ বের করা (বয়স থেকে আনুমানিক সাল বের করা)
    $dob = date('Y-m-d', strtotime("-$age years"));

    // আগে থেকে এই ফোন নম্বর আছে কি না চেক করা
    $check = mysqli_query($conn, "SELECT id FROM patients WHERE phone = '$phone'");
    
    if (mysqli_num_rows($check) > 0) {
        $_SESSION['error'] = "এই মোবাইল নম্বরটি ইতিমধ্যে রেজিস্টার্ড আছে!";
    } else {
        $query = "INSERT INTO patients (name, phone, email, password, gender, date_of_birth, address) 
                  VALUES ('$name', '$phone', '$email', '$password', '$gender', '$dob', '$address')";
        
        if (mysqli_query($conn, $query)) {
            $_SESSION['success'] = "নতুন রোগী সফলভাবে রেজিস্টার করা হয়েছে! (ডিফল্ট পাসওয়ার্ড: মোবাইল নম্বর)";
            header("Location: dashboard.php");
            exit;
        } else {
            $_SESSION['error'] = "দুঃখিত, ডাটা সেভ করা যায়নি।";
        }
    }
}

include_once '../../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- হেডার ও ব্যাক বাটন -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold text-navy mb-0"><i class="fas fa-user-plus me-2 text-primary"></i>সরাসরি রোগী রেজিস্ট্রেশন</h3>
                <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill px-4 btn-sm">ড্যাশবোর্ড</a>
            </div>

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header p-4 text-white text-center" style="background: linear-gradient(135deg, var(--primary-navy), var(--secondary-cyan)); border: none;">
                    <h5 class="mb-0 fw-bold">রোগীর তথ্য সংগ্রহ ফরম</h5>
                    <p class="small mb-0 opacity-75">হাসপাতালে সরাসরি আসা রোগীদের জন্য</p>
                </div>
                
                <div class="card-body p-4 p-md-5 bg-white">
                    <form action="" method="POST">
                        <div class="row g-4">
                            <!-- নাম -->
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">রোগীর পুরো নাম</label>
                                <input type="text" name="name" class="form-control rounded-3" placeholder="যেমন: মোঃ রহিম উদ্দিন" required>
                            </div>

                            <!-- ফোন ও ইমেইল -->
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">মোবাইল নম্বর</label>
                                <input type="text" name="phone" class="form-control rounded-3" placeholder="017XXXXXXXX" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">ইমেইল (ঐচ্ছিক)</label>
                                <input type="email" name="email" class="form-control rounded-3" placeholder="patient@mail.com">
                            </div>

                            <!-- বয়স ও লিঙ্গ -->
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">বর্তমান বয়স</label>
                                <input type="number" name="age" class="form-control rounded-3" placeholder="যেমন: ৩০" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">লিঙ্গ</label>
                                <select name="gender" class="form-select rounded-3" required>
                                    <option value="">নির্বাচন করুন</option>
                                    <option value="Male">পুরুষ (Male)</option>
                                    <option value="Female">মহিলা (Female)</option>
                                    <option value="Other">অন্যান্য (Other)</option>
                                </select>
                            </div>

                            <!-- ঠিকানা -->
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">পুরো ঠিকানা</label>
                                <textarea name="address" class="form-control rounded-3" rows="2" placeholder="গ্রাম, ডাকঘর, উপজেলা, জেলা..."></textarea>
                            </div>
                        </div>

                        <!-- সাবমিট বাটন -->
                        <div class="text-end mt-5 pt-3 border-top">
                            <button type="reset" class="btn btn-light rounded-pill px-4 me-2">রিসেট</button>
                            <button type="submit" name="register_patient" class="btn btn-primary btn-lg rounded-pill px-5 shadow">
                                <i class="fas fa-save me-2"></i> তথ্য সেভ করুন
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- টিপস বক্স -->
            <div class="alert alert-info border-0 shadow-sm rounded-4 mt-4 small">
                <i class="fas fa-info-circle me-2"></i> 
                <b>দ্রষ্টব্য:</b> এই পদ্ধতিতে রেজিস্ট্রেশন করলে রোগীর মোবাইল নম্বরটিই তার ডিফল্ট পাসওয়ার্ড হিসেবে সেট হবে। রোগী পরবর্তীতে এই নম্বর ব্যবহার করে তার নিজস্ব প্যানেলে লগইন করতে পারবেন।
            </div>
        </div>
    </div>
</div>

<style>
.text-navy { color: var(--primary-navy); }
.form-control:focus, .form-select:focus { border-color: var(--secondary-cyan); box-shadow: 0 0 0 4px rgba(42, 167, 229, 0.1); }
</style>

<?php include_once '../../includes/footer.php'; ?>