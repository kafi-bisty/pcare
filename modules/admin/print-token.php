<?php
session_start();
include_once '../../config/database.php';

if (!isset($_GET['id'])) { die("ID Missing"); }
$id = mysqli_real_escape_string($conn, $_GET['id']);
$data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM patient_billings WHERE id = '$id'"));
if (!$data) { die("Not Found"); }
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>Token - <?php echo $data['patient_name']; ?></title>
    <style>
        body { margin: 0; padding: 5px; font-family: 'Segoe UI', Arial, sans-serif; background: #eee; }
        .token {
            width: 80mm; background: #fff; border-radius: 15px; margin: auto; overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1); border: 1px solid #ddd;
        }
        .header {
            background: linear-gradient(135deg, #0A2647 0%, #2AA7E5 100%);
            color: #fff; padding: 15px 10px; text-align: center;
        }
        .header h2 { margin: 0; font-size: 18px; text-transform: uppercase; letter-spacing: 1px; }
        .header p { margin: 5px 0 0; font-size: 10px; opacity: 0.9; }
        
        .serial-box {
            background: #f0faff; border: 2px solid #2AA7E5; border-radius: 10px;
            text-align: center; margin: 15px; padding: 10px;
        }
        .serial-box span { font-size: 10px; color: #0A2647; font-weight: bold; display: block; }
        .serial-box strong { font-size: 28px; color: #0A2647; }

        .info { padding: 0 15px 15px; }
        .row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px dashed #eee; font-size: 12px; }
        .row:last-child { border: none; }
        .label { color: #666; font-weight: 600; }
        .val { color: #000; font-weight: 700; text-align: right; }
        
        .footer { background: #f9f9f9; padding: 10px; text-align: center; font-size: 9px; color: #888; border-top: 1px solid #eee; }
        @media print { .no-print { display: none; } body { background: #fff; padding: 0; } .token { box-shadow: none; border: 1px solid #000; width: 100%; } }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="text-align:center; padding: 10px;"><button onclick="window.print()">Print Now</button></div>
    
    <div class="token">
        <div class="header">
            <h2>পেশেন্ট কেয়ার হাসপাতাল</h2>
            <p>কলেজ রোড, বরগুনা। ফোন: ০১৩৩১৪ ৩৪৩৪৭</p>
        </div>

        <div class="serial-box">
            <span>সিরিয়াল নম্বর / Serial</span>
            <strong>#<?php echo $data['id']; ?></strong>
        </div>

        <div class="info">
            <div class="row"><span class="label">রোগীর নাম:</span> <span class="val"><?php echo $data['patient_name']; ?></span></div>
            <div class="row"><span class="label">বয়স:</span> <span class="val"><?php echo $data['patient_age']; ?> বছর</span></div>
            <div class="row"><span class="label">মোবাইল:</span> <span class="val"><?php echo $data['patient_phone']; ?></span></div>
            <div class="row"><span class="label">ঠিকানা:</span> <span class="val"><?php echo $data['patient_address']; ?></span></div>
            <div class="row"><span class="label">ডাক্তার:</span> <span class="val" style="color:#2AA7E5;"><?php echo $data['doctor_name']; ?></span></div>
            <div class="row"><span class="label">তারিখ:</span> <span class="val"><?php echo date('d-m-Y', strtotime($data['billing_date'])); ?></span></div>
        </div>

        <div class="footer">সফটওয়্যার জেনারেটেড টোকেন। সুস্থ থাকুন।</div>
    </div>
</body>
</html>