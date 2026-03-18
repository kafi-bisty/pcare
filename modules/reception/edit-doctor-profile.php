<?php
// ১. ডাটাবেজ এবং সেশন সবার আগে (কোনো HTML এর আগে)
include_once '../../config/database.php';
include_once '../../config/constants.php';
include_once '../../config/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ২. রিসেপশন লগইন চেক
if (!isset($_SESSION['reception_id']) || $_SESSION['user_role'] != 'reception') {
    header("Location: ../auth/staff-login.php");
    exit;
}

// ৩. ডাক্তারের আইডি চেক
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage-doctors.php");
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// ৪. ডাটাবেজ থেকে বর্তমান তথ্য আনা (আপডেটের আগে বা ফর্ম দেখানোর জন্য)
$query = mysqli_query($conn, "SELECT * FROM doctors WHERE id = '$id'");
$doctor = mysqli_fetch_assoc($query);

if (!$doctor) {
    header("Location: manage-doctors.php");
    exit;
}

// ৫. আপডেট লজিক (এটি অবশ্যই HTML/Header এর আগে থাকতে হবে)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $specialization = mysqli_real_escape_string($conn, $_POST['specialization']);
    $qualification = mysqli_real_escape_string($conn, $_POST['qualification']);
    $fee = mysqli_real_escape_string($conn, $_POST['fee']);
    $chamber = mysqli_real_escape_string($conn, $_POST['chamber_no']);
    $expertise = mysqli_real_escape_string($conn, $_POST['expertise']);
    $bio = mysqli_real_escape_string($conn, $_POST['bio']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $update_query = "UPDATE doctors SET 
                    specialization = '$specialization', 
                    qualification = '$qualification', 
                    fee = '$fee', 
                    chamber_no = '$chamber',
                    expertise = '$expertise',
                    bio = '$bio',
                    status = '$status' 
                    WHERE id = '$id'";

    if (mysqli_query($conn, $update_query)) {
        $_SESSION['success'] = "ডাক্তারের তথ্য সফলভাবে আপডেট করা হয়েছে!";
        // এখন রিডাইরেক্ট করলে কোনো এরর আসবে না
        header("Location: manage-doctors.php");
        exit;
    } else {
        $error = "দুঃখিত, তথ্য আপডেট করা যায়নি।";
    }
}

// ৬. এখন হেডার ইনক্লুড করুন (সব লজিক শেষ হওয়ার পর)
include_once '../../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header p-4 text-white d-flex align-items-center justify-content-between" style="background: linear-gradient(135deg, var(--primary-navy), var(--secondary-cyan));">
                    <div>
                        <h4 class="fw-bold mb-0">ডাক্তার তথ্য আপডেট</h4>
                        <p class="small mb-0 opacity-75">রিসেপশন প্যানেল কন্ট্রোল</p>
                    </div>
                </div>
                
                <div class="card-body p-4 p-md-5 bg-white">
                    <h5 class="fw-bold text-navy mb-4 border-bottom pb-2">নাম: <?php echo $doctor['name']; ?></h5>
                    
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger shadow-sm border-0"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">বিভাগ (Specialization)</label>
                                <select name="specialization" class="form-select" required>
                                    <option value="মেডিসিন" <?php if($doctor['specialization'] == 'মেডিসিন') echo 'selected'; ?>>মেডিসিন</option>
                                    <option value="গাইনি" <?php if($doctor['specialization'] == 'গাইনি') echo 'selected'; ?>>গাইনি</option>
                                    <option value="কার্ডিওলজি" <?php if($doctor['specialization'] == 'কার্ডিওলজি') echo 'selected'; ?>>কার্ডিওলজি</option>
                                    <option value="শিশু" <?php if($doctor['specialization'] == 'শিশু') echo 'selected'; ?>>শিশু </option>
                                    <option value="চর্ম ও যৌন" <?php if($doctor['specialization'] == 'চর্ম ও যৌন') echo 'selected'; ?>>চর্ম ও যৌন</option>
                                    <option value="সার্জারী" <?php if($doctor['specialization'] == 'সার্জারী') echo 'selected'; ?>>সার্জারী</option>
                                    <option value="পেইন মেডিসিন" <?php if($doctor['specialization'] == 'পেইন মেডিসিন') echo 'selected'; ?>>পেইন মেডিসিন</option>

                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold">ভিজিট ফি (৳)</label>
                                <input type="number" name="fee" class="form-control" value="<?php echo $doctor['fee']; ?>" required>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label small fw-bold">শিক্ষাগত যোগ্যতা</label>
                                <input type="text" name="qualification" class="form-control" value="<?php echo $doctor['qualification']; ?>" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold">চেম্বার/রুম নং</label>
                                <input type="text" name="chamber_no" class="form-control" value="<?php echo $doctor['chamber_no']; ?>" placeholder="যেমন: ৪০২">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-primary">বিশেষজ্ঞতা (Expertise)</label>
                                <input type="text" name="expertise" class="form-control" value="<?php echo $doctor['expertise']; ?>">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label small fw-bold">ডাক্তারের বায়ো (Bio)</label>
                                <textarea name="bio" class="form-control" rows="3"><?php echo $doctor['bio']; ?></textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold">স্ট্যাটাস</label>
                                <select name="status" class="form-select">
                                    <option value="active" <?php if($doctor['status'] == 'active') echo 'selected'; ?>>সক্রিয়</option>
                                    <option value="inactive" <?php if($doctor['status'] == 'inactive') echo 'selected'; ?>>নিষ্ক্রিয়</option>
                                </select>
                            </div>
                        </div>

                        <div class="text-end mt-5 pt-3 border-top">
                            <a href="manage-doctors.php" class="btn btn-light rounded-pill px-4 me-2">পিছনে যান</a>
                            <button type="submit" name="update_profile" class="btn btn-primary btn-lg rounded-pill px-5 shadow">
                                <i class="fas fa-save me-2"></i> তথ্য সেভ করুন
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="card-header p-4 text-white d-flex align-items-center justify-content-between" style="background: linear-gradient(135deg, var(--primary-navy), var(--secondary-cyan));">
    <div>
        <h4 class="fw-bold mb-0">ডাক্তার তথ্য আপডেট</h4>
    </div>
    <!-- ব্যাক বাটন -->
    <a href="dashboard.php" class="btn btn-light btn-sm rounded-pill px-3">
        <i class="fas fa-arrow-left me-1"></i> ড্যাশবোর্ড ফিরে যান
    </a>
</div>




<?php include_once '../../includes/footer.php'; ?>