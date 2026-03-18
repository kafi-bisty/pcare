<?php
// ১. কনফিগ এবং ডাটাবেজ সবার আগে
include_once '../../config/database.php';
include_once '../../config/constants.php';
include_once '../../config/functions.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// এই ভেরিয়েবলটি হেডারকে বলবে রিডাইরেক্ট না করতে (যদি হেডারে লজিক থাকে)
$is_public_page = true; 

$error = "";

// ২. সার্চ লজিক
if(isset($_POST['find_now'])) {
    $appt_id = mysqli_real_escape_string($conn, $_POST['appt_id']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);

    $check_query = "SELECT p.id FROM prescriptions p 
                    JOIN appointments a ON p.appointment_id = a.id 
                    WHERE a.id = '$appt_id' AND a.patient_phone = '$phone'";
    
    $check_result = mysqli_query($conn, $check_query);
    
    if($check_result && mysqli_num_rows($check_result) > 0) {
        $data = mysqli_fetch_assoc($check_result);
        header("Location: view-prescription.php?id=" . $data['id']);
        exit;
    } else {
        $error = "দুঃখিত! আপনার তথ্য সঠিক নয়।";
    }
}

include_once '../../includes/header.php';
?>

<div class="container py-5" style="min-height: 80vh;">
    <div class="row justify-content-center">
        <div class="col-md-5 mt-5">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header p-4 text-center text-white border-0" style="background: linear-gradient(135deg, #0A2647, #2AA7E5);">
                    <h4 class="fw-bold mb-0">প্রেসক্রিপশন খুঁজুন</h4>
                </div>
                <div class="card-body p-4 p-md-5 bg-white">
                    <?php if($error): ?>
                        <div class="alert alert-danger small py-2 mb-4"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="small fw-bold">সিরিয়াল আইডি (#)</label>
                            <input type="number" name="appt_id" class="form-control rounded-pill" placeholder="যেমন: 31" required>
                        </div>
                        <div class="mb-4">
                            <label class="small fw-bold">মোবাইল নম্বর</label>
                            <input type="text" name="phone" class="form-control rounded-pill" placeholder="০১XXXXXXXXX" required>
                        </div>
                        <button type="submit" name="find_now" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow">রিপোর্ট দেখুন</button>
                    </form>
                </div>
            </div>
            <div class="text-center mt-4"><a href="../../index.php" class="text-muted small text-decoration-none">হোমপেজে ফিরে যান</a></div>
        </div>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>