<?php
session_start();
include_once '../../config/database.php';

if (!isset($_GET['id'])) { die("Invalid ID"); }
$ad_id = mysqli_real_escape_string($conn, $_GET['id']);

// ১. এডমিশন ডাটা আনা
$ad_query = mysqli_query($conn, "SELECT * FROM admissions WHERE id = '$ad_id'");
$patient = mysqli_fetch_assoc($ad_query);

// ২. সকল সার্ভিসের তালিকা আনা
$services_query = mysqli_query($conn, "SELECT * FROM admission_services WHERE admission_id = '$ad_id' ORDER BY id ASC");

if (!$patient) { die("তথ্য পাওয়া যায়নি!"); }
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>Invoice - <?php echo $patient['patient_name']; ?></title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f0f0f0; margin: 0; padding: 20px; }
        .invoice-box {
            max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee;
            background: #fff; box-shadow: 0 0 10px rgba(0, 0, 0, 0.15); font-size: 14px; color: #333;
        }
        .header { text-align: center; border-bottom: 2px solid #0A2647; padding-bottom: 20px; margin-bottom: 20px; }
        .header h2 { margin: 0; color: #0A2647; text-transform: uppercase; }
        .header p { margin: 5px 0; color: #666; font-size: 12px; }
        
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 5px 0; vertical-align: top; }
        
        .bill-table { width: 100%; border-collapse: collapse; text-align: left; }
        .bill-table th { background: #0A2647; color: #fff; padding: 10px; border: 1px solid #ddd; }
        .bill-table td { padding: 10px; border: 1px solid #ddd; }
        
        .totals { float: right; width: 300px; margin-top: 20px; }
        .totals table { width: 100%; }
        .totals td { padding: 5px 0; font-size: 15px; }
        .final-amount { font-size: 20px; font-weight: bold; color: #0A2647; border-top: 2px solid #333; }
        
        .footer-sig { margin-top: 80px; display: flex; justify-content: space-between; text-align: center; }
        .sig-box { border-top: 1px solid #333; width: 150px; padding-top: 5px; font-size: 12px; }
        
        @media print {
            .no-print { display: none; }
            body { background: #fff; padding: 0; }
            .invoice-box { border: none; box-shadow: none; width: 100%; max-width: 100%; }
        }
    </style>
</head>
<body>

<div class="no-print" style="text-align: center; margin-bottom: 20px;">
    <button onclick="window.print()" style="padding: 10px 30px; background: #0A2647; color: #fff; border: none; border-radius: 5px; cursor: pointer;">Print Invoice</button>
</div>

<div class="invoice-box">
    <!-- হসপিটাল হেডার -->
    <div class="header">
        <h2>পেশেন্ট কেয়ার হাসপাতাল</h2>
        <p>এন্ড ডায়াগনস্টিক সেন্টার</p>
        <p>কলেজ রোড, বরগুনা। ফোন: ০১৩৩১৪ ৩৪৩৪৭, ০১৭১২৩৪৫৬৭৮</p>
        <div style="margin-top:10px; font-weight:bold; text-decoration: underline;">পেশেন্ট ডিসচার্জ বিল / ইনভয়েস</div>
    </div>

    <!-- রোগীর তথ্য -->
    <table class="info-table">
        <tr>
            <td width="50%">
                রোগীর নাম: <strong><?php echo $patient['patient_name']; ?></strong><br>
                মোবাইল: <?php echo $patient['phone']; ?><br>
                কেবিন/রুম নং: <strong><?php echo $patient['room_no']; ?></strong>
            </td>
            <td width="50%" style="text-align: right;">
                ইনভয়েস নং: #AD-<?php echo $patient['id']; ?><br>
                ভর্তির তারিখ: <?php echo date('d/m/Y', strtotime($patient['admission_date'])); ?><br>
                আজকের তারিখ: <?php echo date('d/m/Y'); ?>
            </td>
        </tr>
    </table>

    <!-- খরচের তালিকা -->
    <table class="bill-table">
        <thead>
            <tr>
                <th>ক্রমিক</th>
                <th>বিবরণ (Service Category)</th>
                <th>তারিখ</th>
                <th style="text-align: right;">পরিমাণ (৳)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $sl = 1;
            while($item = mysqli_fetch_assoc($services_query)): ?>
            <tr>
                <td width="10%"><?= $sl++; ?></td>
                <td><?= $item['category']; ?></td>
                <td width="20%"><?= date('d/m/Y', strtotime($item['service_date'])); ?></td>
                <td width="20%" style="text-align: right;"><?= number_format($item['amount'], 2); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- মোট হিসাব -->
    <div class="totals">
        <table>
            <tr>
                <td>মোট বিল (Subtotal):</td>
                <td style="text-align: right;">৳ <?php echo number_format($patient['total_bill'], 2); ?></td>
            </tr>
            <tr>
                <td>পরিশোধিত (Paid):</td>
                <td style="text-align: right; color: green;">৳ <?php echo number_format($patient['paid_amount'], 2); ?></td>
            </tr>
            <tr class="final-amount">
                <td>বকেয়া (Due Payable):</td>
                <td style="text-align: right;">৳ <?php echo number_format($patient['total_bill'] - $patient['paid_amount'], 2); ?></td>
            </tr>
        </table>
    </div>

    <div style="clear: both;"></div>

    <div style="margin-top: 30px; font-size: 12px;">
        কথায়: ......................................................................................... টাকা মাত্র।
    </div>

    <!-- স্বাক্ষর সেকশন -->
    <div class="footer-sig">
        <div class="sig-box">রোগী/অভিভাবকের স্বাক্ষর</div>
        <div class="sig-box">প্রস্তুতকারক</div>
        <div class="sig-box">কর্তৃপক্ষের স্বাক্ষর</div>
    </div>

    <p style="text-align: center; margin-top: 50px; font-size: 10px; color: #999; border-top: 1px dashed #eee; padding-top: 10px;">
        এই রিসিটটি সফটওয়্যার দ্বারা তৈরি করা হয়েছে। কোনো প্রকার ঘষামাজা গ্রহণযোগ্য নয়।
    </p>
</div>

</body>
</html>