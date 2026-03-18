<?php


// manage-all-staff.php এর একদম উপরে
session_start();
if($_SESSION['user_role'] != 'admin') {
    $_SESSION['error'] = "আপনার এই পেজ দেখার অনুমতি নেই!";
    header("Location: dashboard.php");
    exit;
}






// ১. প্রয়োজনীয় ফাইল এবং সেশন চেক
include_once '../../config/database.php';
include_once '../../config/constants.php';
include_once '../../config/functions.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// এডমিন চেক
if (!isset($_SESSION['admin_id'])) { header("Location: ../auth/staff-login.php"); exit; }

// ২. স্টাফ তথ্য আপডেট লজিক
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_staff'])) {
    $id = mysqli_real_escape_string($conn, $_POST['edit_id']);
    $role = $_POST['edit_role'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $nid = mysqli_real_escape_string($conn, $_POST['nid']);
    $password = $_POST['new_password'];

    $table = ($role == 'Doctor') ? 'doctors' : 'receptionists';
    
    // বেসিক আপডেট কুয়েরি
    $update_sql = "UPDATE $table SET name='$name', username='$username', phone='$phone', nid='$nid'";
    
    // যদি নতুন পাসওয়ার্ড দেওয়া হয়
    if (!empty($password)) {
        $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
        $update_sql .= ", password='$hashed_pass'";
    }
    
    $update_sql .= " WHERE id='$id'";

    if (mysqli_query($conn, $update_sql)) {
        log_activity($conn, "UPDATE", "$role মেম্বার ($name) এর তথ্য আপডেট করা হয়েছে।");
        $_SESSION['success'] = "তথ্য সফলভাবে আপডেট করা হয়েছে!";
    }
    header("Location: manage-all-staff.php");
    exit;
}

// ৩. স্ট্যাটাস পরিবর্তন লজিক
if (isset($_GET['toggle_status'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $role = $_GET['role'];
    $new_status = ($_GET['toggle_status'] == 'active') ? 'inactive' : 'active';
    $table = ($role == 'Doctor') ? 'doctors' : 'receptionists';
    mysqli_query($conn, "UPDATE $table SET status = '$new_status' WHERE id = '$id'");
    header("Location: manage-all-staff.php"); exit;
}

// ৪. ডিলিট লজিক
if (isset($_GET['delete_id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    $table = ($_GET['role'] == 'Doctor') ? 'doctors' : 'receptionists';
    mysqli_query($conn, "DELETE FROM $table WHERE id = '$id'");
    header("Location: manage-all-staff.php"); exit;
}

// ডাটা আনা
$staff_query = mysqli_query($conn, "SELECT id, name, username, nid, phone, status, 'Receptionist' as role FROM receptionists");
$doctor_query = mysqli_query($conn, "SELECT id, name, username, nid, phone, status, 'Doctor' as role FROM doctors");

include_once '../../includes/header.php';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-navy">স্টাফ ও মেম্বার ম্যানেজমেন্ট</h3>
        <div class="d-flex gap-2">
            <a href="dashboard.php" class="btn btn-outline-primary rounded-pill btn-sm px-4 shadow-sm">ড্যাশবোর্ড</a>
            <a href="add-staff-unified.php" class="btn btn-primary rounded-pill btn-sm px-4 shadow-sm">+ নতুন স্টাফ</a>
        </div>
    </div>

    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
        <div class="table-responsive p-3">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3">নাম ও পদবি</th>
                        <th>ইউজারনেম</th>
                        <th>মোবাইল</th>
                        <th>অবস্থা</th>
                        <th class="text-center">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    while ($row = mysqli_fetch_assoc($staff_query)) { displayRow($row); }
                    while ($row = mysqli_fetch_assoc($doctor_query)) { displayRow($row); }

                    function displayRow($row) {
                        $status_btn = ($row['status'] == 'active') ? 'btn-success' : 'btn-secondary';
                        echo "<tr>
                            <td class='ps-3'>
                                <div class='fw-bold text-navy'>{$row['name']}</div>
                                <span class='badge bg-light text-dark border x-small rounded-pill'>{$row['role']}</span>
                            </td>
                            <td><code>{$row['username']}</code></td>
                            <td>{$row['phone']}</td>
                            <td>
                                <a href='?id={$row['id']}&role={$row['role']}&toggle_status={$row['status']}' class='btn btn-sm $status_btn rounded-pill px-3 py-1' style='font-size:10px;'>{$row['status']}</a>
                            </td>
                            <td class='text-center'>
                                <!-- এডিট বাটন -->
                                <button class='btn btn-sm btn-outline-primary rounded-circle me-1' 
                                        onclick=\"openEditModal('{$row['id']}', '{$row['role']}', '{$row['name']}', '{$row['username']}', '{$row['phone']}', '{$row['nid']}')\">
                                    <i class='fas fa-edit'></i>
                                </button>
                                <!-- ডিলিট বাটন -->
                                <a href='?delete_id={$row['id']}&role={$row['role']}' class='btn btn-sm btn-outline-danger rounded-circle' onclick=\"return confirm('মুছে ফেলবেন?')\"><i class='fas fa-trash-alt'></i></a>
                            </td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- এডিট মডাল -->
<div class="modal fade" id="editStaffModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-navy text-white" style="background:var(--primary-navy)">
                <h5 class="modal-title">স্টাফ তথ্য এডিট করুন</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="edit_id" id="edit_id">
                    <input type="hidden" name="edit_role" id="edit_role">
                    
                    <div class="mb-3">
                        <label class="small fw-bold">নাম</label>
                        <input type="text" name="name" id="edit_name" class="form-control rounded-3" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="small fw-bold">ইউজারনেম</label>
                            <input type="text" name="username" id="edit_username" class="form-control rounded-3" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="small fw-bold">মোবাইল</label>
                            <input type="text" name="phone" id="edit_phone" class="form-control rounded-3" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold">এনআইডি (NID)</label>
                        <input type="text" name="nid" id="edit_nid" class="form-control rounded-3" required>
                    </div>
                    
                    <!-- পাসওয়ার্ড রিসেট অংশ -->
                    <div class="mb-3 bg-light p-3 rounded-3 border">
                        <label class="small fw-bold text-danger">পাসওয়ার্ড পরিবর্তন (প্রয়োজন হলে লিখুন)</label>
                        <div class="input-group">
                            <input type="password" name="new_password" id="new_password" class="form-control" placeholder="নতুন পাসওয়ার্ড">
                            <span class="input-group-text cursor-pointer" onclick="togglePassword()"><i class="fas fa-eye" id="eyeIcon"></i></span>
                        </div>
                        <small class="text-muted" style="font-size: 10px;">* পাসওয়ার্ড পরিবর্তন না করতে চাইলে খালি রাখুন।</small>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4">
                    <button type="submit" name="update_staff" class="btn btn-primary w-100 rounded-pill py-2 shadow-sm fw-bold">পরিবর্তন সেভ করুন</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditModal(id, role, name, user, phone, nid) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_role').value = role;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_username').value = user;
    document.getElementById('edit_phone').value = phone;
    document.getElementById('edit_nid').value = nid;
    document.getElementById('new_password').value = '';
    new bootstrap.Modal(document.getElementById('editStaffModal')).show();
}

function togglePassword() {
    const pw = document.getElementById('new_password');
    const icon = document.getElementById('eyeIcon');
    if (pw.type === "password") {
        pw.type = "text";
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        pw.type = "password";
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>

<style>
    .text-navy { color: var(--primary-navy); }
    .x-small { font-size: 10px; }
    .cursor-pointer { cursor: pointer; }
</style>

<?php include_once '../../includes/footer.php'; ?>