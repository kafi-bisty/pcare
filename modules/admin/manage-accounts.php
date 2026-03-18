<?php
session_start();
include_once '../../config/database.php';
include_once '../../config/constants.php';
include_once '../../config/functions.php';

// ১. সিকিউরিটি চেক
$allowed_roles = ['admin', 'manager', 'accounts'];
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], $allowed_roles)) {
    header("Location: ../auth/staff-login.php"); exit;
}
$user_role = $_SESSION['user_role'];

// ২. নতুন ডাটা সেভ লজিক
if (isset($_POST['add_transaction'])) {
    $type = $_POST['type'];
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);
    $receipt_no = mysqli_real_escape_string($conn, $_POST['receipt_no']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $date = mysqli_real_escape_string($conn, $_POST['date']);

    mysqli_query($conn, "INSERT INTO hospital_accounts (type, category, amount, receipt_no, description, date) VALUES ('$type', '$category', '$amount', '$receipt_no', '$desc', '$date')");
    log_activity($conn, "ACCOUNTING", "$user_role একটি $type যোগ করেছেন: $category ($amount ৳)");
    header("Location: manage-accounts.php?date=$date"); exit;
}

// ৩. আপডেট লজিক (২ দিনের লক কন্ডিশন সহ)
if (isset($_POST['update_transaction'])) {
    $id = $_POST['edit_id'];
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);
    $receipt_no = mysqli_real_escape_string($conn, $_POST['receipt_no']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    
    // সময় যাচাই
    $check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT date FROM hospital_accounts WHERE id = '$id'"));
    $days_diff = (strtotime(date('Y-m-d')) - strtotime($check['date'])) / 86400;

    if ($days_diff <= 2 || $user_role == 'admin') {
        mysqli_query($conn, "UPDATE hospital_accounts SET amount='$amount', receipt_no='$receipt_no', description='$desc' WHERE id='$id'");
        $_SESSION['success'] = "সফলভাবে আপডেট করা হয়েছে!";
    } else {
        $_SESSION['error'] = "দুঃখিত! ২ দিন পার হয়ে যাওয়ায় এটি লক হয়ে গেছে।";
    }
    header("Location: manage-accounts.php"); exit;
}

// ৪. ডিলিট লজিক
if (isset($_GET['del_id'])) {
    $id = $_GET['del_id'];
    $check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT date FROM hospital_accounts WHERE id = '$id'"));
    $days_diff = (strtotime(date('Y-m-d')) - strtotime($check['date'])) / 86400;

    if ($days_diff <= 2 || $user_role == 'admin') {
        mysqli_query($conn, "DELETE FROM hospital_accounts WHERE id = '$id'");
        $_SESSION['success'] = "সফলভাবে মুছে ফেলা হয়েছে!";
    } else {
        $_SESSION['error'] = "লক করা ডাটা ডিলিট করা সম্ভব নয়।";
    }
    header("Location: manage-accounts.php"); exit;
}

$filter_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// ৫. ডাটা গ্রুপিং ফাংশন
function getGroupedData($conn, $date, $type) {
    $data = [];
    $res = mysqli_query($conn, "SELECT * FROM hospital_accounts WHERE date = '$date' AND type = '$type' ORDER BY category ASC");
    while($row = mysqli_fetch_assoc($res)) {
        $data[$row['category']][] = $row;
    }
    return $data;
}

$income_groups = getGroupedData($conn, $filter_date, 'income');
$expense_groups = getGroupedData($conn, $filter_date, 'expense');

include_once '../../includes/header.php';
?>

<style>
    :root { --navy: #0A2647; --cyan: #2AA7E5; }
    .cat-header { background-color: #f1f5f9; font-weight: 800; color: var(--navy); font-size: 13px; }
    .sub-total { background-color: #fafafa; font-weight: bold; border-top: 1px solid #ddd; }
    .locked-row { opacity: 0.6; }
    .cursor-pointer { cursor: pointer; }
    @media print { .no-print, .navbar, .notice-container, footer { display: none !important; } .container { width: 100% !important; max-width: 100% !important; margin: 0 !important; } .print-only-header { display: block !important; } }
    .print-only-header { display: none; }
</style>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h3 class="fw-bold text-navy mb-0"><i class="fas fa-calculator me-2"></i>আয় ও ব্যয় ব্যবস্থাপনা</h3>
        <div class="d-flex gap-2">
            <form action="" method="GET" class="d-flex gap-2">
                <input type="date" name="date" class="form-control form-control-sm rounded-pill px-3 shadow-none" value="<?php echo $filter_date; ?>">
                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3">Filter</button>
            </form>
            <button onclick="window.print()" class="btn btn-sm btn-dark rounded-pill px-3 shadow-sm"><i class="fas fa-print"></i> Report</button>
        </div>
    </div>

    <!-- ইনপুট ফরম -->
    <?php if($user_role == 'accounts' || $user_role == 'admin'): ?>
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 no-print bg-white border-top border-4 border-primary">
        <form action="" method="POST" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="small fw-bold">ধরণ</label>
                <select name="type" id="mainType" class="form-select shadow-none" onchange="updateCats()" required>
                    <option value="income">আয় (Income)</option>
                    <option value="expense">ব্যয় (Expense)</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="small fw-bold">খাত</label>
                <select name="category" id="catSelect" class="form-select shadow-none" required></select>
            </div>
            <div class="col-md-2">
                <label class="small fw-bold">রশিদ নং</label>
                <input type="text" name="receipt_no" class="form-control shadow-none" required>
            </div>
            <div class="col-md-2">
                <label class="small fw-bold">টাকা</label>
                <input type="number" name="amount" class="form-control shadow-none" required>
            </div>
            <div class="col-md-2">
                <label class="small fw-bold">বিবরণ</label>
                <input type="text" name="description" class="form-control shadow-none">
            </div>
            <input type="hidden" name="date" value="<?php echo $filter_date; ?>">
            <div class="col-md-2">
                <button type="submit" name="add_transaction" class="btn btn-primary w-100 rounded-pill fw-bold">Save</button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- অডিট রিপোর্ট বোর্ড -->
    <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white">
        <div class="print-only-header text-center mb-4">
            <h2 class="fw-bold text-navy mb-0">পেশেন্ট কেয়ার হাসপাতাল এন্ড ডায়াগনস্টিক সেন্টার</h2>
            <p class="small text-muted">বরগুনা। হেল্পলাইন: +৮৮০ ১৩৩১৪ ৩৪৩৪৭</p>
            <h4 class="mt-3 fw-bold border-top border-bottom py-2">দৈনিক আয় ও ব্যয় রিপোর্ট</h4>
            <p class="text-muted small">তারিখ: <?php echo date('d M, Y', strtotime($filter_date)); ?></p>
        </div>

        <div class="row g-4">
            <!-- আয় তালিকা -->
            <div class="col-lg-6">
                <h6 class="fw-bold text-success mb-3 border-bottom pb-2">আয় সমূহ (Income)</h6>
                <table class="table table-sm table-bordered align-middle">
                    <tbody>
                        <?php $grandTotalIn = 0; 
                        foreach($income_groups as $catName => $rows): 
                            $catTotal = 0; ?>
                            <tr class="cat-header"><td colspan="4"><?php echo $catName; ?></td></tr>
                            <?php foreach($rows as $row): 
                                $catTotal += $row['amount']; $grandTotalIn += $row['amount']; 
                                $diff = (strtotime(date('Y-m-d')) - strtotime($row['date'])) / 86400;
                                $is_locked = ($diff > 2 && $user_role != 'admin');
                            ?>
                                <tr class="<?php echo $is_locked ? 'locked-row' : ''; ?>">
                                    <td width="20%" class="small">#<?php echo $row['receipt_no']; ?></td>
                                    <td width="50%" class="small"><?php echo $row['description']; ?></td>
                                    <td width="20%" class="text-end fw-bold">৳<?php echo number_format($row['amount']); ?></td>
                                    <td width="10%" class="text-center no-print">
                                        <?php if(!$is_locked): ?>
                                            <i class="fas fa-edit text-primary cursor-pointer me-1" onclick="openEditModal('<?php echo $row['id']; ?>','<?php echo $row['amount']; ?>','<?php echo $row['receipt_no']; ?>','<?php echo $row['description']; ?>','<?php echo $row['category']; ?>')"></i>
                                            <a href="?del_id=<?php echo $row['id']; ?>" class="text-danger" onclick="return confirm('মুছবেন?')"><i class="fas fa-trash-alt"></i></a>
                                        <?php else: ?> <i class="fas fa-lock text-muted"></i> <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- ব্যয় তালিকা -->
            <div class="col-lg-6">
                <h6 class="fw-bold text-danger mb-3 border-bottom pb-2">ব্যয় সমূহ (Expense)</h6>
                <table class="table table-sm table-bordered align-middle">
                    <tbody>
                        <?php $grandTotalEx = 0; 
                        foreach($expense_groups as $catName => $rows): 
                            $catTotal = 0; ?>
                            <tr class="cat-header"><td colspan="4"><?php echo $catName; ?></td></tr>
                            <?php foreach($rows as $row): 
                                $catTotal += $row['amount']; $grandTotalEx += $row['amount']; 
                                $diff = (strtotime(date('Y-m-d')) - strtotime($row['date'])) / 86400;
                                $is_locked = ($diff > 2 && $user_role != 'admin');
                            ?>
                                <tr class="<?php echo $is_locked ? 'locked-row' : ''; ?>">
                                    <td width="20%" class="small">#<?php echo $row['receipt_no']; ?></td>
                                    <td width="50%" class="small"><?php echo $row['description']; ?></td>
                                    <td width="20%" class="text-end fw-bold">৳<?php echo number_format($row['amount']); ?></td>
                                    <td width="10%" class="text-center no-print">
                                        <?php if(!$is_locked): ?>
                                            <i class="fas fa-edit text-primary cursor-pointer me-1" onclick="openEditModal('<?php echo $row['id']; ?>','<?php echo $row['amount']; ?>','<?php echo $row['receipt_no']; ?>','<?php echo $row['description']; ?>','<?php echo $row['category']; ?>')"></i>
                                            <a href="?del_id=<?php echo $row['id']; ?>" class="text-danger" onclick="return confirm('মুছবেন?')"><i class="fas fa-trash-alt"></i></a>
                                        <?php else: ?> <i class="fas fa-lock text-muted"></i> <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-5 offset-md-7">
                <div class="p-3 border rounded shadow-sm bg-light text-end">
                    <div class="d-flex justify-content-between mb-1"><span>মোট আয়:</span> <span class="text-success fw-bold">৳<?php echo number_format($grandTotalIn); ?></span></div>
                    <div class="d-flex justify-content-between mb-2"><span>মোট ব্যয়:</span> <span class="text-danger fw-bold">৳<?php echo number_format($grandTotalEx); ?></span></div>
                    <div class="d-flex justify-content-between border-top border-dark pt-2">
                        <h5 class="fw-bold text-navy">নিট ব্যালেন্স:</h5>
                        <h5 class="fw-bold text-primary">৳<?php echo number_format($grandTotalIn - $grandTotalEx); ?></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- এডিট মডাল -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-navy text-white" style="background:var(--primary-navy)">
                <h5 class="modal-title">তথ্য সংশোধন করুন</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="edit_id" id="edit_id">
                    <input type="hidden" name="category" id="edit_cat">
                    <div class="mb-3"><label class="small fw-bold">রশিদ/ভাউচার নং</label><input type="text" name="receipt_no" id="edit_receipt" class="form-control rounded-3" required></div>
                    <div class="mb-3"><label class="small fw-bold">টাকার পরিমাণ</label><input type="number" name="amount" id="edit_amount" class="form-control rounded-3" required></div>
                    <div class="mb-0"><label class="small fw-bold">বিস্তারিত বিবরণ</label><input type="text" name="description" id="edit_desc" class="form-control rounded-3"></div>
                </div>
                <div class="modal-footer border-0 pb-4">
                    <button type="submit" name="update_transaction" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow">পরিবর্তন সেভ করুন</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const incomeList = ['ল্যাব (Lab)', 'ডাক্তার ফি', 'সিট ভাড়া', 'অক্সিজেন', 'ওটি (OT)', 'সার্ভিস চার্জ'];
const expenseList = ['স্টাফ বেতন', 'বিদ্যুৎ বিল', 'হাসপাতাল ভাড়া', 'পরিচ্ছন্নতা', 'মেডিকেল সামগ্রী', 'মার্কেটিং','পরিবহন']

function updateCats() {
    const type = document.getElementById('mainType').value;
    const select = document.getElementById('catSelect');
    select.innerHTML = "";
    const list = (type === 'income') ? incomeList : expenseList;
    list.forEach(c => {
        let opt = document.createElement('option');
        opt.value = c; opt.innerHTML = c;
        select.appendChild(opt);
    });
}

function openEditModal(id, amount, receipt, desc, cat) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_cat').value = cat;
    document.getElementById('edit_amount').value = amount;
    document.getElementById('edit_receipt').value = receipt;
    document.getElementById('edit_desc').value = desc;
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

window.onload = updateCats;
</script>

<?php include_once '../../includes/footer.php'; ?>