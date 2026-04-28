<?php
/**
 * ১. সিকিউরিটি এবং সেশন চেক (সবার উপরে - কোনো স্পেস বা HTML এর আগে)
 */
session_start();
include_once '../../config/database.php';
include_once '../../config/constants.php';
include_once '../../config/functions.php';

// লগইন এবং এডমিন রোল চেক (অ্যাডমিন ছাড়া কেউ এই পেজ দেখতে পারবে না)
if (!isset($_SESSION['admin_id']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error'] = "আপনার এই পেজ দেখার অনুমতি নেই! এটি শুধুমাত্র মালিকের জন্য সংরক্ষিত।";
    header("Location: dashboard.php");
    exit;
}

/**
 * ২. অপারেশন লজিক (Update, Toggle Status, Delete)
 */

// ২.১ স্টাফ তথ্য আপডেট লজিক
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
        log_activity($conn, "STAFF_UPDATE", "$role মেম্বার ($name) এর তথ্য আপডেট করা হয়েছে।");
        $_SESSION['success'] = "স্টাফের তথ্য সফলভাবে আপডেট করা হয়েছে!";
    }
    header("Location: manage-all-staff.php");
    exit;
}

// ২.২ স্ট্যাটাস পরিবর্তন লজিক (Active/Inactive)
if (isset($_GET['toggle_status'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $role = $_GET['role'];
    $new_status = ($_GET['toggle_status'] == 'active') ? 'inactive' : 'active';
    $table = ($role == 'Doctor') ? 'doctors' : 'receptionists';
    
    if (mysqli_query($conn, "UPDATE $table SET status = '$new_status' WHERE id = '$id'")) {
        $_SESSION['success'] = "স্ট্যাটাস সফলভাবে পরিবর্তন করা হয়েছে।";
    }
    header("Location: manage-all-staff.php");
    exit;
}

// ২.৩ ডিলিট লজিক
if (isset($_GET['delete_id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    $role = $_GET['role'];
    $table = ($role == 'Doctor') ? 'doctors' : 'receptionists';
    
    if (mysqli_query($conn, "DELETE FROM $table WHERE id = '$id'")) {
        log_activity($conn, "STAFF_DELETE", "একটি $role প্রোফাইল মুছে ফেলা হয়েছে।");
        $_SESSION['success'] = "স্টাফ প্রোফাইলটি স্থায়ীভাবে মুছে ফেলা হয়েছে!";
    }
    header("Location: manage-all-staff.php");
    exit;
}

/**
 * ৩. ডাটা কালেকশন
 */
$staff_query = mysqli_query($conn, "SELECT id, name, username, nid, phone, status, 'Receptionist' as role FROM receptionists");
$doctor_query = mysqli_query($conn, "SELECT id, name, username, nid, phone, status, 'Doctor' as role FROM doctors");

include_once '../../includes/header.php';
?>

<style>
    :root { --navy: #0A2647; --cyan: #2AA7E5; }
    body { background-color: #f1f5f9; }
    .text-navy { color: var(--navy); }
    .bg-navy { background-color: var(--navy) !important; }
    .btn-cyan { background-color: var(--cyan); color: white; border: none; }
    .btn-cyan:hover { background-color: #2391c7; color: white; }
    .staff-card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    .badge-role { font-size: 10px; text-transform: uppercase; padding: 4px 10px; border-radius: 50px; font-weight: bold; }
</style>

<div class="container py-4">
    
    <!-- হেডার সেকশন -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-navy mb-0">স্টাফ ম্যানেজার (Admin Only)</h3>
            <p class="text-muted small">হাসপাতালের সব ডাক্তার ও রিসেপশন স্টাফদের তালিকা এখানে দেখা যাবে।</p>
        </div>
        <div class="d-flex gap-2">
            <a href="dashboard.php" class="btn btn-outline-navy rounded-pill px-4 shadow-sm fw-bold">ড্যাশবোর্ড</a>
            <a href="add-staff-unified.php" class="btn btn-navy rounded-pill px-4 shadow-sm text-white fw-bold">+ নতুন নিয়োগ</a>
        </div>
    </div>

    <!-- সাকসেস মেসেজ -->
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3">
            <i class="fas fa-check-circle me-1"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card staff-card overflow-hidden">
        <div class="card-header bg-navy text-white p-3">
            <h5 class="mb-0 fw-bold"><i class="fas fa-users-cog me-2"></i>বর্তমান সকল মেম্বার তালিকা</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-navy fw-bold">
                        <tr>
                            <th class="ps-4">নাম ও পদবি</th>
                            <th>ইউজারনেম</th>
                            <th>মোবাইল নম্বর</th>
                            <th>এনআইডি (NID)</th>
                            <th>অবস্থা (Status)</th>
                            <th class="text-center pe-4">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // ডাটা শো করার জন্য কমন ফাংশন
                        function renderRows($query) {
                            global $conn;
                            while ($row = mysqli_fetch_assoc($query)) {
                                $role_color = ($row['role'] == 'Doctor') ? 'bg-primary' : 'bg-info';
                                $status_btn = ($row['status'] == 'active') ? 'btn-success' : 'btn-secondary';
                                $status_icon = ($row['status'] == 'active') ? 'fa-toggle-on' : 'fa-toggle-off';
                                
                                echo "<tr>
                                    <td class='ps-4'>
                                        <div class='fw-bold text-dark'>{$row['name']}</div>
                                        <span class='badge $role_color badge-role'>{$row['role']}</span>
                                    </td>
                                    <td><code class='text-primary fw-bold'>{$row['username']}</code></td>
                                    <td>{$row['phone']}</td>
                                    <td>{$row['nid']}</td>
                                    <td>
                                        <a href='?id={$row['id']}&role={$row['role']}&toggle_status={$row['status']}' 
                                           class='btn btn-sm $status_btn rounded-pill px-3 py-1' 
                                           style='font-size:11px;' onclick=\"return confirm('স্ট্যাটাস পরিবর্তন করতে চান?')\">
                                            <i class='fas $status_icon me-1'></i> {$row['status']}
                                        </a>
                                    </td>
                                    <td class='text-center pe-4'>
                                        <button class='btn btn-sm btn-outline-primary rounded-pill me-1 px-3' 
                                                onclick=\"openEditModal('{$row['id']}', '{$row['role']}', '".addslashes($row['name'])."', '{$row['username']}', '{$row['phone']}', '{$row['nid']}')\">
                                            <i class='fas fa-edit me-1'></i>এডিট
                                        </button>
                                        <a href='?delete_id={$row['id']}&role={$row['role']}' 
                                           class='btn btn-sm btn-outline-danger rounded-pill px-3' 
                                           onclick=\"return confirm('আপনি কি নিশ্চিত যে এই প্রোফাইলটি চিরতরে মুছে ফেলতে চান?')\">
                                            <i class='fas fa-trash-alt me-1'></i>মুছুন
                                        </a>
                                    </td>
                                </tr>";
                            }
                        }

                        renderRows($doctor_query);
                        renderRows($staff_query);
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- এডিট মডাল -->
<div class="modal fade" id="editStaffModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-navy text-white py-3">
                <h5 class="modal-title fw-bold">প্রোফাইল সংশোধন করুন</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="edit_id" id="edit_id">
                    <input type="hidden" name="edit_role" id="edit_role">
                    
                    <div class="mb-3 text-center">
                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold" id="role_label"></span>
                    </div>

                    <div class="mb-3">
                        <label class="small fw-bold">পূর্ণ নাম</label>
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
                        <label class="small fw-bold">এনআইডি নম্বর (NID)</label>
                        <input type="text" name="nid" id="edit_nid" class="form-control rounded-3" required>
                    </div>
                    
                    <div class="p-3 bg-light rounded-3 border border-warning border-opacity-50">
                        <label class="small fw-bold text-danger"><i class="fas fa-key me-1"></i>পাসওয়ার্ড পরিবর্তন (প্রয়োজন হলে লিখুন)</label>
                        <div class="input-group">
                            <input type="password" name="new_password" id="new_password" class="form-control" placeholder="নতুন পাসওয়ার্ড">
                            <span class="input-group-text cursor-pointer" onclick="togglePassword()"><i class="fas fa-eye" id="eyeIcon"></i></span>
                        </div>
                        <small class="text-muted" style="font-size: 10px;">* পাসওয়ার্ড পরিবর্তন করতে না চাইলে ঘরটি খালি রাখুন।</small>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4">
                    <button type="submit" name="update_staff" class="btn btn-navy w-100 rounded-pill py-2 shadow-sm fw-bold text-white">পরিবর্তন সেভ করুন</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditModal(id, role, name, user, phone, nid) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_role').value = role;
    document.getElementById('role_label').innerText = "পদবি: " + role;
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

<?php include_once '../../includes/footer.php'; ?>