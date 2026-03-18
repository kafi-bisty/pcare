<?php
session_start();
include_once '../../config/database.php';
include_once '../../config/constants.php';
include_once '../../config/functions.php';

// ১. এডমিন চেক
if (!isset($_SESSION['admin_id'])) { 
    header("Location: login.php"); 
    exit; 
}

// ২. একটি নির্দিষ্ট লগ ডিলিট করার লজিক
if (isset($_GET['delete_id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM activity_logs WHERE id = '$id'");
    $_SESSION['success'] = "লগটি সফলভাবে মুছে ফেলা হয়েছে!";
    header("Location: activity-logs.php");
    exit;
}

// ৩. সব লগ একসাথে ডিলিট (Auto/Clear All) করার লজিক
if (isset($_POST['clear_all_logs'])) {
    // নিরাপত্তা নিশ্চিত করতে ট্রাঙ্কেট করা হলো
    mysqli_query($conn, "TRUNCATE TABLE activity_logs");
    $_SESSION['success'] = "সব লগ হিস্ট্রি সফলভাবে পরিষ্কার করা হয়েছে!";
    header("Location: activity-logs.php");
    exit;
}

// ৪. সব লগ ডাটা আনা (সর্বশেষ ১০০টি)
$query = mysqli_query($conn, "SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 100");

include_once '../../includes/header.php';
?>

<div class="container py-5">
       <a href="dashboard.php" class="btn btn-outline-primary rounded-pill px-4">ড্যাশবোর্ড</a>
    <!-- পেজ হেডার -->
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <div>
            <h3 class="fw-bold text-navy mb-1"><i class="fas fa-history me-2 text-danger"></i>সিস্টেম অ্যাক্টিভিটি লগ</h3>
            <p class="text-muted small mb-0">মালিকের জন্য সব কার্যক্রমের রেকর্ড</p>
        </div>
        <div class="d-flex gap-2">
            <!-- প্রিন্ট বাটন -->
            <button onclick="window.print()" class="btn btn-outline-dark rounded-pill px-3 shadow-sm">
                <i class="fas fa-print me-1"></i> প্রিন্ট
            </button>
            <!-- সব ডিলিট বাটন (Auto Clear) -->
            <form action="" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিত যে সব লগ ডাটা চিরতরে মুছে ফেলতে চান? এটি আর ফিরে পাওয়া যাবে না।')">
                <button type="submit" name="clear_all_logs" class="btn btn-danger rounded-pill px-4 shadow-sm">
                    <i class="fas fa-trash-sweep me-1"></i> Clear All Logs
                </button>
            </form>
        </div>
    </div>

    <!-- সাকসেস মেসেজ -->
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success border-0 shadow-sm rounded-4 py-2 small mb-4">
            <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <!-- লগ টেবিল -->
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden bg-white">
        <div class="table-responsive p-3">
            <table class="table table-hover align-middle">
                <thead class="table-dark" style="background-color: var(--primary-navy);">
                    <tr>
                        <th class="ps-3">সময় ও তারিখ</th>
                        <th>স্টাফের নাম</th>
                        <th>পদবি</th>
                        <th>অ্যাকশন</th>
                        <th>বিস্তারিত তথ্য</th>
                        <th class="text-center d-print-none">মুছুন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($query) > 0): ?>
                        <?php while($log = mysqli_fetch_assoc($query)): ?>
                        <tr>
                            <td class="ps-3 small text-muted">
                                <i class="far fa-clock me-1"></i> <?php echo date('d M, Y (h:i A)', strtotime($log['created_at'])); ?>
                            </td>
                            <td class="fw-bold text-navy"><?php echo $log['user_name']; ?></td>
                            <td>
                                <?php 
                                $role = $log['user_role'];
                                $color = ($role == 'reception') ? 'warning' : (($role == 'doctor') ? 'info' : 'danger');
                                echo "<span class='badge bg-$color text-dark small px-3 rounded-pill'>$role</span>";
                                ?>
                            </td>
                            <td><span class="badge bg-light text-danger border border-danger small px-2"><?php echo $log['action_type']; ?></span></td>
                            <td class="text-muted small" style="max-width: 300px;"><?php echo $log['details']; ?></td>
                            <td class="text-center d-print-none">
                                <!-- সিঙ্গেল ডিলিট বাটন -->
                                <a href="?delete_id=<?php echo $log['id']; ?>" 
                                   class="btn btn-sm btn-outline-danger border-0 rounded-circle p-2" 
                                   onclick="return confirm('এই লগটি মুছে ফেলতে চান?')"
                                   title="মুছে ফেলুন">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center py-5 text-muted">কোনো অ্যাক্টিভিটি রেকর্ড পাওয়া যায়নি।</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.text-navy { color: var(--primary-navy); }
.table thead th { border: none; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }

@media print {
    .navbar, .btn, form, footer, .d-print-none { display: none !important; }
    .card { box-shadow: none !important; border: 1px solid #eee !important; }
    body { background: white !important; }
}
</style>

<?php include_once '../../includes/footer.php'; ?>