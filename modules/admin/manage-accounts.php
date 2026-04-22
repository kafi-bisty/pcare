<?php
session_start();
include_once '../../config/database.php';
include_once '../../config/constants.php';
include_once '../../config/functions.php';

// ১. সিকিউরিটি ও রোল চেক
$allowed_roles = ['admin', 'manager', 'accounts'];
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], $allowed_roles)) {
    header("Location: ../auth/staff-login.php"); exit;
}
$user_role = $_SESSION['user_role'];

// ২. নতুন হিসাব যোগ করার লজিক
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
        $_SESSION['success'] = "সফলভাবে আপডেট হয়েছে!";
    }
    header("Location: manage-accounts.php"); exit;
}

// ৪. ডিলিট লজিক
if (isset($_GET['del_id']) && $user_role == 'admin') {
    mysqli_query($conn, "DELETE FROM hospital_accounts WHERE id = '{$_GET['del_id']}'");
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
    body { background-color: #f4f7f6; }
    .report-card { background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: none; }
    .cat-header { background-color: #f1f5f9; font-weight: 800; color: var(--navy); font-size: 13px; }
    .sub-total { background-color: #fafafa; font-weight: bold; border-top: 1px solid #ddd; }
    .locked-row { opacity: 0.6; }
    .print-only-header { display: none; }
    
    @media print {
        @page { size: A4; margin: 10mm; }
        .no-print, .navbar, .notice-container, footer, .top-header { display: none !important; }
        .container { width: 100% !important; max-width: 100% !important; margin: 0 !important; padding: 0 !important; }
        .report-card { box-shadow: none !important; border: 1px solid #eee !important; border-radius: 0; }
        .print-only-header { display: block !important; }
    }
</style>

<div class="container py-4">
    
    <!-- পেজ হেডার (আপনার নতুন যোগ করা সেকশন) -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h3 class="fw-bold text-navy mb-0"><i class="fas fa-calculator me-2 text-primary"></i>আয় ও ব্যয় ব্যবস্থাপনা</h3>
        
        <div class="d-flex gap-2">
            <!-- রোগীর মানি রিসিট বাটন -->
            <a href="patient-billing.php" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm fw-bold">
                <i class="fas fa-file-invoice me-1"></i> রোগীর মানি রিসিট
            </a>

             <a href="lab-billing.php" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm fw-bold">
                <i class="fas fa-file-invoice me-1"></i> ল্যাব বিলিং
            </a>
   
            <a href="manage-lab-tests.php" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm fw-bold">
                <i class="fas fa-file-invoice me-1"></i> ল্যাব টেস্ট ম্যানেজমেন্ট
            </a>
   


            <!-- ফিল্টার ফরম -->
            <form action="" method="GET" class="d-flex gap-2">
                <input type="date" name="date" class="form-control form-control-sm rounded-pill px-3 shadow-none border-primary" value="<?php echo $filter_date; ?>">
                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3">Filter</button>
            </form>

            <!-- প্রিন্ট বাটন -->
            <button onclick="window.print()" class="btn btn-sm btn-dark rounded-pill px-3 shadow-sm">
                <i class="fas fa-print me-1"></i> Report
            </button>
        </div>
    </div>

    <!-- ইনপুট ফরম (No Print) -->
    <?php if(in_array($user_role, ['admin', 'accounts'])): ?>
    <div class="card report-card p-4 mb-4 no-print border-top border-4 border-primary shadow">
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
                <input type="text" name="receipt_no" class="form-control shadow-none" placeholder="No" required>
            </div>
            <div class="col-md-2">
                <label class="small fw-bold">পরিমাণ (৳)</label>
                <input type="number" name="amount" class="form-control shadow-none" placeholder="0.00" required>
            </div>
            <div class="col-md-2">
                <label class="small fw-bold">বিবরণ</label>
                <input type="text" name="description" class="form-control shadow-none" placeholder="বিস্তারিত">
            </div>
            <input type="hidden" name="date" value="<?php echo $filter_date; ?>">
            <div class="col-md-2 text-end">
                <button type="submit" name="add_transaction" class="btn btn-primary w-100 rounded-pill fw-bold shadow">Save</button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- মেইন রিপোর্ট বোর্ড -->
    <div class="card report-card p-4 p-md-5 bg-white">
        <!-- প্রিন্ট হেডার -->
        <div class="print-only-header text-center mb-4">
            <h2 class="fw-bold text-navy mb-0">পেশেন্ট কেয়ার হাসপাতাল এন্ড ডায়াগনস্টিক সেন্টার</h2>
            <p class="small text-muted mb-0">বরগুনা। হেল্পলাইন: +৮৮০ ১৩৩১৪ ৩৪৩৪৭</p>
            <h4 class="mt-4 fw-bold border-top border-bottom py-2 uppercase">Daily Income-Expense Report</h4>
            <p class="text-muted fw-bold">তারিখ: <b><?php echo date('d F, Y', strtotime($filter_date)); ?></b></p>
        </div>

        <!-- আয়ের তালিকা -->
        <div class="mb-5">
            <h6 class="fw-bold text-success mb-3 border-bottom pb-2">আয় সমূহ (Income Details)</h6>
            <table class="table table-bordered align-middle">
                <tbody>
                    <?php $grandTotalIn = 0; 
                    foreach($income_groups as $catName => $rows): 
                        $catTotal = 0; ?>
                        <tr class="cat-header"><td colspan="4"><?php echo $catName; ?></td></tr>
                        <?php foreach($rows as $row): 
                            $catTotal += $row['amount']; $grandTotalIn += $row['amount']; 
                            $is_locked = ((strtotime(date('Y-m-d')) - strtotime($row['date'])) / 86400 > 2 && $user_role != 'admin');
                        ?>
                            <tr class="<?php echo $is_locked ? 'locked-row' : ''; ?>">
                                <td width="15%" class="small">#<?php echo $row['receipt_no']; ?></td>
                                <td width="50%" class="small"><?php echo $row['description']; ?></td>
                                <td width="20%" class="text-end fw-bold">৳<?php echo number_format($row['amount']); ?></td>
                                <td width="15%" class="text-center no-print">
                                    <?php if(!$is_locked): ?>
                                        <i class="fas fa-edit text-primary cursor-pointer me-2" onclick="openEditModal('<?php echo $row['id']; ?>','<?php echo $row['amount']; ?>','<?php echo $row['receipt_no']; ?>','<?php echo $row['description']; ?>','<?php echo $row['category']; ?>')"></i>
                                        <a href="?del_id=<?php echo $row['id']; ?>" class="text-danger" onclick="return confirm('মুছবেন?')"><i class="fas fa-trash-alt"></i></a>
                                    <?php else: ?> <i class="fas fa-lock text-muted"></i> <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="sub-total"><td colspan="2" class="text-end small">মোট (<?php echo $catName; ?>):</td><td class="text-end text-success" colspan="2">৳<?php echo number_format($catTotal); ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- ব্যায়ের তালিকা -->
        <div>
            <h6 class="fw-bold text-danger mb-3 border-bottom pb-2">ব্যয় সমূহ (Expense Details)</h6>
            <table class="table table-bordered align-middle">
                <tbody>
                    <?php $grandTotalEx = 0; 
                    foreach($expense_groups as $catName => $rows): 
                        $catTotal = 0; ?>
                        <tr class="cat-header"><td colspan="4"><?php echo $catName; ?></td></tr>
                        <?php foreach($rows as $row): 
                            $catTotal += $row['amount']; $grandTotalEx += $row['amount']; 
                            $is_locked = ((strtotime(date('Y-m-d')) - strtotime($row['date'])) / 86400 > 2 && $user_role != 'admin');
                        ?>
                            <tr class="<?php echo $is_locked ? 'locked-row' : ''; ?>">
                                <td width="15%" class="small">#<?php echo $row['receipt_no']; ?></td>
                                <td width="50%" class="small"><?php echo $row['description']; ?></td>
                                <td width="20%" class="text-end fw-bold">৳<?php echo number_format($row['amount']); ?></td>
                                <td width="15%" class="text-center no-print">
                                    <?php if(!$is_locked): ?>
                                        <i class="fas fa-edit text-primary cursor-pointer me-2" onclick="openEditModal('<?php echo $row['id']; ?>','<?php echo $row['amount']; ?>','<?php echo $row['receipt_no']; ?>','<?php echo $row['description']; ?>','<?php echo $row['category']; ?>')"></i>
                                        <a href="?del_id=<?php echo $row['id']; ?>" class="text-danger" onclick="return confirm('মুছবেন?')"><i class="fas fa-trash-alt"></i></a>
                                    <?php else: ?> <i class="fas fa-lock text-muted"></i> <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="sub-total"><td colspan="2" class="text-end small">মোট (<?php echo $catName; ?>):</td><td class="text-end text-danger" colspan="2">৳<?php echo number_format($catTotal); ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- গ্র্যান্ড টোটাল -->
        <div class="row mt-4">
            <div class="col-md-5 offset-md-7">
                <div class="p-3 border rounded shadow-sm bg-light text-end">
                    <div class="d-flex justify-content-between mb-1"><span>সর্বমোট আয়:</span> <span class="text-success fw-bold">৳<?php echo number_format($grandTotalIn); ?></span></div>
                    <div class="d-flex justify-content-between mb-2"><span>সর্বমোট ব্যয়:</span> <span class="text-danger fw-bold">৳<?php echo number_format($grandTotalEx); ?></span></div>
                    <div class="d-flex justify-content-between border-top border-dark pt-2">
                        <h5 class="fw-bold text-navy">নিট ক্যাশ (Balance):</h5>
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
                <h5 class="modal-title">সংশোধন করুন</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="edit_id" id="edit_id">
                    <input type="hidden" name="category" id="edit_cat">
                    <div class="mb-3"><label class="small fw-bold">রশিদ নং</label><input type="text" name="receipt_no" id="edit_receipt" class="form-control rounded-3" required></div>
                    <div class="mb-3"><label class="small fw-bold">টাকার পরিমাণ</label><input type="number" name="amount" id="edit_amount" class="form-control rounded-3" required></div>
                    <div class="mb-0"><label class="small fw-bold">বিস্তারিত বিবরণ</label><input type="text" name="description" id="edit_desc" class="form-control rounded-3"></div>
                </div>
                <div class="modal-footer border-0 pb-4">
                    <button type="submit" name="update_transaction" class="btn btn-primary w-100 rounded-pill py-2 shadow fw-bold">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const incomeList = ['ল্যাব (Lab)', 'ভর্তি ফি', 'অন্যান্য আয়', 'ডাক্তার ফি', 'সিট ভাড়া', 'অক্সিজেন', 'ওটি (OT)', 'সার্ভিস চার্জ'];
const expenseList = ['স্টাফ বেতন', 'বিদ্যুৎ বিল', 'হাসপাতাল ভাড়া', 'পরিচ্ছন্নতা', 'মেডিকেল সামগ্রী', 'ল্যাব রি-এজেন্ট', 'অন্যান্য ব্যয়', 'মার্কেটিং', 'পরিবহন'];

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