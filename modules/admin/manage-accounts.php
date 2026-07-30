<?php
/**
 * ১. লজিক ও ডাটা প্রসেসিং
 */
session_start();
include_once '../../config/database.php';
include_once '../../config/constants.php';
include_once '../../config/functions.php';

// সিকিউরিটি চেক
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'manager', 'accounts'])) {
    header("Location: ../auth/staff-login.php"); exit;
}
$user_role = $_SESSION['user_role'];
$filter_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// --- অপারেশন লজিক (Delete) ---
if (isset($_GET['del_id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['del_id']);
    $check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT date FROM hospital_accounts WHERE id = '$id'"));
    if ($check) {
        $days_diff = (strtotime(date('Y-m-d')) - strtotime($check['date'])) / 86400;
        if ($days_diff <= 2 || $user_role == 'admin') {
            mysqli_query($conn, "DELETE FROM hospital_accounts WHERE id = '$id'");
            $_SESSION['success'] = "সফলভাবে মুছে ফেলা হয়েছে!";
        } else {
            $_SESSION['error'] = "২ দিন পার হয়ে যাওয়ায় এটি আর ডিলিট করা সম্ভব নয়।";
        }
    }
    header("Location: manage-accounts.php?date=$filter_date"); exit;
}

// --- অপারেশন লজিক (Update) ---
if (isset($_POST['update_transaction'])) {
    $id = $_POST['edit_id'];
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);
    $receipt_no = mysqli_real_escape_string($conn, $_POST['receipt_no']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT date FROM hospital_accounts WHERE id = '$id'"));
    $days_diff = (strtotime(date('Y-m-d')) - strtotime($check['date'])) / 86400;
    if ($days_diff <= 2 || $user_role == 'admin') {
        mysqli_query($conn, "UPDATE hospital_accounts SET amount='$amount', receipt_no='$receipt_no', description='$desc' WHERE id='$id'");
        $_SESSION['success'] = "সফলভাবে আপডেট করা হয়েছে!";
    }
    header("Location: manage-accounts.php?date=$filter_date"); exit;
}

// --- অপারেশন লজিক (Add) ---
if (isset($_POST['add_transaction'])) {
    $type = $_POST['type'];
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);
    $receipt_no = mysqli_real_escape_string($conn, $_POST['receipt_no']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    mysqli_query($conn, "INSERT INTO hospital_accounts (type, category, amount, receipt_no, description, date) VALUES ('$type', '$category', '$amount', '$receipt_no', '$desc', '$date')");
    $_SESSION['success'] = "হিসাবটি সফলভাবে যোগ করা হয়েছে!";
    header("Location: manage-accounts.php?date=$date"); exit;
}

// --- ডাটা সংগ্রহের ফাংশনসমূহ ---
function getSectorTotal($conn, $date, $category) {
    $sql = "SELECT SUM(amount) as total FROM hospital_accounts WHERE date = '$date' AND type = 'income' AND category = '$category'";
    $res = mysqli_fetch_assoc(mysqli_query($conn, $sql));
    return $res['total'] ?? 0;
}

function getGroupedData($conn, $date, $type) {
    $data = [];
    $res = mysqli_query($conn, "SELECT * FROM hospital_accounts WHERE date = '$date' AND type = '$type' ORDER BY id DESC");
    while($row = mysqli_fetch_assoc($res)) { $data[$row['category']][] = $row; }
    return $data;
}

$income_groups = getGroupedData($conn, $filter_date, 'income');
$expense_groups = getGroupedData($conn, $filter_date, 'expense');

// ডাক্তার ভিত্তিক রিপোর্ট
$doctor_report_sql = "SELECT description as doc_info, SUM(amount) as total, COUNT(id) as patient_count FROM hospital_accounts WHERE date = '$filter_date' AND category = 'ডাক্তার ফি' GROUP BY description";
$doctor_report = mysqli_query($conn, $doctor_report_sql);

include_once '../../includes/header.php';
?>

<style>
    :root { --navy: #0A2647; --cyan: #2AA7E5; }
    body { background-color: #f1f5f9; font-family: 'Segoe UI', sans-serif; }
    .report-card { border: none; border-radius: 20px; background: white; box-shadow: 0 5px 25px rgba(0,0,0,0.05); }
    .cat-header { background-color: #f8faff; font-weight: 800; color: var(--navy); font-size: 13px; border-bottom: 2px solid #eee; }
    .locked-row { opacity: 0.6; background-color: #fcfcfc !important; }
    .summary-box { border-radius: 15px; padding: 18px; color: white; text-align: center; box-shadow: 0 10px 20px rgba(0,0,0,0.1); transition: 0.3s; }
    .summary-box:hover { transform: translateY(-5px); }
    .toggle-btn { padding: 18px; border-radius: 15px; transition: 0.3s; cursor: pointer; border: none; width: 100%; color: white; font-weight: bold; }
    .btn-inc { background: #10b981; } .btn-exp { background: #ef4444; }
    #formContainer { display: none; animation: slideDown 0.4s ease; }
    @keyframes slideDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
    @media print { .no-print { display: none !important; } }
</style>

<div class="container py-4">
    <!-- ১. হেডার নেভিগেশন (আপনার বাটনগুলো এখানে যোগ করা হয়েছে) -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print flex-wrap gap-2">
        <h3 class="fw-bold text-navy mb-0">অ্যাকাউন্টস মাস্টার প্যানেল</h3>
        <div class="d-flex gap-2 flex-wrap">
            <a href="admission-manager.php" class="btn btn-sm btn-dark rounded-pill px-3 fw-bold shadow-sm"><i class="fas fa-bed"></i> Admission</a>
            <a href="lab-billing.php" class="btn btn-sm btn-info text-white rounded-pill px-3 fw-bold shadow-sm"><i class="fas fa-flask"></i> Lab Bill</a>
            <a href="manage-lab-tests.php" class="btn btn-sm btn-warning text-white rounded-pill px-3 fw-bold shadow-sm"><i class="fas fa-vial"></i> Lab Tests</a>
            <a href="patient-billing.php" class="btn btn-sm btn-success rounded-pill px-3 fw-bold shadow-sm"><i class="fas fa-ticket-alt"></i> Money Receipt</a>
            
            <a href="print-report.php?date=<?php echo $filter_date; ?>" target="_blank" class="btn btn-sm btn-danger rounded-pill px-3 shadow-sm fw-bold">
                <i class="fas fa-file-pdf"></i> Report
            </a>
            
            <form action="" method="GET" class="d-flex gap-1">
                <input type="date" name="date" class="form-control form-control-sm rounded-pill border-primary shadow-none" value="<?php echo $filter_date; ?>">
                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3">Filter</button>
            </form>
        </div>
    </div>

    <?php if(isset($_SESSION['error'])): ?><div class="alert alert-danger no-print"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div><?php endif; ?>
    <?php if(isset($_SESSION['success'])): ?><div class="alert alert-success no-print"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div><?php endif; ?>

    <!-- ২. কুইক এন্ট্রি বাটনসমূহ -->
    <div class="row g-4 mb-4 no-print">
        <div class="col-md-6">
            <button class="toggle-btn btn-inc shadow-sm" onclick="toggleForm('income')"><i class="fas fa-plus-circle me-2"></i> আয়ের হিসাব যোগ করুন</button>
        </div>
        <div class="col-md-6">
            <button class="toggle-btn btn-exp shadow-sm" onclick="toggleForm('expense')"><i class="fas fa-minus-circle me-2"></i> ব্যয়ের হিসাব যোগ করুন</button>
        </div>
    </div>

    <!-- ৩. হিডেন এন্ট্রি ফরম -->
    <div id="formContainer" class="card report-card p-4 mb-5 no-print border-top border-5 border-primary shadow-lg">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 id="formTitle" class="fw-bold text-navy mb-0">নতুন হিসাব যোগ করুন</h5>
            <button type="button" class="btn-close" onclick="document.getElementById('formContainer').style.display='none'"></button>
        </div>
        <form action="" method="POST" class="row g-3">
            <input type="hidden" name="type" id="typeSelect">
            <div class="col-md-3"><label class="small fw-bold">খাত (Category)</label><select name="category" id="catSelect" class="form-select shadow-none" required></select></div>
            <div class="col-md-2"><label class="small fw-bold">রশিদ নং</label><input type="text" name="receipt_no" class="form-control shadow-none" placeholder="No"></div>
            <div class="col-md-2"><label class="small fw-bold">পরিমাণ (৳)</label><input type="number" name="amount" class="form-control shadow-none fw-bold" required></div>
            <div class="col-md-4"><label class="small fw-bold">বিবরণ</label><input type="text" name="description" class="form-control shadow-none" placeholder="বিস্তারিত লিখুন"></div>
            <input type="hidden" name="date" value="<?php echo $filter_date; ?>">
            <div class="col-md-1"><label>&nbsp;</label><button type="submit" name="add_transaction" class="btn btn-primary w-80 fw-bold shadow">Save</button></div>
        </form>
    </div>

    <!-- ৪. সেক্টর ভিত্তিক সামারি গ্রিড -->
    <div class="row g-3 mb-4 text-center">
        <div class="col-md-3"><div class="summary-box" style="background:#0A2647"><span>ডাক্তার ফি (মোট)</span><h3 class="fw-bold mb-0">৳<?php echo number_format(getSectorTotal($conn, $filter_date, 'ডাক্তার ফি')); ?></h3></div></div>
        <div class="col-md-3"><div class="summary-box" style="background:#10b981"><span>ল্যাব ইনকাম</span><h3 class="fw-bold mb-0">৳<?php echo number_format(getSectorTotal($conn, $filter_date, 'ল্যাব (Lab)')); ?></h3></div></div>
        <div class="col-md-3"><div class="summary-box" style="background:#f59e0b"><span>সিট ও অন্যান্য</span><h3 class="fw-bold mb-0">৳<?php 
            $seat = getSectorTotal($conn, $filter_date, 'সিট ভাড়া');
            $adm = getSectorTotal($conn, $filter_date, 'ভর্তি ও চিকিৎসা');
            echo number_format($seat + $adm); 
        ?></h3></div></div>
        <div class="col-md-3"><div class="summary-box" style="background:#dc3545"><span>মোট ব্যয় (Expense)</span><h3 class="fw-bold mb-0">৳<?php 
            $exp_sql = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as total FROM hospital_accounts WHERE date = '$filter_date' AND type = 'expense'"));
            echo number_format($exp_sql['total'] ?? 0);
        ?></h3></div></div>
    </div>

    <!-- ৫. বিস্তারিত রিপোর্ট (ডাক্তার ও ক্যাটাগরি) -->
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card report-card h-100 overflow-hidden">
                <div class="card-header bg-white py-3 border-0"><h5 class="fw-bold text-navy mb-0"><i class="fas fa-user-md me-2 text-primary"></i>ডাক্তারদের ফি কালেকশন লিস্ট</h5></div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small"><tr><th>ডাক্তারের নাম</th><th class="text-center">রোগী</th><th class="text-end pe-4">মোট টাকা</th></tr></thead>
                        <tbody>
                            <?php while($doc = mysqli_fetch_assoc($doctor_report)): ?>
                            <tr>
                                <td class="ps-3"><strong><?php echo preg_replace('/.*ডাক্তার: /', '', $doc['doc_info']); ?></strong></td>
                                <td class="text-center"><span class="badge bg-light text-navy border"><?php echo $doc['patient_count']; ?> জন</span></td>
                                <td class="text-end pe-4 fw-bold text-primary">৳<?php echo number_format($doc['total']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card report-card p-4 h-100">
                <h5 class="fw-bold text-navy mb-3 border-bottom pb-2">হিসাব বিবরণী (আয়)</h5>
                <table class="table table-sm table-bordered mt-2">
                    <?php foreach($income_groups as $catName => $rows): ?>
                        <tr class="cat-header"><td colspan="3"><?php echo $catName; ?></td></tr>
                        <?php foreach($rows as $row): 
                            $locked = ((strtotime(date('Y-m-d')) - strtotime($row['date'])) / 86400 > 2 && $user_role != 'admin');
                        ?>
                        <tr class="<?php echo $locked ? 'locked-row' : ''; ?>">
                            <td><small class="text-muted">#<?php echo $row['receipt_no']; ?></small><br><?php echo $row['description']; ?></td>
                            <td class="text-end fw-bold">৳<?php echo number_format($row['amount']); ?></td>
                            <td class="text-center no-print">
                                <?php if(!$locked): ?>
                                    <i class="fas fa-edit text-primary cursor-pointer me-2" onclick="editModal('<?php echo $row['id']; ?>','<?php echo $row['amount']; ?>','<?php echo $row['receipt_no']; ?>','<?php echo $row['description']; ?>')"></i>
                                    <a href="?del_id=<?php echo $row['id']; ?>&date=<?= $filter_date ?>" class="text-danger" onclick="return confirm('মুছবেন?')"><i class="fas fa-trash-alt"></i></a>
                                <?php else: ?><i class="fas fa-lock text-muted"></i><?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- এডিট মডাল -->
<div class="modal fade" id="eMod" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="" method="POST" class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-navy text-white" style="background:#0A2647"><h5 class="modal-title">সংশোধন করুন</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-4">
                <input type="hidden" name="edit_id" id="eid">
                <div class="mb-3"><label class="small fw-bold">রশিদ নং</label><input type="text" name="receipt_no" id="erec" class="form-control rounded-3 shadow-none"></div>
                <div class="mb-3"><label class="small fw-bold">পরিমাণ</label><input type="number" name="amount" id="eam" class="form-control rounded-3 shadow-none" required></div>
                <div class="mb-0"><label class="small fw-bold">বিবরণ</label><input type="text" name="description" id="edesc" class="form-control rounded-3 shadow-none"></div>
            </div>
            <div class="modal-footer border-0"><button type="submit" name="update_transaction" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow">Save Changes</button></div>
        </form>
    </div>
</div>

<script>
const cats = {
    income: ['ল্যাব (Lab)', 'ডাক্তার ফি', 'ভর্তি ফি', 'সিট ভাড়া', 'ওটি (OT)', 'অক্সিজেন', 'ভর্তি ও চিকিৎসা', 'সার্ভিস চার্জ', 'অন্যান্য আয়'],
    expense: ['স্টাফ বেতন', 'ল্যাব রি-এজেন্ট', 'হাসপাতাল ভাড়া', 'বিদ্যুৎ বিল', 'মেডিকেল সামগ্রী', 'পরিচ্ছন্নতা', 'পরিবহন', 'মার্কেটিং', 'অন্যান্য ব্যয়']
};

function toggleForm(type) {
    document.getElementById('formContainer').style.display = 'block';
    document.getElementById('typeSelect').value = type;
    document.getElementById('formTitle').innerText = (type === 'income') ? 'নতুন আয়ের হিসাব যোগ করুন' : 'নতুন ব্যয়ের হিসাব যোগ করুন';
    updateCategories();
    window.scrollTo({ top: document.getElementById('formContainer').offsetTop - 50, behavior: 'smooth' });
}

function updateCategories() {
    const type = document.getElementById('typeSelect').value;
    const select = document.getElementById('catSelect');
    if(!select) return;
    select.innerHTML = "";
    cats[type].forEach(c => {
        let opt = document.createElement('option');
        opt.value = c; opt.innerHTML = c;
        select.appendChild(opt);
    });
}

function editModal(id, am, rec, desc) {
    document.getElementById('eid').value = id;
    document.getElementById('eam').value = am;
    document.getElementById('erec').value = rec;
    document.getElementById('edesc').value = desc;
    new bootstrap.Modal(document.getElementById('eMod')).show();
}
window.onload = updateCategories;
</script>

<?php include_once '../../includes/footer.php'; ?>