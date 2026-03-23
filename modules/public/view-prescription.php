<?php
// ১. স্মার্ট ডাটাবেজ কানেকশন (Localhost এবং Online উভয় জায়গার জন্য)
if ($_SERVER['HTTP_HOST'] == 'localhost') {
    $conn = mysqli_connect("localhost", "root", "", "patient_care_hospital");
} else {
    $conn = mysqli_connect("sql108.infinityfree.com", "if0_41421837", "w6slJzLdiNhUI", "if0_41421837_pcarebd");
}

if (!$conn) { die("Connection failed: " . mysqli_connect_error()); }
mysqli_set_charset($conn, 'utf8mb4');

if (session_status() === PHP_SESSION_NONE) { session_start(); }

if(!isset($_GET['id']) || empty($_GET['id'])) { die("Invalid Prescription ID!"); }
$id = mysqli_real_escape_string($conn, $_GET['id']);

// ২. ডাটা আনা (ডাক্তার, রোগী ও প্রেসক্রিপশন)
$query = mysqli_query($conn, "SELECT p.*, 
          d.name as doc_name, d.qualification as doc_qual, d.specialization, d.chamber_no,
          a.patient_name, a.age, a.gender, a.patient_phone, a.id as appointment_serial 
          FROM prescriptions p 
          JOIN doctors d ON p.doctor_id = d.id 
          JOIN appointments a ON p.appointment_id = a.id 
          WHERE p.id = '$id'");

$data = mysqli_fetch_assoc($query);
if(!$data) { die("<h2 style='text-align:center; margin-top:50px;'>Prescription not found!</h2>"); }

// ৩. কিউআর কোড জেনারেট
$actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$qr_api = "https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=" . urlencode($actual_link);
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>Prescription_#<?php echo $data['appointment_serial']; ?>_<?php echo $data['patient_name']; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root { --navy: #0A2647; --cyan: #2AA7E5; }
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, sans-serif; -webkit-print-color-adjust: exact; }

        /* প্যাড ডিজাইন */
        .prescription-pad {
            background: #fff; width: 820px; min-height: 1050px; 
            margin: 30px auto; padding: 40px 50px; position: relative;
            box-shadow: 0 0 40px rgba(0,0,0,0.1); border-top: 15px solid var(--navy);
        }

        /* হেডার */
        .doc-name { color: var(--navy); font-weight: 800; font-size: 1.8rem; margin-bottom: 2px; }
        .doc-qual { font-size: 0.85rem; color: #444; line-height: 1.4; margin-bottom: 2px; }
        .hospital-name-side { color: var(--navy); font-weight: 700; font-size: 1.3rem; }

        /* রোগীর তথ্য বার */
        .patient-info-bar { 
            background: #f8f9fa; border: 1px solid #eee; padding: 10px 20px; 
            border-radius: 8px; margin-bottom: 30px; display: flex; 
            justify-content: space-between; font-size: 0.9rem; font-weight: 600; color: var(--navy);
        }

        /* মেইন কন্টেন্ট লেআউট (২ কলাম) */
        .main-content { display: grid; grid-template-columns: 220px 1px 1fr; gap: 25px; min-height: 650px; }
        .sidebar { padding-right: 10px; }
        .divider { background: #eee; height: 100%; }
        
        .rx-icon { font-size: 2.8rem; font-weight: 900; color: var(--navy); margin-bottom: 20px; font-style: italic; }
        .section-label { font-weight: 800; color: var(--navy); text-transform: uppercase; font-size: 0.8rem; margin-bottom: 10px; border-bottom: 1px solid #eee; }

        /* ওষুধ তালিকা ও পরামর্শ */
        .medicine-list { font-size: 1.05rem; line-height: 2.2; white-space: pre-line; color: #000; font-weight: 500; }
        .advice-box { background: #fffcf0; padding: 12px; border-radius: 8px; border-left: 5px solid var(--cyan); margin-top: 30px; }

        /* ফুটার ও কিউআর কোড */
        .pad-footer { position: absolute; bottom: 40px; width: calc(100% - 100px); border-top: 1px solid #eee; padding-top: 20px; }
        .qr-box { text-align: right; }
        .qr-box img { width: 75px; height: 75px; border: 1px solid #eee; padding: 4px; background: #fff; }

        @media print { 
            .no-print { display: none !important; } 
            .prescription-pad { margin: 0; width: 100%; box-shadow: none; border-top: none; } 
            body { background: white !important; }
        }
    </style>
</head>
<body>

<div class="text-center py-4 no-print">
    <button onclick="window.print()" class="btn btn-primary rounded-pill px-5 fw-bold shadow-lg">
        <i class="fas fa-print me-2"></i> Print Prescription
    </button>
    <button onclick="window.history.back()" class="btn btn-light rounded-pill px-4 ms-2 border">পিছনে যান</button>
</div>

<div class="prescription-pad">
    <!-- ১. হেডার সেকশন -->
    <header class="row align-items-center mb-4">
        <div class="col-7 border-end">
            <h1 class="doc-name mb-1"><?php echo $data['doc_name']; ?></h1>
            <div class="doc-qual"><?php echo nl2br($data['doc_qual']); ?></div>
            <div class="small fw-bold text-primary mt-1"><?php echo $data['specialization']; ?></div>
        </div>
        <div class="col-5 text-end">
            <h3 class="hospital-name-side mb-0">পেশেন্ট কেয়ার হাসপাতাল</h3>
            <p class="small text-muted mb-0">কলেজ রোড, বরগুনা</p>
            <p class="x-small text-muted" style="font-size: 10px;">হেল্পলাইন: +৮৮০ ১৩৩১৪ ৩৪৩৪৭</p>
        </div>
    </header>

    <!-- ২. রোগীর তথ্য বার -->
    <section class="patient-info-bar">
        <span>Name: <b><?php echo $data['patient_name']; ?></b></span>
        <span>Age: <b><?php echo $data['age']; ?>Y</b></span>
        <span>Sex: <b><?php echo $data['gender']; ?></b></span>
        <span>Date: <b><?php echo date('d/m/Y', strtotime($data['created_at'])); ?></b></span>
        <span>Serial: <b>#<?php echo $data['appointment_serial']; ?></b></span>
    </section>

    <!-- ৩. মূল কন্টেন্ট (২ কলাম) -->
    <div class="main-content">
        <!-- বাম সাইডবার -->
        <aside class="sidebar">
            <div class="mb-4">
                <div class="section-label">Complaints</div>
                <p class="small text-dark"><?php echo nl2br($data['symptoms'] ?: 'None'); ?></p>
            </div>
            
            <div class="mb-4">
                <div class="section-label">O/E Findings</div>
                <div class="small lh-lg">
                    <?php if($data['pulse']) echo "Pulse: <b>".$data['pulse']."</b>/min<br>"; ?>
                    <?php if($data['bp']) echo "BP: <b>".$data['bp']."</b> mmHg<br>"; ?>
                    <?php if($data['temperature']) echo "Temp: <b>".$data['temperature']."</b><br>"; ?>
                    <?php if($data['weight']) echo "Weight: <b>".$data['weight']."</b> kg<br>"; ?>
                </div>
            </div>

            <div class="mb-4">
                <div class="section-label">Diagnosis</div>
                <p class="small fw-bold text-navy"><?php echo $data['diagnosis'] ?: 'N/A'; ?></p>
            </div>
        </aside>

        <!-- বিভাজক দাগ -->
        <div class="divider"></div>

        <!-- ডান কলাম (Rx) -->
        <article class="ps-3">
            <div class="rx-icon">R<sub>x</sub></div>
            <div class="medicine-list">
                <?php echo nl2br($data['medicines']); ?>
            </div>

            <?php if($data['advice']): ?>
            <div class="advice-box shadow-sm">
                <div class="section-label" style="border:none; margin-bottom: 5px;">Advice / পরামর্শ:</div>
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
                    <p class="small fw-bold mb-0">Physician Signature</p>
                </div>
            </div>
            <p class="text-muted mt-4" style="font-size: 10px; font-style: italic;">
                * দয়া করে পরবর্তী সাক্ষাতের সময় এটি সাথে নিয়ে আসবেন।
            </p>
        </div>
        
        <div class="col-4 qr-box">
            <img src="<?php echo $qr_api; ?>" alt="QR Code">
            <p class="text-muted mt-1 mb-0" style="font-size: 8px;">Scan to Verify Digital Copy</p>
        </div>
    </footer>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>