<?php
session_start();
include_once '../../config/database.php';
include_once '../../config/constants.php';
include_once '../../config/functions.php';

// ১. এডমিন চেক
if (!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit; }

// ২. নোটিশ সেভ বা আপডেট করার লজিক
if (isset($_POST['save_msg'])) {
    $id = mysqli_real_escape_string($conn, $_POST['msg_id']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $msg = mysqli_real_escape_string($conn, $_POST['message']);
    $priority = $_POST['priority'];

    if ($id == "") {
        // নতুন নোটিশ যোগ করা
        $query = "INSERT INTO staff_announcements (title, message, priority) VALUES ('$title', '$msg', '$priority')";
        $action_text = "নতুন নোটিশ তৈরি করা হয়েছে।";
    } else {
        // পুরানো নোটিশ আপডেট করা
        $query = "UPDATE staff_announcements SET title='$title', message='$msg', priority='$priority' WHERE id='$id'";
        $action_text = "নোটিশ আপডেট করা হয়েছে।";
    }

    if (mysqli_query($conn, $query)) {
        log_activity($conn, "UPDATE", $action_text);
        $_SESSION['success'] = "সফলভাবে সম্পন্ন হয়েছে!";
    }
    header("Location: manage-staff-announcement.php"); exit;
}

// ৩. নোটিশ ডিলিট করার লজিক
if (isset($_GET['del_id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['del_id']);
    mysqli_query($conn, "DELETE FROM staff_announcements WHERE id = '$id'");
    log_activity($conn, "DELETE", "একটি স্টাফ নোটিশ মুছে ফেলা হয়েছে।");
    $_SESSION['success'] = "নোটিশটি সফলভাবে মুছে ফেলা হয়েছে!";
    header("Location: manage-staff-announcement.php"); exit;
}

// ৪. সব নোটিশ ডাটাবেজ থেকে আনা
$all_notices = mysqli_query($conn, "SELECT * FROM staff_announcements ORDER BY id DESC");

// ৫. যদি এডিট বাটন চাপ দেওয়া হয়
$edit_data = null;
if (isset($_GET['edit_id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['edit_id']);
    $edit_res = mysqli_query($conn, "SELECT * FROM staff_announcements WHERE id = '$id'");
    $edit_data = mysqli_fetch_assoc($edit_res);
}

include_once '../../includes/header.php';
?>

<div class="container py-5">
    <!-- হেডার বাটন -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-navy"><i class="fas fa-bullhorn me-2 text-danger"></i>স্টাফ নোটিশ বোর্ড</h3>
        <a href="dashboard.php" class="btn btn-outline-primary rounded-pill px-4 shadow-sm">
            <i class="fas fa-arrow-left me-1"></i> ড্যাশবোর্ড
        </a>
    </div>

    <div class="row g-4">
        <!-- বাম পাশ: অ্যাড/এডিট ফর্ম -->
        <div class="col-md-5">
            <div class="card border-0 shadow-lg rounded-4 p-4 sticky-top" style="top: 100px;">
                <h5 class="fw-bold text-navy mb-4 border-bottom pb-2">
                    <?php echo $edit_data ? "নোটিশ এডিট করুন" : "নতুন নোটিশ লিখুন"; ?>
                </h5>
                <form action="" method="POST">
                    <input type="hidden" name="msg_id" value="<?php echo $edit_data['id'] ?? ''; ?>">
                    
                    <div class="mb-3">
                        <label class="small fw-bold">শিরোনাম</label>
                        <input type="text" name="title" class="form-control rounded-3" value="<?php echo $edit_data['title'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="small fw-bold">বিস্তারিত মেসেজ</label>
                        <textarea name="message" class="form-control rounded-3" rows="5" required><?php echo $edit_data['message'] ?? ''; ?></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label class="small fw-bold">গুরুত্ব (Priority)</label>
                        <select name="priority" class="form-select rounded-3">
                            <option value="info" <?php if(($edit_data['priority'] ?? '') == 'info') echo 'selected'; ?>>সাধারণ (Blue)</option>
                            <option value="warning" <?php if(($edit_data['priority'] ?? '') == 'warning') echo 'selected'; ?>>সতর্কবার্তা (Yellow)</option>
                            <option value="danger" <?php if(($edit_data['priority'] ?? '') == 'danger') echo 'selected'; ?>>জরুরি (Red)</option>
                        </select>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" name="save_msg" class="btn btn-primary rounded-pill py-2 shadow fw-bold">
                            <?php echo $edit_data ? "পরিবর্তন সেভ করুন" : "নোটিশ পাবলিশ করুন"; ?>
                        </button>
                        <?php if($edit_data): ?>
                            <a href="manage-staff-announcement.php" class="btn btn-light rounded-pill btn-sm">বাতিল করুন</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- ডান পাশ: বর্তমান নোটিশ তালিকা -->
        <div class="col-md-7">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h6 class="fw-bold text-navy mb-4 border-bottom pb-2">চলমান নোটিশসমূহ</h6>
                <div class="table-responsive">
                    <table class="table table-hover align-middle small">
                        <thead>
                            <tr class="text-muted">
                                <th>নোটিশ</th>
                                <th>টাইপ</th>
                                <th class="text-end">অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($all_notices) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($all_notices)): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-navy"><?php echo $row['title']; ?></div>
                                        <div class="text-muted x-small"><?php echo date('d M, Y', strtotime($row['updated_at'])); ?></div>
                                    </td>
                                    <td>
                                        <?php 
                                        $p = $row['priority'];
                                        $color = ($p == 'info') ? 'primary' : (($p == 'warning') ? 'warning' : 'danger');
                                        echo "<span class='badge bg-$color rounded-pill px-2'>$p</span>";
                                        ?>
                                    </td>
                                    <td class="text-end">
                                        <!-- এডিট বাটন -->
                                        <a href="?edit_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary border-0 me-1"><i class="fas fa-edit"></i></a>
                                        <!-- ডিলিট বাটন -->
                                        <a href="?del_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger border-0" onclick="return confirm('আপনি কি নিশ্চিতভাবে এই নোটিশটি মুছে ফেলতে চান?')"><i class="fas fa-trash-alt"></i></a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center py-4 text-muted">কোনো নোটিশ পাওয়া যায়নি।</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.text-navy { color: var(--primary-navy); }
.x-small { font-size: 10px; }
.form-control:focus, .form-select:focus { border-color: var(--secondary-cyan); box-shadow: 0 0 10px rgba(42, 167, 229, 0.1); }
</style>

<?php include_once '../../includes/footer.php'; ?>