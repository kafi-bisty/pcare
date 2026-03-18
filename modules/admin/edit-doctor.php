<?php
session_start();
include_once '../../config/database.php';
include_once '../../config/constants.php';

// এডমিন চেক
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// ডাক্তারের আইডি চেক করা
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage-doctors.php");
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// বর্তমান তথ্য ডাটাবেজ থেকে আনা
$query = mysqli_query($conn, "SELECT * FROM doctors WHERE id = '$id'");
$doctor = mysqli_fetch_assoc($query);

if (!$doctor) {
    header("Location: manage-doctors.php");
    exit;
}

// আপডেট লজিক
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_doctor'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $specialization = mysqli_real_escape_string($conn, $_POST['specialization']);
    $qualification = mysqli_real_escape_string($conn, $_POST['qualification']);
    $fee = mysqli_real_escape_string($conn, $_POST['fee']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $chamber = mysqli_real_escape_string($conn, $_POST['chamber_no']);
    $expertise = mysqli_real_escape_string($conn, $_POST['expertise']);
    $bio = mysqli_real_escape_string($conn, $_POST['bio']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    // ইমেজ আপলোড লজিক
    $image_name = $doctor['image']; // আগে যা ছিল তা রাখা হলো
    if (isset($_FILES['doctor_image']) && $_FILES['doctor_image']['error'] == 0) {
        $target_dir = "../../assets/images/doctors/";
        $file_ext = pathinfo($_FILES["doctor_image"]["name"], PATHINFO_EXTENSION);
        $image_name = "doc_" . time() . "." . $file_ext;
        move_uploaded_file($_FILES["doctor_image"]["tmp_name"], $target_dir . $image_name);
        
        // পুরানো ছবি ডিলিট করা (যদি সেটি ডিফল্ট না হয়)
        if($doctor['image'] != 'default-doctor.jpg' && file_exists($target_dir . $doctor['image'])){
            unlink($target_dir . $doctor['image']);
        }
    }

    $update_query = "UPDATE doctors SET 
                    name = '$name', 
                    specialization = '$specialization', 
                    qualification = '$qualification', 
                    fee = '$fee', 
                    phone = '$phone',
                    email = '$email',
                    gender = '$gender',
                    chamber_no = '$chamber',
                    expertise = '$expertise',
                    bio = '$bio',
                    image = '$image_name',
                    status = '$status' 
                    WHERE id = '$id'";

    if (mysqli_query($conn, $update_query)) {
        $_SESSION['success'] = "ডাক্তারের তথ্য সফলভাবে আপডেট করা হয়েছে!";
        header("Location: manage-doctors.php");
        exit;
    } else {
        $error = "ডাটাবেজ এরর: " . mysqli_error($conn);
    }
}

include_once '../../includes/header.php'; 
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-11">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header p-4 text-white text-center" style="background: linear-gradient(135deg, var(--primary-navy), var(--secondary-cyan)); border:none;">
                    <h3 class="fw-bold mb-0"><i class="fas fa-user-edit me-2"></i>ডাক্তার প্রোফাইল এডিট করুন</h3>
                </div>
                <div class="card-body p-4 p-md-5 bg-white">
                    
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="row g-4">
                            <!-- বাম পাশ: ছবি ও লিঙ্গ -->
                            <div class="col-md-3 text-center border-end">
                                <div class="mb-4">
                                    <label class="form-label fw-bold d-block">প্রোফাইল ছবি</label>
                                    <div class="position-relative d-inline-block">
                                        <img id="imgPreview" src="../../assets/images/doctors/<?php echo $doctor['image']; ?>" class="rounded-circle shadow-sm mb-3" width="160" height="160" style="object-fit: cover; border: 4px solid var(--light-bg);">
                                        <label for="doctor_image" class="btn btn-sm btn-primary position-absolute bottom-0 end-0 rounded-circle">
                                            <i class="fas fa-camera"></i>
                                        </label>
                                    </div>
                                    <input type="file" name="doctor_image" id="doctor_image" class="d-none" accept="image/*" onchange="previewImage(this)">
                                </div>
                                <div class="text-start">
                                    <label class="form-label small fw-bold">লিঙ্গ (Gender)</label>
                                    <select name="gender" class="form-select">
                                        <option value="Male" <?php if($doctor['gender'] == 'Male') echo 'selected'; ?>>পুরুষ</option>
                                        <option value="Female" <?php if($doctor['gender'] == 'Female') echo 'selected'; ?>>মহিলা</option>
                                        <option value="Other" <?php if($doctor['gender'] == 'Other') echo 'selected'; ?>>অন্যান্য</option>
                                    </select>
                                </div>
                            </div>

                            <!-- ডান পাশ: সব তথ্যাদি -->
                            <div class="col-md-9">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">পুরো নাম</label>
                                        <input type="text" name="name" class="form-control" value="<?php echo $doctor['name']; ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">বিভাগ (Specialization)</label>
                                        <select name="specialization" class="form-select" required>
                                            <option value="মেডিসিন" <?php if($doctor['specialization'] == 'মেডিসিন') echo 'selected'; ?>>মেডিসিন</option>
                                            <option value="গাইনি" <?php if($doctor['specialization'] == 'গাইনি') echo 'selected'; ?>>গাইনি</option>
                                            <option value="কার্ডিওলজি" <?php if($doctor['specialization'] == 'কার্ডিওলজি') echo 'selected'; ?>>কার্ডিওলজি</option>
                                            <option value="শিশু" <?php if($doctor['specialization'] == 'শিশু') echo 'selected'; ?>>শিশু বিভাগ</option>
                                            <option value="চর্ম" <?php if($doctor['specialization'] == 'চর্ম') echo 'selected'; ?>>চর্ম ও যৌন</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">ইমেইল ঠিকানা</label>
                                        <input type="email" name="email" class="form-control" value="<?php echo $doctor['email']; ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">মোবাইল নম্বর</label>
                                        <input type="text" name="phone" class="form-control" value="<?php echo $doctor['phone']; ?>" required>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label small fw-bold">শিক্ষাগত যোগ্যতা (ডিগ্রি)</label>
                                        <input type="text" name="qualification" class="form-control" value="<?php echo $doctor['qualification']; ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">ভিজিট ফি (৳)</label>
                                        <input type="number" name="fee" class="form-control" value="<?php echo $doctor['fee']; ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-success">রুম/চেম্বার নম্বর</label>
                                        <input type="text" name="chamber_no" class="form-control" value="<?php echo $doctor['chamber_no']; ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">স্ট্যাটাস</label>
                                        <select name="status" class="form-select">
                                            <option value="active" <?php if($doctor['status'] == 'active') echo 'selected'; ?>>সক্রিয়</option>
                                            <option value="inactive" <?php if($doctor['status'] == 'inactive') echo 'selected'; ?>>নিষ্ক্রিয়</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label small fw-bold text-primary">Expertise (রোগ দেখুন)</label>
                                        <input type="text" name="expertise" class="form-control" value="<?php echo $doctor['expertise']; ?>">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label small fw-bold">ডাক্তার সম্পর্কে (Bio)</label>
                                        <textarea name="bio" class="form-control" rows="3"><?php echo $doctor['bio']; ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-5 pt-3 border-top">
                            <a href="manage-doctors.php" class="btn btn-light rounded-pill px-4 me-2">বাতিল করুন</a>
                            <button type="submit" name="update_doctor" class="btn btn-primary btn-lg rounded-pill px-5 shadow">
                                <i class="fas fa-save me-2"></i> তথ্য আপডেট করুন
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imgPreview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include_once '../../includes/footer.php'; ?>