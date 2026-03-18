<?php
include_once '../../includes/header.php';

// লগইন করা না থাকলে বা রোল 'patient' না হলে লগইন পেজে পাঠিয়ে দাও
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'patient') {
    echo "<script>window.location.href='../public/patient-login.php';</script>";
    exit;
}

$patient_id = $_SESSION['user_id'];

// ১. মোট অ্যাপয়েন্টমেন্ট সংখ্যা আনা
$total_query = mysqli_query($conn, "SELECT id FROM appointments WHERE patient_id = '$patient_id'");
$total_appointments = mysqli_num_rows($total_query);

// ২. পেন্ডিং অ্যাপয়েন্টমেন্ট সংখ্যা আনা
$pending_query = mysqli_query($conn, "SELECT id FROM appointments WHERE patient_id = '$patient_id' AND status = 'pending'");
$pending_appointments = mysqli_num_rows($pending_query);

// ৩. সাম্প্রতিক ৫টি অ্যাপয়েন্টমেন্ট লিস্ট (ডাক্তারের নামসহ)
$recent_query = mysqli_query($conn, "
    SELECT a.*, d.name as doctor_name, d.specialization 
    FROM appointments a 
    JOIN doctors d ON a.doctor_id = d.id 
    WHERE a.patient_id = '$patient_id' 
    ORDER BY a.created_at DESC LIMIT 5
");
?>

<div class="container-fluid bg-light py-5" style="min-height: 90vh;">
    <div class="container">
        <!-- স্বাগতম সেকশন -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm p-4 rounded-4" style="background: linear-gradient(135deg, var(--primary-navy) 0%, #1a4a7a 100%); color: white;">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="fw-bold mb-1">স্বাগতম, <?php echo $_SESSION['user_name']; ?>!</h2>
                            <p class="opacity-75 mb-0">আপনার স্বাস্থ্যের অবস্থা এবং অ্যাপয়েন্টমেন্টের তথ্য এখানে দেখুন।</p>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <a href="../public/doctors.php" class="btn btn-light rounded-pill px-4 fw-bold" style="color: var(--primary-navy);">
                                <i class="fas fa-plus me-1"></i> নতুন অ্যাপয়েন্টমেন্ট
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- স্ট্যাটিস্টিক কার্ডস -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-4 rounded-4 h-100">
                    <div class="d-flex align-items-center">
                        <div class="icon-box rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary" style="width: 60px; height: 60px;">
                            <i class="fas fa-calendar-alt fa-2x"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="fw-bold mb-0"><?php echo $total_appointments; ?></h3>
                            <p class="text-muted mb-0">মোট অ্যাপয়েন্টমেন্ট</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-4 rounded-4 h-100">
                    <div class="d-flex align-items-center">
                        <div class="icon-box rounded-circle d-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning" style="width: 60px; height: 60px;">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="fw-bold mb-0"><?php echo $pending_appointments; ?></h3>
                            <p class="text-muted mb-0">অপেক্ষমাণ (Pending)</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-4 rounded-4 h-100">
                    <div class="d-flex align-items-center">
                        <div class="icon-box rounded-circle d-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success" style="width: 60px; height: 60px;">
                            <i class="fas fa-file-prescription fa-2x"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="fw-bold mb-0">০</h3>
                            <p class="text-muted mb-0">নতুন প্রেসক্রিপশন</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- সাম্প্রতিক অ্যাপয়েন্টমেন্ট টেবিল -->
        <div class="row">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0" style="color: var(--primary-navy);">সাম্প্রতিক অ্যাপয়েন্টমেন্ট</h5>
                        <a href="my-appointments.php" class="btn btn-sm btn-outline-primary rounded-pill px-3">সবগুলো দেখুন</a>
                    </div>
                    <div class="table-responsive p-3">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ডাক্তার</th>
                                    <th>বিভাগ</th>
                                    <th>তারিখ</th>
                                    <th>অবস্থা (Status)</th>
                                    <th>অ্যাকশন</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($recent_query) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($recent_query)): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold"><?php echo $row['doctor_name']; ?></div>
                                            </td>
                                            <td><?php echo $row['specialization']; ?></td>
                                            <td><?php echo date('d M, Y', strtotime($row['appointment_date'])); ?></td>
                                            <td>
                                                <?php if ($row['status'] == 'pending'): ?>
                                                    <span class="badge bg-warning text-dark px-3 rounded-pill">পেন্ডিং</span>
                                                <?php elseif ($row['status'] == 'approved'): ?>
                                                    <span class="badge bg-success px-3 rounded-pill">এপ্রুভড</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary px-3 rounded-pill"><?php echo $row['status']; ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="view-appointment.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-light border rounded-pill shadow-sm">বিস্তারিত</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">আপনার কোনো অ্যাপয়েন্টমেন্ট রেকর্ড পাওয়া যায়নি।</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>