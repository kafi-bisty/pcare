<?php
/**
 * ১. লজিক ও সেশন কন্ট্রোল (সবার আগে)
 */
include_once '../../config/database.php';
include_once '../../config/constants.php';
include_once '../../config/functions.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// রিসেপশন লগইন চেক
if (!isset($_SESSION['reception_id']) || $_SESSION['user_role'] != 'reception') {
    header("Location: ../auth/staff-login.php");
    exit;
}

// ২. অনুমোদন এবং ফি কালেকশন লজিক
if (isset($_POST['confirm_approval'])) {
    $id = mysqli_real_escape_string($conn, $_POST['appt_id']);
    $time = mysqli_real_escape_string($conn, $_POST['arrival_time']);
    $fee = mysqli_real_escape_string($conn, $_POST['fee']);
    $p_name = mysqli_real_escape_string($conn, $_POST['patient_name']);
    $doctor_id = mysqli_real_escape_string($conn, $_POST['doctor_id']);
    $today = date('Y-m-d');

    // ক. অ্যাপয়েন্টমেন্ট স্ট্যাটাস ও ডাক্তার আপডেট
    $update_appt = "UPDATE appointments SET status = 'approved', doctor_id = '$doctor_id' WHERE id = '$id'";
    
    if (mysqli_query($conn, $update_appt)) {
        // খ. মেইন অ্যাকাউন্টসে ফি যোগ করা
        $receipt_no = "APP-" . $id;
        $desc = "অ্যাপয়েন্টমেন্ট ফি (রোগী: $p_name, সময়: $time)";
        mysqli_query($conn, "INSERT INTO hospital_accounts (type, category, amount, receipt_no, description, date) 
                             VALUES ('income', 'ডাক্তার ফি', '$fee', '$receipt_no', '$desc', '$today')");

        // গ. হোয়াটসঅ্যাপ ও সাকসেস এলার্ট সেট করা
        $_SESSION['show_success_alert'] = true;
        $_SESSION['send_wa'] = $id;
        $_SESSION['wa_time'] = $time;
    }
    header("Location: pending-appointments.php");
    exit;
}

// ৩. বাতিল লজিক
if (isset($_GET['action']) && $_GET['action'] == 'cancel') {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    mysqli_query($conn, "UPDATE appointments SET status = 'cancelled' WHERE id = '$id'");
    $_SESSION['success'] = "অ্যাপয়েন্টমেন্ট বাতিল করা হয়েছে।";
    header("Location: pending-appointments.php");
    exit;
}

include_once '../../includes/header.php';

// ৪. ডাটাবেজ থেকে পেন্ডিং তালিকা আনা (ডাক্তারের ফি সহ)
$query = mysqli_query($conn, "
    SELECT a.*, d.name as doctor_name, d.chamber_no, d.fee as doctor_fee 
    FROM appointments a 
    JOIN doctors d ON a.doctor_id = d.id 
    WHERE a.status = 'pending' 
    ORDER BY a.appointment_date ASC
");
?>

<style>
    :root { --navy: #0A2647; --cyan: #2AA7E5; }
    html { overflow-y: scroll !important; }
    body.modal-open { padding-right: 0 !important; overflow: hidden !important; }
    .bg-navy { background-color: var(--navy) !important; }
</style>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-navy"><i class="fas fa-calendar-check me-2 text-warning"></i>পেন্ডিং অ্যাপয়েন্টমেন্ট ও ফি</h3>
        <a href="dashboard.php" class="btn btn-outline-primary rounded-pill btn-sm px-4">ড্যাশবোর্ড</a>
    </div>

    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-danger py-2 rounded-4 mb-4"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive p-3">
            <table class="table table-hover align-middle">
                <thead class="table-light text-navy">
                    <tr>
                        <th class="ps-3">রোগীর তথ্য</th>
                        <th>ডাক্তার ও ফি</th>
                        <th>তারিখ</th>
                        <th class="text-center">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($query && mysqli_num_rows($query) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($query)): ?>
                            <tr>
                                <td class="ps-3">
                                    <span class="fw-bold text-navy"><?php echo $row['patient_name']; ?></span><br>
                                    <small class="text-muted"><?php echo $row['patient_phone']; ?></small>
                                </td>
                                <td>
                                    <span class="small fw-bold"><?php echo $row['doctor_name']; ?></span><br>
                                    <small class="badge bg-light text-success border">ফি: ৳ <?php echo $row['doctor_fee']; ?></small>
                                </td>
                                <td><?php echo date('d M, Y', strtotime($row['appointment_date'])); ?></td>
                                <td class="text-center">
                                    <button class="btn btn-success btn-sm rounded-pill px-4 shadow-sm" 
                                            onclick="openApproveModal('<?php echo $row['id']; ?>', '<?php echo $row['patient_name']; ?>', '<?php echo $row['doctor_id']; ?>', '<?php echo $row['doctor_fee']; ?>')">
                                        Approve & Pay
                                    </button>
                                    <a href="?action=cancel&id=<?php echo $row['id']; ?>" class="text-danger small ms-2" onclick="return confirm('বাতিল করতে চান?')">বাতিল</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center py-5 text-muted">কোনো পেন্ডিং অ্যাপয়েন্টমেন্ট নেই।</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- অনুমোদন ও ফি মডাল -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="" method="POST" class="modal-content border-0 shadow rounded-4">
            <div class="modal-header bg-navy text-white">
                <h5 class="modal-title">অ্যাপ্রুভাল এবং ফি কালেকশন</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="appt_id" id="m_appt_id">
                <input type="hidden" name="doctor_id" id="m_doc_id">
                <input type="hidden" name="patient_name" id="m_p_name">

                <p class="mb-3">রোগী: <strong id="m_display_name"></strong></p>
                
                <!-- ১. ফি ডিসপ্লে -->
                <div class="p-3 bg-light rounded-4 border text-center mb-4">
                    <span class="small text-muted d-block">প্রদেয় ডাক্তার ফি</span>
                    <h2 class="fw-bold text-navy mb-0">৳ <span id="m_display_fee">0</span></h2>
                    <input type="hidden" name="fee" id="m_fee_input">
                </div>

                <!-- ২. সময় সেট করা -->
                <div class="mb-3">
                    <label class="form-label small fw-bold">আসার সময় দিন</label>
                    <div class="input-group">
                        <input type="text" name="arrival_time" id="m_arrival_time" class="form-control rounded-start-3 shadow-none border-primary" placeholder="যেমন: আজ বিকাল ৫:৩০ টায়" required>
                        <button type="button" class="btn btn-primary" onclick="setAutoTime()">অটো সময়</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pb-4">
                <button type="submit" name="confirm_approval" class="btn btn-success w-100 rounded-pill py-2 fw-bold shadow">
                    ফি গ্রহণ ও অ্যাপ্রুভ করুন <i class="fas fa-check-circle ms-1"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
function openApproveModal(id, name, docId, fee) {
    document.getElementById('m_appt_id').value = id;
    document.getElementById('m_p_name').value = name;
    document.getElementById('m_display_name').innerText = name;
    document.getElementById('m_doc_id').value = docId;
    document.getElementById('m_display_fee').innerText = fee;
    document.getElementById('m_fee_input').value = fee;
    $('#approveModal').modal('show');
}

function setAutoTime() {
    const now = new Date();
    let h = now.getHours(); let m = now.getMinutes();
    const ampm = h >= 12 ? 'PM' : 'AM'; h = h % 12 || 12;
    m = m < 10 ? '0' + m : m;
    document.getElementById('m_arrival_time').value = "আজ " + h + ":" + m + " " + ampm + " টায়";
}
</script>

<!-- হোয়াটসঅ্যাপ ও SweetAlert পপ-আপ -->

<!-- হোয়াটসঅ্যাপ ও SweetAlert পপ-আপ -->
<?php if(isset($_SESSION['show_success_alert'])): 
    $id = $_SESSION['send_wa']; 
    $wa_time = $_SESSION['wa_time'];
    // ডাক্তার ফি এবং সব তথ্য সংগ্রহ
    $wa_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT a.*, d.name as d_name, d.fee as d_fee FROM appointments a JOIN doctors d ON a.doctor_id = d.id WHERE a.id = '$id'"));
    
    $wa_msg = "*আসসালামু আলাইকুম " . $wa_data['patient_name'] . "*\n🏥 *পেশেন্ট কেয়ার হাসপাতাল* থেকে আপনার অ্যাপয়েন্টমেন্ট অনুমোদিত হয়েছে।\n👨‍⚕️ *ডাক্তার:* ডা. " . $wa_data['d_name'] . "\n⏰ *সময়:* " . $wa_time . "\n💰 *ফি:* ৳ " . number_format($wa_data['d_fee']) . " (পরিশোধিত)\nধন্যবাদ।";
    $phone = preg_replace('/^0/', '', $wa_data['patient_phone']);
    $wa_url = "https://wa.me/880".$phone."?text=".urlencode($wa_msg);
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
Swal.fire({
    title: 'অনুমোদন ও পেমেন্ট সফল!',
    html: 'আপনি এখন টোকেন প্রিন্ট করতে পারেন এবং রোগীকে হোয়াটসঅ্যাপ পাঠাতে পারেন।',
    icon: 'success',
    showCancelButton: true,
    showDenyButton: true,
    confirmButtonColor: '#0A2647', // Navy
    denyButtonColor: '#25D366',    // WA Green
    cancelButtonColor: '#d33',
    confirmButtonText: '<i class="fas fa-print me-1"></i> প্রিন্ট টোকেন',
    denyButtonText: '<i class="fab fa-whatsapp me-1"></i> হোয়াটসঅ্যাপ',
    cancelButtonText: 'বন্ধ করুন'
}).then((result) => {
    if (result.isConfirmed) {
        // টোকেন প্রিন্ট করার জন্য নতুন উইন্ডো খুলবে
        window.open('print-token.php?id=<?php echo $id; ?>', '_blank');
    } else if (result.isDenied) {
        // হোয়াটসঅ্যাপ উইন্ডো খুলবে
        window.open('<?php echo $wa_url; ?>', '_blank');
    }
});
</script>
<?php unset($_SESSION['show_success_alert'], $_SESSION['send_wa'], $_SESSION['wa_time']); endif; ?>



















<?php if(isset($_SESSION['show_success_alert'])): 
    $id = $_SESSION['send_wa']; $wa_time = $_SESSION['wa_time'];
    $wa_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT a.*, d.name as d_name FROM appointments a JOIN doctors d ON a.doctor_id = d.id WHERE a.id = '$id'"));
    
    $wa_msg = "*আসসালামু আলাইকুম " . $wa_data['patient_name'] . "*\n🏥 *পেশেন্ট কেয়ার হাসপাতাল* থেকে আপনার অ্যাপয়েন্টমেন্ট অনুমোদিত হয়েছে।\n👨‍⚕️ *ডাক্তার:* ডা. " . $wa_data['d_name'] . "\n⏰ *সময়:* " . $wa_time . "\n💰 *ফি:* ৳ " . $wa_data['doctor_fee'] . " (পরিশোধিত)\nধন্যবাদ।";
    $phone = preg_replace('/^0/', '', $wa_data['patient_phone']);
    $wa_url = "https://wa.me/880".$phone."?text=".urlencode($wa_msg);
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
Swal.fire({
    title: 'অনুমোদন ও পেমেন্ট সফল!',
    text: 'রোগীর হোয়াটসঅ্যাপে মেসেজটি পাঠিয়ে দিন।',
    icon: 'success',
    showCancelButton: true,
    confirmButtonColor: '#25D366',
    confirmButtonText: 'হোয়াটসঅ্যাপে পাঠান',
    cancelButtonText: 'বন্ধ করুন'
}).then((result) => { if (result.isConfirmed) { window.open('<?php echo $wa_url; ?>', '_blank'); } });
</script>
<?php unset($_SESSION['show_success_alert'], $_SESSION['send_wa'], $_SESSION['wa_time']); endif; ?>

<?php include_once '../../includes/footer.php'; ?>