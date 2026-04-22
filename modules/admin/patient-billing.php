<?php
session_start();
include_once '../../config/database.php';
include_once '../../config/constants.php';
include_once '../../config/functions.php';

// ১. সিকিউরিটি চেক
$allowed_roles = ['admin', 'manager', 'accounts', 'reception'];
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], $allowed_roles)) {
    header("Location: ../auth/staff-login.php"); exit;
}

// ডাক্তারদের তালিকা আনা (ড্রপডাউনের জন্য)
$doctors_list = mysqli_query($conn, "SELECT id, name FROM doctors WHERE status='active'");

// ২. বিল সেভ করার লজিক
if (isset($_POST['save_bill'])) {
    $name = mysqli_real_escape_string($conn, $_POST['patient_name']);
    $age = mysqli_real_escape_string($conn, $_POST['patient_age']);
    $phone = mysqli_real_escape_string($conn, $_POST['patient_phone']);
    $address = mysqli_real_escape_string($conn, $_POST['patient_address']);
    $doctor_name = mysqli_real_escape_string($conn, $_POST['doctor_name']);
    $total = $_POST['total_bill'];
    $paid = $_POST['paid_amount'];
    $due = $total - $paid;
    $service = $_POST['service_type'];
    $date = $_POST['billing_date']; // ইউজার সিলেক্টেড ডেট

    // ২.১ পেশেন্ট বিলিং টেবিলে সেভ
    // নিশ্চিত করুন আপনার টেবিলে doctor_name, patient_age, patient_address কলাম আছে
    $query = "INSERT INTO patient_billings (patient_name, patient_age, patient_phone, patient_address, doctor_name, total_bill, paid_amount, due_amount, service_type, billing_date) 
              VALUES ('$name', '$age', '$phone', '$address', '$doctor_name', '$total', '$paid', '$due', '$service', '$date')";
    
    if (mysqli_query($conn, $query)) {
        $bill_id = mysqli_insert_id($conn);
        // ২.২ আয়ের মেইন খাত (hospital_accounts) এ অটোমেটিক সেভ
        $income_desc = "পেশেন্ট: $name, ডাক্তার: $doctor_name";
        mysqli_query($conn, "INSERT INTO hospital_accounts (type, category, amount, receipt_no, description, date) 
                             VALUES ('income', '$service', '$paid', 'BILL-$bill_id', '$income_desc', '$date')");
        
        $_SESSION['success'] = "বিল সফলভাবে সেভ হয়েছে!";
        header("Location: patient-billing.php?print_id=" . $bill_id);
        exit;
    }
}

// ৩. প্রিন্ট ডাটা আনা
$print_data = null;
if (isset($_GET['print_id'])) {
    $id = $_GET['print_id'];
    $print_res = mysqli_query($conn, "SELECT * FROM patient_billings WHERE id = '$id'");
    $print_data = mysqli_fetch_assoc($print_res);
}

include_once '../../includes/header.php';
?>

<style>
    :root { --navy: #0A2647; --cyan: #2AA7E5; }
    .billing-card { background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    .receipt-pad { width: 100%; max-width: 700px; margin: 0 auto; background: white; border: 2px solid #eee; padding: 40px; position: relative; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    .watermark { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); font-size: 60px; color: rgba(0,0,0,0.03); font-weight: bold; pointer-events: none; white-space: nowrap; text-transform: uppercase; }
    
    @media print {
        .no-print { display: none !important; }
        body { background: white !important; }
        .receipt-pad { border: none; box-shadow: none; width: 100%; padding: 0; }
    }
</style>

<div class="container py-4">
    <div class="row g-4">
        <!-- বাম পাশ: নতুন বিল এন্ট্রি ফর্ম -->
        <div class="col-md-5 no-print">
            <div class="card billing-card p-4 border-0">
                <h5 class="fw-bold text-navy mb-4"><i class="fas fa-plus-circle me-2"></i>নতুন রিসিট তৈরি</h5>
                <form action="" method="POST">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="small fw-bold">রোগীর নাম</label>
                            <input type="text" name="patient_name" class="form-control rounded-3" placeholder="নাম লিখুন" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small fw-bold">বয়স</label>
                            <input type="number" name="patient_age" class="form-control rounded-3" placeholder="বয়স">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold">মোবাইল নম্বর</label>
                        <input type="text" name="patient_phone" class="form-control rounded-3" placeholder="০১XXXXXXXXX">
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold">ঠিকানা</label>
                        <input type="text" name="patient_address" class="form-control rounded-3" placeholder="গ্রাম/রাস্তা, শহর">
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold">ডাক্তার নির্বাচন করুন</label>
                        <select name="doctor_name" class="form-select rounded-3" required>
                            <option value="">ডাক্তার বেছে নিন</option>
                            <?php while($doc = mysqli_fetch_assoc($doctors_list)) { 
                                echo "<option value='".$doc['name']."'>".$doc['name']."</option>";
                            } ?>
                            <option value="অন্যান্য/হাসপাতাল">অন্যান্য/হাসপাতাল</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="small fw-bold">তারিখ</label>
                            <input type="date" name="billing_date" class="form-control rounded-3" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small fw-bold">সেবার ধরণ</label>
                            <select name="service_type" class="form-select rounded-3" required>
                                <option value="ডাক্তার ফি">ডাক্তার ফি</option>
                                <option value="ল্যাব (Lab)">ল্যাব (Lab)</option>
                                <option value="ওটি (OT)">ওটি (OT)</option>
                                <option value="ভর্তি ফি">ভর্তি ফি</option>
                            </select>
                        </div>
                    </div>
                    <div class="row bg-light p-3 rounded-4 mx-0 mb-3">
                        <div class="col-6">
                            <label class="small fw-bold">মোট বিল</label>
                            <input type="number" name="total_bill" id="total" class="form-control border-primary" required onkeyup="calcDue()">
                        </div>
                        <div class="col-6">
                            <label class="small fw-bold text-success">জমা (Paid)</label>
                            <input type="number" name="paid_amount" id="paid" class="form-control border-success" required onkeyup="calcDue()">
                        </div>
                    </div>
                    <div class="text-center mb-3">
                        <span class="small text-muted">বকেয়া (Due):</span>
                        <h4 class="fw-bold text-danger">৳ <span id="due_display">0.00</span></h4>
                    </div>
                    <button type="submit" name="save_bill" class="btn btn-navy w-100 rounded-pill py-2 shadow" style="background: var(--navy); color:white;">সেভ ও রিসিট জেনারেট</button>
                </form>
            </div>
        </div>

        <!-- ডান পাশ: মানি রিসিট ডিজাইন -->
        <div class="col-md-7">
            <?php if ($print_data): ?>
                <div class="receipt-pad shadow rounded-4" id="printableReceipt">
                    <div class="watermark">OFFICIAL RECEIPT</div>
                    
                    <div class="text-center mb-4">
                        <h2 class="fw-bold text-navy mb-0">পেশেন্ট কেয়ার হাসপাতাল</h2>
                        <p class="small mb-1 fw-bold text-cyan text-uppercase">এন্ড ডায়াগনস্টিক সেন্টার</p>
                        <p class="x-small text-muted mb-0">কলেজ রোড, বরগুনা। ফোন: ০১৩৩১৪ ৩৪৩৪৭</p>
                        <div class="mt-3 bg-dark text-white d-inline-block px-4 py-1 rounded-pill">মানি রিসিট</div>
                    </div>

                    <div class="row mb-4 small">
                        <div class="col-6">
                            <p class="mb-1">রিসিট নং: <b>#BILL-<?php echo $print_data['id']; ?></b></p>
                            <p class="mb-1">রোগীর নাম: <b><?php echo $print_data['patient_name']; ?></b></p>
                            <p class="mb-1">বয়স: <b><?php echo $print_data['patient_age']; ?> বছর</b></p>
                            <p class="mb-0">ঠিকানা: <b><?php echo $print_data['patient_address']; ?></b></p>
                        </div>
                        <div class="col-6 text-end">
                            <p class="mb-1">তারিখ: <b><?php echo date('d/m/Y', strtotime($print_data['billing_date'])); ?></b></p>
                            <p class="mb-1 text-primary">ডাক্তার: <b><?php echo $print_data['doctor_name']; ?></b></p>
                            <p class="mb-0">মোবাইল: <b><?php echo $print_data['patient_phone']; ?></b></p>
                        </div>
                    </div>

                    <table class="table table-bordered border-secondary">
                        <thead class="bg-light">
                            <tr class="small text-center">
                                <th>সেবার বিবরণ (Particulars)</th>
                                <th width="150">টাকা (৳)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="min-height: 150px;">
                                <td class="py-4">
                                    <h6 class="fw-bold mb-1"><?php echo $print_data['service_type']; ?></h6>
                                    <small class="text-muted">হসপিটাল চার্জ এবং অন্যান্য সংশ্লিষ্ট ফি।</small>
                                </td>
                                <td class="text-end py-4 fw-bold">৳<?php echo number_format($print_data['total_bill'], 2); ?></td>
                            </tr>
                            <tr>
                                <td class="text-end fw-bold">মোট বিল (Total Bill):</td>
                                <td class="text-end fw-bold">৳<?php echo number_format($print_data['total_bill'], 2); ?></td>
                            </tr>
                            <tr>
                                <td class="text-end fw-bold text-success">জমা (Paid Amount):</td>
                                <td class="text-end fw-bold text-success">৳<?php echo number_format($print_data['paid_amount'], 2); ?></td>
                            </tr>
                            <tr>
                                <td class="text-end fw-bold text-danger">বকেয়া (Due Amount):</td>
                                <td class="text-end fw-bold text-danger">৳<?php echo number_format($print_data['due_amount'], 2); ?></td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="row mt-4 mb-3">
                        <div class="col-12 small">
                            <p><b>কথায়:</b> <?php echo "........................................................................"; ?> টাকা মাত্র।</p>
                        </div>
                    </div>

                    <div class="mt-5 d-flex justify-content-between text-center">
                        <div style="width: 150px;"><hr class="mb-1"> <small>গ্রহীতার স্বাক্ষর</small></div>
                        <div style="width: 150px;"><hr class="mb-1"> <small>ক্যাশিয়ার</small></div>
                    </div>
                </div>
                
                <div class="text-center mt-4 no-print">
                    <button onclick="window.print()" class="btn btn-navy rounded-pill px-5 shadow" style="background: var(--navy); color:white;"><i class="fas fa-print me-2"></i> রিসিট প্রিন্ট করুন</button>
                    <a href="patient-billing.php" class="btn btn-link text-muted mt-2 d-block">নতুন রিসিট তৈরি করুন</a>
                </div>
            <?php else: ?>
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
                    <i class="fas fa-file-invoice-dollar fa-4x text-light mb-3"></i>
                    <h5 class="text-muted">রিসিট প্রিভিউ</h5>
                    <p class="small text-muted">তথ্য পূরণ করে সেভ করলে এখানে রিসিট জেনারেট হবে।</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function calcDue() {
    const total = parseFloat(document.getElementById('total').value) || 0;
    const paid = parseFloat(document.getElementById('paid').value) || 0;
    document.getElementById('due_display').innerText = (total - paid).toFixed(2);
}
</script>

<?php include_once '../../includes/footer.php'; ?>