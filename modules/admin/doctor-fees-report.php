<?php
session_start();
include_once '../../config/database.php';
include_once '../../includes/header.php';

// লগইন চেক
if (!isset($_SESSION['user_role'])) { header("Location: ../auth/login.php"); exit; }

$today = date('Y-m-d');

// ১. ডাটাবেজ থেকে ডাক্তারদের তালিকা এবং তাদের ফিক্সড ফি আনা
$doctors_list_query = mysqli_query($conn, "SELECT name, specialization, fee FROM doctors WHERE status='active' ORDER BY name ASC");

// ২. আজকে কোন ডাক্তার কত টাকা ইনকাম করলেন তা বের করা (hospital_accounts টেবিল থেকে)
$doctor_income_sql = "SELECT description as doc_info, SUM(amount) as total_earned, COUNT(id) as total_patients 
                      FROM hospital_accounts 
                      WHERE date = '$today' AND category = 'ডাক্তার ফি' 
                      GROUP BY description";
$doctor_income_query = mysqli_query($conn, $doctor_income_sql);
?>

<style>
    :root { --navy: #0A2647; --cyan: #2AA7E5; }
    body { background-color: #f1f5f9; }
    .fee-card { border: none; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
    .doc-avatar { width: 45px; height: 45px; background: var(--navy); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; }
</style>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-navy">ডাক্তার ফি ও আজকের আয় রিপোর্ট</h3>
        <a href="manage-accounts.php" class="btn btn-outline-navy rounded-pill px-4 btn-sm">হিসাব খাতায় ফিরে যান</a>
    </div>

    <div class="row g-4">
        <!-- বাম পাশ: নির্ধারিত ফি তালিকা (Master List) -->
        <div class="col-md-5">
            <div class="card fee-card p-4">
                <h5 class="fw-bold text-navy mb-3 border-bottom pb-2">ডাক্তারদের নির্ধারিত ফি</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-light">
                            <tr><th>ডাক্তারের নাম</th><th class="text-end">ফি (৳)</th></tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($doctors_list_query)): ?>
                            <tr>
                                <td>
                                    <strong><?= $row['name']; ?></strong><br>
                                    <small class="text-muted"><?= $row['specialization']; ?></small>
                                </td>
                                <td class="text-end fw-bold text-primary">৳ <?= number_format($row['fee']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ডান পাশ: আজকের কালেকশন রিপোর্ট (Live Report) -->
        <div class="col-md-7">
            <div class="card fee-card p-4 border-top border-5 border-success">
                <h5 class="fw-bold text-success mb-3 border-bottom pb-2">আজকের ফি কালেকশন (<?= date('d M, Y'); ?>)</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>ডাক্তারের নাম ও তথ্য</th>
                                <th class="text-center">রোগী</th>
                                <th class="text-end">মোট আয় (৳)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($doctor_income_query) > 0): ?>
                                <?php while($inc = mysqli_fetch_assoc($doctor_income_query)): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="doc-avatar me-2"><?= mb_substr($inc['doc_info'], -2, 1); ?></div>
                                            <div>
                                                <strong><?= preg_replace('/.*ডাক্তার: /', '', $inc['doc_info']); ?></strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center"><span class="badge bg-info rounded-pill"><?= $inc['total_patients']; ?> জন</span></td>
                                    <td class="text-end fw-bold text-navy">৳ <?= number_format($inc['total_earned']); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center py-5 text-muted">আজ এখন পর্যন্ত কোনো ফি জমা হয়নি।</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>