<?php
/**
 * ১. লজিক সেকশন (সবার আগে - কোনো স্পেস বা HTML এর আগে)
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../../config/database.php';

// ১.১ নতুন টেস্ট সেভ করার লজিক
if (isset($_POST['add_test'])) {
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $test_name = mysqli_real_escape_string($conn, $_POST['test_name']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);

    $sql = "INSERT INTO lab_tests (category, test_name, price) VALUES ('$category', '$test_name', '$price')";
    if (mysqli_query($conn, $sql)) {
        header("Location: manage-lab-tests.php?msg=added");
        exit();
    }
}

// ১.২ টেস্ট আপডেট করার লজিক
if (isset($_POST['update_test'])) {
    $id = $_POST['test_id'];
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $test_name = mysqli_real_escape_string($conn, $_POST['test_name']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);

    $sql = "UPDATE lab_tests SET category='$category', test_name='$test_name', price='$price' WHERE id='$id'";
    if (mysqli_query($conn, $sql)) {
        header("Location: manage-lab-tests.php?msg=updated");
        exit();
    }
}

// ১.৩ টেস্ট ডিলিট করার লজিক
if (isset($_GET['delete_id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM lab_tests WHERE id = '$id'");
    header("Location: manage-lab-tests.php?msg=deleted");
    exit();
}

/** 
 * ২. ডাটা সংগ্রহ 
 */
$all_tests = mysqli_query($conn, "SELECT * FROM lab_tests ORDER BY category ASC, test_name ASC");

include_once '../../includes/header.php';
?>

<style>
    :root { --navy: #0A2647; --cyan: #2AA7E5; }
    body { background-color: #f4f7f6; }
    .text-navy { color: var(--navy); }
    .btn-outline-navy { border: 2px solid var(--navy); color: var(--navy); font-weight: bold; }
    .btn-outline-navy:hover { background: var(--navy); color: white; }
    .report-card { background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: none; }
    .table-responsive { max-height: 70vh; overflow-y: auto; }
</style>

<div class="container py-4">
    
    <!-- হেডার বাটন -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-navy mb-0"><i class="fas fa-vial me-2 text-cyan"></i>ল্যাব টেস্ট ম্যানেজমেন্ট</h3>
        <a href="manage-accounts.php" class="btn btn-outline-navy rounded-pill px-4 shadow-sm">
            <i class="fas fa-calculator me-2"></i> হিসাব খাতায় ফিরে যান
        </a>
    </div>

    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0 mb-4">
            <i class="fas fa-check-circle me-1"></i> অপারেশন সফল হয়েছে!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- বাম পাশ: নতুন টেস্ট যোগ করার ফর্ম -->
        <div class="col-md-4">
            <div class="card report-card p-4 border-top border-5 border-primary">
                <h5 class="fw-bold text-navy mb-3"><i class="fas fa-plus-circle me-2 text-primary"></i>নতুন টেস্ট যোগ করুন</h5>
                <form action="" method="POST">
                    <div class="mb-3">
                        <label class="small fw-bold">টেস্টের বিভাগ (Category)</label>
                        <select name="category" class="form-select shadow-none" required>
                            <option value="HEMATOLOGY">HEMATOLOGY</option>
                            <option value="BIOCHEMISTRY">BIOCHEMISTRY</option>
                            <option value="SEROLOGY">SEROLOGY</option>
                            <option value="DIGITAL X-RAY">DIGITAL X-RAY</option>
                            <option value="ULTRASONOGRAPHY">ULTRASONOGRAPHY</option>
                            <option value="HORMONE & ELISA">HORMONE & ELISA</option>
                            <option value="MICROBIOLOGY">MICROBIOLOGY</option>
                            <option value="OTHER">OTHER</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold">টেস্টের নাম (Test Name)</label>
                        <input type="text" name="test_name" class="form-control shadow-none" placeholder="যেমন: CBC, HIV" required>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold">মূল্য (Price ৳)</label>
                        <input type="number" name="price" class="form-control shadow-none fw-bold" placeholder="0.00" required>
                    </div>
                    <button type="submit" name="add_test" class="btn btn-primary w-100 rounded-pill fw-bold shadow">Save Test</button>
                </form>
            </div>
        </div>

        <!-- ডান পাশ: বর্তমান টেস্টের তালিকা -->
        <div class="col-md-8">
            <div class="card report-card overflow-hidden">
                <div class="card-header bg-navy text-white py-3 d-flex justify-content-between align-items-center" style="background:#0A2647">
                    <h5 class="mb-0 fw-bold">বর্তমান টেস্টের তালিকা</h5>
                    <input type="text" id="testSearch" class="form-control form-control-sm w-50 rounded-pill shadow-none" placeholder="খুঁজুন...">
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="testTable">
                            <thead class="table-light">
                                <tr>
                                    <th>টেস্টের নাম</th>
                                    <th>বিভাগ</th>
                                    <th>মূল্য</th>
                                    <th class="text-center">অ্যাকশন</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($all_tests)): ?>
                                <tr>
                                    <td class="fw-bold text-navy"><?= $row['test_name']; ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= $row['category']; ?></span></td>
                                    <td class="fw-bold">৳ <?= number_format($row['price'], 0); ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-info text-white me-1" onclick="openEditModal('<?= $row['id']; ?>', '<?= $row['test_name']; ?>', '<?= $row['category']; ?>', '<?= $row['price']; ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="?delete_id=<?= $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('আপনি কি নিশ্চিত?')">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
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

<!-- এডিট মডাল -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="" method="POST" class="modal-content border-0 shadow rounded-4">
            <div class="modal-header bg-navy text-white" style="background:#0A2647">
                <h5 class="modal-title">টেস্ট সংশোধন করুন</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="test_id" id="edit_id">
                <div class="mb-3">
                    <label class="small fw-bold">বিভাগ</label>
                    <select name="category" id="edit_cat" class="form-select shadow-none">
                        <option value="HEMATOLOGY">HEMATOLOGY</option>
                        <option value="BIOCHEMISTRY">BIOCHEMISTRY</option>
                        <option value="SEROLOGY">SEROLOGY</option>
                        <option value="DIGITAL X-RAY">DIGITAL X-RAY</option>
                        <option value="ULTRASONOGRAPHY">ULTRASONOGRAPHY</option>
                        <option value="HORMONE & ELISA">HORMONE & ELISA</option>
                        <option value="MICROBIOLOGY">MICROBIOLOGY</option>
                        <option value="OTHER">OTHER</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="small fw-bold">টেস্টের নাম</label>
                    <input type="text" name="test_name" id="edit_name" class="form-control shadow-none" required>
                </div>
                <div class="mb-0">
                    <label class="small fw-bold">মূল্য (৳)</label>
                    <input type="number" name="price" id="edit_price" class="form-control shadow-none fw-bold" required>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="submit" name="update_test" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow">Update Test Data</button>
            </div>
        </form>
    </div>
</div>

<script>
// সার্চ ফিল্টার
document.getElementById('testSearch').addEventListener('keyup', function() {
    let filter = this.value.toUpperCase();
    let rows = document.querySelector("#testTable tbody").rows;
    for (let i = 0; i < rows.length; i++) {
        let firstCol = rows[i].cells[0].textContent.toUpperCase();
        let secondCol = rows[i].cells[1].textContent.toUpperCase();
        if (firstCol.indexOf(filter) > -1 || secondCol.indexOf(filter) > -1) {
            rows[i].style.display = "";
        } else {
            rows[i].style.display = "none";
        }      
    }
});

// এডিট মডাল ওপেন করা
function openEditModal(id, name, cat, price) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_cat').value = cat;
    document.getElementById('edit_price').value = price;
    new bootstrap.Modal(document.getElementById('editModal')).show();
}
</script>

<?php include_once '../../includes/footer.php'; ?>