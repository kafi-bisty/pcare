<?php
/**
 * Patient Care Hospital - Official OPD Ticket Generator
 * Developed for GitHub Upload
 */
session_start();
include_once '../../config/database.php';

// ১. সিকিউরিটি চেক: নিশ্চিত করুন যে ইউজার লগইন করা আছে
if (!isset($_SESSION['user_role'])) {
    die("Access Denied: Please login to generate tickets.");
}

// ২. আইডি রিসিভ ও ডাটা ভ্যালিডেশন
if (!isset($_GET['id']) || empty($_GET['id'])) { 
    die("<div style='text-align:center; padding:50px;'>Error: ID Missing!</div>"); 
}

$id = mysqli_real_escape_string($conn, $_GET['id']);
$query = "SELECT * FROM patient_billings WHERE id = '$id'";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) { 
    die("<div style='text-align:center; padding:50px;'>Error: No Record Found in Database!</div>"); 
}

// ৩. ডাইনামিক কিউআর কোড (অফিসিয়াল ভেরিফিকেশন লিঙ্ক)
$qr_content = "Serial: #$id | Patient: " . $data['patient_name'] . " | Date: " . $data['billing_date'];
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=" . urlencode($qr_content);
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official OPD Ticket - <?php echo htmlspecialchars($data['patient_name']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&display=swap');
        
        body { 
            background: #f1f5f9; 
            font-family: 'Hind Siliguri', sans-serif; 
            margin: 0; padding: 10px;
            display: flex; justify-content: center; align-items: center; min-height: 100vh;
        }

        .govt-ticket {
            width: 85mm;
            background: #fff;
            padding: 20px;
            border: 2px solid #0A2647;
            border-radius: 4px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            position: relative;
        }

        .govt-ticket::before {
            content: "OFFICIAL";
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 60px;
            color: rgba(0,0,0,0.02);
            font-weight: 900;
            pointer-events: none;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #0A2647;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header h1 { margin: 0; font-size: 20px; color: #0A2647; font-weight: 700; text-transform: uppercase; }
        .header p { margin: 2px 0; font-size: 11px; color: #444; }
        .opd-label {
            background: #0A2647; color: #fff;
            padding: 3px 15px; border-radius: 50px;
            display: inline-block; font-size: 11px;
            margin-top: 5px; font-weight: bold; text-transform: uppercase;
        }

        .serial-section {
            display: flex; justify-content: space-between; align-items: center;
            background: #fcfcfc; border: 1px solid #eee;
            padding: 10px; border-radius: 5px; margin-bottom: 15px;
        }
        .serial-box h2 { margin: 0; font-size: 26px; color: #d32f2f; font-weight: 800; }

        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .info-table td { padding: 8px 0; font-size: 13px; border-bottom: 1px solid #f9f9f9; }
        .label { color: #666; width: 35%; font-weight: 600; }
        .value { color: #000; font-weight: 700; }

        .doctor-box {
            border: 1.5px dashed #0A2647;
            padding: 12px; border-radius: 5px;
            margin-bottom: 15px; background: #f8faff;
        }
        .doctor-box strong { font-size: 15px; color: #0A2647; display: block; margin-top: 2px; }

        .footer-grid {
            display: flex; justify-content: space-between; align-items: center;
            border-top: 1.5px solid #0A2647; padding-top: 10px;
        }
        .qr-code img { width: 80px; height: 80px; border: 1px solid #eee; padding: 2px; }
        .instructions { font-size: 10px; color: #555; text-align: left; line-height: 1.4; }

        .footer-msg {
            background: #0A2647; color: white;
            padding: 8px; text-align: center;
            font-size: 11px; margin-top: 15px; border-radius: 2px;
        }

        .no-print { position: fixed; bottom: 20px; text-align: center; width: 100%; z-index: 100; }
        .btn-official {
            background: #0A2647; color: white; border: none;
            padding: 12px 30px; border-radius: 50px; cursor: pointer;
            font-weight: bold; box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            transition: 0.3s;
        }
        .btn-official:hover { background: #2AA7E5; transform: scale(1.05); }

        @media print {
            .no-print { display: none !important; }
            body { background: white; padding: 0; }
            .govt-ticket { border: 1.5px solid #000; box-shadow: none; width: 100%; border-radius: 0; }
            .header, .opd-label, .footer-msg { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button class="btn-official" onclick="window.print()">
            <i class="fas fa-print me-2"></i> PRINT OFFICIAL TICKET
        </button>
    </div>

    <div class="govt-ticket">
        <div class="header">
            <h1>Patient Care Hospital</h1>
            <p>College Road, Barguna | Help-Line: 01331-434347</p>
            <div class="opd-label">OPD Ticket (বহিঃবিভাগ)</div>
        </div>

        <div class="serial-section">
            <div class="serial-box">
                <span style="font-size: 10px; font-weight: bold; color: #666; text-transform: uppercase;">Serial No</span>
                <h2>#<?php echo $id; ?></h2>
            </div>
            <div style="text-align: right;">
                <span style="font-size: 10px; color: #666; font-weight: bold; display: block; text-transform: uppercase;">Date & Time</span>
                <strong style="font-size: 12px;"><?php echo date('d/m/Y | h:i A', strtotime($data['billing_date'])); ?></strong>
            </div>
        </div>

        <table class="info-table">
            <tr>
                <td class="label">Patient Name:</td>
                <td class="value"><?php echo htmlspecialchars($data['patient_name']); ?></td>
            </tr>
            <tr>
                <td class="label">Age / Gender:</td>
                <td class="value"><?php echo $data['patient_age']; ?>Y / <?php echo $data['patient_gender'] ?? 'N/A'; ?></td>
            </tr>
            <tr>
                <td class="label">Contact:</td>
                <td class="value"><?php echo $data['patient_phone']; ?></td>
            </tr>
        </table>

        <div class="doctor-box">
            <span style="font-size: 10px; font-weight: bold; text-transform: uppercase; color: #666;">Consultant Physician</span>
            <strong><?php echo htmlspecialchars($data['doctor_name']); ?></strong>
        </div>

        <div class="footer-grid">
            <div class="instructions">
                <strong>Instructions:</strong><br>
                1. Ticket is valid for today only.<br>
                2. Please wait for your serial.<br>
                3. Preserve this for next visit.
            </div>
            <div class="qr-code">
                <img src="<?php echo $qr_url; ?>" alt="QR Verification">
            </div>
        </div>

        <div class="footer-msg">
            Serving Humanity with Care & Quality.
        </div>
    </div>

</body>
</html>