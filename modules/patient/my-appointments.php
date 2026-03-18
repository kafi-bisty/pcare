<?php
include_once '../../includes/header.php';

// লগইন চেক (শুধুমাত্র রোগীদের জন্য)
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'patient') {
    echo "<script>window.location.href='../public/patient-login.php';</script>";
    exit;
}

$patient_id = $_SESSION['user_id'];

// সার্চ বা ফিল্টার অপশন (ঐচ্ছিক)
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

// ডাটাবেজ থেকে অ্যাপয়েন্টমেন্টের তথ্য আনা (ডাক্তারের নামসহ)
$query = "SELECT a.*, d.name as doctor_name, d.specialization, d.fee 
          FROM appointments a 
          JOIN doctors d ON a.doctor_id = d.id 
          WHERE a.patient_id = '$patient_id'";

if (!empty($status_filter)) {
    $query .= " AND a.status = '$status_filter'";
}

$query .= " ORDER BY a.appointment_date DESC";
$result = mysqli_query($conn, $query);
?>

<div class="container-fluid bg-light py-5" style="min-height: 90vh;">
    <div class="container">
        <!-- হেডার এবং ব্যাক বাটন -->
        <div class="row mb-4 align-items-center">
            <div class="col-md-6">
                <h3 class="fw-bold mb-0" style="color: var(--primary-navy);">
                    <i class="fas fa-calendar-check me-2 text-primary"></i>আমার অ্যাপয়েন্টমেন্ট তালিকা
                </h3>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill px-4 me-2">
                    <i class="fas fa-tachometer-alt me-1"></i> ড্যাশবোর্ড
                </a>
                <a href="../public/doctors.php" class="btn btn-primary rounded-pill px-4" style="background-color: var(--secondary-cyan); border: none;">
                    <i class="fas fa-plus me-1"></i> নতুন অ্যাপয়েন্টমেন্ট
                </a>
            </div>
        </div>

        <!-- ফিল্টার ট্যাব -->
        <div class="mb-4">
            <div class="btn-group" role="group">
                <a href="my-appointments.php" class="btn btn-sm <?php echo $status_filter == '' ? 'btn-primary' : 'btn-outline-primary'; ?> px-4 rounded-start-pill">সবগুলো</a>
                <a href="my-appointments.php?status=pending" class="btn btn-sm <?php echo $status_filter == 'pending' ? 'btn-primary' : 'btn-outline-primary'; ?> px-4">পেন্ডিং</a>
                <a href="my-appointments.php?status=approved" class="btn btn-sm <?php echo $status_filter == 'approved' ? 'btn-primary' : 'btn-outline-primary'; ?> px-4">অনুমোদিত</a>
                <a href="my-appointments.php?status=completed" class="btn btn-sm <?php echo $status_filter == 'completed' ? 'btn-primary' : 'btn-outline-primary'; ?> px-4 rounded-end-pill">সম্পন্ন</a>
            </div>
        </div>

        <!-- অ্যাপয়েন্টমেন্ট তালিকা টেবিল -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="table-responsive p-3">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">আইডি</th>
                            <th>ডাক্তারের নাম</th>
                            <th>বিভাগ</th>
                            <th>তারিখ</th>
                            <th>ভিজিট ফি</th>
                            <th>অবস্থা (Status)</th>
                            <th class="text-center">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td class="ps-4">#<?php echo $row['id']; ?></td>
                                    <td>
                                        <div class="fw-bold" style="color: var(--primary-navy);"><?php echo $row['doctor_name']; ?></div>
                                    </td>
                                    <td><span class="small text-muted"><?php echo $row['specialization']; ?></span></td>
                                    <td>
                                        <div class="small fw-bold text-dark">
                                            <i class="far fa-calendar-alt me-1 text-primary"></i> 
                                            <?php echo date('d M, Y', strtotime($row['appointment_date'])); ?>
                                        </div>
                                    </td>
                                    <td>৳ <?php echo $row['fee']; ?></td>
                                    <td>
                                        <?php if ($row['status'] == 'pending'): ?>
                                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><i class="fas fa-spinner fa-spin me-1"></i> অপেক্ষমাণ</span>
                                        <?php elseif ($row['status'] == 'approved'): ?>
                                            <span class="badge bg-success px-3 py-2 rounded-pill"><i class="fas fa-check-circle me-1"></i> অনুমোদিত</span>
                                        <?php elseif ($row['status'] == 'completed'): ?>
                                            <span class="badge bg-info px-3 py-2 rounded-pill text-dark"><i class="fas fa-flag-checkered me-1"></i> সম্পন্ন</span>
                                        <?php elseif ($row['status'] == 'cancelled'): ?>
                                            <span class="badge bg-danger px-3 py-2 rounded-pill"><i class="fas fa-times-circle me-1"></i> বাতিল</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-light border rounded-circle shadow-sm" title="বিস্তারিত দেখুন">
                                            <i class="fas fa-eye text-primary"></i>
                                        </button>
                                        <?php if ($row['status'] == 'pending'): ?>
                                            <button class="btn btn-sm btn-light border rounded-circle shadow-sm ms-1" title="বাতিল করুন">
                                                <i class="fas fa-trash text-danger"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-calendar-times fa-3x mb-3 opacity-25"></i>
                                        <p>দুঃখিত, কোনো অ্যাপয়েন্টমেন্ট পাওয়া যায়নি!</p>
                                        <a href="../public/doctors.php" class="btn btn-sm btn-primary rounded-pill">এখনই বুক করুন</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- টিপস সেকশন -->
        <div class="mt-4">
            <div class="alert alert-light border-0 shadow-sm rounded-4 p-3">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1 text-info"></i> 
                    আপনার অ্যাপয়েন্টমেন্টটি অনুমোদিত হলে আপনি আমাদের হাসপাতালের পক্ষ থেকে কল বা কনফার্মেশন পাবেন। 
                    জরুরি প্রয়োজনে কল করুন: +৮৮০ ১৭১২ ৩৪৫৬৭৮
                </small>
            </div>
        </div>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>