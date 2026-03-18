<?php

header("Location: ../auth/staff-login.php");
exit;

include_once '../../config/database.php';
include_once '../../config/functions.php';
include_once '../../config/constants.php';
session_start();

if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM admins WHERE username = '$username'");
    
    if (mysqli_num_rows($query) > 0) {
        $admin = mysqli_fetch_assoc($query);
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['user_role'] = 'admin';
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "ভুল পাসওয়ার্ড!";
        }
    } else {
        $error = "এডমিন ইউজারনেম সঠিক নয়!";
    }
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>এডমিন লগইন | পেশেন্ট কেয়ার</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <style>
        body { background-color: #f4f7f6; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { width: 400px; border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .btn-admin { background-color: #0A2647; color: white; border-radius: 30px; }
    </style>
</head>
<body>
    <div class="card login-card p-4">
        <div class="text-center mb-4">
            <h3 class="fw-bold" style="color: #0A2647;">এডমিন প্যানেল</h3>
            <p class="text-muted small">সিকিউর লগইন</p>
        </div>
        <?php if(isset($error)): ?>
            <div class="alert alert-danger small"><?php echo $error; ?></div>
        <?php endif; ?>
        <form action="" method="POST">
            <div class="mb-3">
                <label class="form-label small">ইউজারনেম</label>
                <input type="text" name="username" class="form-control shadow-none" required>
            </div>
            <div class="mb-4">
                <label class="form-label small">পাসওয়ার্ড</label>
                <input type="password" name="password" class="form-control shadow-none" required>
            </div>
            <div class="d-grid">
                <button type="submit" name="login" class="btn btn-admin btn-lg">লগইন করুন</button>
            </div>
        </form>
    </div>
</body>
</html>