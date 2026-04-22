<?php
include_once '../../config/database.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if ($data) {
    $bill_no = "LB-" . time();
    $p_name = mysqli_real_escape_string($conn, $data['p_name']);
    $p_age = $data['p_age'];
    $p_sex = $data['p_sex'];
    $total = $data['total'];
    $discount = $data['discount'];
    $net = $data['net'];
    $tests = $data['tests'];

    // ১. বিল মেইন টেবিলে সেভ
    $query = "INSERT INTO lab_billings (bill_no, patient_name, patient_age, patient_gender, total_amount, discount_percent, net_amount) 
              VALUES ('$bill_no', '$p_name', '$p_age', '$p_sex', '$total', '$discount', '$net')";
    
    if (mysqli_query($conn, $query)) {
        $bill_id = mysqli_insert_id($conn);

        // ২. প্রতিটি টেস্ট আইটেম সেভ
        foreach ($tests as $test) {
            $t_name = $test['name'];
            $t_price = $test['price'];
            mysqli_query($conn, "INSERT INTO lab_bill_items (bill_id, test_name, price) VALUES ('$bill_id', '$t_name', '$t_price')");
        }

        // ৩. হাসপাতালের মেইন ইনকাম অ্যাকাউন্টে যোগ (Hospital Accounts)
        $desc = "ল্যাব বিল #$bill_no (রোগী: $p_name)";
        $today = date('Y-m-d');
        mysqli_query($conn, "INSERT INTO hospital_accounts (type, category, amount, receipt_no, description, date) 
                             VALUES ('income', 'ল্যাব (Lab)', '$net', '$bill_no', '$desc', '$today')");

        echo json_encode(['success' => true, 'bill_no' => $bill_no]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
    }
}
?>