<?php
// functions.php - সব ধরণের হেল্পার ফাংশন

/**
 * ইনপুট ক্লিন করার ফাংশন
 */
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * তারিখ ফরম্যাট পরিবর্তন (Y-m-d থেকে d/m/Y)
 */
function formatDate($date, $format = 'd/m/Y') {
    if($date == '0000-00-00' || empty($date)) {
        return 'N/A';
    }
    return date($format, strtotime($date));
}

/**
 * সময় ফরম্যাট (12 ঘন্টা ফরম্যাটে)
 */
function formatTime($time) {
    if(empty($time)) {
        return 'N/A';
    }
    return date('h:i A', strtotime($time));
}

/**
 * বর্তমান তারিখ ও সময়
 */
function getCurrentDateTime() {
    return date('Y-m-d H:i:s');
}

/**
 * ইউনিক অ্যাপয়েন্টমেন্ট নম্বর জেনারেট
 */
function generateAppointmentNo() {
    return 'APT-' . date('Ymd') . '-' . rand(1000, 9999);
}

/**
 * ইউনিক পেশেন্ট আইডি জেনারেট
 */
function generatePatientNo() {
    return 'PAT-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

/**
 * ইউজার লগইন চেক
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * রোল বেসড রিডাইরেক্ট
 */
function redirectBasedOnRole($role) {
    switch($role) {
        case 'admin':
            header('Location: modules/admin/dashboard.php');
            break;
        case 'doctor':
            header('Location: modules/doctor/dashboard.php');
            break;
        case 'reception':
            header('Location: modules/reception/dashboard.php');
            break;
        case 'patient':
            header('Location: modules/patient/dashboard.php');
            break;
        default:
            header('Location: index.php');
    }
    exit();
}

/**
 * সাকসেস মেসেজ সেট
 */
function setSuccess($message) {
    $_SESSION['success'] = $message;
}

/**
 * এরর মেসেজ সেট
 */
function setError($message) {
    $_SESSION['error'] = $message;
}

/**
 * বয়স ক্যালকুলেট (জন্ম তারিখ থেকে)
 */
function calculateAge($dob) {
    if(empty($dob) || $dob == '0000-00-00') {
        return 'N/A';
    }
    $birthDate = new DateTime($dob);
    $today = new DateTime('today');
    $age = $birthDate->diff($today)->y;
    return $age . ' বছর';
}

/**
 * ব্লাড গ্রুপের অপশন
 */
function getBloodGroups() {
    return ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
}

/**
 * সপ্তাহের দিনসমূহ বাংলায়
 */
function getWeekDays() {
    return [
        'saturday' => 'শনিবার',
        'sunday' => 'রবিবার',
        'monday' => 'সোমবার',
        'tuesday' => 'মঙ্গলবার',
        'wednesday' => 'বুধবার',
        'thursday' => 'বৃহস্পতিবার',
        'friday' => 'শুক্রবার'
    ];
}

/**
 * টাকা ফরম্যাট
 */
function formatMoney($amount) {
    return '৳ ' . number_format($amount, 2);
}

/**
 * স্ট্যাটাস ব্যাজ ক্লাস
 */
function getStatusBadgeClass($status) {
    switch($status) {
        case 'pending':
            return 'status-pending';
        case 'approved':
            return 'status-approved';
        case 'completed':
            return 'status-completed';
        case 'cancelled':
            return 'status-cancelled';
        default:
            return 'status-pending';
    }
}

/**
 * স্ট্যাটাস বাংলায়
 */
function getStatusBangla($status) {
    switch($status) {
        case 'pending':
            return 'অপেক্ষমাণ';
        case 'approved':
            return 'নিশ্চিত';
        case 'completed':
            return 'সম্পন্ন';
        case 'cancelled':
            return 'বাতিল';
        default:
            return $status;
    }
}

/**
 * ==========================================================
 * হাসপাতাল স্টাফদের অ্যাক্টিভিটি লগ রেকর্ড করার ফাংশন
 * ==========================================================
 */
function log_activity($conn, $action_type, $details) {
    // লগইন করা ইউজারের তথ্য সেশন থেকে নেওয়া
    $u_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
    $u_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'System';
    $u_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'Unknown';
    
    $details = mysqli_real_escape_string($conn, $details);
    $action_type = mysqli_real_escape_string($conn, $action_type);
    
    $query = "INSERT INTO activity_logs (user_id, user_name, user_role, action_type, details) 
              VALUES ('$u_id', '$u_name', '$u_role', '$action_type', '$details')";
    mysqli_query($conn, $query);
}
?>