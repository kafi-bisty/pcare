<?php
// এরর দেখার জন্য (কাজ শেষে মুছে দিবেন)
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once '../../includes/header.php';

// যদি আগে থেকেই লগইন করা থাকে
if (isset($_SESSION['reception_id'])) {
    echo "<script>window.location.href='dashboard.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // ডাটাবেজে ইউজার খোঁজা
    $query = "SELECT * FROM receptionists WHERE username = '$username'";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // পাসওয়ার্ড চেক
        if (password_verify($password, $user['password'])) {
            $_SESSION['reception_id'] = $user['id'];
            $_SESSION['reception_name'] = $user['name'];
            $_SESSION['user_role'] = 'reception';
            
            $_SESSION['success'] = "রিসেপশন প্যানেলে স্বাগতম!";
            echo "<script>window.location.href='dashboard.php';</script>";
            exit;
        } else {
            $_SESSION['error'] = "পাসওয়ার্ড ভুল!";
        }
    } else {
        $_SESSION['error'] = "ইউজারনেম পাওয়া যায়নি!";
    }
}
?>

<div class="container py-5" style="min-height: 70vh;">
    <div class="row justify-content-center mt-5">
        <div class="col-md-5">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="p-4 text-center text-white" style="background-color: var(--primary-navy);">
                    <i class="fas fa-concierge-bell fa-3x mb-2"></i>
                    <h3 class="fw-bold mb-0">রিসেপশন লগইন</h3>
                </div>
                <div class="card-body p-4 p-md-5 bg-white">
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">ইউজারনেম</label>
                            <input type="text" name="username" class="form-control rounded-pill shadow-none" placeholder="reception" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold">পাসওয়ার্ড</label>
                            <input type="password" name="password" class="form-control rounded-pill shadow-none" placeholder="******" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="login" class="btn btn-primary btn-lg rounded-pill" style="background-color: var(--secondary-cyan); border: none;">প্রবেশ করুন</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="text-center mt-3">
                <a href="../../index.php" class="text-decoration-none text-muted small"><i class="fas fa-arrow-left me-1"></i> হোমপেজে ফিরে যান</a>
            </div>
        </div>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>