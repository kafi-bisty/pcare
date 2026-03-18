<?php
// ১. প্রোজেক্টের মেইন ইউআরএল
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/patient-care-hospital/');
}

// ২. প্রোজেক্টের রুট পাথ (সার্ভার সাইড ইনক্লুডের জন্য)
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__) . '/');
}

/**
 * ৩. ডিজিটাল প্রেসক্রিপশন ব্রিজ (Future-Ready)
 * ভবিষ্যতে প্রেসক্রিপশন লিঙ্ক পরিবর্তন করার জন্য শুধু নিচের লিঙ্কটি বদলে দিবেন।
 * এতে পুরো ওয়েবসাইটের সব ডাক্তারের প্রেসক্রিপশন বাটন অটোমেটিক নতুন লিঙ্কে কাজ করবে।
 */
if (!defined('DIGITAL_PRESCRIPTION_URL')) {
    define('DIGITAL_PRESCRIPTION_URL', BASE_URL . 'modules/doctor/write-prescription.php');
}

// যদি ভবিষ্যতে অন্য কোনো সফটওয়্যার বা সাইট ব্যবহার করেন, তবে শুধু নিচের মতো বদলে দিবেন:
// define('DIGITAL_PRESCRIPTION_URL', 'https://new-prescription-software.com/api/generate');

?>