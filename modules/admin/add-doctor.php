<?php
session_start();
include_once '../../config/database.php';
include_once '../../config/constants.php';

// এডমিন চেক
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_doctor'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // নিচের এই ঘরগুলো অবশ্যই ডাটাবেজ এবং ফর্মে থাকতে হবে
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']); // এটি আপনার মিসিং ছিল
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $specialization = mysqli_real_escape_string($conn, $_POST['specialization']);
    $qualification = mysqli_real_escape_string($conn, $_POST['qualification']);
    $fee = mysqli_real_escape_string($conn, $_POST['fee']);
    $chamber = mysqli_real_escape_string($conn, $_POST['chamber_no']);
    $expertise = mysqli_real_escape_string($conn, $_POST['expertise']);
    $bio = mysqli_real_escape_string($conn, $_POST['bio']);
    $status = 'active';

    // ইমেজ আপলোড
    $image_name = "default-doctor.jpg";
    if (isset($_FILES['doctor_image']) && $_FILES['doctor_image']['error'] == 0) {
        $target_dir = "../../assets/images/doctors/";
        $file_ext = pathinfo($_FILES["doctor_image"]["name"], PATHINFO_EXTENSION);
        $image_name = "doc_" . time() . "." . $file_ext;
        move_uploaded_file($_FILES["doctor_image"]["tmp_name"], $target_dir . $image_name);
    }

    // ইনসার্ট কোয়েরি (নিশ্চিত করুন ডাটাবেজ কলামের নাম হুবহু এক)
    $query = "INSERT INTO doctors (name, username, password, phone, email, gender, specialization, qualification, fee, chamber_no, expertise, image, bio, status) 
              VALUES ('$name', '$username', '$password', '$phone', '$email', '$gender', '$specialization', '$qualification', '$fee', '$chamber', '$expertise', '$image_name', '$bio', '$status')";

    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "নতুন ডাক্তার সফলভাবে যোগ করা হয়েছে!";
        echo "<script>window.location.href='manage-doctors.php';</script>";
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
                    <h3 class="fw-bold mb-0"><i class="fas fa-user-md me-2"></i>ডাক্তার প্রোফাইল তৈরি করুন</h3>
                </div>
                <div class="card-body p-4 p-md-5 bg-white">
                    
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="row g-4">
                            <!-- বাম পাশ: ছবি -->
                            <div class="col-md-3 text-center border-end">
                                <div class="mb-4">
                                    <label class="form-label fw-bold d-block">প্রোফাইল ছবি</label>
                                    <div class="position-relative d-inline-block">
                                        <img id="imgPreview" src="../../assets/images/doctors/default-doctor.jpg" class="rounded-circle shadow-sm mb-3" width="160" height="160" style="object-fit: cover; border: 4px solid var(--light-bg);">
                                        <label for="doctor_image" class="btn btn-sm btn-primary position-absolute bottom-0 end-0 rounded-circle">
                                            <i class="fas fa-camera"></i>
                                        </label>
                                    </div>
                                    <input type="file" name="doctor_image" id="doctor_image" class="d-none" accept="image/*" onchange="previewImage(this)">
                                </div>
                                <div class="text-start">
                                    <label class="form-label small fw-bold">লিঙ্গ</label>
                                    <select name="gender" class="form-select">
                                        <option value="Male">পুরুষ</option>
                                        <option value="Female">মহিলা</option>
                                        <option value="Other">অন্যান্য</option>
                                    </select>
                                </div>
                            </div>

                            <!-- ডান পাশ -->
                            <div class="col-md-9">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">পুরো নাম</label>
                                        <input type="text" name="name" class="form-control" placeholder="ডা. নাম এখানে" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">ইউজারনেম</label>
                                        <input type="text" name="username" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">পাসওয়ার্ড</label>
                                        <input type="password" name="password" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">মোবাইল নম্বর</label>
                                        <input type="text" name="phone" class="form-control" required>
                                    </div>
                                    <!-- ইমেইল ইনপুট যা মিসিং ছিল -->
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">ইমেইল ঠিকানা</label>
                                        <input type="email" name="email" class="form-control" placeholder="doctor@mail.com" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">বিভাগ</label>
                                        <select name="specialization" class="form-select" required>
                                            <option value="মেডিসিন">মেডিসিন</option>
                                            <option value="গাইনি">গাইনি</option>
                                            <option value="কার্ডিওলজি">কার্ডিওলজি</option>
                                            <option value="শিশু">শিশু বিভাগ</option>
                                            <option value="চর্ম">চর্ম ও যৌন</option>
                                        </select>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label small fw-bold">ডিগ্রি/যোগ্যতা</label>
                                        <input type="text" name="qualification" class="form-control" placeholder="MBBS, FCPS" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">ভিজিট ফি</label>
                                        <input type="number" name="fee" class="form-control" required>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label small fw-bold">রুম/চেম্বার নম্বর</label>
                                        <input type="text" name="chamber_no" class="form-control" placeholder="যেমন: রুম-৪০১">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label small fw-bold text-success">Expertise (রোগ দেখুন)</label>
                                        <input type="text" name="expertise" class="form-control" placeholder="উচ্চ রক্তচাপ, এজমা ইত্যাদি">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label small fw-bold">ছোট বায়ো (Bio)</label>
                                        <textarea name="bio" class="form-control" rows="2"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-5">
                            <button type="submit" name="add_doctor" class="btn btn-primary btn-lg rounded-pill px-5">সংরক্ষণ করুন</button>
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