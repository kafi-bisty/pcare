<?php
session_start();

// ১. লগআউট করার আগে ইউজারের রোলটি (Role) একটি ভেরিয়েবলে সেভ করে রাখা
$role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'guest';

// ২. সমস্ত সেশন ডাটা মুছে ফেলা
$_SESSION = array();

// ৩. সেশন পুরোপুরি ধ্বংস করা
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// ৪. রোল অনুযায়ী রিডাইরেক্ট করা
session_start(); // সেশন মেসেজ দেখানোর জন্য আবার স্টার্ট করা
$_SESSION['success'] = "আপনি সফলভাবে লগআউট করেছেন।";

if ($role == 'admin' || $role == 'doctor' || $role == 'reception') {
    // যদি স্টাফ হয়, তবে স্টাফ লগইন পেজে পাঠাবে
    header("Location: staff-login.php");
} else {
    // যদি রোগী হয়, তবে মেইন হোমপেজে অথবা রোগী লগইন পেজে পাঠাবে
    header("Location: ../public/patient-login.php");
}
exit;