<?php
// ডাটাবেজ এবং অন্যান্য ফাইল আগে ইনক্লুড করুন (হেডারের আগে)
include_once '../../config/database.php';
include_once '../../config/constants.php';
include_once '../../config/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// লগইন প্রসেসিং (হেডার প্রিন্ট হওয়ার আগে)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM doctors WHERE username = '$username'");
    
    if (mysqli_num_rows($query) > 0) {
        $doctor = mysqli_fetch_assoc($query);
        if (password_verify($password, $doctor['password'])) {
            $_SESSION['doctor_id'] = $doctor['id'];
            $_SESSION['doctor_name'] = $doctor['name'];
            $_SESSION['user_role'] = 'doctor';
            
            $_SESSION['success'] = "ডাক্তার প্যানেলে স্বাগতম!";
            // হেডার এরর এড়াতে জাভাস্ক্রিপ্ট দিয়ে রিডাইরেক্ট করা হচ্ছে
            echo "<script>window.location.href='dashboard.php';</script>";
            exit;
        } else {
            $_SESSION['error'] = "ভুল পাসওয়ার্ড!";
        }
    } else {
        $_SESSION['error'] = "ইউজারনেম সঠিক নয়!";
    }
}

// এখন হেডার ইনক্লুড করুন
include_once '../../includes/header.php';
?>

<div class="container py-5" style="min-height: 70vh;">
    <div class="row justify-content-center mt-5">
        <div class="col-md-5">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="p-4 text-center text-white" style="background-color: var(--primary-navy);">
                    <i class="fas fa-user-md fa-3x mb-2"></i>
                    <h3 class="fw-bold mb-0">ডাক্তার লগইন</h3>
                </div>
                <div class="card-body p-4 p-md-5 bg-white">
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">ইউজারনেম</label>
                            <input type="text" name="username" class="form-control rounded-pill shadow-none" placeholder="doctor1" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold">পাসওয়ার্ড</label>
                            <input type="password" name="password" class="form-control rounded-pill shadow-none" placeholder="******" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="login" class="btn btn-primary btn-lg rounded-pill" style="background-color: var(--secondary-cyan); border: none;">লগইন করুন</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>