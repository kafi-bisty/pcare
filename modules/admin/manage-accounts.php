<?php
/**
 * ১. লজিক সেকশন (সবার আগে - কোনো স্পেস বা HTML এর আগে)
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../../config/database.php';
include_once '../../config/constants.php';
include_once '../../config/functions.php';

// সিকিউরিটি চেক
$allowed_roles = ['admin', 'manager', 'accounts'];
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], $allowed_roles)) {
    header("Location: ../auth/staff-login.php"); exit;
}
$user_role = $_SESSION['user_role'];

// ২. ডিলিট লজিক (২ দিনের লক সহ)
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
    header("Location: manage-accounts.php"); exit;
}

// ৩. আপডেট লজিক (২ দিনের লক সহ)
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
    } else {
        $_SESSION['error'] = "২ দিন পার হয়ে যাওয়ায় এডিট করা সম্ভব নয়।";
    }
    header("Location: manage-accounts.php"); exit;
}

// ৪. নতুন হিসাব যোগ করার লজিক
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

$filter_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

function getGroupedData($conn, $date, $type) {
    $data = [];
    $res = mysqli_query($conn, "SELECT * FROM hospital_accounts WHERE date = '$date' AND type = '$type' ORDER BY id DESC");
    while($row = mysqli_fetch_assoc($res)) { $data[$row['category']][] = $row; }
    return $data;
}

$income_groups = getGroupedData($conn, $filter_date, 'income');
$expense_groups = getGroupedData($conn, $filter_date, 'expense');

include_once '../../includes/header.php';
?>

<style>
    :root { --navy: #0A2647; --cyan: #2AA7E5; }
    body { background-color: #f4f7f6; }
    .report-card { background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: none; }
    .cat-header { background-color: #f1f5f9; font-weight: 800; color: var(--navy); font-size: 13px; }
    .locked-row { opacity: 0.6; background-color: #fcfcfc; }
    .summary-box { border-radius: 15px; padding: 18px; color: white; text-align: center; }
    
    @media print {
        .no-print, .navbar, footer, .btn-close { display: none !important; }
        body { background: white !important; }
        .container { width: 100% !important; max-width: 100% !important; }
        .report-card { box-shadow: none !important; border: 1px solid #eee !important; }
        .print-header { display: block !important; text-align: center; border-bottom: 2px solid #000; margin-bottom: 20px; }
    }
    .print-header { display: none; }
</style>

<div class="container py-4">
    <!-- প্রিন্ট হেডার -->
    <div class="print-header">
        <h2 class="fw-bold">PATIENT CARE HOSPITAL</h2>
        <p>Daily Income & Expenditure Statement</p>
        <strong>Date: <?php echo date('d F, Y', strtotime($filter_date)); ?></strong>
    </div>

    <!-- ২. পেজ হেডার এবং আপনার দেওয়া বাটনসমূহ -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print flex-wrap gap-2">
        <h3 class="fw-bold text-navy mb-0"><i class="fas fa-file-invoice-dollar me-2 text-primary"></i>মেইন ক্যাশ অ্যাকাউন্ট</h3>
        <div class="d-flex gap-2 flex-wrap">
            <a href="admission-manager.php" class="btn btn-sm btn-dark rounded-pill px-3 shadow-sm fw-bold"><i class="fas fa-bed"></i> পেশেন্ট এডমিশন</a>
            <a href="lab-billing.php" class="btn btn-sm btn-info text-white rounded-pill px-3 shadow-sm fw-bold"><i class="fas fa-flask"></i> ল্যাব বিলিং</a>
            <a href="manage-lab-tests.php" class="btn btn-sm btn-warning text-white rounded-pill px-3 shadow-sm fw-bold"><i class="fas fa-vial"></i> ল্যাব টেস্ট ম্যানেজমেন্ট</a>
            <a href="patient-billing.php" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm fw-bold"><i class="fas fa-ticket-alt"></i> মানি রিসিট</a>
            
            <form action="" method="GET" class="d-flex gap-1">
                <input type="date" name="date" class="form-control form-control-sm rounded-pill border-primary" value="<?php echo $filter_date; ?>">
                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3">Filter</button>
            </form>
           <!-- আগের বাটনের জায়গায় এটি দিন -->
<a href="print-report.php?date=<?php echo $filter_date; ?>" target="_blank" class="btn btn-sm btn-danger rounded-pill px-3 shadow-sm fw-bold">
    <i class="fas fa-file-pdf me-1"></i> Print Professional Report
</a>
        </div>
    </div>

    <!-- ৩. মেসেজ ডিসপ্লে -->
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger no-print alert-dismissible fade show"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?> <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success no-print alert-dismissible fade show"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?> <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <!-- ৪. নতুন এন্ট্রি ফরম -->
    <div class="card report-card p-4 mb-4 no-print border-top border-5 border-primary">
        <h6 class="fw-bold text-navy mb-3"><i class="fas fa-plus-circle me-1"></i>ম্যানুয়াল হিসাব এন্ট্রি</h6>
        <form action="" method="POST" class="row g-3">
            <div class="col-md-2">
                <label class="small fw-bold">ধরণ</label>
                <select name="type" id="typeSelect" class="form-select shadow-none" onchange="updateCategories()" required>
                    <option value="income">আয়</option>
                    <option value="expense">ব্যয়</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="small fw-bold">খাত</label>
                <select name="category" id="catSelect" class="form-select shadow-none" required></select>
            </div>
            <div class="col-md-2">
                <label class="small fw-bold">রশিদ নং</label>
                <input type="text" name="receipt_no" class="form-control shadow-none">
            </div>
            <div class="col-md-2">
                <label class="small fw-bold">পরিমাণ</label>
                <input type="number" name="amount" class="form-control shadow-none" required>
            </div>
            <div class="col-md-3">
                <label class="small fw-bold">বিবরণ</label>
                <input type="text" name="description" class="form-control shadow-none">
            </div>
            <input type="hidden" name="date" value="<?php echo $filter_date; ?>">
            <div class="col-md-1"><label>&nbsp;</label><button type="submit" name="add_transaction" class="btn btn-primary w-100 fw-bold">Save</button></div>
        </form>
    </div>

    <!-- ৫. সামারি কার্ডস -->
    <?php
    $t_in = 0; foreach($income_groups as $cat) foreach($cat as $r) $t_in += $r['amount'];
    $t_ex = 0; foreach($expense_groups as $cat) foreach($cat as $r) $t_ex += $r['amount'];
    ?>
    <div class="row g-3 mb-4 text-center">
        <div class="col-md-4"><div class="summary-box" style="background:#28a745"><span>Total Income</span><h2 class="fw-bold mb-0">৳<?php echo number_format($t_in); ?></h2></div></div>
        <div class="col-md-4"><div class="summary-box" style="background:#dc3545"><span>Total Expense</span><h2 class="fw-bold mb-0">৳<?php echo number_format($t_ex); ?></h2></div></div>
        <div class="col-md-4"><div class="summary-box" style="background:#0A2647"><span>Net Balance</span><h2 class="fw-bold mb-0">৳<?php echo number_format($t_in - $t_ex); ?></h2></div></div>
    </div>

    <!-- ৬. মেইন টেবিল -->
    <div class="row g-4">
        <!-- আয় তালিকা -->
        <div class="col-md-6">
            <div class="card report-card p-3 h-100">
                <h6 class="fw-bold text-success border-bottom pb-2">Incomes (আয় সমূহ)</h6>
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
                                    <a href="?del_id=<?php echo $row['id']; ?>" class="text-danger" onclick="return confirm('মুছবেন?')"><i class="fas fa-trash-alt"></i></a>
                                <?php else: ?> <i class="fas fa-lock text-muted"></i> <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>

        <!-- ব্যয় তালিকা -->
        <div class="col-md-6">
            <div class="card report-card p-3 h-100">
                <h6 class="fw-bold text-danger border-bottom pb-2">Expenses (ব্যয় সমূহ)</h6>
                <table class="table table-sm table-bordered mt-2">
                    <?php foreach($expense_groups as $catName => $rows): ?>
                        <tr class="cat-header"><td colspan="3"><?php echo $catName; ?></td></tr>
                        <?php foreach($rows as $row): 
                            $locked = ((strtotime(date('Y-m-d')) - strtotime($row['date'])) / 86400 > 2 && $user_role != 'admin');
                        ?>
                        <tr class="<?php echo $locked ? 'locked-row' : ''; ?>">
                            <td><small class="text-muted">#<?php echo $row['receipt_no']; ?></small><br><?php echo $row['description']; ?></td>
                            <td class="text-end fw-bold text-danger">৳<?php echo number_format($row['amount']); ?></td>
                            <td class="text-center no-print">
                                <?php if(!$locked): ?>
                                    <i class="fas fa-edit text-primary cursor-pointer me-2" onclick="editModal('<?php echo $row['id']; ?>','<?php echo $row['amount']; ?>','<?php echo $row['receipt_no']; ?>','<?php echo $row['description']; ?>')"></i>
                                    <a href="?del_id=<?php echo $row['id']; ?>" class="text-danger" onclick="return confirm('মুছবেন?')"><i class="fas fa-trash-alt"></i></a>
                                <?php else: ?> <i class="fas fa-lock text-muted"></i> <?php endif; ?>
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
        <form action="" method="POST" class="modal-content border-0 shadow rounded-4">
            <div class="modal-header bg-navy text-white" style="background:#0A2647"><h5 class="modal-title">সংশোধন করুন</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-4">
                <input type="hidden" name="edit_id" id="eid">
                <div class="mb-3"><label class="small fw-bold">রশিদ নং</label><input type="text" name="receipt_no" id="erec" class="form-control"></div>
                <div class="mb-3"><label class="small fw-bold">পরিমাণ</label><input type="number" name="amount" id="eam" class="form-control" required></div>
                <div class="mb-0"><label class="small fw-bold">বিবরণ</label><input type="text" name="description" id="edesc" class="form-control"></div>
            </div>
            <div class="modal-footer border-0"><button type="submit" name="update_transaction" class="btn btn-primary w-100 rounded-pill py-2 fw-bold">Update Changes</button></div>
        </form>
    </div>
</div>

<script>
const cats = {
    income: ['ল্যাব (Lab)', 'ডাক্তার ফি', 'ভর্তি ফি', 'সিট ভাড়া', 'ওটি (OT)', 'অক্সিজেন', 'ভর্তি ও চিকিৎসা', 'সার্ভিস চার্জ', 'অন্যান্য আয়'],
    expense: ['স্টাফ বেতন', 'বিদ্যুৎ বিল', 'ল্যাব রি-এজেন্ট', 'হাসপাতাল ভাড়া', 'মেডিকেল সামগ্রী', 'পরিচ্ছন্নতা', 'পরিবহন', 'মার্কেটিং', 'অন্যান্য ব্যয়']
};
function updateCategories() {
    const type = document.getElementById('typeSelect').value;
    const select = document.getElementById('catSelect');
    select.innerHTML = "";
    cats[type].forEach(c => { let opt = document.createElement('option'); opt.value = c; opt.innerHTML = c; select.appendChild(opt); });
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