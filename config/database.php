<?php
// database.php - অটোমেটিক কানেকশন ডিটেকশন ফাইল

// আপনার পিসিতে (Localhost) চলছে নাকি অনলাইনে (Online) তা চেক করুন
if ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_NAME'] == '127.0.0.1') {
    
    // লোকালহোস্টের (XAMPP) কনফিগারেশন
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'patient_care_hospital');

} else {
    
    // অনলাইন (InfinityFree) এর কনফিগারেশন
    define('DB_HOST', 'sql108.infinityfree.com');
    define('DB_USER', 'if0_41421837');
    define('DB_PASS', 'w6slJzLdiNhUI'); // এখানে আপনার আসল পাসওয়ার্ড দিন
    define('DB_NAME', 'if0_41421837_pcarebd');

}

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