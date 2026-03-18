<?php
include_once '../../includes/header.php';

// লগইন চেক
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'patient') {
    echo "<script>window.location.href='../public/patient-login.php';</script>";
    exit;
}

$patient_id = $_SESSION['user_id'];

// ১. প্রোফাইল তথ্য আপডেট করা
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    $update_query = "UPDATE patients SET 
                    name = '$name', 
                    phone = '$phone', 
                    gender = '$gender', 
                    date_of_birth = '$dob', 
                    address = '$address' 
                    WHERE id = '$patient_id'";

    if (mysqli_query($conn, $update_query)) {
        $_SESSION['user_name'] = $name; // সেশন নাম আপডেট
        $_SESSION['success'] = "প্রোফাইল সফলভাবে আপডেট করা হয়েছে!";
    } else {
        $_SESSION['error'] = "দুঃখিত, আপডেট করা যায়নি।";
    }
}

// ২. পাসওয়ার্ড পরিবর্তন করা
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $old_pass = $_POST['old_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    $user_query = mysqli_query($conn, "SELECT password FROM patients WHERE id = '$patient_id'");
    $user_data = mysqli_fetch_assoc($user_query);

    if (password_verify($old_pass, $user_data['password'])) {
        if ($new_pass === $confirm_pass) {
            $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
            mysqli_query($conn, "UPDATE patients SET password = '$hashed_password' WHERE id = '$patient_id'");
            $_SESSION['success'] = "পাসওয়ার্ড সফলভাবে পরিবর্তন করা হয়েছে!";
        } else {
            $_SESSION['error'] = "নতুন পাসওয়ার্ড দুটি মেলেনি!";
        }
    } else {
        $_SESSION['error'] = "বর্তমান পাসওয়ার্ডটি ভুল!";
    }
}

// বর্তমান ডাটাবেজ থেকে তথ্য আনা
$query = mysqli_query($conn, "SELECT * FROM patients WHERE id = '$patient_id'");
$patient = mysqli_fetch_assoc($query);
?>

<div class="container-fluid bg-light py-5" style="min-height: 90vh;">
    <div class="container">
        <div class="row">
            <!-- বাম পাশে প্রোফাইল ওভারভিউ -->
            <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow-sm rounded-4 text-center p-4">
                    <div class="mb-3">
                        <img src="<?php echo BASE_URL; ?>assets/images/patients/default-patient.png" class="rounded-circle shadow-sm" width="120" height="120" style="border: 4px solid var(--light-blue); object-fit: cover;">
                    </div>
                    <h4 class="fw-bold mb-1" style="color: var(--primary-navy);"><?php echo $patient['name']; ?></h4>
                    <p class="text-muted small mb-3"><?php echo $patient['email']; ?></p>
                    <hr>
                    <div class="text-start">
                        <p class="small mb-1 text-muted"><i class="fas fa-phone me-2"></i> ফোন: <?php echo $patient['phone']; ?></p>
                        <p class="small mb-0 text-muted"><i class="fas fa-venus-mars me-2"></i> লিঙ্গ: <?php echo $patient['gender'] ?? 'Not Set'; ?></p>
                    </div>
                </div>
            </div>

            <!-- ডান পাশে এডিট ফর্ম -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <ul class="nav nav-tabs border-0" id="profileTab" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active fw-bold border-0 bg-transparent text-primary" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button">ব্যক্তিগত তথ্য</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link fw-bold border-0 bg-transparent text-muted" id="pass-tab" data-bs-toggle="tab" data-bs-target="#pass" type="button">পাসওয়ার্ড পরিবর্তন</button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-4">
                        <div class="tab-content" id="profileTabContent">
                            <!-- ব্যক্তিগত তথ্য ট্যাব -->
                            <div class="tab-pane fade show active" id="info">
                                <form action="" method="POST">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">পুরো নাম</label>
                                            <input type="text" name="name" class="form-control rounded-pill" value="<?php echo $patient['name']; ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">মোবাইল নম্বর</label>
                                            <input type="text" name="phone" class="form-control rounded-pill" value="<?php echo $patient['phone']; ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">লিঙ্গ</label>
                                            <select name="gender" class="form-select rounded-pill">
                                                <option value="Male" <?php echo ($patient['gender'] == 'Male') ? 'selected' : ''; ?>>পুরুষ</option>
                                                <option value="Female" <?php echo ($patient['gender'] == 'Female') ? 'selected' : ''; ?>>মহিলা</option>
                                                <option value="Other" <?php echo ($patient['gender'] == 'Other') ? 'selected' : ''; ?>>অন্যান্য</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">জন্ম তারিখ</label>
                                            <input type="date" name="dob" class="form-control rounded-pill" value="<?php echo $patient['date_of_birth']; ?>">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label small fw-bold">ঠিকানা</label>
                                            <textarea name="address" class="form-control rounded-4" rows="3"><?php echo $patient['address']; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <button type="submit" name="update_profile" class="btn btn-primary rounded-pill px-4" style="background-color: var(--secondary-cyan); border: none;">আপডেট করুন</button>
                                    </div>
                                </form>
                            </div>

                            <!-- পাসওয়ার্ড পরিবর্তন ট্যাব -->
                            <div class="tab-pane fade" id="pass">
                                <form action="" method="POST">
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">বর্তমান পাসওয়ার্ড</label>
                                        <input type="password" name="old_password" class="form-control rounded-pill" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">নতুন পাসওয়ার্ড</label>
                                        <input type="password" name="new_password" class="form-control rounded-pill" required>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label small fw-bold">নতুন পাসওয়ার্ড নিশ্চিত করুন</label>
                                        <input type="password" name="confirm_password" class="form-control rounded-pill" required>
                                    </div>
                                    <button type="submit" name="change_password" class="btn btn-danger rounded-pill px-4">পাসওয়ার্ড পরিবর্তন করুন</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.nav-tabs .nav-link.active {
    border-bottom: 3px solid var(--secondary-cyan) !important;
    color: var(--secondary-cyan) !important;
}
.form-control:focus {
    box-shadow: none;
    border-color: var(--secondary-cyan);
}
</style>

<?php include_once '../../includes/footer.php'; ?>