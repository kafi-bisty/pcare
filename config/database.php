<?php
// database.php - ডাটাবেজ কানেকশন ফাইল

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'patient_care_hospital');

// ডাটাবেজ কানেকশন তৈরি
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// কানেকশন চেক
if (!$conn) {
    die("সংযোগ ব্যর্থ: " . mysqli_connect_error());
}

// চারসেট সেট করুন
mysqli_set_charset($conn, 'utf8mb4');

// টাইমজোন সেট করুন
date_default_timezone_set('Asia/Dhaka');

// ফাংশন: কানেকশন রিটার্ন
function getConnection() {
    global $conn;
    return $conn;
}
?>