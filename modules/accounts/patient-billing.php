<?php
// ১. সেশন এবং ডাটাবেজ (সবার আগে)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../../config/database.php';

// ২. সিকিউরিটি চেক
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] != 'admin' && $_SESSION['user_role'] != 'accounts' && $_SESSION['user_role'] != 'reception')) {
    header("Location: ../auth/login.php");
    exit();
}

// ৩. বিল সেভ করার লজিক (বাটন ক্লিক করলে)
if (isset($_POST['save_bill'])) {
    $name = mysqli_real_escape_string($conn, $_POST['patient_name']);
    $total = (float)$_POST['total_bill'];
    $paid = (float)$_POST['paid_amount'];
    $date = $_POST['billing_date'];
    $service = $_POST['service_type'];

    // ডাটাবেজ অপারেশন...
    $sql = "INSERT INTO patient_billings (patient_name, total_bill, paid_amount, service_type, billing_date) 
            VALUES ('$name', '$total', '$paid', '$service', '$date')";
    
    if (mysqli_query($conn, $sql)) {
        $bill_id = mysqli_insert_id($conn);
        header("Location: patient-billing.php?print_id=$bill_id");
        exit();
    }
}

// ৪. এখন হেডার ইনক্লুড করুন
include_once '../../includes/header.php';
?>

<div class="container py-4">
    <h3 class="text-navy fw-bold">পেশেন্ট বিলিং এবং মানি রিসিট</h3>
    <!-- আপনার বাকি বিলিং ফর্মের কোড এখানে থাকবে -->
</div>

<?php include_once '../../includes/footer.php'; ?>