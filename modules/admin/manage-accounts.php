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

// ২. ডিলিট লজিক (২ দিনের লক সহ)
if (isset($_GET['del_id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['del_id']);
    $check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT date FROM hospital_accounts WHERE id = '$id'"));
    if ($check) {
        $days_diff = (strtotime(date('Y-m-d')) - strtotime($check['date'])) / 86400;
        if ($days_diff <= 2 || $user_role == 'admin') {
            mysqli_query($conn, "DELETE FROM hospital_accounts WHERE id = '$id'");
            $_SESSION['success'] = "হিসাবটি সফলভাবে মুছে ফেলা হয়েছে!";
        } else {
            $_SESSION['error'] = "২ দিন পার হয়ে যাওয়ায় এটি আর ডিলিট করা সম্ভব নয়।";
        }
    }
    header("Location: manage-accounts.php"); exit;
}

// ৩. নতুন লেনদেন (আয়/ব্যয়) যোগ করার লজিক
if (isset($_POST['add_transaction'])) {
    $type = $_POST['type'];
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);
    $receipt_no = mysqli_real_escape_string($conn, $_POST['receipt_no']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    
    $sql = "INSERT INTO hospital_accounts (type, category, amount, receipt_no, description, date) 
            VALUES ('$type', '$category', '$amount', '$receipt_no', '$desc', '$date')";
    
    if(mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "সফলভাবে যোগ করা হয়েছে!";
    }
    header("Location: manage-accounts.php?date=$date"); exit;
}

$filter_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// ৪. ডাটা গ্রুপিং ফাংশন
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
    .locked-row { opacity: 0.5; background-color: #fcfcfc; }
    .summary-box { border-radius: 15px; padding: 20px; color: white; text-align: center; transition: 0.3s; }
    .summary-box:hover { transform: translateY(-5px); }
    .bg-income { background: linear-gradient(45deg, #28a745, #5dd373); }
    .bg-expense { background: linear-gradient(45deg, #dc3545, #ff6b6b); }
    .bg-balance { background: linear-gradient(45deg, #0A2647, #2AA7E5); }
    @media print { .no-print { display: none !important; } }
</style>

<div class="container py-4">
    <!-- ১. এলার্ট মেসেজ -->
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show no-print"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?> <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <?php endif; ?>
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show no-print"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?> <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <?php endif; ?>

    <!-- ২. পেজ হেডার এবং কুইক নেভিগেশন -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print flex-wrap">
        <h3 class="fw-bold text-navy mb-2"><i class="fas fa-file-invoice-dollar me-2 text-primary"></i>মেইন ক্যাশ অ্যাকাউন্ট</h3>
        <div class="d-flex gap-2 flex-wrap">
            <a href="admission-manager.php" class="btn btn-sm btn-dark rounded-pill px-3 shadow-sm fw-bold"><i class="fas fa-bed"></i> পেশেন্ট এডমিশন</a>
            <a href="lab-billing.php" class="btn btn-sm btn-info text-white rounded-pill px-3 shadow-sm fw-bold"><i class="fas fa-flask"></i> ল্যাব বিলিং</a>
             <a href="manage-lab-tests.php" class="btn btn-sm btn-warning text-white rounded-pill px-3 shadow-sm fw-bold"><i class="fas fa-vial"></i> ল্যাব টেস্ট ম্যানেজমেন্ট</a>
            <a href="patient-billing.php" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm fw-bold"><i class="fas fa-ticket-alt"></i> মানি রিসিট</a>
            
            <form action="" method="GET" class="d-flex gap-1 ms-lg-3">
                <input type="date" name="date" class="form-control form-control-sm rounded-pill border-primary" value="<?php echo $filter_date; ?>">
                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3">Filter</button>
            </form>
        </div>
    </div>

    <!-- ৩. নতুন ইনকাম/এক্সপেন্স এন্ট্রি ফরম -->
    <div class="card report-card p-4 mb-4 no-print border-top border-5 border-primary">
        <h6 class="fw-bold text-navy mb-3"><i class="fas fa-plus-circle me-1 text-primary"></i>ম্যানুয়াল হিসাব এন্ট্রি</h6>
        <form action="" method="POST" class="row g-3">
            <div class="col-md-2">
                <label class="small fw-bold">ধরণ</label>
                <select name="type" id="typeSelect" class="form-select shadow-none" onchange="updateCategories()" required>
                    <option value="income">আয় (Income)</option>
                    <option value="expense">ব্যয় (Expense)</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="small fw-bold">খাত (Category)</label>
                <select name="category" id="catSelect" class="form-select shadow-none" required></select>
            </div>
            <div class="col-md-2">
                <label class="small fw-bold">রশিদ/ভাউচার নং</label>
                <input type="text" name="receipt_no" class="form-control shadow-none" placeholder="No">
            </div>
            <div class="col-md-2">
                <label class="small fw-bold">পরিমাণ (৳)</label>
                <input type="number" name="amount" class="form-control shadow-none fw-bold" placeholder="0.00" required>
            </div>
            <div class="col-md-3">
                <label class="small fw-bold">বিবরণ</label>
                <input type="text" name="description" class="form-control shadow-none" placeholder="বিস্তারিত লিখুন">
            </div>
            <input type="hidden" name="date" value="<?php echo $filter_date; ?>">
            <div class="col-md-1">
                <label class="d-none d-md-block">&nbsp;</label>
                <button type="submit" name="add_transaction" class="btn btn-primary w-100 rounded-3 shadow fw-bold">Save</button>
            </div>
        </form>
    </div>

    <!-- ৪. মোট হিসাব সামারি -->
    <?php
    $grand_total_income = 0;
    foreach($income_groups as $cat) foreach($cat as $r) $grand_total_income += $r['amount'];
    $grand_total_expense = 0;
    foreach($expense_groups as $cat) foreach($cat as $r) $grand_total_expense += $r['amount'];
    $net_balance = $grand_total_income - $grand_total_expense;
    ?>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="summary-box bg-income shadow-sm">
                <small class="text-uppercase fw-bold opacity-75">মোট আয় (Incomes)</small>
                <h2 class="fw-bold mb-0">৳ <?php echo number_format($grand_total_income); ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="summary-box bg-expense shadow-sm">
                <small class="text-uppercase fw-bold opacity-75">মোট ব্যয় (Expenses)</small>
                <h2 class="fw-bold mb-0">৳ <?php echo number_format($grand_total_expense); ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="summary-box bg-balance shadow-sm">
                <small class="text-uppercase fw-bold opacity-75">নিট ব্যালেন্স (Balance)</small>
                <h2 class="fw-bold mb-0">৳ <?php echo number_format($net_balance); ?></h2>
            </div>
        </div>
    </div>

    <!-- ৫. আয় ও ব্যয় বিস্তারিত তালিকা -->
    <div class="row g-4">
        <!-- আয় সমূহ -->
        <div class="col-lg-6">
            <div class="card report-card p-3 h-100">
                <h6 class="fw-bold text-success border-bottom pb-2"><i class="fas fa-coins me-1"></i>আয় সমূহ (Income Details)</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mt-2">
                        <?php if(empty($income_groups)) echo "<tr><td colspan='3' class='text-center text-muted'>কোনো আয়ের তথ্য নেই</td></tr>"; ?>
                        <?php foreach($income_groups as $catName => $rows): ?>
                            <tr class="cat-header"><td colspan="3"><?php echo $catName; ?></td></tr>
                            <?php foreach($rows as $row): 
                                $is_locked = ((strtotime(date('Y-m-d')) - strtotime($row['date'])) / 86400 > 2 && $user_role != 'admin');
                            ?>
                            <tr class="<?php echo $is_locked ? 'locked-row' : ''; ?>">
                                <td><small class="text-muted">#<?php echo $row['receipt_no']; ?></small><br><?php echo $row['description']; ?></td>
                                <td class="text-end fw-bold">৳<?php echo number_format($row['amount']); ?></td>
                                <td class="text-center no-print">
                                    <?php if(!$is_locked): ?>
                                        <a href="?del_id=<?php echo $row['id']; ?>" class="text-danger" onclick="return confirm('এটি ডিলিট করতে চান?')"><i class="fas fa-trash-alt"></i></a>
                                    <?php else: ?><i class="fas fa-lock text-muted"></i><?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
        </div>

        <!-- ব্যয় সমূহ -->
        <div class="col-lg-6">
            <div class="card report-card p-3 h-100">
                <h6 class="fw-bold text-danger border-bottom pb-2"><i class="fas fa-shopping-cart me-1"></i>ব্যয় সমূহ (Expense Details)</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mt-2">
                        <?php if(empty($expense_groups)) echo "<tr><td colspan='3' class='text-center text-muted'>কোনো ব্যয়ের তথ্য নেই</td></tr>"; ?>
                        <?php foreach($expense_groups as $catName => $rows): ?>
                            <tr class="cat-header"><td colspan="3"><?php echo $catName; ?></td></tr>
                            <?php foreach($rows as $row): 
                                $is_locked = ((strtotime(date('Y-m-d')) - strtotime($row['date'])) / 86400 > 2 && $user_role != 'admin');
                            ?>
                            <tr class="<?php echo $is_locked ? 'locked-row' : ''; ?>">
                                <td><small class="text-muted">#<?php echo $row['receipt_no']; ?></small><br><?php echo $row['description']; ?></td>
                                <td class="text-end fw-bold text-danger">৳<?php echo number_format($row['amount']); ?></td>
                                <td class="text-center no-print">
                                    <?php if(!$is_locked): ?>
                                        <a href="?del_id=<?php echo $row['id']; ?>" class="text-danger" onclick="return confirm('এটি ডিলিট করতে চান?')"><i class="fas fa-trash-alt"></i></a>
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
</div>

<script>
// আয় এবং ব্যয়ের পূর্ণাঙ্গ ক্যাটাগরি তালিকা
const cats = {
    income: ['ল্যাব (Lab)', 'ডাক্তার ফি', 'ভর্তি ফি', 'সিট ভাড়া', 'ওটি (OT)', 'অক্সিজেন', 'ভর্তি ও চিকিৎসা', 'সার্ভিস চার্জ', 'অন্যান্য আয়'],
    expense: ['স্টাফ বেতন', 'বিদ্যুৎ বিল', 'ল্যাব রি-এজেন্ট', 'হাসপাতাল ভাড়া', 'মেডিকেল সামগ্রী', 'পরিচ্ছন্নতা', 'পরিবহন', 'মার্কেটিং', 'অন্যান্য ব্যয়']
};

function updateCategories() {
    const type = document.getElementById('typeSelect').value;
    const select = document.getElementById('catSelect');
    select.innerHTML = "";
    cats[type].forEach(c => {
        let opt = document.createElement('option');
        opt.value = c; opt.innerHTML = c;
        select.appendChild(opt);
    });
}
window.onload = updateCategories;
</script>

<?php include_once '../../includes/footer.php'; ?>