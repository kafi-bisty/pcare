<?php
session_start();
include_once '../../config/database.php';
include_once '../../config/constants.php';

// এডমিন চেক (লগইন না থাকলে লগইন পেজে পাঠিয়ে দেবে)
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$message = "";

// স্টাফ যোগ করার লজিক (বাটন ক্লিক করলে যা হবে)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_account'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = $_POST['password'];

    // ১. ইউজারনেম আগে থেকে আছে কিনা চেক করা
    $check_user = mysqli_query($conn, "SELECT id FROM receptionists WHERE username = '$username'");
    
    if (mysqli_num_rows($check_user) > 0) {
        $_SESSION['error'] = "দুঃখিত, এই ইউজারনেমটি ইতিমধ্যে ব্যবহৃত হয়েছে!";
    } else {
        // ২. পাসওয়ার্ডটি হ্যাশ (Hash) করা যাতে ডাটাবেজে নিরাপদ থাকে
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // ৩. ডাটাবেজে ইনসার্ট করা
        $query = "INSERT INTO receptionists (name, username, password, phone) 
                  VALUES ('$name', '$username', '$hashed_password', '$phone')";

        if (mysqli_query($conn, $query)) {
            $_SESSION['success'] = "অভিনন্দন! স্টাফ একাউন্ট এবং পাসওয়ার্ড সফলভাবে তৈরি হয়েছে।";
            header("Location: manage-receptionists.php");
            exit;
        } else {
            $_SESSION['error'] = "দুঃখিত, ডাটাবেজে সেভ করার সময় কোনো সমস্যা হয়েছে।";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>স্টাফ একাউন্ট তৈরি | এডমিন প্যানেল</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { height: 100vh; width: 250px; position: fixed; background: #0A2647; color: white; padding-top: 20px; }
        .sidebar a { color: white; text-decoration: none; padding: 15px 25px; display: block; }
        .sidebar a:hover { background: #1a4a7a; }
        .main-content { margin-left: 250px; padding: 40px; }
        .card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .btn-navy { background-color: #0A2647; color: white; border-radius: 30px; transition: 0.3s; }
        .btn-navy:hover { background-color: #2AA7E5; color: white; }
    </style>
</head>
<body>

    <!-- সাইডবার -->
    <div class="sidebar">
        <h4 class="text-center fw-bold mb-4">Admin Panel</h4>
        <a href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>ড্যাশবোর্ড</a>
        <a href="manage-doctors.php"><i class="fas fa-user-md me-2"></i>ডাক্তার ম্যানেজমেন্ট</a>
        <a href="add-receptionist.php" class="bg-primary"><i class="fas fa-user-plus me-2"></i>রিসেপশন স্টাফ যোগ</a>
        <a href="manage-receptionists.php"><i class="fas fa-users-cog me-2"></i>স্টাফ তালিকা</a>
        <a href="../auth/logout.php" class="text-danger mt-5"><i class="fas fa-sign-out-alt me-2"></i>লগআউট</a>
    </div>

    <!-- মেইন কন্টেন্ট -->
    <div class="main-content">
        <h3 class="fw-bold mb-4" style="color: #0A2647;">নতুন স্টাফ একাউন্ট তৈরি করুন</h3>
        
        <div class="row">
            <div class="col-md-7">
                <div class="card p-4">
                    <!-- এরর বা সাকসেস মেসেজ -->
                    <?php if(isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger py-2 small"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">স্টাফের পুরো নাম</label>
                                <input type="text" name="name" class="form-control" placeholder="যেমন: সাকিব আহমেদ" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">ইউজারনেম (লগইন আইডি)</label>
                                <input type="text" name="username" class="form-control" placeholder="যেমন: staff_sakib" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold">মোবাইল নম্বর</label>
                                <input type="text" name="phone" class="form-control" placeholder="০১XXXXXXXXX" required>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-primary">লগইন পাসওয়ার্ড সেট করুন</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                    <input type="password" name="password" class="form-control" placeholder="গোপন পাসওয়ার্ড দিন" required>
                                </div>
                                <div class="form-text small">এই পাসওয়ার্ডটি ব্যবহার করেই স্টাফ তার প্যানেলে লগইন করতে পারবে।</div>
                            </div>

                            <div class="col-12 mt-4 text-center">
                                <!-- আপনার কাঙ্ক্ষিত সাবমিট বাটন -->
                                <button type="submit" name="create_account" class="btn btn-navy btn-lg px-5 shadow">
                                    <i class="fas fa-save me-2"></i> পাসওয়ার্ড ও একাউন্ট তৈরি করুন
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card p-4 bg-light border-0 shadow-none">
                    <h6 class="fw-bold"><i class="fas fa-lightbulb text-warning me-2"></i> মনে রাখবেন:</h6>
                    <ul class="small text-muted mt-2">
                        <li>ইউজারনেমটি অবশ্যই ইউনিক হতে হবে।</li>
                        <li>পাসওয়ার্ডটি স্টাফকে জানিয়ে দিতে হবে যাতে সে লগইন করতে পারে।</li>
                        <li>একবার একাউন্ট তৈরি হয়ে গেলে স্টাফ তার নিজের প্যানেল থেকে পাসওয়ার্ড পরিবর্তন করে নিতে পারবে।</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

</body>
</html>