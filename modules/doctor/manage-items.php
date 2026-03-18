<?php
include_once '../../config/database.php';
include_once '../../config/constants.php';
include_once '../../config/functions.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// ১. ডাক্তার লগইন চেক
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'doctor') {
    header("Location: ../auth/staff-login.php");
    exit;
}

// ২. নতুন আইটেম যোগ করার লজিক
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_item'])) {
    $type = $_POST['type']; 
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $table = ($type == 'medicine') ? 'medicines_list' : 'lab_tests_list';
    $col = ($type == 'medicine') ? 'medicine_name' : 'test_name';

    if (mysqli_query($conn, "INSERT INTO $table ($col) VALUES ('$name')")) {
        $_SESSION['success'] = "সফলভাবে তালিকায় যোগ করা হয়েছে!";
    }
    header("Location: manage-items.php"); exit;
}

// ৩. এডিট/আপডেট লজিক
if (isset($_POST['update_item'])) {
    $id = $_POST['edit_id'];
    $type = $_POST['edit_type'];
    $name = mysqli_real_escape_string($conn, $_POST['edit_name']);
    $table = ($type == 'medicine') ? 'medicines_list' : 'lab_tests_list';
    $col = ($type == 'medicine') ? 'medicine_name' : 'test_name';

    if (mysqli_query($conn, "UPDATE $table SET $col = '$name' WHERE id = '$id'")) {
        $_SESSION['success'] = "সফলভাবে তথ্য আপডেট করা হয়েছে!";
    }
    header("Location: manage-items.php"); exit;
}

// ৪. ডিলিট লজিক
if (isset($_GET['del_id']) && isset($_GET['type'])) {
    $id = $_GET['del_id'];
    $type = $_GET['type'];
    $table = ($type == 'medicine') ? 'medicines_list' : 'lab_tests_list';
    
    if (mysqli_query($conn, "DELETE FROM $table WHERE id = '$id'")) {
        $_SESSION['success'] = "আইটেমটি তালিকা থেকে মুছে ফেলা হয়েছে!";
    }
    header("Location: manage-items.php"); exit;
}

// ডাটা নিয়ে আসা
$meds = mysqli_query($conn, "SELECT * FROM medicines_list ORDER BY medicine_name ASC");
$tests = mysqli_query($conn, "SELECT * FROM lab_tests_list ORDER BY test_name ASC");

include_once '../../includes/header.php';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-navy"><i class="fas fa-edit me-2"></i>My Prescription Items</h3>
        <a href="dashboard.php" class="btn btn-outline-primary rounded-pill px-4">ড্যাশবোর্ড</a>
    </div>

    <div class="row g-4">
        <!-- বাম পাশ: নতুন যোগ করার ফরম -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top: 100px;">
                <h6 class="fw-bold mb-3 border-bottom pb-2">নতুন আইটেম যোগ করুন</h6>
                <form action="" method="POST">
                    <div class="mb-3">
                        <label class="small fw-bold">টাইপ নির্বাচন করুন</label>
                        <select name="type" class="form-select shadow-none" required>
                            <option value="medicine">ওষুধ (Medicine)</option>
                            <option value="test">ল্যাব টেস্ট (Lab Test)</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="small fw-bold">নাম (Item Name)</label>
                        <input type="text" name="name" class="form-control shadow-none" placeholder="যেমন: Napa 500mg" required>
                    </div>
                    <button type="submit" name="add_item" class="btn btn-primary w-100 rounded-pill py-2 shadow-sm fw-bold">তালিকায় যোগ করুন</button>
                </form>
            </div>
        </div>

        <!-- ডান পাশ: বর্তমান তালিকা -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <ul class="nav nav-tabs border-0 mb-3 bg-light p-1 rounded-pill" id="doctorTab">
                    <li class="nav-item w-50">
                        <button class="nav-link active w-100 rounded-pill fw-bold border-0" data-bs-toggle="tab" data-bs-target="#medTab">ওষুধের তালিকা</button>
                    </li>
                    <li class="nav-item w-50">
                        <button class="nav-link w-100 rounded-pill fw-bold border-0" data-bs-toggle="tab" data-bs-target="#testTab">ল্যাব টেস্ট</button>
                    </li>
                </ul>

                <div class="tab-content pt-2">
                    <!-- ওষুধ তালিকা -->
                    <div class="tab-pane fade show active" id="medTab">
                        <div class="table-responsive" style="max-height: 500px;">
                            <table class="table table-hover align-middle">
                                <tbody>
                                    <?php while($m = mysqli_fetch_assoc($meds)): ?>
                                    <tr>
                                        <td class="fw-bold text-navy small"><?php echo $m['medicine_name']; ?></td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-primary border-0" onclick="openEdit('medicine', '<?php echo $m['id']; ?>', '<?php echo $m['medicine_name']; ?>')"><i class="fas fa-edit"></i></button>
                                            <a href="?del_id=<?php echo $m['id']; ?>&type=medicine" class="btn btn-sm btn-outline-danger border-0" onclick="return confirm('মুছতে চান?')"><i class="fas fa-trash-alt"></i></a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- টেস্ট তালিকা -->
                    <div class="tab-pane fade" id="testTab">
                        <div class="table-responsive" style="max-height: 500px;">
                            <table class="table table-hover align-middle">
                                <tbody>
                                    <?php while($t = mysqli_fetch_assoc($tests)): ?>
                                    <tr>
                                        <td class="fw-bold text-danger small"><?php echo $t['test_name']; ?></td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-primary border-0" onclick="openEdit('test', '<?php echo $t['id']; ?>', '<?php echo $t['test_name']; ?>')"><i class="fas fa-edit"></i></button>
                                            <a href="?del_id=<?php echo $t['id']; ?>&type=test" class="btn btn-sm btn-outline-danger border-0" onclick="return confirm('মুছতে চান?')"><i class="fas fa-trash-alt"></i></a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- এডিট মডাল -->
<div class="modal fade" id="editItemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-navy text-white" style="background: var(--primary-navy);">
                <h5 class="modal-title">আইটেম পরিবর্তন করুন</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="edit_id" id="edit_id">
                    <input type="hidden" name="edit_type" id="edit_type">
                    <div class="mb-3">
                        <label class="small fw-bold">সঠিক নামটি লিখুন</label>
                        <input type="text" name="edit_name" id="edit_name" class="form-control rounded-3" required>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4">
                    <button type="submit" name="update_item" class="btn btn-primary w-100 rounded-pill py-2">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEdit(type, id, name) {
    document.getElementById('edit_type').value = type;
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_name').value = name;
    new bootstrap.Modal(document.getElementById('editItemModal')).show();
}
</script>

<?php include_once '../../includes/footer.php'; ?>