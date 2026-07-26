<?php
/**
 * ১. লজিক ও সেশন কন্ট্রোল
 */
include_once '../../config/database.php';
include_once '../../config/constants.php';
include_once '../../config/functions.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// রিসেপশন লগইন চেক
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'reception') {
    header("Location: ../auth/staff-login.php"); exit;
}

$today = date('Y-m-d');

// --- লজিক ২.১: শুধুমাত্র অনুমোদন (পেমেন্ট ছাড়া) ---
if (isset($_GET['approve_id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['approve_id']);
    if(mysqli_query($conn, "UPDATE appointments SET status = 'approved' WHERE id = '$id'")) {
        $_SESSION['show_wa_only'] = $id;
        session_write_close(); // সেশন নিশ্চিতভাবে সেভ করার জন্য
    }
    header("Location: pending-appointments.php"); exit;
}

// --- লজিক ২.২: পেমেন্ট গ্রহণ ও টোকেন ইস্যু ---
if (isset($_POST['confirm_payment'])) {
    $appt_id = mysqli_real_escape_string($conn, $_POST['appt_id']);
    $fee = mysqli_real_escape_string($conn, $_POST['fee']);
    $p_name = mysqli_real_escape_string($conn, $_POST['p_name']);
    $doc_name = mysqli_real_escape_string($conn, $_POST['doc_name']);

    $receipt_no = "APP-" . $appt_id;
    $desc = "ডক্টর ফি (রোগী: $p_name, ডাক্তার: $doc_name)";
    
    $sql_acc = "INSERT INTO hospital_accounts (type, category, amount, receipt_no, description, date) 
                VALUES ('income', 'ডাক্তার ফি', '$fee', '$receipt_no', '$desc', '$today')";
    
    if (mysqli_query($conn, $sql_acc)) {
        $_SESSION['show_print_token'] = $appt_id;
        session_write_close();
    }
    header("Location: pending-appointments.php?status=paid"); exit;
}

// --- লজিক ২.৩: ফি ফেরত (Refund) ---
if (isset($_GET['refund_id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['refund_id']);
    $data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT a.*, d.fee FROM appointments a JOIN doctors d ON a.doctor_id = d.id WHERE a.id = '$id'"));
    
    if ($data) {
        $refund_amount = $data['fee'];
        $p_name = $data['patient_name'];
        mysqli_query($conn, "UPDATE appointments SET status = 'cancelled' WHERE id = '$id'");
        $desc = "টাকা ফেরত (রোগী: $p_name, সিরিয়াল: #$id)";
        mysqli_query($conn, "INSERT INTO hospital_accounts (type, category, amount, receipt_no, description, date) 
                             VALUES ('expense', 'অন্যান্য ব্যয়', '$refund_amount', 'REFUND-$id', '$desc', '$today')");
        $_SESSION['msg'] = "টাকা ফেরত সফল হয়েছে।";
        session_write_close();
    }
    header("Location: pending-appointments.php"); exit;
}

include_once '../../includes/header.php';

// ৩. ডাটা কুয়েরি
$pending_list = mysqli_query($conn, "SELECT a.*, d.name as doc_name FROM appointments a JOIN doctors d ON a.doctor_id = d.id WHERE a.status = 'pending' ORDER BY a.id ASC");
$approved_list = mysqli_query($conn, "SELECT a.*, d.name as doc_name, d.fee as doc_fee FROM appointments a JOIN doctors d ON a.doctor_id = d.id WHERE a.status = 'approved' AND a.appointment_date = '$today' ORDER BY a.id ASC");
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-navy"><i class="fas fa-hospital-user me-2 text-primary"></i>সিরিয়াল ও পেমেন্ট</h3>
        <a href="dashboard.php" class="btn btn-outline-primary rounded-pill btn-sm px-4">ড্যাশবোর্ড</a>
    </div>

    <!-- সেকশন ১: পেন্ডিং -->
    <div class="card border-0 shadow-sm rounded-4 mb-5 overflow-hidden">
        <div class="card-header bg-navy text-white p-3">১. পেন্ডিং রিকোয়েস্ট (অনুমোদন করুন)</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>রোগীর তথ্য</th><th>ডাক্তার</th><th class="text-center">অ্যাকশন</th></tr></thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($pending_list)): ?>
                    <tr>
                        <td class="ps-3"><b><?php echo $row['patient_name']; ?></b><br><small><?php echo $row['patient_phone']; ?></small></td>
                        <td><?php echo $row['doc_name']; ?></td>
                        <td class="text-center">
                            <a href="?approve_id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm rounded-pill px-3">Approve Serial</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- সেকশন ২: অনুমোদিত -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
        <div class="card-header bg-success text-white p-3">২. অনুমোদিত সিরিয়াল (পেমেন্ট ও টোকেন)</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>সিরিয়াল #</th><th>রোগী</th><th>ডাক্তার</th><th class="text-end">ফি</th><th class="text-center">অ্যাকশন</th></tr></thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($approved_list)): 
                        $check_pay = mysqli_query($conn, "SELECT id FROM hospital_accounts WHERE receipt_no = 'APP-{$row['id']}'");
                        $is_paid = mysqli_num_rows($check_pay) > 0;
                    ?>
                    <tr>
                        <td class="ps-3">#<?php echo $row['id']; ?></td>
                        <td><?php echo $row['patient_name']; ?></td>
                        <td><?php echo $row['doc_name']; ?></td>
                        <td class="text-end">৳<?php echo $row['doc_fee']; ?></td>
                        <td class="text-center">
                            <?php if(!$is_paid): ?>
                                <button class="btn btn-warning btn-sm rounded-pill px-3" onclick="payModal('<?php echo $row['id']; ?>', '<?php echo $row['patient_name']; ?>', '<?php echo $row['doc_name']; ?>', '<?php echo $row['doc_fee']; ?>')">Collect Fee</button>
                            <?php else: ?>
                                <a href="../admin/print-token.php?id=<?php echo $row['id']; ?>" target="_blank" class="btn btn-dark btn-sm"><i class="fas fa-print"></i></a>
                                <a href="?refund_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm rounded-pill ms-1" onclick="return confirm('টাকা ফেরত দিবেন?')">Refund</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- পেমেন্ট মডাল -->
<div class="modal fade" id="payModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="" method="POST" class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-navy text-white"><h5 class="modal-title">ফি সংগ্রহ ও টোকেন</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-4 text-center">
                <input type="hidden" name="appt_id" id="m_id"><input type="hidden" name="p_name" id="m_pname"><input type="hidden" name="doc_name" id="m_dname"><input type="hidden" name="fee" id="m_fee">
                <h6 id="d_pname"></h6>
                <div class="p-3 bg-light rounded-4 border my-3"><h2 class="fw-bold text-navy mb-0">৳ <span id="d_fee"></span></h2></div>
                <button type="submit" name="confirm_payment" class="btn btn-success w-100 rounded-pill py-2">Confirm Payment</button>
            </div>
        </form>
    </div>
</div>

<!-- SweetAlert & WhatsApp Logic -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function payModal(id, pname, dname, fee) {
    $('#m_id').val(id); $('#m_pname').val(pname); $('#m_dname').val(dname); $('#m_fee').val(fee);
    $('#d_pname').html("রোগী: <b>" + pname + "</b>"); $('#d_fee').text(fee);
    new bootstrap.Modal(document.getElementById('payModal')).show();
}

<?php 
// ১. শুধুমাত্র হোয়াটসঅ্যাপ পাঠানো (অনুমোদনের পর)
if(isset($_SESSION['show_wa_only'])): 
    $id = $_SESSION['show_wa_only'];
    $wa_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT a.*, d.name as d_name FROM appointments a JOIN doctors d ON a.doctor_id = d.id WHERE a.id = '$id'"));
    
    // ফোন নম্বর ফরম্যাট (০ বাদ দিয়ে ৮৮০ যোগ করা)
    $clean_phone = preg_replace('/[^0-9]/', '', $wa_data['patient_phone']);
    $clean_phone = ltrim($clean_phone, '0');
    
    $msg = "*আসসালামু আলাইকুম " . $wa_data['patient_name'] . "*\n\n🏥 *পেশেন্ট কেয়ার হাসপাতাল* থেকে আপনার সিরিয়াল অনুমোদিত হয়েছে।\n👨‍⚕️ *ডাক্তার:* ডা. " . $wa_data['d_name'] . "\n🔢 *সিরিয়াল:* #" . $wa_data['id'] . "\n📍 অনুগ্রহ করে হাসপাতালে এসে পেমেন্ট সম্পন্ন করে টোকেন সংগ্রহ করুন।";
    $wa_url = "https://api.whatsapp.com/send?phone=880".$clean_phone."&text=".urlencode($msg);
?>
    Swal.fire({
        title: 'অনুমোদন সফল!',
        text: 'রোগীকে হোয়াটসঅ্যাপ মেসেজ পাঠান।',
        icon: 'success',
        confirmButtonText: 'WhatsApp পাঠান',
        confirmButtonColor: '#25D366'
    }).then((result) => {
        if (result.isConfirmed) {
            window.open('<?php echo $wa_url; ?>', '_blank');
        }
    });
<?php unset($_SESSION['show_wa_only']); endif; ?>

<?php 
// ২. টোকেন প্রিন্ট করা (পেমেন্টের পর)
if(isset($_SESSION['show_print_token'])): 
    $id = $_SESSION['show_print_token'];
?>
    Swal.fire({
        title: 'পেমেন্ট সফল!',
        text: 'এখন রোগীর টোকেনটি প্রিন্ট করুন।',
        icon: 'success',
        confirmButtonText: 'Print Token',
        confirmButtonColor: '#0A2647'
    }).then((result) => {
        if (result.isConfirmed) {
            window.open('../admin/print-token.php?id=<?php echo $id; ?>', '_blank');
        }
    });
<?php unset($_SESSION['show_print_token']); endif; ?>
</script>

<style>
    :root { --navy: #0A2647; }
    .bg-navy { background-color: var(--navy) !important; }
</style>

<?php include_once '../../includes/footer.php'; ?>