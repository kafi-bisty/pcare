
<?php
// modules/auth/logout.php
session_start();

// ১. সেশনের সকল ডাটা ক্লিয়ার করা
$_SESSION = array();

// ২. সেশন ধ্বংস করা
session_destroy();

// ৩. স্টাফ লগইন পেজে রিডাইরেক্ট করা
header("Location: staff-login.php");
exit;
?>