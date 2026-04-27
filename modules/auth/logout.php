<?php
session_start();

// ১. সেশনের সব ডাটা মুছে ফেলা
$_SESSION = array();

// ২. সেশন কুকি ডিলিট করা
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// ৩. সেশন ধ্বংস করা
session_destroy();

// ৪. স্টাফ লগইন পেজে রিডাইরেক্ট করা
header("Location: staff-login.php");
exit;
?>