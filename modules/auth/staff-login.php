<?php
// ১. সকল এরর দেখার জন্য (ডিবাগিং মোড)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ২. সেশন শুরু
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// ৩. প্রয়োজনীয় ফাইল লোড (পাথ চেক করুন)
// আপনার ফোল্ডার স্ট্রাকচার অনুযায়ী পাথ ঠিক আছে কি না নিশ্চিত হোন
require_once '../../config/database.php'; 
require_once '../../config/constants.php';

// ৪. যদি অলরেডি লগইন করা থাকে, তবে রিডাইরেক্ট
if (isset($_SESSION['user_role'])) {
    $role = $_SESSION['user_role'];
    if(in_array($role, ['admin', 'manager', 'accounts'])) {
        header("Location: ../admin/dashboard.php");
    } elseif($role == 'doctor') {
        header("Location: ../doctor/dashboard.php");
    } elseif($role == 'reception') {
        header("Location: ../reception/dashboard.php");
    }
    exit;
}

$error = "";

// ৫. লগইন প্রসেসিং
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $role = $_POST['role']; 
    $user_input = mysqli_real_escape_string($conn, $_POST['username']);
    $pass_input = $_POST['password'];

    // রোল অনুযায়ী টেবিল নির্ধারণ
    if (in_array($role, ['admin', 'manager', 'accounts'])) {
        $table = 'admins';
    } elseif ($role == 'doctor') {
        $table = 'doctors';
    } else {
        $table = 'receptionists';
    }

    // ডাটাবেজ কুয়েরি
    $sql = "SELECT * FROM $table WHERE username = '$user_input'";
    if($table == 'admins') { $sql .= " AND role = '$role'"; }
    
    $query = mysqli_query($conn, $sql);
    
    if ($query && mysqli_num_rows($query) > 0) {
        $user = mysqli_fetch_assoc($query);
        if (password_verify($pass_input, $user['password'])) {
            // সেশন সেট করা
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $role;
            
            if($role == 'doctor') {
                $_SESSION['doctor_id'] = $user['id'];
                header("Location: ../doctor/dashboard.php");
            } elseif($role == 'reception') {
                $_SESSION['reception_id'] = $user['id'];
                header("Location: ../reception/dashboard.php");
            } else {
                $_SESSION['admin_id'] = $user['id'];
                header("Location: ../admin/dashboard.php");
            }
            exit;
        } else {
            $error = "ভুল পাসওয়ার্ড! আবার চেষ্টা করুন।";
        }
    } else {
        $error = "ইউজারনেমটি এই পদের জন্য সঠিক নয়!";
    }
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>স্টাফ লগইন | পেশেন্ট কেয়ার</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --navy: #0A2647; --cyan: #2AA7E5; }
        body { 
            background: linear-gradient(135deg, var(--navy) 0%, #1a4a7a 100%);
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
            font-family: 'Segoe UI', sans-serif; margin: 0;
        }
        .login-card { width: 100%; max-width: 400px; background: #fff; border-radius: 25px; box-shadow: 0 15px 35px rgba(0,0,0,0.3); padding: 40px 30px; }
        .logo-img { width: 80px; display: block; margin: 0 auto 15px; border-radius: 50%; border: 3px solid var(--cyan); padding: 2px; }
        .btn-login { background: linear-gradient(45deg, var(--navy), var(--cyan)); color: #fff; border: none; padding: 12px; border-radius: 12px; width: 100%; font-weight: bold; }
    </style>
</head>
<body>

<div class="login-card">
    <div class="text-center mb-4">
        <img src="<?php echo BASE_URL; ?>assets/images/logo.png" alt="Logo" class="logo-img">
        <h4 class="fw-bold text-navy">স্টাফ লগইন</h4>
        <p class="text-muted small">পেশেন্ট কেয়ার হাসপাতাল</p>
    </div>

    <?php if($error): ?>
        <div class="alert alert-danger py-2 small text-center"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="small fw-bold opacity-75">আপনার পদবি</label>
            <select name="role" class="form-select shadow-none" required>
                <option value="reception">রিসেপশনিস্ট</option>
                <option value="doctor">ডাক্তার</option>
                <option value="admin">মালিক (Admin)</option>
                <option value="manager">ম্যানেজার</option>
                <option value="accounts">হিসাবরক্ষক</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="small fw-bold opacity-75">ইউজারনেম</label>
            <input type="text" name="username" class="form-control shadow-none" required>
        </div>
        <div class="mb-4">
            <label class="small fw-bold opacity-75">পাসওয়ার্ড</label>
            <input type="password" name="password" class="form-control shadow-none" required>
        </div>
        <button type="submit" name="login" class="btn btn-login shadow">লগইন করুন</button>
    </form>
</div>

</body>
</html>