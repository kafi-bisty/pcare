<?php
// ১. সার্ভার ডিটেক্ট করে BASE_URL সেট করা
if ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_NAME'] == '127.0.0.1') {
    // লোকাল পিসির জন্য
    define('BASE_URL', 'http://localhost/patient-care-hospital/');
} else {
    // অনলাইন সার্ভারের জন্য (আপনার ডোমেইন)
    define('BASE_URL', 'https://patientcarebd.rf.gd/');
}

// ২. প্রোজেক্টের রুট পাথ
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__) . '/');
}

// ৩. ডিজিটাল প্রেসক্রিপশন লিঙ্ক
if (!defined('DIGITAL_PRESCRIPTION_URL')) {
    define('DIGITAL_PRESCRIPTION_URL', BASE_URL . 'modules/doctor/write-prescription.php');
}
?>