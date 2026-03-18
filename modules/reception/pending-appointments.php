<?php
// ১. ডাটাবেজ এবং সেশন সবার আগে (Redirect এরর এড়াতে)
include_once '../../config/database.php';
include_once '../../config/constants.php';
include_once '../../config/functions.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// ২. রিসেপশন লগইন চেক
if (!isset($_SESSION['reception_id']) || $_SESSION['user_role'] != 'reception') {
    header("Location: ../auth/staff-login.php");
    exit;
}

// ৩. অনুমোদন লজিক (ডাটা আপডেট এবং হোয়াটসঅ্যাপ সেশন সেট)
if (isset($_POST['approve_with_time'])) {
    $id = mysqli_real_escape_string($conn, $_POST['appt_id']);
    $time = mysqli_real_escape_string($conn, $_POST['arrival_time']);
    
    if (mysqli_query($conn, "UPDATE appointments SET status = 'approved' WHERE id = '$id'")) {
        $_SESSION['show_success_alert'] = true;
        $_SESSION['send_wa'] = $id;
        $_SESSION['wa_time'] = $time;
    }
    header("Location: pending-appointments.php");
    exit;
}

// ৪. বাতিল লজিক
if (isset($_GET['action']) && $_GET['action'] == 'cancel') {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    mysqli_query($conn, "UPDATE appointments SET status = 'cancelled' WHERE id = '$id'");
    $_SESSION['success'] = "অ্যাপয়েন্টমেন্ট বাতিল করা হয়েছে।";
    header("Location: pending-appointments.php");
    exit;
}

// ৫. এখন হেডার ইনক্লুড করুন
include_once '../../includes/header.php';

// ৬. ডাটাবেজ থেকে তথ্য আনা
$query = mysqli_query($conn, "
    SELECT a.*, d.name as doctor_name, d.chamber_no 
    FROM appointments a 
    JOIN doctors d ON a.doctor_id = d.id 
    WHERE a.status = 'pending' 
    ORDER BY a.appointment_date ASC
");
?>

<!-- স্ক্রিন কাঁপা বন্ধ করার জন্য আল্টিমেট সিএসএস প্যাচ -->
<style>
    html { overflow-y: scroll !important; }
    body.modal-open { padding-right: 0 !important; overflow: hidden !important; }
    .navbar.sticky-top, .top-header, .notice-container { padding-right: 0 !important; margin-right: 0 !important; }
    .cursor-pointer { cursor: pointer; }
    .badge:hover { filter: brightness(0.9); }
</style>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-navy"><i class="fas fa-calendar-check me-2 text-warning"></i>পেন্ডিং অ্যাপয়েন্টমেন্ট</h3>
        <a href="dashboard.php" class="btn btn-outline-primary rounded-pill btn-sm px-4 shadow-sm">
            <i class="fas fa-arrow-left me-1"></i> ড্যাশবোর্ড
        </a>
    </div>
    
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive p-3">
            <table class="table table-hover align-middle">
                <thead class="table-light text-navy">
                    <tr>
                        <th class="ps-3">রোগীর তথ্য</th>
                        <th>ডাক্তার ও চেম্বার</th>
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
                                    <small class="text-muted"><i class="fas fa-phone-alt me-1"></i><?php echo $row['patient_phone']; ?> | বয়স: <?php echo $row['age']; ?></small>
                                </td>
                                <td>
                                    <span class="small fw-bold">ডা. <?php echo $row['doctor_name']; ?></span><br>
                                    <small class="badge bg-light text-primary border">রুম: <?php echo $row['chamber_no'] ? $row['chamber_no'] : 'N/A'; ?></small>
                                </td>
                                <td><i class="far fa-calendar-alt me-1 text-primary"></i> <?php echo date('d M, Y', strtotime($row['appointment_date'])); ?></td>
                                <td class="text-center">
                                    <button class="btn btn-success btn-sm rounded-pill px-4 shadow-sm btn-approve" 
                                            data-id="<?php echo $row['id']; ?>" 
                                            data-name="<?php echo $row['patient_name']; ?>">
                                        অনুমোদন করুন
                                    </button>
                                    <a href="?action=cancel&id=<?php echo $row['id']; ?>" class="text-danger small ms-2" onclick="return confirm('বাতিল করতে চান?')">বাতিল</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center py-5 text-muted">কোনো পেন্ডিং অনুরোধ নেই।</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- অনুমোদন করার পপ-আপ (Modal) - মাত্র ১টি লুপের বাইরে -->
<div class="modal shadow" id="approveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-navy text-white" style="background:var(--primary-navy)">
                <h5 class="modal-title"><i class="fab fa-whatsapp me-2 text-success"></i>রোগীর সময় সেট করুন</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="appt_id" id="modal_appt_id">
                    <p class="small text-muted mb-3">রোগীর নাম: <b id="modal_patient_name" class="text-dark"></b></p>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">রোগীকে আসার সময় দিন</label>
                        <div class="input-group">
                            <input type="text" name="arrival_time" id="modal_arrival_time" class="form-control rounded-start-3 shadow-none border-primary" placeholder="যেমন: আজ বিকাল ৫:৩০ টায়" required>
                            <button type="button" class="btn btn-primary" onclick="setAutoTime()">
                                <i class="fas fa-magic me-1"></i> অটো
                            </button>
                        </div>
                        <div class="form-text mt-3">
                            <label class="d-block small text-muted mb-2">কুইক টাইম সিলেক্ট করুন:</label>
                            <span class="badge bg-light text-dark border cursor-pointer me-1 py-2 px-3 mb-1 d-inline-block" onclick="setTime('আজ বিকাল ৫:০০ টায়')">বিকাল ৫:০০</span>
                            <span class="badge bg-light text-dark border cursor-pointer me-1 py-2 px-3 mb-1 d-inline-block" onclick="setTime('আজ সন্ধ্যা ৭:০০ টায়')">সন্ধ্যা ৭:০০</span>
                            <span class="badge bg-light text-dark border cursor-pointer py-2 px-3 mb-1 d-inline-block" onclick="setTime('আগামীকাল সকাল ১০:০০ টায়')">কাল ১০:০০</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="submit" name="approve_with_time" class="btn btn-success w-100 rounded-pill py-2 shadow-sm fw-bold">
                        কনফার্ম ও হোয়াটসঅ্যাপ পাঠান <i class="fab fa-whatsapp ms-1"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- জাভাস্ক্রিপ্ট সেকশন -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
// ১. বর্তমান সময় অটোমেটিক বসানো
function setAutoTime() {
    const now = new Date();
    let hours = now.getHours();
    let minutes = now.getMinutes();
    const ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12; hours = hours ? hours : 12;
    minutes = minutes < 10 ? '0' + minutes : minutes;
    document.getElementById('modal_arrival_time').value = "আজ " + hours + ":" + minutes + " " + ampm + " টায়";
}

// ২. কুইক ব্যাজ ক্লিক করলে সময় সেট করা
function setTime(val) { 
    document.getElementById('modal_arrival_time').value = val; 
}

$(document).ready(function() {
    // ৩. অনুমোদন বাটন ক্লিক করলে মডাল ওপেন হবে
    $('.btn-approve').on('click', function() {
        $('#modal_appt_id').val($(this).data('id'));
        $('#modal_patient_name').text($(this).data('name'));
        $('#modal_arrival_time').val(''); 
        $('#approveModal').modal('show');
    });

    // ৪. বুটস্ট্র্যাপ স্ক্রিন কাঁপাকাঁপি বন্ধের প্যাচ
    $('#approveModal').on('show.bs.modal', function () { 
        $('body').css('padding-right', '0px'); 
    });
});
</script>

<!-- হোয়াটসঅ্যাপ ও SweetAlert পপ-আপ লজিক -->
<?php if(isset($_SESSION['show_success_alert'])): 
    $id = $_SESSION['send_wa']; 
    $wa_time = $_SESSION['wa_time'];
    $wa_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT a.*, d.name as d_name, d.chamber_no FROM appointments a JOIN doctors d ON a.doctor_id = d.id WHERE a.id = '$id'"));
    
    // হোয়াটসঅ্যাপ মেসেজ বডি
    $msg = "*আসসালামু আলাইকুম " . $wa_data['patient_name'] . "*\n\n";
    $msg .= "🏥 *পেশেন্ট কেয়ার হাসপাতাল* থেকে আপনার অ্যাপয়েন্টমেন্ট অনুমোদিত হয়েছে।\n\n";
    $msg .= "👨‍⚕️ *ডাক্তার:* ডা. " . $wa_data['d_name'] . "\n";
    $msg .= "🔢 *সিরিয়াল:* #" . $wa_data['id'] . "\n";
    $msg .= "🚪 *রুম:* " . ($wa_data['chamber_no'] ? $wa_data['chamber_no'] : 'N/A') . "\n";
    $msg .= "⏰ *সময়:* " . $wa_time . "\n";
    $msg .= "📍 *লোকেশন:* কলেজ রোড, বরগুনা।\n\n";
    $msg .= "ধন্যবাদ।";

    $phone = preg_replace('/^0/', '', $wa_data['patient_phone']);
    $wa_url = "https://wa.me/880".$phone."?text=".urlencode($msg);
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    Swal.fire({
        title: 'অনুমোদন সফল!',
        text: 'রোগীর হোয়াটসঅ্যাপে মেসেজটি পাঠিয়ে দিন।',
        icon: 'success',
        showCancelButton: true,
        confirmButtonColor: '#25D366', // WhatsApp Green
        cancelButtonColor: '#d33',
        confirmButtonText: '<i class="fab fa-whatsapp me-2"></i> হোয়াটসঅ্যাপে পাঠান',
        cancelButtonText: 'পরে পাঠাবো'
    }).then((result) => {
        if (result.isConfirmed) {
            window.open('<?php echo $wa_url; ?>', '_blank');
        }
    });
});
</script>
<?php unset($_SESSION['show_success_alert'], $_SESSION['send_wa'], $_SESSION['wa_time']); endif; ?>

<?php include_once '../../includes/footer.php'; ?>