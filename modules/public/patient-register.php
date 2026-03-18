<?php
// ১. কনফিগ এবং ডাটাবেজ সবার আগে
include_once '../../config/database.php';
include_once '../../config/constants.php';
include_once '../../config/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ২. যদি অলরেডি লগইন করা থাকে, তবে ড্যাশবোর্ডে পাঠিয়ে দাও
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'patient') {
    header("Location: ../patient/dashboard.php");
    exit;
}

// ৩. রেজিস্ট্রেশন প্রসেসিং লজিক
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);

    // ভ্যালিডেশন
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "পাসওয়ার্ড দুটি মেলেনি!";
    } else {
        // ইমেইল চেক
        $check_email = mysqli_query($conn, "SELECT id FROM patients WHERE email = '$email'");
        if (mysqli_num_rows($check_email) > 0) {
            $_SESSION['error'] = "এই ইমেইলটি ইতিপূর্বে ব্যবহার করা হয়েছে!";
        } else {
            // পাসওয়ার্ড হ্যাশ করা
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // ডাটাবেজে ইনসার্ট
            $query = "INSERT INTO patients (name, email, phone, password, gender, date_of_birth) 
                      VALUES ('$name', '$email', '$phone', '$hashed_password', '$gender', '$dob')";
            
            if (mysqli_query($conn, $query)) {
                // পপ-আপ দেখানোর জন্য সেশন সেট করা
                $_SESSION['registration_success'] = "yes";
                
                // সফল হলে লগইন পেজে পাঠিয়ে দেওয়া হচ্ছে
                header("Location: patient-login.php");
                exit;
            } else {
                $_SESSION['error'] = "দুঃখিত, কিছু ভুল হয়েছে। " . mysqli_error($conn);
            }
        }
    }
}

// এখন হেডার ইনক্লুড করুন
include_once '../../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden mt-4">
                <div class="card-header p-4 text-center text-white border-0" style="background: linear-gradient(135deg, var(--primary-navy), var(--secondary-cyan));">
                    <h3 class="fw-bold mb-0">রোগী রেজিস্ট্রেশন</h3>
                    <p class="small opacity-75 mb-0">নতুন একাউন্ট তৈরি করতে তথ্য দিন</p>
                </div>
                <div class="card-body p-4 p-md-5 bg-white">
                    
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">পুরো নাম</label>
                            <input type="text" name="name" class="form-control rounded-pill" placeholder="আপনার নাম" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">ইমেইল ঠিকানা</label>
                            <input type="email" name="email" class="form-control rounded-pill" placeholder="mail@example.com" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">মোবাইল নম্বর</label>
                            <input type="text" name="phone" class="form-control rounded-pill" placeholder="017XXXXXXXX" required>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label small fw-bold">লিঙ্গ</label>
                                <select name="gender" class="form-select rounded-pill" required>
                                    <option value="Male">পুরুষ</option>
                                    <option value="Female">মহিলা</option>
                                    <option value="Other">অন্যান্য</option>
                                </select>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label small fw-bold">জন্ম তারিখ</label>
                                <input type="date" name="dob" class="form-control rounded-pill" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">পাসওয়ার্ড</label>
                            <input type="password" name="password" class="form-control rounded-pill" placeholder="পাসওয়ার্ড দিন" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold">পাসওয়ার্ড নিশ্চিত করুন</label>
                            <input type="password" name="confirm_password" class="form-control rounded-pill" placeholder="আবার লিখুন" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="register" class="btn btn-primary btn-lg rounded-pill shadow" style="background-color: var(--secondary-cyan); border: none;">রেজিস্ট্রেশন করুন</button>
                        </div>
                        <div class="text-center mt-3">
                            <p class="small text-muted">ইতিমধ্যে একাউন্ট আছে? <a href="patient-login.php" class="fw-bold text-navy text-decoration-none">লগইন করুন</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>