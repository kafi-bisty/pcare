<?php
include_once '../../includes/header.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'patient') {
    header("Location: ../public/patient-login.php"); exit;
}

$patient_id = $_SESSION['user_id'];

// এই রোগীর সব প্রেসক্রিপশন ডাটাবেজ থেকে আনা
$query = mysqli_query($conn, "SELECT p.id as pres_id, p.created_at, d.name as doc_name, d.specialization 
          FROM prescriptions p 
          JOIN doctors d ON p.doctor_id = d.id 
          WHERE p.patient_id = '$patient_id' 
          ORDER BY p.created_at DESC");
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-navy"><i class="fas fa-file-prescription me-2 text-primary"></i>আমার প্রেসক্রিপশনসমূহ</h3>
        <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill px-4">ড্যাশবোর্ড</a>
    </div>

    <div class="row g-4">
        <?php if(mysqli_num_rows($query) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($query)): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4">
                        <div class="d-flex justify-content-between mb-3">
                            <span class="badge bg-light text-primary border px-3 rounded-pill">#PR-<?php echo $row['pres_id']; ?></span>
                            <small class="text-muted"><?php echo date('d M, Y', strtotime($row['created_at'])); ?></small>
                        </div>
                        <h5 class="fw-bold text-navy mb-1">ডা. <?php echo $row['doc_name']; ?></h5>
                        <p class="small text-muted mb-4"><?php echo $row['specialization']; ?></p>
                        
                        <a href="../public/view-prescription.php?id=<?php echo $row['pres_id']; ?>" target="_blank" class="btn btn-primary w-100 rounded-pill">
                            <i class="fas fa-eye me-1"></i> প্রেসক্রিপশন দেখুন
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <p class="text-muted">আপনার এখনো কোনো ডিজিটাল প্রেসক্রিপশন তৈরি হয়নি।</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>