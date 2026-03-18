<?php
include_once '../../includes/header.php';

// লগইন চেক
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'patient') {
    echo "<script>window.location.href='../public/patient-login.php';</script>";
    exit;
}

// আইডি চেক করা
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>window.location.href='my-appointments.php';</script>";
    exit;
}

$appointment_id = mysqli_real_escape_string($conn, $_GET['id']);
$patient_id = $_SESSION['user_id'];

// অ্যাপয়েন্টমেন্টের তথ্য ডাটাবেজ থেকে আনা (নিরাপত্তার জন্য patient_id চেক করা হয়েছে)
$query = "SELECT a.*, d.name as doctor_name, d.specialization, d.qualification, d.fee 
          FROM appointments a 
          JOIN doctors d ON a.doctor_id = d.id 
          WHERE a.id = '$appointment_id' AND a.patient_id = '$patient_id'";

$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

// যদি কোনো ডাটা না পাওয়া যায়
if (!$data) {
    echo "<div class='container mt-5 py-5 text-center'>
            <div class='alert alert-danger'>দুঃখিত, অ্যাপয়েন্টমেন্টটি খুঁজে পাওয়া যায়নি অথবা আপনার এই তথ্য দেখার অনুমতি নেই।</div>
            <a href='my-appointments.php' class='btn btn-primary'>তালিকা ফিরে যান</a>
          </div>";
    include_once '../../includes/footer.php';
    exit;
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- প্রিন্ট বাটন এবং টাইটেল -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold mb-0" style="color: var(--primary-navy);">অ্যাপয়েন্টমেন্ট ডিটেইলস</h3>
                <button onclick="window.print();" class="btn btn-outline-dark btn-sm rounded-pill px-3 shadow-sm">
                    <i class="fas fa-print me-1"></i> প্রিন্ট স্লিপ
                </button>
            </div>

            <!-- মূল কার্ড -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <!-- উপরের হেডার অংশ -->
                <div class="p-4 text-white d-flex justify-content-between align-items-center" style="background-color: var(--primary-navy);">
                    <div>
                        <p class="small opacity-75 mb-0">অ্যাপয়েন্টমেন্ট আইডি</p>
                        <h5 class="fw-bold mb-0">#APPT-<?php echo $data['id']; ?></h5>
                    </div>
                    <div class="text-end">
                        <p class="small opacity-75 mb-0">স্ট্যাটাস</p>
                        <?php if ($data['status'] == 'pending'): ?>
                            <span class="badge bg-warning text-dark px-3 rounded-pill">পেন্ডিং</span>
                        <?php elseif ($data['status'] == 'approved'): ?>
                            <span class="badge bg-success px-3 rounded-pill">অনুমোদিত</span>
                        <?php else: ?>
                            <span class="badge bg-secondary px-3 rounded-pill"><?php echo $data['status']; ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-body p-4 p-md-5 bg-white">
                    <div class="row g-4">
                        <!-- ডাক্তার সংক্রান্ত তথ্য -->
                        <div class="col-md-6 border-end">
                            <h6 class="text-uppercase text-muted small fw-bold mb-3">ডাক্তারের তথ্য</h6>
                            <h5 class="fw-bold text-primary mb-1"><?php echo $data['doctor_name']; ?></h5>
                            <p class="small mb-1 text-muted"><?php echo $data['specialization']; ?></p>
                            <p class="small mb-0 text-muted"><?php echo $data['qualification']; ?></p>
                        </div>

                        <!-- অ্যাপয়েন্টমেন্ট সংক্রান্ত তথ্য -->
                        <div class="col-md-6 ps-md-4">
                            <h6 class="text-uppercase text-muted small fw-bold mb-3">বুকিং তথ্য</h6>
                            <div class="mb-2">
                                <i class="far fa-calendar-alt text-secondary me-2"></i>
                                <span class="fw-bold"><?php echo date('d F, Y', strtotime($data['appointment_date'])); ?></span>
                            </div>
                            <div class="mb-2">
                                <i class="fas fa-money-bill-wave text-success me-2"></i>
                                <span class="fw-bold">ভিজিট ফি: ৳ <?php echo $data['fee']; ?></span>
                            </div>
                            <div class="mb-0">
                                <i class="far fa-clock text-info me-2"></i>
                                <span class="small text-muted">হাসপাতাল থেকে সময় জানিয়ে কল করা হবে।</span>
                            </div>
                        </div>

                        <!-- রোগীর বার্তা -->
                        <div class="col-12 mt-4 pt-3 border-top">
                            <h6 class="text-uppercase text-muted small fw-bold mb-2">আপনার বার্তা / সমস্যা</h6>
                            <p class="bg-light p-3 rounded-3 small">
                                <?php echo !empty($data['message']) ? $data['message'] : 'কোনো বার্তা দেওয়া হয়নি।'; ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- নিচের অংশ -->
                <div class="card-footer bg-light p-3 text-center border-0">
                    <small class="text-muted">বুকিং করার সময়: <?php echo date('d M, Y (h:i A)', strtotime($data['created_at'])); ?></small>
                </div>
            </div>

            <div class="text-center">
                <a href="my-appointments.php" class="btn btn-link text-decoration-none text-secondary">
                    <i class="fas fa-arrow-left me-1"></i> তালিকায় ফিরে যান
                </a>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .navbar, footer, .breadcrumb, .btn-link {
        display: none !important;
    }
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
    }
}
</style>

<?php include_once '../../includes/footer.php'; ?>