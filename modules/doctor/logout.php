<?php
// সেশন শুরু করা
session_start();

// ডাক্তার সংক্রান্ত সেশন ডাটা মুছে ফেলা
unset($_SESSION['doctor_id']);
unset($_SESSION['doctor_name']);
unset($_SESSION['user_role']);

// সেশন পুরোপুরি ধ্বংস করা (অপশনাল, তবে ভালো)
// session_destroy(); 

// নতুন সেশন শুরু করে মেসেজ সেট করা
session_start();
$_SESSION['success'] = "আপনি সফলভাবে লগআউট করেছেন।";

// ডাক্তার লগইন পেজে পাঠিয়ে দেওয়া
header("Location: login.php");
exit;
?>