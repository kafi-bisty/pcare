<?php
// ১. কনফিগ এবং ডাটাবেজ সবার আগে (কোনো HTML আউটপুটের আগে)
include_once '../../config/database.php';
include_once '../../config/constants.php';
include_once '../../config/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ২. ডাক্তারের ID চেক করা
if (!isset($_GET['doctor_id']) || empty($_GET['doctor_id'])) {
    header("Location: doctors.php");
    exit;
}

$doctor_id = mysqli_real_escape_string($conn, $_GET['doctor_id']);

// ৩. অ্যাপয়েন্টমেন্ট সেভ করার লজিক (header.php এর উপরে থাকতে হবে)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_appointment'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $age = mysqli_real_escape_string($conn, $_POST['age']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    
    // লগইন করা থাকলে আইডি নিবে, নাহলে NULL
    $patient_id = (isset($_SESSION['user_id']) && $_SESSION['user_role'] == 'patient') ? $_SESSION['user_id'] : 'NULL';

    // তারিখ ভ্যালিডেশন (ডাক্তার ওইদিন বসেন কি না চেক)
    $day_name = date('l', strtotime($date));
    $check_sched = mysqli_query($conn, "SELECT * FROM doctor_schedules WHERE doctor_id = '$doctor_id' AND day_of_week = '$day_name' AND is_available = 1");

    if (mysqli_num_rows($check_sched) == 0) {
        $_SESSION['error'] = "দুঃখিত! এই তারিখে ডাক্তার চেম্বারে বসবেন না। দয়া করে অন্য তারিখ চেষ্টা করুন।";
    } else {
        // ডাটাবেজে ইনসার্ট
        $insert = "INSERT INTO appointments (doctor_id, patient_id, patient_name, patient_phone, age, gender, address, appointment_date, message, status) 
                   VALUES ('$doctor_id', $patient_id, '$name', '$phone', '$age', '$gender', '$address', '$date', '$message', 'pending')";

        if (mysqli_query($conn, $insert)) {
            $last_id = mysqli_insert_id($conn); // এই মাত্র তৈরি হওয়া আইডি
            
            // সফল হলে সাকসেস পেজে পাঠিয়ে দেওয়া হচ্ছে
            header("Location: booking-success.php?id=" . $last_id);
            exit;
        } else {
            $_SESSION['error'] = "দুঃখিত, বুকিং করা সম্ভব হয়নি। " . mysqli_error($conn);
        }
    }
}

// ৪. এখন হেডার ইনক্লুড করুন এবং ফর্ম দেখান
include_once '../../includes/header.php';

// ডাক্তারের তথ্য আনা
$doctor_query = mysqli_query($conn, "SELECT * FROM doctors WHERE id = '$doctor_id'");
$doctor = mysqli_fetch_assoc($doctor_query);

// যদি রোগী লগইন করা থাকে, তার তথ্য আনা
$p_name = ""; $p_phone = ""; $p_gender = ""; $p_age = ""; $p_address = "";
if (isset($_SESSION['user_id']) && $_SESSION['user_role'] == 'patient') {
    $u_id = $_SESSION['user_id'];
    $p_query = mysqli_query($conn, "SELECT name, phone, gender, date_of_birth, address FROM patients WHERE id = '$u_id'");
    $p_data = mysqli_fetch_assoc($p_query);
    if ($p_data) {
        $p_name = $p_data['name'];
        $p_phone = $p_data['phone'];
        $p_gender = $p_data['gender'];
        $p_address = $p_data['address'];
        if(!empty($p_data['date_of_birth'])){
            $dob = new DateTime($p_data['date_of_birth']);
            $p_age = (new DateTime())->diff($dob)->y;
        }
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header p-4 text-white text-center" style="background: linear-gradient(135deg, var(--primary-navy), var(--secondary-cyan)); border: none;">
                    <h4 class="fw-bold mb-0">অ্যাপয়েন্টমেন্ট ফর্ম</h4>
                    <p class="small opacity-75 mb-0">ডাক্তার: <?php echo $doctor['name']; ?> (<?php echo $doctor['specialization']; ?>)</p>
                </div>
                
                <div class="card-body p-4 p-md-5 bg-white">
                    <form action="" method="POST">
                        <div class="row g-3">
                            <!-- নাম ও ফোন -->
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">রোগীর নাম</label>
                                <input type="text" name="name" class="form-control rounded-3" value="<?php echo $p_name; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">মোবাইল নম্বর</label>
                                <input type="text" name="phone" class="form-control rounded-3" value="<?php echo $p_phone; ?>" required>
                            </div>

                            <!-- বয়স ও লিঙ্গ -->
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">রোগীর বয়স</label>
                                <input type="number" name="age" class="form-control rounded-3" value="<?php echo $p_age; ?>" placeholder="যেমন: ২৫" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">লিঙ্গ</label>
                                <select name="gender" class="form-select rounded-3" required>
                                    <option value="Male" <?php echo ($p_gender == 'Male') ? 'selected' : ''; ?>>পুরুষ (Male)</option>
                                    <option value="Female" <?php echo ($p_gender == 'Female') ? 'selected' : ''; ?>>মহিলা (Female)</option>
                                    <option value="Other" <?php echo ($p_gender == 'Other') ? 'selected' : ''; ?>>অন্যান্য (Other)</option>
                                </select>
                            </div>

                            <!-- ঠিকানা -->
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">ঠিকানা</label>
                                <input type="text" name="address" class="form-control rounded-3" value="<?php echo $p_address; ?>" placeholder="গ্রাম, থানা, জেলা" required>
                            </div>

                            <!-- অ্যাপয়েন্টমেন্ট তারিখ -->
                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-primary">তারিখ নির্বাচন করুন</label>
                                <input type="date" name="date" class="form-control rounded-3 border-primary" min="<?php echo date('Y-m-d'); ?>" required>
                            </div>

                            <!-- সমস্যা -->
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">আপনার সমস্যা (ঐচ্ছিক)</label>
                                <textarea name="message" class="form-control rounded-3" rows="3" placeholder="আপনার শারীরিক সমস্যাটি সংক্ষেপে লিখুন..."></textarea>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" name="submit_appointment" class="btn btn-primary btn-lg w-100 rounded-pill shadow fw-bold">
                                    বুকিং কনফার্ম করুন <i class="fas fa-check-circle ms-2"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>