<?php
// ১. কনফিগ এবং ডাটাবেজ সবার আগে (কোনো HTML আউটপুটের আগে)
include_once '../../config/database.php';
include_once '../../config/constants.php';
include_once '../../config/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ২. আইডি চেক করা
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ../../index.php");
    exit;
}

$appt_id = mysqli_real_escape_string($conn, $_GET['id']);

// অ্যাপয়েন্টমেন্ট এবং ডক্টরের তথ্য আনা
$query = mysqli_query($conn, "SELECT a.*, d.name as doc_name, d.specialization, d.chamber_no, d.fee 
          FROM appointments a 
          JOIN doctors d ON a.doctor_id = d.id 
          WHERE a.id = '$appt_id'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    header("Location: ../../index.php");
    exit;
}

// ৩. এখন হেডার ইনক্লুড করুন
include_once '../../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 text-center">
            <!-- সাকসেস আইকন -->
            <div class="mb-4">
                <i class="fas fa-check-circle fa-5x text-success animate-bounce"></i>
            </div>
            <h2 class="fw-bold text-navy">বুকিং সফল হয়েছে!</h2>
            <p class="text-muted small">আপনার অ্যাপয়েন্টমেন্টের তথ্য নিচে দেওয়া হলো। এটি সংরক্ষণ করুন।</p>

            <!-- প্রিন্ট স্লিপ কার্ড -->
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden mt-4 mb-4 text-start bg-white" id="printableSlip">
                <div class="card-header p-4 text-center text-white border-0" style="background: var(--primary-navy);">
                    <h5 class="mb-0 fw-bold">বুকিং স্লিপ (Booking Slip)</h5>
                    <p class="small mb-0 opacity-75">পেশেন্ট কেয়ার হাসপাতাল এন্ড ডায়াগনস্টিক সেন্টার</p>
                </div>
                
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4 border-bottom pb-3">
                        <small class="text-uppercase text-muted fw-bold">সিরিয়াল আইডি</small>
                        <h1 class="display-3 fw-bold text-primary mb-0">#<?php echo $data['id']; ?></h1>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <label class="small text-muted d-block text-uppercase">রোগীর নাম</label>
                            <span class="fw-bold"><?php echo $data['patient_name']; ?></span>
                        </div>
                        <div class="col-6 text-end">
                            <label class="small text-muted d-block text-uppercase">মোবাইল নম্বর</label>
                            <span class="fw-bold"><?php echo $data['patient_phone']; ?></span>
                        </div>
                        <div class="col-6">
                            <label class="small text-muted d-block text-uppercase">ডাক্তার</label>
                            <span class="fw-bold text-primary"> <?php echo $data['doc_name']; ?></span>
                        </div>
                        <div class="col-6 text-end">
                            <label class="small text-muted d-block text-uppercase">তারিখ</label>
                            <span class="fw-bold"><?php echo date('d M, Y', strtotime($data['appointment_date'])); ?></span>
                        </div>
                        <div class="col-6">
                            <label class="small text-muted d-block text-uppercase">চেম্বার/রুম নং</label>
                            <span class="fw-bold"><?php echo $data['chamber_no'] ? $data['chamber_no'] : 'রিসেপশনে দেখুন'; ?></span>
                        </div>
                        <div class="col-6 text-end">
                            <label class="small text-muted d-block text-uppercase">ভিজিট ফি</label>
                            <span class="fw-bold text-success">৳ <?php echo $data['fee']; ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer bg-light p-3 text-center border-0">
                    <small class="text-muted">কলেজ রোড, বরগুনা | হেল্পলাইন: +01331434347</small>
                </div>
            </div>

            <!-- ফেসবুক শেয়ার সেকশন (আপনার নতুন চাহিদা) -->
            <div class="mt-4 p-4 rounded-4 shadow-sm bg-white no-print border border-info border-opacity-25">
                <h6 class="fw-bold text-navy mb-3"><i class="fas fa-bullhorn me-2 text-primary"></i>আমাদের সেবাটি আপনার বন্ধুদের সাথে শেয়ার করুন:</h6>
                <p class="small text-muted mb-4">আপনার শেয়ারে অন্য কেউ উপকৃত হতে পারে!</p>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(BASE_URL); ?>" target="_blank" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm" style="background-color: #1877F2; border: none;">
                    <i class="fab fa-facebook me-2"></i> ফেসবুকে শেয়ার করুন
                </a>
            </div>

            <!-- কন্ট্রোল বাটন -->
            <div class="d-flex gap-2 justify-content-center mt-5 no-print">
                <button onclick="window.print()" class="btn btn-outline-dark rounded-pill px-4 fw-bold">
                    <i class="fas fa-print me-1"></i> স্লিপ প্রিন্ট করুন
                </button>
                <a href="../../index.php" class="btn btn-primary rounded-pill px-5 shadow fw-bold">
                    হোমপেজে ফিরে যান
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.text-navy { color: var(--primary-navy); }

/* প্রিন্ট সেটিংস: প্রিন্টের সময় অপ্রয়োজনীয় জিনিস হাইড করা */
@media print {
    .no-print, .navbar, footer, .notice-container, .top-header { display: none !important; }
    body { background: white !important; }
    .card { box-shadow: none !important; border: 1px solid #ddd !important; }
    .container { width: 100% !important; max-width: 100% !important; margin: 0 !important; }
}

/* সাকসেস এনিমেশন */
.animate-bounce {
    animation: bounce 2s infinite;
}
@keyframes bounce {
    0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
    40% {transform: translateY(-20px);}
    60% {transform: translateY(-10px);}
}
</style>

<?php include_once '../../includes/footer.php'; ?>