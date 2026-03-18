<?php
include_once '../../includes/header.php';

// রিসেপশন লগইন চেক
if (!isset($_SESSION['reception_id']) || $_SESSION['user_role'] != 'reception') {
    echo "<script>window.location.href='../auth/staff-login.php';</script>";
    exit;
}

// সার্চ লজিক
$search = isset($_GET['query']) ? mysqli_real_escape_string($conn, $_GET['query']) : '';
$result = null;

if (!empty($search)) {
    // মোবাইল নম্বর বা নাম দিয়ে সার্চ করা
    $query = "SELECT * FROM patients WHERE phone LIKE '%$search%' OR name LIKE '%$search%' ORDER BY id DESC";
    $result = mysqli_query($conn, $query);
}
?>

<div class="container-fluid bg-light py-5" style="min-height: 90vh;">
    <div class="container">
        <!-- হেডার এবং ব্যাক বাটন -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-navy mb-0"><i class="fas fa-search-plus me-2 text-primary"></i>রোগী অনুসন্ধান করুন</h3>
            <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill px-4 btn-sm shadow-sm">ড্যাশবোর্ড</a>
        </div>

        <!-- সার্চ বক্স -->
        <div class="row justify-content-center mb-5">
            <div class="col-md-8">
                <div class="card border-0 shadow-lg rounded-4 p-4">
                    <form action="" method="GET">
                        <div class="input-group input-group-lg shadow-sm rounded-pill overflow-hidden border">
                            <span class="input-group-text bg-white border-0 ps-4"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="query" class="form-control border-0 shadow-none" 
                                   placeholder="রোগীর মোবাইল নম্বর বা নাম লিখুন..." 
                                   value="<?php echo $search; ?>" required>
                            <button type="submit" class="btn btn-primary px-5 fw-bold" style="background-color: var(--secondary-cyan); border: none;">সার্চ করুন</button>
                        </div>
                        <div class="text-center mt-3">
                            <small class="text-muted italic">টিপস: রোগীর ১০ সংখ্যার মোবাইল নম্বরটি দিয়ে সার্চ করলে দ্রুত পাওয়া যাবে।</small>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- সার্চ রেজাল্ট তালিকা -->
        <div class="row justify-content-center">
            <div class="col-md-11">
                <?php if ($result): ?>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                            <div class="card-header bg-white border-bottom p-3">
                                <h6 class="mb-0 fw-bold text-navy"><i class="fas fa-user-check me-2 text-success"></i>পাওয়া গেছে (<?php echo mysqli_num_rows($result); ?> জন)</h6>
                            </div>
                            <div class="table-responsive p-3">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">নাম ও আইডি</th>
                                            <th>মোবাইল নম্বর</th>
                                            <th>লিঙ্গ ও বয়স</th>
                                            <th>ঠিকানা</th>
                                            <th class="text-center">অ্যাকশন</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                            <?php 
                                                // বয়স বের করা
                                                $dob = new DateTime($row['date_of_birth']);
                                                $now = new DateTime();
                                                $age = $now->diff($dob)->y;
                                            ?>
                                            <tr>
                                                <td class="ps-3">
                                                    <span class="fw-bold d-block text-navy"><?php echo $row['name']; ?></span>
                                                    <small class="text-muted">Patient ID: #<?php echo $row['id']; ?></small>
                                                </td>
                                                <td><span class="fw-bold"><?php echo $row['phone']; ?></span></td>
                                                <td><?php echo $row['gender']; ?> (<?php echo $age; ?> বছর)</td>
                                                <td class="small text-muted"><?php echo $row['address']; ?></td>
                                                <td class="text-center">
                                                    <!-- এই রোগীর জন্য নতুন অ্যাপয়েন্টমেন্ট বুক করার লিঙ্ক -->
                                                    <a href="../public/doctors.php" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">
                                                        <i class="fas fa-calendar-plus me-1"></i> সিরিয়াল দিন
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- কোনো রেজাল্ট না পাওয়া গেলে -->
                        <div class="text-center py-5">
                            <div class="mb-3"><i class="fas fa-user-slash fa-4x text-muted opacity-25"></i></div>
                            <h5 class="text-muted">দুঃখিত, এই নামে বা নম্বরে কোনো রোগী পাওয়া যায়নি।</h5>
                            <a href="add-new-patient.php" class="btn btn-link text-decoration-none fw-bold">নতুন রোগী হিসেবে রেজিস্টার করুন?</a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.text-navy { color: var(--primary-navy); }
.italic { font-style: italic; }
.input-group:focus-within { border-color: var(--secondary-cyan) !important; box-shadow: 0 0 10px rgba(42, 167, 229, 0.2) !important; }
.card { transition: all 0.3s ease; }
</style>

<?php include_once '../../includes/footer.php'; ?>