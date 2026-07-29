<?php
/**
 * Patient Care Hospital - Premium Reception Token
 * Updated with Doctor Profile Image & Profile QR
 */
session_start();
include_once '../../config/database.php';
include_once '../../config/constants.php'; // BASE_URL এর জন্য

// ১. আইডি রিসিভ ও ডাটা সংগ্রহ
if (!isset($_GET['id'])) { die("ID Missing!"); }
$id = mysqli_real_escape_string($conn, $_GET['id']);

// কুয়েরিতে d.image এবং d.id (doctor_id হিসেবে) নিশ্চিত করা হয়েছে
$query = mysqli_query($conn, "
    SELECT a.*, d.id as doctor_id, d.name as doctor_name, d.chamber_no, d.fee, d.image, d.specialization 
    FROM appointments a 
    JOIN doctors d ON a.doctor_id = d.id 
    WHERE a.id = '$id'
");
$data = mysqli_fetch_assoc($query);

if (!$data) { die("Record Not Found!"); }

// ২. সিরিয়াল ভেরিফিকেশন কিউআর কোড (নিচের বড় কোডটি)
$qr_content = "Serial: #$id | Patient: " . $data['patient_name'] . " | Status: Paid";
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=" . urlencode($qr_content);

// ৩. ডাক্তারের প্রোফাইল লিঙ্কের জন্য ইউআরএল এবং কিউআর কোড
$profile_link = BASE_URL . "modules/public/doctor-profile.php?id=" . $data['doctor_id'];
$qr_profile_url = "https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=" . urlencode($profile_link);
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reception Token - #<?php echo $id; ?></title>
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

        /* প্রিন্ট সেটিংস */
        .no-print { position: fixed; bottom: 20px; text-align: center; width: 100%; z-index: 100; }
        .btn-official {
            background: #0A2647; color: white; border: none;
            padding: 12px 30px; border-radius: 50px; cursor: pointer;
            font-weight: bold; box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        @media print {
            .no-print { display: none !important; }
            body { background: white; padding: 0; }
            .govt-ticket { border: 1.5px solid #000; box-shadow: none; width: 100%; border-radius: 0; }
            .header, .opd-label, .footer-msg { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print">
        <button class="btn-official" onclick="window.print()">
            <i class="fas fa-print me-2"></i> PRINT OFFICIAL TICKET
        </button>
    </div>

    <div class="govt-ticket">
        <!-- হেডার -->
        <div class="header">
            <h1>Patient Care Hospital</h1>
            <p>College Road, Barguna | Help-Line: 01331-434347</p>
            <div class="opd-label">OPD Ticket (বহিঃবিভাগ)</div>
        </div>

        <!-- সিরিয়াল ও সময় -->
        <div class="serial-section">
            <div class="serial-box">
                <span style="font-size: 10px; font-weight: bold; color: #666; text-transform: uppercase;">Serial No</span>
                <h2>#<?php echo $id; ?></h2>
            </div>
            <div style="text-align: right;">
                <span style="font-size: 10px; color: #666; font-weight: bold; display: block; text-transform: uppercase;">Date & Time</span>
                <strong style="font-size: 12px;"><?php echo date('d/m/Y | h:i A'); ?></strong>
            </div>
        </div>

        <!-- রোগীর তথ্য -->
        <table class="info-table">
            <tr>
                <td class="label">Patient Name:</td>
                <td class="value"><?php echo htmlspecialchars($data['patient_name']); ?></td>
            </tr>
            <tr>
                <td class="label">Age / Gender:</td>
                <td class="value"><?php echo $data['age']; ?>Y / <?php echo $data['gender'] ?? 'N/A'; ?></td>
            </tr>
            <tr>
                <td class="label">Contact:</td>
                <td class="value"><?php echo $data['patient_phone']; ?></td>
            </tr>
        </table>

        <!-- নির্ধারিত ডাক্তার সেকশন (ছবি ও কিউআর সহ) -->
        <div style="border: 1.5px dashed #0A2647; padding: 12px; border-radius: 8px; margin-bottom: 15px; background: #f8faff; position: relative;">
            <div style="display: flex; align-items: center;">
                <!-- ডাক্তারের ছোট ছবি -->
                <div style="margin-right: 12px;">
                    <img src="../../assets/images/doctors/<?php echo $data['image']; ?>" 
                         style="width: 55px; height: 55px; border-radius: 50%; border: 2px solid #2AA7E5; object-fit: cover; background: #fff;">
                </div>
                
                <!-- ডাক্তারের তথ্য -->
                <div style="flex: 1;">
                    <span style="display: block; font-size: 9px; color: #666; font-weight: bold; text-transform: uppercase;">Consultant Physician</span>
                    <strong style="font-size: 16px; color: #0A2647; display: block; line-height: 1.2;"><?php echo htmlspecialchars($data['doctor_name']); ?></strong>
                    <small style="color: #444; font-weight: 600; font-size: 11px;">
                        Room: <?php echo $data['chamber_no']; ?> | Fee: ৳<?php echo number_format($data['fee']); ?> (Paid)
                    </small>
                </div>

                <!-- প্রোফাইল কিউআর কোড -->
                <div style="text-align: center; border-left: 1px solid #ddd; padding-left: 8px; margin-left: 5px;">
                    <img src="<?php echo $qr_profile_url; ?>" style="width: 45px; height: 45px;">
                    <span style="display: block; font-size: 6px; font-weight: bold; color: #2AA7E5; margin-top: 2px; text-transform: uppercase;">Profile</span>
                </div>
            </div>
        </div>

        <!-- কিউআর ও নির্দেশাবলী -->
        <div class="footer-grid">
            <div class="instructions">
                <strong style="color:#0A2647">Instructions:</strong><br>
                1. Ticket is valid for today only.<br>
                2. Please wait for your serial.<br>
                3. Preserve this for next visit.
            </div>
            <div class="qr-code">
                <img src="<?php echo $qr_url; ?>" alt="Verification QR">
            </div>
        </div>

        <!-- ফুটার মেসেজ -->
        <div class="footer-msg">
            Serving Humanity with Care & Quality.
        </div>
    </div>

</body>
</html>