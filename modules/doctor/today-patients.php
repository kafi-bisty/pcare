<?php
// ১. ডাটাবেজ এবং কনফিগ ফাইল আগে ইনক্লুড করুন (কোনো HTML এর আগে)
include_once '../../config/database.php';
include_once '../../config/constants.php';
include_once '../../config/functions.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// ২. ডাক্তার লগইন চেক (এটি হেডারের আগে থাকতে হবে)
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'doctor') {
    header("Location: ../auth/staff-login.php"); 
    exit;
}

$doctor_id = $_SESSION['user_id'];
$today = date('Y-m-d');
$day_name = date('l');

// ৩. শুধু সিরিয়াল বাড়ানোর লজিক (এটিও হেডারের আগে থাকতে হবে)
if (isset($_GET['action']) && $_GET['action'] == 'mark_done') {
    $appt_id = mysqli_real_escape_string($conn, $_GET['appt_id']);
    
    // অ্যাপয়েন্টমেন্ট সম্পন্ন করা
    mysqli_query($conn, "UPDATE appointments SET status = 'completed' WHERE id = '$appt_id'");
    
    // লাইভ সিরিয়াল আপডেট করা
    mysqli_query($conn, "UPDATE doctor_schedules SET current_serial = current_serial + 1 
                         WHERE doctor_id = '$doctor_id' AND day_of_week = '$day_name' AND is_available = 1");
    
    $_SESSION['success'] = "রোগী দেখা সম্পন্ন হয়েছে! সিরিয়াল আপডেট করা হয়েছে।";
    
    // সফলভাবে আপডেটের পর রিডাইরেক্ট (এখন আর এরর আসবে না)
    header("Location: today-patients.php"); 
    exit;
}

// ৪. এখন হেডার ইনক্লুড করুন (সব লজিক শেষ হওয়ার পর)
include_once '../../includes/header.php';

// ৫. আজকের অনুমোদিত রোগীদের তালিকা আনা
$query = mysqli_query($conn, "SELECT * FROM appointments WHERE doctor_id = '$doctor_id' AND appointment_date = '$today' AND status = 'approved' ORDER BY id ASC");
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-navy mb-0">আজকের সিরিয়াল তালিকা</h3>
            <p class="text-muted small mb-0">তারিখ: <?php echo date('d M, Y (l)'); ?></p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill btn-sm px-4 shadow-sm">ড্যাশবোর্ড</a>
    </div>

    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
        <div class="table-responsive p-3">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">সিরিয়াল #</th>
                        <th>রোগীর নাম</th>
                        <th>ফোন ও বয়স</th>
                        <th class="text-center">অ্যাকশন</th>
                    </tr>
                </thead>
                
<tbody>
    <?php 
    if(mysqli_num_rows($query) > 0): 
        // ১. সিরিয়াল গণনার জন্য একটি ভেরিয়েবল শুরু করা
        $serial_number = 1; 

        while($row = mysqli_fetch_assoc($query)): 
    ?>
        <tr class="patient-row">
            <td class="ps-4">
                <!-- ২. এখানে ১, ২, ৩... সিরিয়াল দেখাবে -->
                <div class="serial-circle">#<?php echo $serial_number++; ?></div>
                <!-- ব্র্যাকেটে চাইলে ডাটাবেজ আইডি ছোট করে দেখাতে পারেন -->
                <small class="text-muted" style="font-size: 9px;">ID: <?php echo $row['id']; ?></small>
            </td>
            <td>
                <span class="fw-bold text-navy d-block"><?php echo $row['patient_name']; ?></span>
                <small class="badge bg-light text-primary border rounded-pill" style="font-size: 10px;">
                    <?php echo ($row['patient_id']) ? 'রেজিস্টার্ড' : 'গেস্ট'; ?>
                </small>
            </td>
            <td>
                <div class="small fw-bold text-dark"><?php echo $row['patient_phone']; ?></div>
                <div class="small text-muted">বয়স: <?php echo $row['age']; ?> | <?php echo $row['gender']; ?></div>
            </td>
            <td class="text-center">
                <!-- দেখা হয়েছে বাটন -->
                <a href="?action=mark_done&appt_id=<?php echo $row['id']; ?>" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm me-1">
                    <i class="fas fa-check-circle me-1"></i> Done (Next)
                </a>
                <!-- প্রেসক্রিপশন বাটন -->
                <a href="<?php echo DIGITAL_PRESCRIPTION_URL; ?>?appointment_id=<?php echo $row['id']; ?>" class="btn btn-prescription shadow-sm">
                    <i class="fas fa-file-prescription me-1"></i> প্রেসক্রিপশন
                </a>
            </td>
        </tr>
    <?php endwhile; else: ?>
        <tr><td colspan="4" class="text-center py-5 text-muted">আজকের জন্য আর কোনো রোগী অপেক্ষায় নেই।</td></tr>
    <?php endif; ?>
</tbody>

            </table>
        </div>
    </div>
</div>

<!-- সেশন মেসেজ এবং পপ-আপ লজিক (আগের মতোই থাকবে) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if(isset($_SESSION['prescription_saved']) && $_SESSION['prescription_saved'] == "yes"): 
    $link = BASE_URL . "modules/public/view-prescription.php?id=" . $_SESSION['p_id_for_link'];
    $msg = "*পেশেন্ট কেয়ার হাসপাতাল*\n\nপ্রিয় *" . $_SESSION['p_name'] . "*,\nআপনার ডিজিটাল প্রেসক্রিপশনটি তৈরি হয়েছে। লিঙ্ক:\n\n" . $link;
    $wa_url = "https://wa.me/880" . preg_replace('/^0/', '', $_SESSION['p_phone']) . "?text=" . urlencode($msg);
?>
<script>
$(document).ready(function() {
    Swal.fire({
        title: 'প্রেসক্রিপশন সংরক্ষিত!',
        text: 'রোগীর হোয়াটসঅ্যাপে প্রেসক্রিপশন লিঙ্কটি পাঠিয়ে দিন।',
        icon: 'success',
        showCancelButton: true,
        confirmButtonColor: '#25D366', 
        confirmButtonText: '<i class="fab fa-whatsapp me-2"></i> হোয়াটসঅ্যাপে পাঠান',
    }).then((result) => { if (result.isConfirmed) { window.open('<?php echo $wa_url; ?>', '_blank'); } });
});
</script>
<?php unset($_SESSION['prescription_saved'], $_SESSION['p_id_for_link'], $_SESSION['p_phone'], $_SESSION['p_name']); endif; ?>

<style>
.text-navy { color: var(--primary-navy); }
.btn-success { background-color: #27ae60; border: none; }
.btn-prescription { background: linear-gradient(135deg, var(--secondary-cyan), var(--primary-navy)); border: none; border-radius: 50px; padding: 7px 20px; font-weight: 600; font-size: 0.85rem; transition: 0.3s; }
.btn-prescription:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(42, 167, 229, 0.3); }
</style>

<?php include_once '../../includes/footer.php'; ?>