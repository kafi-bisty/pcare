<?php
/**
 * ১. লজিক সেকশন (সবার উপরে - কোনো স্পেস বা HTML এর আগে)
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../../config/database.php';

// বিল সেভ করার লজিক
if (isset($_POST['save_bill'])) {
    $name = mysqli_real_escape_string($conn, $_POST['patient_name']);
    $age = mysqli_real_escape_string($conn, $_POST['patient_age']);
    $phone = mysqli_real_escape_string($conn, $_POST['patient_phone']);
    $address = mysqli_real_escape_string($conn, $_POST['patient_address']);
    $doctor = mysqli_real_escape_string($conn, $_POST['doctor_name']);
    $service = mysqli_real_escape_string($conn, $_POST['service_type']);
    $total = (float)$_POST['total_bill'];
    $paid = (float)$_POST['paid_amount'];
    $due = $total - $paid;
    $date = date('Y-m-d');

    // ২.১ পেশেন্ট বিলিং টেবিলে তথ্য সেভ
    $sql = "INSERT INTO patient_billings (patient_name, patient_age, patient_phone, patient_address, doctor_name, service_type, total_bill, paid_amount, due_amount, billing_date) 
            VALUES ('$name', '$age', '$phone', '$address', '$doctor', '$service', '$total', '$paid', '$due', '$date')";
    
    if (mysqli_query($conn, $sql)) {
        $bill_id = mysqli_insert_id($conn);
        $receipt_no = "BILL-" . $bill_id;

        // ২.২ মেইন ইনকাম অ্যাকাউন্টে (hospital_accounts) টাকা জমা করা
        $income_desc = "রিসিট #$receipt_no (রোগী: $name, ডাক্তার: $doctor)";
        mysqli_query($conn, "INSERT INTO hospital_accounts (type, category, amount, receipt_no, description, date) 
                             VALUES ('income', '$service', '$paid', '$receipt_no', '$income_desc', '$date')");

        // ৩. সেভ হওয়ার পর প্রিন্ট আইডি সহ রিডাইরেক্ট
        header("Location: patient-billing.php?print_id=$bill_id");
        exit(); 
    }
}

/**
 * ২. ইন্টারফেস সেকশন (HTML শুরু)
 */
include_once '../../includes/header.php';

// প্রিন্টের জন্য ডাটা আনা
$print_data = null;
if (isset($_GET['print_id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['print_id']);
    $print_res = mysqli_query($conn, "SELECT * FROM patient_billings WHERE id = '$id'");
    $print_data = mysqli_fetch_assoc($print_res);
}

// ডাক্তারদের লিস্ট আনা
$doctors = mysqli_query($conn, "SELECT name FROM doctors WHERE status='active'");
?>

<div class="container py-4 no-print">
    
    <!-- ★ নতুন হেডার বাটন সেকশন ★ -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-navy mb-0"><i class="fas fa-ticket-alt me-2 text-cyan"></i>পেশেন্ট বিলিং ও টোকেন</h3>
        <a href="manage-accounts.php" class="btn btn-outline-navy rounded-pill px-4 shadow-sm fw-bold">
            <i class="fas fa-calculator me-2"></i> হিসাব খাতায় ফিরে যান
        </a>
    </div>

    <div class="row g-4">
        <!-- বাম পাশ: ইনপুট ফর্ম -->
        <div class="col-md-5">
            <div class="card shadow-sm border-0 rounded-4 p-4">
                <h5 class="fw-bold text-navy mb-4 border-bottom pb-2">
                    <i class="fas fa-file-invoice-dollar me-2 text-primary"></i>নতুন মানি রিসিট তৈরি
                </h5>
                <form action="" method="POST">
                    <div class="row g-2">
                        <div class="col-8">
                            <label class="small fw-bold">রোগীর নাম</label>
                            <input type="text" name="patient_name" class="form-control rounded-3 mb-2" placeholder="নাম" required>
                        </div>
                        <div class="col-4">
                            <label class="small fw-bold">বয়স</label>
                            <input type="number" name="patient_age" class="form-control rounded-3 mb-2" placeholder="বয়স">
                        </div>
                    </div>

                    <label class="small fw-bold">মোবাইল নম্বর</label>
                    <input type="text" name="patient_phone" class="form-control rounded-3 mb-2" placeholder="মোবাইল">
                    
                    <label class="small fw-bold">ঠিকানা</label>
                    <input type="text" name="patient_address" class="form-control rounded-3 mb-2" placeholder="ঠিকানা">
                    
                    <label class="small fw-bold">ডাক্তার নির্বাচন</label>
                    <select name="doctor_name" class="form-select rounded-3 mb-2" required>
                        <option value="">ডাক্তার বেছে নিন</option>
                        <?php while($d = mysqli_fetch_assoc($doctors)) echo "<option value='".$d['name']."'>".$d['name']."</option>"; ?>
                        <option value="Hospital Staff">অন্যান্য</option>
                    </select>

                    <label class="small fw-bold">সেবার ধরণ</label>
                    <select name="service_type" class="form-select rounded-3 mb-3">
                        <option value="ডাক্তার ফি">ডাক্তার ফি</option>
                        <option value="ল্যাব (Lab)">ল্যাব (Lab)</option>
                        <option value="ওটি (OT)">ওটি (OT)</option>
                        <option value="সিট ভাড়া">সিট ভাড়া</option>
                    </select>

                    <div class="row g-2 mb-4">
                        <div class="col-6">
                            <label class="small fw-bold">মোট বিল</label>
                            <input type="number" name="total_bill" class="form-control rounded-3" placeholder="0.00" required>
                        </div>
                        <div class="col-6">
                            <label class="small fw-bold">জমা (Paid)</label>
                            <input type="number" name="paid_amount" class="form-control rounded-3 text-success fw-bold" placeholder="0.00" required>
                        </div>
                    </div>

                    <button type="submit" name="save_bill" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow">
                        <i class="fas fa-save me-1"></i> সেভ এবং টোকেন জেনারেট
                    </button>
                </form>
            </div>
        </div>

        <!-- ডান পাশ: প্রিন্ট অপশন এরিয়া -->
        <div class="col-md-7 text-center">
            <?php if ($print_data): ?>
                <div class="card p-5 shadow-sm border-0 rounded-4 bg-white animate__animated animate__fadeIn">
                    <div class="icon-circle mb-3 mx-auto" style="width: 70px; height: 70px; background: #e8f5e9; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-check-circle text-success fa-3x"></i>
                    </div>
                    <h3 class="fw-bold text-navy">পেমেন্ট সফল হয়েছে!</h3>
                    <p class="text-muted mb-1">রোগীর নাম: <strong><?php echo $print_data['patient_name']; ?></strong></p>
                    <p class="text-muted">রিসিট নম্বর: <strong>#BILL-<?php echo $print_data['id']; ?></strong></p>
                    <hr>
                    
                    <div class="d-grid gap-2">
                        <a href="print-token.php?id=<?php echo $print_data['id']; ?>" target="_blank" class="btn btn-cyan text-white rounded-pill py-3 fw-bold shadow-sm">
                            <i class="fas fa-ticket-alt me-2"></i> ছোট কালার টোকেন প্রিন্ট করুন
                        </a>
                    </div>

                    <a href="patient-billing.php" class="btn btn-link text-muted mt-3">পরবর্তী রোগী</a>
                </div>
            <?php else: ?>
                <div class="card p-5 shadow-sm border-0 rounded-4 bg-white opacity-50 h-100 d-flex align-items-center justify-content-center">
                    <div class="text-center">
                        <i class="fas fa-print fa-4x text-light mb-3"></i>
                        <h5 class="text-muted">বামে তথ্য পূরণ করে সেভ করলে প্রিন্ট অপশন এখানে আসবে।</h5>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    :root { --navy: #0A2647; --cyan: #2AA7E5; }
    .text-navy { color: var(--navy); }
    .text-cyan { color: var(--cyan); }
    .btn-cyan { background: var(--cyan); color: white; border: none; transition: 0.3s; }
    .btn-cyan:hover { background: #2391c7; transform: translateY(-2px); color: white; }
    .btn-outline-navy { border: 2px solid var(--navy); color: var(--navy); font-weight: bold; transition: 0.3s; }
    .btn-outline-navy:hover { background: var(--navy); color: white; }
    .card { transition: 0.3s; }
</style>

<?php include_once '../../includes/footer.php'; ?>