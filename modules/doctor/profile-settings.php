<?php
// ১. সেশন এবং ডাটাবেজ চেক (সবার আগে)
include_once '../../config/database.php';
include_once '../../config/constants.php';
include_once '../../config/functions.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'doctor') {
    header("Location: ../auth/staff-login.php"); exit;
}

$doctor_id = $_SESSION['user_id'];

// ২. তথ্য আপডেট লজিক
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $qualification = mysqli_real_escape_string($conn, $_POST['qualification']);
    $fee = mysqli_real_escape_string($conn, $_POST['fee']);
    $chamber = mysqli_real_escape_string($conn, $_POST['chamber_no']);
    $bio = mysqli_real_escape_string($conn, $_POST['bio']);
    $expertise = mysqli_real_escape_string($conn, $_POST['expertise']);

    $update_img_sql = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../../assets/images/doctors/";
        $file_ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $image_name = "doc_" . time() . "." . $file_ext;
        if(move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $image_name)){
            $update_img_sql = ", image = '$image_name'";
        }
    }

    $update_query = "UPDATE doctors SET 
                    name = '$name', email = '$email', phone = '$phone', 
                    qualification = '$qualification', fee = '$fee', 
                    chamber_no = '$chamber', bio = '$bio', expertise = '$expertise' 
                    $update_img_sql
                    WHERE id = '$doctor_id'";

    if (mysqli_query($conn, $update_query)) {
        $_SESSION['user_name'] = $name;
        $_SESSION['success'] = "প্রোফাইল সফলভাবে আপডেট করা হয়েছে!";
        header("Location: profile-settings.php"); exit;
    }
}

// ৩. পাসওয়ার্ড পরিবর্তন লজিক
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $old_pass = $_POST['old_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    $pass_query = mysqli_query($conn, "SELECT password FROM doctors WHERE id = '$doctor_id'");
    $user_data = mysqli_fetch_assoc($pass_query);

    if (password_verify($old_pass, $user_data['password'])) {
        if ($new_pass === $confirm_pass) {
            $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
            mysqli_query($conn, "UPDATE doctors SET password = '$hashed_password' WHERE id = '$doctor_id'");
            $_SESSION['success'] = "পাসওয়ার্ড সফলভাবে পরিবর্তন হয়েছে!";
        } else { $_SESSION['error'] = "নতুন পাসওয়ার্ড দুটি মেলেনি!"; }
    } else { $_SESSION['error'] = "পুরানো পাসওয়ার্ডটি সঠিক নয়!"; }
    header("Location: profile-settings.php"); exit;
}

$doctor = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM doctors WHERE id = '$doctor_id'"));
include_once '../../includes/header.php';
?>

<style>
    :root { --navy: #0A2647; --cyan: #2AA7E5; }
    body { background-color: #f8fafc; }
    .card-modern { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); transition: 0.3s; }
    .card-modern:hover { box-shadow: 0 15px 40px rgba(10, 38, 71, 0.1); }
    .form-label { font-weight: 600; font-size: 0.85rem; color: var(--navy); margin-bottom: 5px; }
    .form-control, .form-select { padding: 12px 15px; border-radius: 12px; border: 1.5px solid #eee; background: #fdfdfd; transition: 0.3s; }
    .form-control:focus { border-color: var(--cyan); background: #fff; box-shadow: 0 0 15px rgba(42, 167, 229, 0.1); }
    .btn-save { background: linear-gradient(135deg, var(--navy), #1a4a7a); color: white; border: none; padding: 12px 30px; border-radius: 50px; font-weight: 700; transition: 0.3s; }
    .btn-save:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(10, 38, 71, 0.3); color: #fff; }
    .input-group-text { border-radius: 0 12px 12px 0; background: #fff; border: 1.5px solid #eee; border-left: none; cursor: pointer; }
    .pw-input { border-right: none; }
</style>

<div class="container py-5">
    <div class="row align-items-center mb-4">
        <div class="col-6 text-start">
            <h3 class="fw-bold text-navy mb-0"><i class="fas fa-tools me-2"></i>Account Settings</h3>
        </div>
        <div class="col-6 text-end">
            <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm"><i class="fas fa-arrow-left me-1"></i> Dashboard</a>
        </div>
    </div>

    <div class="row g-4">
        <!-- বাম পাশ: প্রোফাইল প্রিভিউ এবং পাসওয়ার্ড -->
        <div class="col-lg-4">
            <!-- ইমেজ কার্ড -->
            <div class="card card-modern p-4 text-center mb-4 border-top border-4 border-primary">
                <div class="position-relative d-inline-block mx-auto mb-3">
                    <img id="preview" src="../../assets/images/doctors/<?php echo $doctor['image']; ?>" class="rounded-circle shadow-lg border border-5 border-white" width="160" height="160" style="object-fit: cover;">
                </div>
                <h5 class="fw-bold text-navy mb-1"><?php echo $doctor['name']; ?></h5>
                <p class="badge bg-light text-primary border rounded-pill px-3 py-2 small mb-0"><?php echo $doctor['specialization']; ?></p>
            </div>

            <!-- পাসওয়ার্ড কার্ড -->
            <div class="card card-modern p-4">
                <h6 class="fw-bold text-navy border-bottom pb-2 mb-4"><i class="fas fa-key me-2"></i>নিরাপত্তা এবং পাসওয়ার্ড</h6>
                <form action="" method="POST">
                    <div class="mb-3">
                        <label class="form-label">পুরানো পাসওয়ার্ড</label>
                        <div class="input-group">
                            <input type="password" name="old_password" class="form-control pw-input" id="old_pw" required>
                            <span class="input-group-text" onclick="togglePW('old_pw')"><i class="far fa-eye"></i></span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">নতুন পাসওয়ার্ড</label>
                        <div class="input-group">
                            <input type="password" name="new_password" class="form-control pw-input" id="new_pw" required>
                            <span class="input-group-text" onclick="togglePW('new_pw')"><i class="far fa-eye"></i></span>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">কনফার্ম পাসওয়ার্ড</label>
                        <div class="input-group">
                            <input type="password" name="confirm_password" class="form-control pw-input" id="conf_pw" required>
                            <span class="input-group-text" onclick="togglePW('conf_pw')"><i class="far fa-eye"></i></span>
                        </div>
                    </div>
                    <button type="submit" name="change_password" class="btn btn-danger w-100 rounded-pill py-2 shadow-sm fw-bold">Update Password</button>
                </form>
            </div>
        </div>

        <!-- ডান পাশ: মেইন ফরম -->
        <div class="col-lg-8">
            <div class="card card-modern p-4 p-md-5">
                <h5 class="fw-bold text-navy border-bottom pb-2 mb-4"><i class="fas fa-id-card me-2"></i>প্রফেশনাল ইনফরমেশন</h5>
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">ডাক্তারের পুরো নাম</label>
                            <input type="text" name="name" class="form-control" value="<?php echo $doctor['name']; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ইমেইল ঠিকানা</label>
                            <input type="email" name="email" class="form-control" value="<?php echo $doctor['email']; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">মোবাইল নম্বর</label>
                            <input type="text" name="phone" class="form-control" value="<?php echo $doctor['phone']; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">চেম্বার/রুম নং</label>
                            <input type="text" name="chamber_no" class="form-control" value="<?php echo $doctor['chamber_no']; ?>">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">শিক্ষাগত যোগ্যতা ও ডিগ্রী</label>
                            <input type="text" name="qualification" class="form-control" value="<?php echo $doctor['qualification']; ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">ভিজিট ফি (৳)</label>
                            <input type="number" name="fee" class="form-control" value="<?php echo $doctor['fee']; ?>" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Expertise (বিশেষ দক্ষতা)</label>
                            <input type="text" name="expertise" class="form-control" value="<?php echo $doctor['expertise']; ?>">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">আপনার সংক্ষিপ্ত পরিচয় (Bio)</label>
                            <textarea name="bio" class="form-control" rows="3"><?php echo $doctor['bio']; ?></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">প্রোফাইল ছবি পরিবর্তন করুন</label>
                            <input type="file" name="image" class="form-control" onchange="previewImg(this)">
                        </div>
                    </div>

                    <div class="text-end mt-5 pt-3 border-top">
                        <button type="submit" name="update_profile" class="btn btn-save btn-lg px-5 shadow">
                            Save Profile Changes <i class="fas fa-check-circle ms-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
// ১. পাসওয়ার্ড দেখা/লুকানোর ফাংশন (Toggle Visibility)
function togglePW(id) {
    const input = document.getElementById(id);
    const icon = event.currentTarget.querySelector('i');
    if (input.type === "password") {
        input.type = "text";
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = "password";
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

// ২. ছবি প্রিভিউ করার ফাংশন
function previewImg(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) { $('#preview').attr('src', e.target.result); }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include_once '../../includes/footer.php'; ?>