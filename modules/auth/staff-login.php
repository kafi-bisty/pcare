<?php
// ১. যেকোনো ভুল থাকলে স্ক্রিনে দেখানোর জন্য এই লাইনগুলো বাধ্যতামূলক
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ২. ডাটাবেজ কানেকশন সরাসরি (অন্য কোনো ফাইল ইনক্লুড করা ছাড়াই)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "patient_care_hospital";

// কানেকশন তৈরি
$conn = mysqli_connect($servername, $username, $password, $dbname);

// কানেকশন চেক
if (!$conn) {
    die("<h2 style='color:red; text-align:center; margin-top:50px;'>ডাটাবেজ কানেকশন হচ্ছে না! দয়া করে নিশ্চিত হোন ডাটাবেজের নাম 'patient_care_hospital' কি না। Error: " . mysqli_connect_error() . "</h2>");
}

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// ৩. লগইন প্রসেসিং
$error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $role = $_POST['role']; 
    $user_input = mysqli_real_escape_string($conn, $_POST['username']);
    $pass_input = $_POST['password'];

    // রোল অনুযায়ী টেবিল ও রিডাইরেক্ট পাথ
    if ($role == 'admin' || $role == 'manager' || $role == 'accounts') {
        $table = 'admins';
        $redirect = "../admin/dashboard.php";
    } elseif ($role == 'doctor') {
        $table = 'doctors';
        $redirect = "../doctor/dashboard.php";
    } else {
        $table = 'receptionists';
        $redirect = "../reception/dashboard.php";
    }

    $query = mysqli_query($conn, "SELECT * FROM $table WHERE username = '$user_input'");
    
    if ($query && mysqli_num_rows($query) > 0) {
        $user = mysqli_fetch_assoc($query);
        if (password_verify($pass_input, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $role;
            
            // সেশন আইডি সেট করা
            if($role == 'doctor') $_SESSION['doctor_id'] = $user['id'];
            elseif($role == 'reception') $_SESSION['reception_id'] = $user['id'];
            else $_SESSION['admin_id'] = $user['id'];

            echo "<script>window.location.href='$redirect';</script>";
            exit;
        } else {
            $error = "ভুল পাসওয়ার্ড! আবার চেষ্টা করুন।";
        }
    } else {
        $error = "ইউজারনেমটি পাওয়া যায়নি!";
    }
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>স্টাফ লগইন | পেশেন্ট কেয়ার হাসপাতাল</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --navy: #0A2647; --cyan: #2AA7E5; }
        body { 
            background: linear-gradient(135deg, var(--navy) 0%, #1a4a7a 100%);
            height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px;
            font-family: 'Segoe UI', sans-serif;
        }
        .login-card { 
            width: 100%; max-width: 400px; background: #fff; border-radius: 25px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.3); overflow: hidden;
        }
        .card-header { padding: 30px 20px 10px; text-align: center; background: #fff; border: none; }
        .logo-img { width: 80px; height: 80px; border-radius: 50%; border: 3px solid var(--cyan); margin-bottom: 10px; object-fit: cover; }
        .form-control, .form-select { border-radius: 10px; padding: 12px; background: #f8fafc; border: 1.5px solid #eee; }
        .btn-login { 
            background: linear-gradient(45deg, var(--navy), var(--cyan)); 
            color: #fff; border: none; padding: 14px; border-radius: 12px; 
            font-weight: 700; width: 100%; transition: 0.3s;
        }
        .btn-login:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.2); color: #fff; }
    </style>
</head>
<body>

<div class="login-card">
    <div class="card-header">
        <!-- লোগো সরাসরি পাথ দেওয়া হলো যাতে এরর না হয় -->
        <img src="../../assets/images/logo.png" alt="Logo" class="logo-img">
        <h4 class="fw-bold text-navy mb-0">পেশেন্ট কেয়ার</h4>
        <p class="text-info small fw-bold text-uppercase" style="font-size: 10px; letter-spacing: 1px;">হাসপাতাল এন্ড ডায়াগনস্টিক সেন্টার</p>
    </div>

    <div class="card-body p-4 p-md-5 pt-0">
        <?php if($error): ?>
            <div class="alert alert-danger py-2 small text-center"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="mb-3">
                <label class="small fw-bold mb-1 opacity-75">পদবি নির্বাচন করুন</label>
                <select name="role" class="form-select" required>
                    <option value="reception">রিসেপশনিস্ট (Receptionist)</option>
                    <option value="doctor">ডাক্তার (Doctor)</option>
                    <option value="admin">মালিক (Admin)</option>
                    <option value="manager">ম্যানেজার (Manager)</option>
                    <option value="accounts">হিসাবরক্ষক (Accounts)</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="small fw-bold mb-1 opacity-75">ইউজারনেম</label>
                <input type="text" name="username" class="form-control" placeholder="Username" required>
            </div>
            <div class="mb-4">
                <label class="small fw-bold mb-1 opacity-75">পাসওয়ার্ড</label>
                <input type="password" name="password" class="form-control" placeholder="******" required>
            </div>
            <button type="submit" name="login" class="btn btn-login shadow">লগইন করুন <i class="fas fa-sign-in-alt ms-2"></i></button>
        </form>
    </div>
    <div class="text-center pb-4">
        <a href="../../index.php" class="text-muted small text-decoration-none"><i class="fas fa-arrow-left me-1"></i> হোমপেজে ফিরে যান</a>
    </div>
</div>

</body>
</html>