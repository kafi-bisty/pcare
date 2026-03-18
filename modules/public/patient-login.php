<?php
include_once '../../includes/header.php';

// ১. যদি অলরেডি লগইন করা থাকে, তবে সরাসরি ড্যাশবোর্ডে পাঠিয়ে দাও
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'patient') {
    echo "<script>window.location.href='../patient/dashboard.php';</script>";
    exit;
}

// ২. লগইন প্রসেসিং
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // ইমেইল দিয়ে রোগী খোঁজা
    $query = "SELECT * FROM patients WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // পাসওয়ার্ড যাচাই করা
        if (password_verify($password, $user['password'])) {
            // সেশন ডাটা সেট করা
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = 'patient';

            $_SESSION['success'] = "লগইন সফল হয়েছে! স্বাগতম, " . $user['name'];
            
            // ড্যাশবোর্ডে রিডাইরেক্ট
            echo "<script>window.location.href='../patient/dashboard.php';</script>";
            exit;
        } else {
            $_SESSION['error'] = "ভুল পাসওয়ার্ড! আবার চেষ্টা করুন।";
        }
    } else {
        $_SESSION['error'] = "এই ইমেইল দিয়ে কোনো একাউন্ট পাওয়া যায়নি।";
    }
}
?>

<div class="container-fluid bg-light py-5" style="min-height: 80vh;">
    <div class="container">
        <div class="row justify-content-center align-items-center mt-4">
            <div class="col-md-5">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="p-4 text-center text-white" style="background: linear-gradient(135deg, var(--primary-navy), var(--secondary-cyan));">
                        <i class="fas fa-user-circle fa-3x mb-2"></i>
                        <h3 class="fw-bold mb-0">রোগী লগইন</h3>
                        <p class="small opacity-75 mb-0">আপনার একাউন্টে প্রবেশ করুন</p>
                    </div>
                    
                    <div class="card-body p-4 p-md-5 bg-white">
                        <form action="" method="POST">
                            <!-- ইমেইল -->
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-navy">ইমেইল ঠিকানা</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                                    <input type="email" name="email" class="form-control border-start-0 shadow-none" placeholder="example@mail.com" required>
                                </div>
                            </div>

                            <!-- পাসওয়ার্ড -->
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-navy">পাসওয়ার্ড</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                                    <input type="password" name="password" class="form-control border-start-0 shadow-none" placeholder="আপনার পাসওয়ার্ড দিন" required>
                                </div>
                                <div class="text-end mt-2">
                                    <a href="forgot-password.php" class="text-decoration-none small text-muted">পাসওয়ার্ড ভুলে গেছেন?</a>
                                </div>
                            </div>

                            <!-- লগইন বাটন -->
                            <div class="d-grid mb-4">
                                <button type="submit" name="login" class="btn btn-primary btn-lg rounded-pill shadow" style="background-color: var(--secondary-cyan); border: none;">
                                    লগইন করুন <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>

                            <div class="text-center">
                                <p class="small text-muted mb-0">একাউন্ট নেই? <a href="patient-register.php" class="text-decoration-none fw-bold" style="color: var(--primary-navy);">নতুন রেজিস্ট্রেশন করুন</a></p>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <a href="../../index.php" class="text-decoration-none text-muted small"><i class="fas fa-long-arrow-alt-left me-1"></i> হোমপেজে ফিরে যান</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================================================
   ৩. রেজিস্ট্রেশন সফল পপ-আপ স্ক্রিপ্ট (SweetAlert2)
   ========================================================================== -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php if(isset($_SESSION['registration_success']) && $_SESSION['registration_success'] == "yes"): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            title: 'অভিনন্দন! রেজিস্ট্রেশন সফল',
            text: 'আপনার একাউন্ট তৈরি সম্পন্ন। এখন লগইন করে আপনার প্রোফাইল ভিজিট করুন এবং বিশেষজ্ঞ ডাক্তারের সিরিয়াল সংগ্রহ করুন।',
            icon: 'success',
            confirmButtonColor: '#2AA7E5',
            confirmButtonText: '<i class="fas fa-sign-in-alt me-1"></i> ঠিক আছে',
            backdrop: `rgba(10, 38, 71, 0.4)`
        });
    });
    </script>
<?php 
    // পপ-আপ একবার দেখিয়ে সেশন মুছে ফেলা
    unset($_SESSION['registration_success']); 
endif; 
?>

<style>
.form-control:focus { border-color: var(--secondary-cyan); background-color: #fff; }
.text-navy { color: var(--primary-navy); }
</style>

<?php include_once '../../includes/footer.php'; ?>