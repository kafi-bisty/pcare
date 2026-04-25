<?php
// ১. সেশন এবং ডাটাবেজ কানেকশন সবার আগে (কোনো স্পেস ছাড়া)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../../config/database.php';

/** 
 * ২. লজিক সেকশন (Redirect লজিকগুলো header.php এর উপরে থাকতে হবে)
 */

// ২.১ নতুন রোগী ভর্তি করা
if (isset($_POST['admit_patient'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $room = mysqli_real_escape_string($conn, $_POST['room']);
    $date = date('Y-m-d');
    
    mysqli_query($conn, "INSERT INTO admissions (patient_name, phone, room_no, admission_date) VALUES ('$name', '$phone', '$room', '$date')");
    header("Location: admission-manager.php");
    exit();
}

// ২.২ রোগীর বিলে সার্ভিস যোগ করা
if (isset($_POST['add_service'])) {
    $ad_id = $_POST['admission_id'];
    $cat = $_POST['category'];
    $amount = $_POST['amount'];
    
    mysqli_query($conn, "INSERT INTO admission_services (admission_id, category, amount) VALUES ('$ad_id', '$cat', '$amount')");
    mysqli_query($conn, "UPDATE admissions SET total_bill = total_bill + $amount WHERE id = '$ad_id'");
    header("Location: admission-manager.php?msg=service_added");
    exit();
}

// ২.৩ ফাইনাল ডিসচার্জ ও ইনকামে যোগ (লাইন ৪৩ এর সমস্যা এখানে ছিল)
if (isset($_GET['discharge_id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['discharge_id']);
    $data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM admissions WHERE id = '$id'"));
    
    if ($data) {
        $total = $data['total_bill'];
        $p_name = $data['patient_name'];
        $today = date('Y-m-d');

        // মেইন ইনকাম একাউন্টে যোগ
        mysqli_query($conn, "INSERT INTO hospital_accounts (type, category, amount, description, date) 
                             VALUES ('income', 'ভর্তি ও চিকিৎসা', '$total', 'ডিসচার্জ বিল: $p_name', '$today')");
        
        // স্ট্যাটাস আপডেট
        mysqli_query($conn, "UPDATE admissions SET status = 'discharged' WHERE id = '$id'");
    }
    header("Location: admission-manager.php?msg=discharged");
    exit();
}

/** 
 * ৩. ভিজ্যুয়াল সেকশন (এখন header.php ইনক্লুড করুন)
 */
include_once '../../includes/header.php';

// বর্তমানে ভর্তি রোগীদের তালিকা আনা
$active_patients = mysqli_query($conn, "SELECT * FROM admissions WHERE status = 'admitted' ORDER BY id DESC");
?>

<div class="container py-4">
    <div class="row g-4">
        <!-- নতুন ভর্তি ফরম -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 p-4">
                <h5 class="fw-bold text-navy mb-3"><i class="fas fa-user-plus me-2"></i>রোগী ভর্তি ফরম</h5>
                <form action="" method="POST">
                    <input type="text" name="name" class="form-control mb-2" placeholder="রোগীর নাম" required>
                    <input type="text" name="phone" class="form-control mb-2" placeholder="ফোন নম্বর" required>
                    <input type="text" name="room" class="form-control mb-3" placeholder="কেবিন/বেড নং">
                    <button type="submit" name="admit_patient" class="btn btn-primary w-100 rounded-pill fw-bold">ভর্তি নিশ্চিত করুন</button>
                </form>
            </div>
        </div>

        <!-- বর্তমানে ভর্তি রোগীদের তালিকা -->
        <div class="col-md-8">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-navy text-white py-3" style="background-color: #0A2647;">
                    <h5 class="mb-0 fw-bold">বর্তমানে ভর্তি রোগী (IPD List)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>রোগী ও কেবিন</th>
                                    <th>ভর্তির তারিখ</th>
                                    <th>মোট বিল</th>
                                    <th>অ্যাকশন</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($active_patients)): ?>
                                <tr>
                                    <td>
                                        <strong><?= $row['patient_name']; ?></strong><br>
                                        <small class="text-muted">কেবিন: <?= $row['room_no']; ?></small>
                                    </td>
                                    <td><?= date('d M, Y', strtotime($row['admission_date'])); ?></td>
                                    <td class="fw-bold text-danger">৳ <?= number_format($row['total_bill']); ?></td>
                                    <td>
                                        <!-- খরচ যোগ -->
                                        <button class="btn btn-sm btn-info text-white" onclick="openBillModal(<?= $row['id']; ?>, '<?= $row['patient_name']; ?>')">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                        <!-- প্রিন্ট বাটন -->
                                        <a href="print-admission-bill.php?id=<?= $row['id']; ?>" target="_blank" class="btn btn-sm btn-primary">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        <!-- ডিসচার্জ -->
                                        <a href="?discharge_id=<?= $row['id']; ?>" class="btn btn-sm btn-success" onclick="return confirm('ডিসচার্জ নিশ্চিত?')">
                                            Discharge
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <?php if(mysqli_num_rows($active_patients) == 0) echo "<tr><td colspan='4' class='text-center py-4 text-muted'>কোনো রোগী ভর্তি নেই।</td></tr>"; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- খরচ যোগ করার মডাল (Modal) -->
<div class="modal fade" id="billModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="" method="POST" class="modal-content rounded-4 border-0">
            <div class="modal-header bg-navy text-white" style="background:#0A2647;">
                <h5 class="modal-title">খরচ যোগ করুন</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="admission_id" id="modal_ad_id">
                <p>রোগী: <strong id="modal_p_name"></strong></p>
                <div class="mb-3">
                    <label class="small fw-bold">খরচের খাত</label>
                    <select name="category" class="form-select" required>
                        <option value="সিট ভাড়া">সিট ভাড়া</option>
                        <option value="ওটি চার্জ (OT)">ওটি চার্জ (OT)</option>
                        <option value="ডাক্তার ভিজিট">ডাক্তার ভিজিট</option>
                        <option value="অক্সিজেন">অক্সিজেন</option>
                        <option value="সার্ভিস চার্জ">সার্ভিস চার্জ</option>
                        <option value="ওষুধ">ওষুধ</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="small fw-bold">টাকার পরিমাণ (৳)</label>
                    <input type="number" name="amount" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="submit" name="add_service" class="btn btn-primary w-100 rounded-pill">বিলে যোগ করুন</button>
            </div>
        </form>
    </div>
</div>

<script>
function openBillModal(id, name) {
    document.getElementById('modal_ad_id').value = id;
    document.getElementById('modal_p_name').innerText = name;
    new bootstrap.Modal(document.getElementById('billModal')).show();
}
</script>

<?php include_once '../../includes/footer.php'; ?>