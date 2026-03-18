<?php
include_once '../../includes/header.php';

// ডাক্তার লগইন চেক
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'doctor') {
    header("Location: ../auth/staff-login.php"); exit;
}

$doctor_id = $_SESSION['user_id'];

// তারিখ ফিল্টার (ডিফল্ট আজকের তারিখ)
$filter_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// ডাটাবেজ থেকে ওই তারিখের 'Completed' (দেখা হয়েছে) রোগীদের তালিকা আনা
$query = mysqli_query($conn, "SELECT * FROM appointments 
          WHERE doctor_id = '$doctor_id' 
          AND appointment_date = '$filter_date' 
          AND status = 'completed' 
          ORDER BY id ASC");

$total_seen = mysqli_num_rows($query);
?>

<div class="container py-5">
    <!-- পেজ হেডার -->
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <div>
            <h3 class="fw-bold text-navy mb-1"><i class="fas fa-history me-2 text-info"></i>রোগী দেখার হিস্ট্রি</h3>
            <p class="text-muted small mb-0">আপনার প্রতিদিনের দেখা রোগীদের পূর্ণাঙ্গ তালিকা</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill px-4">ড্যাশবোর্ড</a>
    </div>

    <!-- ফিল্টার এবং রিপোর্ট কার্ড -->
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 d-print-none">
        <form action="" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold">তারিখ নির্বাচন করুন</label>
                <input type="date" name="date" class="form-control rounded-pill border-info" value="<?php echo $filter_date; ?>">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-info w-100 rounded-pill text-white fw-bold">তালিকা দেখুন</button>
            </div>
            <div class="col-md-5 text-md-end">
                <button type="button" onclick="window.print()" class="btn btn-dark rounded-pill px-4 shadow">
                    <i class="fas fa-file-pdf me-2"></i> রিপোর্ট ডাউনলোড / প্রিন্ট
                </button>
            </div>
        </form>
    </div>

    <!-- হিস্ট্রি টেবিল -->
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden bg-white">
        <div class="card-header bg-light py-3 border-0 d-flex justify-content-between">
            <span class="fw-bold text-navy">তারিখ: <?php echo date('d M, Y', strtotime($filter_date)); ?></span>
            <span class="badge bg-primary rounded-pill px-3">মোট রোগী: <?php echo $total_seen; ?> জন</span>
        </div>
        
        <div class="table-responsive p-3">
            <!-- প্রিন্ট আউটের জন্য হেডার (শুধু প্রিন্টে দেখা যাবে) -->
            <div class="d-none d-print-block text-center mb-4">
                <h2 class="fw-bold">পেশেন্ট কেয়ার হাসপাতাল এন্ড ডায়াগনস্টিক সেন্টার</h2>
                <h4 class="text-navy">ডাক্তারের প্রতিদিনের রোগী রিপোর্ট</h4>
                <hr>
                <div class="row text-start mt-4">
                    <div class="col-6"><strong>ডাক্তার:</strong> ডা. <?php echo $_SESSION['user_name']; ?></div>
                    <div class="col-6 text-end"><strong>তারিখ:</strong> <?php echo date('d M, Y', strtotime($filter_date)); ?></div>
                </div>
            </div>

            <table class="table table-hover align-middle mt-3">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">সিরিয়াল #</th>
                        <th>রোগীর নাম</th>
                        <th>মোবাইল নম্বর</th>
                        <th>বয়স ও লিঙ্গ</th>
                        <th>চিকিৎসার সময়</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($total_seen > 0): $sl = 1; ?>
                        <?php while($row = mysqli_fetch_assoc($query)): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-primary">#<?php echo $sl++; ?></td>
                                <td><span class="fw-bold"><?php echo $row['patient_name']; ?></span></td>
                                <td><?php echo $row['patient_phone']; ?></td>
                                <td><?php echo $row['age']; ?> বছর (<?php echo $row['gender']; ?>)</td>
                                <td class="small text-muted"><?php echo date('h:i A', strtotime($row['created_at'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">দুঃখিত, এই তারিখে কোনো রোগী দেখা হয়নি।</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- প্রিন্ট করার জন্য বিশেষ সিএসএস -->
<style>
@media print {
    body { background: white !important; }
    .navbar, .d-print-none, .notice-container, footer { display: none !important; }
    .card { box-shadow: none !important; border: none !important; }
    .table thead { background-color: #f8f9fa !important; color: black !important; }
    .container { width: 100% !important; max-width: 100% !important; margin: 0 !important; padding: 0 !important; }
}
</style>

<?php include_once '../../includes/footer.php'; ?>