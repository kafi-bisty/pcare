<?php
session_start();
include_once '../../config/database.php';

if (!isset($_GET['id'])) { die("ID Missing!"); }
$id = mysqli_real_escape_string($conn, $_GET['id']);
$query = "SELECT * FROM patient_billings WHERE id = '$id'";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) { die("No Record Found!"); }

// ডাইনামিক কিউআর কোড (অফিসিয়াল ভেরিফিকেশনের জন্য)
$qr_content = "ID: #$id | Patient: " . $data['patient_name'] . " | Status: Verified";
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=" . urlencode($qr_content);
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Ticket - <?php echo $data['patient_name']; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&display=swap');
        
        body { 
            background: #e9ecef; 
            font-family: 'Hind Siliguri', sans-serif; 
            margin: 0; padding: 10px;
            display: flex; justify-content: center; align-items: center; min-height: 100vh;
        }

        /* অফিসিয়াল টিকেট বক্স */
        .govt-ticket {
            width: 85mm;
            background: #fff;
            padding: 20px;
            border: 2px solid #0A2647;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }

        /* সরকারি মনোগ্রাম বা ওয়াটারমার্ক স্টাইল */
        .govt-ticket::before {
            content: "OFFICIAL";
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 50px;
            color: rgba(0,0,0,0.03);
            font-weight: 900;
            z-index: 0;
            pointer-events: none;
        }

        /* হেডার সেকশন */
        .header {
            text-align: center;
            border-bottom: 2px solid #0A2647;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header h1 { margin: 0; font-size: 19px; color: #0A2647; font-weight: 700; text-transform: uppercase; }
        .header p { margin: 2px 0; font-size: 11px; color: #333; font-weight: 400; }
        .opd-label {
            background: #0A2647; color: #fff;
            padding: 2px 15px; border-radius: 50px;
            display: inline-block; font-size: 12px;
            margin-top: 5px; font-weight: bold;
        }

        /* সিরিয়াল নম্বর সেকশন */
        .serial-section {
            display: flex; justify-content: space-between; align-items: center;
            background: #f8f9fa; border: 1px solid #dee2e6;
            padding: 8px 12px; border-radius: 5px; margin-bottom: 15px;
        }
        .serial-box span { font-size: 10px; color: #666; font-weight: bold; }
        .serial-box h2 { margin: 0; font-size: 22px; color: #d9534f; }

        /* ইনফরমেশন টেবিল */
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; position: relative; z-index: 1; }
        .info-table td { padding: 6px 0; font-size: 13px; border-bottom: 1px solid #f1f1f1; }
        .label { color: #555; width: 40%; font-weight: 600; }
        .value { color: #000; font-weight: 700; }

        /* ডাক্তার বক্স */
        .doctor-box {
            border: 1.5px dashed #0A2647;
            padding: 10px; border-radius: 5px;
            margin-bottom: 15px; background: #fcfcfc;
        }
        .doctor-box span { display: block; font-size: 10px; color: #0A2647; font-weight: bold; }
        .doctor-box strong { font-size: 14px; color: #000; }

        /* নিচের অংশ */
        .footer-grid {
            display: flex; justify-content: space-between; align-items: center;
            border-top: 1.5px solid #0A2647; padding-top: 10px;
        }
        .qr-code img { width: 75px; height: 75px; }
        .instructions { font-size: 9px; color: #666; text-align: left; line-height: 1.3; }

        .footer-msg {
            background: #0A2647; color: white;
            padding: 6px; text-align: center;
            font-size: 10px; margin-top: 15px; border-radius: 3px;
        }

        /* প্রিন্ট বাটন */
        .no-print { position: fixed; top: 20px; text-align: center; width: 100%; z-index: 100; }
        .btn-official {
            background: #0A2647; color: white; border: none;
            padding: 10px 25px; border-radius: 5px; cursor: pointer;
            font-weight: bold; box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        @media print {
            .no-print { display: none; }
            body { background: white; padding: 0; }
            .govt-ticket { border: 1.5px solid #000; box-shadow: none; width: 100%; max-width: 100%; border-radius: 0; }
            .header, .opd-label, .footer-msg { -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body onload="/* window.print(); */">

    <div class="no-print">
        <button class="btn-official" onclick="window.print()">
            <i class="fas fa-print me-2"></i> অফিসিয়াল টিকেট প্রিন্ট করুন
        </button>
    </div>

    <div class="govt-ticket">
        <!-- হেডার -->
        <div class="header">
            <h1>পেশেন্ট কেয়ার হাসপাতাল</h1>
            <p>কলেজ রোড, বরগুনা। হেল্পলাইন: ০১৩৩১৪ ৩৪৩৪৭</p>
            <div class="opd-label">বহিঃবিভাগ টিকেট (OPD Ticket)</div>
        </div>

        <!-- সিরিয়াল ও তারিখ -->
        <div class="serial-section">
            <div class="serial-box">
                <span>সিরিয়াল নম্বর</span>
                <h2>#<?php echo $id; ?></h2>
            </div>
            <div style="text-align: right;">
                <span style="font-size: 10px; color: #666; font-weight: bold; display: block;">তারিখ ও সময়</span>
                <strong style="font-size: 12px;"><?php echo date('d/m/Y | h:i A', strtotime($data['billing_date'])); ?></strong>
            </div>
        </div>

        <!-- রোগীর তথ্য -->
        <table class="info-table">
            <tr>
                <td class="label">রোগীর নাম:</td>
                <td class="value"><?php echo $data['patient_name']; ?></td>
            </tr>
            <tr>
                <td class="label">বয়স ও লিঙ্গ:</td>
                <td class="value"><?php echo $data['patient_age']; ?> বছর / <?php echo $data['patient_gender'] ?? 'N/A'; ?></td>
            </tr>
            <tr>
                <td class="label">মোবাইল নম্বর:</td>
                <td class="value"><?php echo $data['patient_phone']; ?></td>
            </tr>
            <tr>
                <td class="label">ঠিকানা:</td>
                <td class="value text-truncate"><?php echo $data['patient_address']; ?></td>
            </tr>
        </table>

        <!-- নির্ধারিত ডাক্তার -->
        <div class="doctor-box">
            <span>নির্ধারিত চিকিৎসক (Consultant Physician):</span>
            <strong><?php echo $data['doctor_name']; ?></strong>
        </div>

        <!-- কিউআর ও নির্দেশাবলী -->
        <div class="footer-grid">
            <div class="instructions">
                <strong>নির্দেশাবলী:</strong><br>
                ১. টিকেটটি শুধুমাত্র আজকের জন্য প্রযোজ্য।<br>
                ২. সিরিয়াল অনুযায়ী অপেক্ষা করুন।<br>
                ৩. পরবর্তী সাক্ষাতের জন্য টিকেটটি সংরক্ষণ করুন।
            </div>
            <div class="qr-code">
                <img src="<?php echo $qr_url; ?>" alt="QR">
            </div>
        </div>

        <div class="footer-msg">
            রোগীর সেবায় আমরা সর্বদা আপনার পাশে। সুস্থ থাকুন।
        </div>
    </div>

</body>
</html>