<?php
// ১. ডাটাবেজ এবং কনফিগ ফাইল (হেডারের আগে)
include_once '../../config/database.php';
include_once '../../config/constants.php';
include_once '../../config/functions.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// ২. ডাক্তার লগইন চেক
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'doctor') {
    header("Location: ../auth/staff-login.php"); 
    exit;
}

$doctor_id = $_SESSION['user_id'];
$today = date('Y-m-d');
$day_name = date('l');

// ৩. সিরিয়াল আপডেট লজিক
if (isset($_GET['action']) && $_GET['action'] == 'mark_done') {
    $appt_id = mysqli_real_escape_string($conn, $_GET['appt_id']);
    mysqli_query($conn, "UPDATE appointments SET status = 'completed' WHERE id = '$appt_id'");
    
    $current_time = date('H:i:s');
    mysqli_query($conn, "UPDATE doctor_schedules SET current_serial = current_serial + 1 
                         WHERE doctor_id = '$doctor_id' AND day_of_week = '$day_name' 
                         AND is_available = 1 AND ('$current_time' BETWEEN start_time AND end_time)");
    
    $_SESSION['success'] = "রোগী দেখা সম্পন্ন হয়েছে!";
    header("Location: today-patients.php"); exit;
}

// ৪. আজকের অনুমোদিত রোগীদের তালিকা আনা (সংশোধিত কুয়েরি)
// এখানে LEFT JOIN ব্যবহার করা হয়েছে যাতে কোনোভাবেই নাম মিস না হয়
$query = mysqli_query($conn, "
    SELECT a.*, p.name as member_name 
    FROM appointments a 
    LEFT JOIN patients p ON a.patient_id = p.id 
    WHERE a.doctor_id = '$doctor_id' 
    AND a.appointment_date = '$today' 
    AND a.status = 'approved' 
    ORDER BY a.id ASC
");

include_once '../../includes/header.php';
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
                <thead class="table-light text-navy">
                    <tr>
                        <th class="ps-3">সিরিয়াল #</th>
                        <th>রোগীর নাম</th>
                        <th>ফোন ও তথ্য</th>
                        <th class="text-center">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($query) > 0): $sl = 1; ?>
                        <?php while($row = mysqli_fetch_assoc($query)): 
                            // নাম ঠিক করার লজিক: যদি অ্যাপয়েন্টমেন্ট টেবিলে নাম না থাকে, তবে মেম্বার টেবিল থেকে নিবে
                            $display_name = !empty($row['patient_name']) ? $row['patient_name'] : ($row['member_name'] ?? 'Unknown Patient');
                        ?>
                            <tr class="patient-row">
                                <td class="ps-3 fw-bold text-danger fs-5">#<?php echo $sl++; ?></td>
                                <td>
                                    <span class="fw-bold text-navy d-block"><?php echo $display_name; ?></span>
                                    <small class="badge bg-light text-primary border rounded-pill" style="font-size: 10px;">
                                        <?php echo ($row['patient_id']) ? 'রেজিস্টার্ড' : 'গেস্ট'; ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="small fw-bold text-dark"><i class="fas fa-phone-alt me-1 text-info small"></i> <?php echo $row['patient_phone']; ?></div>
                                    <div class="small text-muted">বয়স: <?php echo $row['age']; ?> | <?php echo $row['gender']; ?></div>
                                </td>
                                <td class="text-center">
                                    <a href="?action=mark_done&appt_id=<?php echo $row['id']; ?>" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm me-1" onclick="return confirm('রোগী দেখা কি সম্পন্ন হয়েছে?')">
                                        Done (Next)
                                    </a>
                                    <a href="<?php echo DIGITAL_PRESCRIPTION_URL; ?>?appointment_id=<?php echo $row['id']; ?>" class="btn btn-prescription shadow-sm text-white">
                                        <i class="fas fa-file-prescription me-1"></i> প্রেসক্রিপশন
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center py-5 text-muted">আজকের জন্য আর কোনো অনুমোদিত রোগী নেই।</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- জাভাস্ক্রিপ্ট এবং স্টাইল আগের মতোই থাকবে... -->

<style>
.btn-prescription { background: linear-gradient(135deg, var(--secondary-cyan), var(--primary-navy)); border: none; border-radius: 50px; padding: 7px 15px; font-weight: 600; font-size: 0.85rem; }
.text-navy { color: var(--primary-navy); }
</style>

<?php include_once '../../includes/footer.php'; ?>