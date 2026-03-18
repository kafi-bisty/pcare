<?php
// ১. এরর রিপোর্টিং
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ২. সরাসরি লাইভ ডাটাবেজ কানেকশন
$servername = "sql108.infinityfree.com";
$username = "if0_41421837";
$password = "w6slJzLdiNhUI"; 
$dbname = "if0_41421837_pcarebd";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// ৩. আইডি চেক
if(!isset($_GET['id']) || empty($_GET['id'])) {
    die("<h2 style='text-align:center;'>Invalid Prescription ID!</h2>");
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// ৪. সংশোধিত ডাটা কুয়েরি (a.appointment_id এর বদলে a.id ব্যবহার করা হয়েছে)
$query = mysqli_query($conn, "SELECT p.*, 
          d.name as doc_name, d.qualification as doc_qual, d.specialization, d.chamber_no,
          a.patient_name, a.age, a.gender, a.id as appointment_serial 
          FROM prescriptions p 
          JOIN doctors d ON p.doctor_id = d.id 
          JOIN appointments a ON p.appointment_id = a.id 
          WHERE p.id = '$id'");

$data = mysqli_fetch_assoc($query);

if(!$data) {
    die("<h2 style='text-align:center; margin-top:50px;'>Prescription not found!</h2>");
}

// ৫. কিউআর কোড জেনারেট
$actual_link = "https://patientcarebd.rf.gd/modules/public/view-prescription.php?id=" . $id;
$qr_api = "https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=" . urlencode($actual_link);
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>ডিজিটাল প্রেসক্রিপশন - <?php echo $data['patient_name']; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, sans-serif; }
        .prescription-wrapper {
            background: #fff; width: 850px; min-height: 1050px; 
            margin: 30px auto; padding: 50px; position: relative;
            box-shadow: 0 0 30px rgba(0,0,0,0.1); border-top: 10px solid #0A2647;
        }
        .doc-name { color: #0A2647; font-weight: 800; font-size: 1.8rem; }
        .patient-info-bar { background: #f8f9fa; padding: 12px; border-radius: 8px; margin-bottom: 30px; display: flex; justify-content: space-between; font-weight: 600; font-size: 0.9rem; border: 1px solid #eee; color: #0A2647; }
        .main-content { display: grid; grid-template-columns: 220px 2px 1fr; gap: 30px; min-height: 600px; }
        .rx-title { font-size: 2.5rem; font-weight: 900; color: #0A2647; }
        .medicine-text { font-size: 1.1rem; line-height: 2.2; white-space: pre-line; color: #000; }
        @media print { .no-print { display: none !important; } .prescription-wrapper { margin: 0; width: 100%; box-shadow: none; border-top: none; } }
        @media (max-width: 880px) { .prescription-wrapper { width: 95%; margin: 10px auto; padding: 20px; } .main-content { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<div class="text-center py-4 no-print">
    <button onclick="window.print()" class="btn btn-primary rounded-pill px-5 fw-bold shadow">
        <i class="fas fa-print me-2"></i> প্রিন্ট বা পিডিএফ সেভ করুন
    </button>
    <a href="https://patientcarebd.rf.gd/" class="btn btn-light rounded-pill px-4 ms-2 border">হোমপেজ</a>
</div>

<div class="prescription-wrapper">
    <header class="row align-items-center border-bottom pb-3 mb-4">
        <div class="col-8">
            <h1 class="doc-name mb-1">ডাাঃ <?php echo $data['doc_name']; ?></h1>
            <p class="small text-muted mb-0"><?php echo $data['doc_qual']; ?></p>
            <p class="small fw-bold text-primary mb-0"><?php echo $data['specialization']; ?></p>
        </div>
        <div class="col-4 text-end">
            <h4 class="fw-bold mb-0" style="color:#0A2647">পেশেন্ট কেয়ার হাসপাতাল</h4>
            <p class="small text-muted mb-0">বরগুনা, বাংলাদেশ</p>
        </div>
    </header>

    <section class="patient-info-bar">
        <span>রোগী: <b><?php echo $data['patient_name']; ?></b></span>
        <span>বয়স: <b><?php echo $data['age']; ?>Y</b></span>
        <span>তারিখ: <b><?php echo date('d/m/Y', strtotime($data['created_at'])); ?></b></span>
        <!-- এখানে appointment_serial ব্যবহার করা হয়েছে -->
        <span>ID: <b>#<?php echo $data['appointment_serial']; ?></b></span>
    </section>

    <div class="main-content">
        <aside>
            <h6 class="fw-bold mb-2 border-bottom pb-1">Complaints</h6>
            <p class="small"><?php echo nl2br($data['symptoms']); ?></p>
            <h6 class="fw-bold mt-4 mb-2 border-bottom pb-1">O/E Findings</h6>
            <div class="small lh-lg">
                Pulse: <?php echo $data['pulse']; ?><br>
                BP: <?php echo $data['bp']; ?><br>
                Temp: <?php echo $data['temperature']; ?>
            </div>
            <h6 class="fw-bold mt-4 mb-2 border-bottom pb-1">Diagnosis</h6>
            <p class="small fw-bold text-danger"><?php echo $data['diagnosis']; ?></p>
        </aside>
        <div style="background:#eee;"></div>
        <article class="ps-3">
            <div class="rx-title">R<sub>x</sub></div>
            <div class="medicine-text"><?php echo nl2br($data['medicines']); ?></div>
            <?php if($data['advice']): ?>
                <div class="mt-5 p-3 bg-light rounded border-start border-4 border-info">
                    <h6 class="fw-bold small">Advice:</h6>
                    <p class="small mb-0"><?php echo nl2br($data['advice']); ?></p>
                </div>
            <?php endif; ?>
        </article>
    </div>

    <footer class="mt-5 pt-5 row align-items-end" style="position: absolute; bottom: 50px; width: calc(100% - 100px);">
        <div class="col-8">
            <div style="border-top: 1px solid #333; width: 180px; padding-top: 5px; text-align:center;">
                <p class="small fw-bold mb-0">Physician Signature</p>
            </div>
        </div>
        <div class="col-4 text-end">
            <img src="<?php echo $qr_api; ?>" alt="QR" style="width: 80px; border:1px solid #eee; padding:5px; background:#fff;">
            <p style="font-size: 8px;" class="text-muted mt-1">Scan to Verify Digital Copy</p>
        </div>
    </footer>
</div>

</body>
</html>