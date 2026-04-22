<?php
session_start();
include_once '../../config/database.php';
include_once '../../includes/header.php';

// ১. নতুন টেস্ট সেভ করার লজিক
if (isset($_POST['add_test'])) {
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $test_name = mysqli_real_escape_string($conn, $_POST['test_name']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);

    $sql = "INSERT INTO lab_tests (category, test_name, price) VALUES ('$category', '$test_name', '$price')";
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('নতুন টেস্ট সফলভাবে যোগ হয়েছে!'); window.location.href='manage-lab-tests.php';</script>";
    }
}

// ২. টেস্ট ডিলিট করার লজিক
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    mysqli_query($conn, "DELETE FROM lab_tests WHERE id = $id");
    header("Location: manage-lab-tests.php");
}

$all_tests = mysqli_query($conn, "SELECT * FROM lab_tests ORDER BY id DESC");
?>

<div class="container py-4">
    <div class="row g-4">
        <!-- বাম পাশ: নতুন টেস্ট যোগ করার ফর্ম -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 p-4">
                <h5 class="fw-bold text-navy mb-3"><i class="fas fa-plus-circle me-2"></i>নতুন টেস্ট যোগ করুন</h5>
                <form action="" method="POST">
                    <div class="mb-3">
                        <label class="small fw-bold">টেস্টের বিভাগ (Category)</label>
                        <select name="category" class="form-select" required>
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
                        <input type="text" name="test_name" class="form-control" placeholder="যেমন: CBC, HIV" required>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold">মূল্য (Price ৳)</label>
                        <input type="number" name="price" class="form-control" placeholder="0.00" required>
                    </div>
                    <button type="submit" name="add_test" class="btn btn-primary w-100 rounded-pill fw-bold">সেভ করুন</button>
                </form>
            </div>
        </div>

        <!-- ডান পাশ: বর্তমান টেস্টের তালিকা -->
        <div class="col-md-8">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-navy text-white py-3">
                    <h5 class="mb-0 fw-bold">বর্তমান টেস্টের তালিকা</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
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
                                        <a href="?delete_id=<?= $row['id']; ?>" class="text-danger" onclick="return confirm('আপনি কি নিশ্চিত যে এই টেস্টটি ডিলিট করতে চান?')">
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

<?php include_once '../../includes/footer.php'; ?>