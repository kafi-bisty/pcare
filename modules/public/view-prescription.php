<?php
// ১. সরাসরি ডাটাবেজ কানেকশন এবং কনফিগ
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "patient_care_hospital";

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) { die("Connection failed: " . mysqli_connect_error()); }

if (session_status() === PHP_SESSION_NONE) { session_start(); }

if(!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid Prescription ID!");
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// ২. ডাটা আনা (প্রেসক্রিপশন, ডাক্তার এবং অ্যাপয়েন্টমেন্টের তথ্য)
$query = mysqli_query($conn, "SELECT p.*, 
          d.name as doc_name, d.qualification as doc_qual, d.specialization, d.chamber_no, d.image as doc_img,
          a.patient_name, a.age, a.gender, a.patient_phone, a.appointment_date 
          FROM prescriptions p 
          JOIN doctors d ON p.doctor_id = d.id 
          JOIN appointments a ON p.appointment_id = a.id 
          WHERE p.id = '$id'");

$data = mysqli_fetch_assoc($query);

if(!$data) { die("<h2 style='text-align:center; margin-top:50px;'>দুঃখিত, প্রেসক্রিপশনটি পাওয়া যায়নি!</h2>"); }

// ৩. কিউআর কোড জেনারেট (রোগী স্ক্যান করলে এই লিঙ্কটিই মোবাইলে দেখতে পাবে)
$actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$qr_api = "https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=" . urlencode($actual_link);
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription_#<?php echo $id; ?>_<?php echo $data['patient_name']; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root { --navy: #0A2647; --cyan: #2AA7E5; }
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, sans-serif; -webkit-print-color-adjust: exact; }
        
        /* প্যাড ডিজাইন (A4 Size Friendly) */
        .prescription-wrapper {
            background: #fff;
            width: 850px;
            min-height: 1100px;
            margin: 30px auto;
            padding: 40px 50px;
            position: relative;
            box-shadow: 0 0 40px rgba(0,0,0,0.1);
            border-top: 15px solid var(--navy);
        }

        /* প্যাড হেডার */
        .pad-header { border-bottom: 2px solid var(--navy); padding-bottom: 20px; margin-bottom: 20px; }
        .doc-name { color: var(--navy); font-weight: 800; font-size: 1.8rem; }
        .doc-qual { font-size: 0.9rem; color: #333; line-height: 1.5; }
        
        /* রোগীর তথ্য বার */
        .patient-info-bar { 
            background: #f8f9fa; 
            border: 1px solid #eee; 
            padding: 12px 20px; 
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--navy);
        }

        /* মেইন বডি লেআউট */
        .main-content { display: grid; grid-template-columns: 240px 2px 1fr; gap: 25px; min-height: 700px; }
        .sidebar { padding-right: 10px; }
        .vertical-divider { background: #eee; height: 100%; }
        
        .rx-icon { font-size: 2.8rem; font-weight: 900; color: var(--navy); margin-bottom: 15px; font-style: italic; }
        .section-label { font-weight: 800; color: var(--navy); text-transform: uppercase; font-size: 0.85rem; margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 3px; }

        /* ওষুধ তালিকা ও পরামর্শ */
        .medicine-list { font-size: 1.1rem; line-height: 2.2; white-space: pre-line; color: #000; font-weight: 500; }
        .advice-box { background: #fffcf0; padding: 15px; border-radius: 8px; border-left: 5px solid var(--cyan); margin-top: 30px; }

        /* ফুটার */
        .pad-footer {
            position: absolute;
            bottom: 40px;
            width: calc(100% - 100px);
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        .qr-code img { width: 85px; height: 85px; border: 1px solid #eee; padding: 5px; background: #fff; border-radius: 5px; }

        /* প্রিন্ট বাটন হাইড */
        @media print {
            body { background: white; margin: 0; }
            .prescription-wrapper { margin: 0; box-shadow: none; width: 100%; border-top: none; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<!-- প্রিন্ট কন্ট্রোল -->
<div class="text-center py-4 no-print">
    <button onclick="window.print()" class="btn btn-primary btn-lg rounded-pill px-5 shadow-lg fw-bold">
        <i class="fas fa-print me-2"></i> Print / Save as PDF
    </button>
    <button onclick="window.history.back()" class="btn btn-light rounded-pill px-4 ms-2 border">Back</button>
</div>

<div class="prescription-wrapper">
    <!-- ১. হেডার অংশ -->
    <header class="pad-header row align-items-center">
        <div class="col-8">
            <h1 class="doc-name mb-1">ডাঃ <?php echo $data['doc_name']; ?></h1>
            <div class="doc-qual">
                <?php echo nl2br($data['doc_qual']); ?><br>
                <span class="fw-bold text-primary"><?php echo $data['specialization']; ?></span>
            </div>
        </div>
        <div class="col-4 text-end">
            <h4 class="fw-bold text-navy mb-0">পেশেন্ট কেয়ার হাসপাতাল</h4>
            <p class="small text-muted mb-0">কলেজ রোড, বরগুনা</p>
            <p class="small text-muted mb-0">হেল্পলাইন: +৮৮০ ১৩৩১৪ ৩৪৩৪৭</p>
            <p class="x-small text-muted" style="font-size: 10px;">Web: patientcarehospital.com</p>
        </div>
    </header>

    <!-- ২. রোগীর তথ্য -->
    <section class="patient-info-bar">
        <span>Name: <b><?php echo $data['patient_name']; ?></b></span>
        <span>Age: <b><?php echo $data['age']; ?>Y</b></span>
        <span>Sex: <b><?php echo $data['gender']; ?></b></span>
        <span>Date: <b><?php echo date('d/m/Y', strtotime($data['created_at'])); ?></b></span>
        <span>সিরিয়াল আইডি: <b>#<?php echo $data['appointment_id']; ?></b></span>
    </section>

    <!-- ৩. মূল বডি (ডাবল কলাম) -->
    <div class="main-content">
        <!-- বাম কলাম (O/E Findings) -->
        <aside class="sidebar">
            <div class="mb-4">
                <div class="section-label">Chief Complaints</div>
                <p class="small text-dark"><?php echo nl2br($data['symptoms']); ?></p>
            </div>

            <div class="mb-4">
                <div class="section-label">On Examination</div>
                <div class="small lh-lg">
                    <?php if($data['pulse']) echo "Pulse: <b>".$data['pulse']."</b>/min<br>"; ?>
                    <?php if($data['bp']) echo "BP: <b>".$data['bp']."</b> mmHg<br>"; ?>
                    <?php if($data['temperature']) echo "Temp: <b>".$data['temperature']."</b><br>"; ?>
                    <?php if($data['weight']) echo "Weight: <b>".$data['weight']."</b> kg<br>"; ?>
                </div>
            </div>

            <?php if($data['diagnosis']): ?>
            <div class="mb-4">
                <div class="section-label">Diagnosis</div>
                <p class="small fw-bold text-navy"><?php echo $data['diagnosis']; ?></p>
            </div>
            <?php endif; ?>
        </aside>

        <!-- মাঝখানের দাগ -->
        <div class="vertical-divider"></div>

        <!-- ডান কলাম (Rx) -->
        <article class="ps-3">
            <div class="rx-icon">R<sub>x</sub></div>
            
            <div class="medicine-list">
                <?php echo nl2br($data['medicines']); ?>
            </div>

            <?php if($data['advice']): ?>
            <div class="advice-box shadow-sm">
                <div class="section-label" style="border:none; margin-bottom: 5px;">Advice / বিশেষ পরামর্শ:</div>
                <p class="small mb-0 text-dark"><?php echo nl2br($data['advice']); ?></p>
            </div>
            <?php endif; ?>
        </article>
    </div>

    <!-- ৪. ফুটার এবং QR Code -->
    <footer class="pad-footer row align-items-end">
        <div class="col-8">
            <div class="signature-area mt-4">
                <div style="border-top: 1px solid #333; width: 200px; padding-top: 5px; text-align: center;">
                    <p class="small fw-bold mb-0">Registered Physician Signature</p>
                </div>
            </div>
            <p class="text-muted mt-4" style="font-size: 10px; font-style: italic;">
                * দয়া করে পরবর্তী সাক্ষাতের সময় এই প্রেসক্রিপশনটি সাথে নিয়ে আসবেন।
            </p>
        </div>
        
        <div class="col-4 text-end qr-code">
            <img src="<?php echo $qr_api; ?>" alt="Scan QR">
            <p class="text-muted mt-1 mb-0" style="font-size: 9px;">Scan to view digital copy</p>
        </div>
    </footer>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>