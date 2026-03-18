<?php
// ১. প্রয়োজনীয় ফাইল এবং সেশন চেক
include_once '../../config/database.php';
include_once '../../config/constants.php';
include_once '../../config/functions.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['reception_id']) || $_SESSION['user_role'] != 'reception') {
    header("Location: ../auth/staff-login.php");
    exit;
}

// ২. নতুন টিপস যোগ করার লজিক
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_tip'])) {
    $doc_id = mysqli_real_escape_string($conn, $_POST['doctor_id']);
    $tip_text = mysqli_real_escape_string($conn, $_POST['tip_text']);
    
    if (mysqli_query($conn, "INSERT INTO doctor_tips (doctor_id, tip_text) VALUES ('$doc_id', '$tip_text')")) {
        $_SESSION['success'] = "নতুন পরামর্শ সফলভাবে যুক্ত হয়েছে!";
        header("Location: dashboard.php"); exit;
    }
}

// ৩. টিপস আপডেট (Edit) করার লজিক
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_tip'])) {
    $id = mysqli_real_escape_string($conn, $_POST['edit_id']);
    $doc_id = mysqli_real_escape_string($conn, $_POST['doctor_id']);
    $tip_text = mysqli_real_escape_string($conn, $_POST['tip_text']);
    
    if (mysqli_query($conn, "UPDATE doctor_tips SET doctor_id='$doc_id', tip_text='$tip_text' WHERE id='$id'")) {
        $_SESSION['success'] = "পরামর্শটি সফলভাবে আপডেট করা হয়েছে!";
        header("Location: manage-tips.php"); exit;
    }
}

// ৪. টিপস ডিলিট করার লজিক
if (isset($_GET['del_id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['del_id']);
    mysqli_query($conn, "DELETE FROM doctor_tips WHERE id = '$id'");
    $_SESSION['success'] = "পরামর্শটি মুছে ফেলা হয়েছে!";
    header("Location: manage-tips.php"); exit;
}

// ৫. ডাটাবেজ থেকে তথ্য আনা
$tips_query = mysqli_query($conn, "SELECT t.*, d.name as doc_name, d.image as doc_img FROM doctor_tips t JOIN doctors d ON t.doctor_id = d.id ORDER BY t.id DESC");

include_once '../../includes/header.php';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-navy mb-1"><i class="fas fa-list-ul me-2 text-info"></i>টিপস ম্যানেজমেন্ট</h3>
            <p class="text-muted small mb-0">পরামর্শগুলো পয়েন্ট আকারে লিখতে প্রতিটি লাইনের পর এন্টার (Enter) চাপুন</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-primary rounded-pill btn-sm px-4">ড্যাশবোর্ড</a>
    </div>

    <div class="row g-4">
        <!-- বাম পাশ: নতুন পরামর্শ যোগ -->
        <div class="col-md-5">
            <div class="card border-0 shadow-lg rounded-4 p-4 bg-white">
                <h5 class="fw-bold text-navy mb-4 border-bottom pb-2">নতুন পরামর্শ লিখুন</h5>
                <form action="" method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">ডাক্তার নির্বাচন করুন</label>
                        <select name="doctor_id" class="form-select rounded-3" required>
                            <option value="">সিলেক্ট করুন</option>
                            <?php 
                            $docs = mysqli_query($conn, "SELECT id, name FROM doctors WHERE status='active'");
                            while($d = mysqli_fetch_assoc($docs)) echo "<option value='{$d['id']}'>{$d['name']}</option>";
                            ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold">পরামর্শের বর্ণনা (পয়েন্ট আকারে লিখুন)</label>
                        <textarea name="tip_text" class="form-control rounded-3 bg-light" rows="6" placeholder="১. প্রতিদিন সকালে হাঁটুন&#10;২. প্রচুর পানি পান করুন..." required></textarea>
                    </div>
                    <button type="submit" name="add_tip" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow">পাবলিশ করুন</button>
                </form>
            </div>
        </div>

        <!-- ডান পাশ: বর্তমান তালিকা -->
        <div class="col-md-7">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <h6 class="fw-bold text-navy mb-4 border-bottom pb-2">বর্তমান পরামর্শসমূহ</h6>
                <div class="table-responsive">
                    <table class="table table-hover align-middle small">
                        <thead>
                            <tr class="text-muted">
                                <th>ডাক্তার</th>
                                <th>পরামর্শের প্রিভিউ</th>
                                <th class="text-end">অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($tips_query)): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="../../assets/images/doctors/<?php echo $row['doc_img']; ?>" class="rounded-circle me-2 border" width="30" height="30" style="object-fit: cover;">
                                        <span class="fw-bold text-navy"><?php echo $row['doc_name']; ?></span>
                                    </div>
                                </td>
                                <td class="text-muted">
                                    <div class="tip-preview">
                                        <?php echo nl2br(substr($row['tip_text'], 0, 60)); ?>...
                                    </div>
                                </td>
                                <td class="text-end">
                                    <!-- এডিট বাটন -->
                                    <button class="btn btn-sm btn-outline-primary border-0 rounded-circle me-1" 
                                            onclick="openEditModal('<?php echo $row['id']; ?>', '<?php echo $row['doctor_id']; ?>', '<?php echo htmlspecialchars(addslashes($row['tip_text'])); ?>')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <!-- ডিলিট বাটন -->
                                    <a href="?del_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger border-0 rounded-circle" onclick="return confirm('মুছে ফেলবেন?')">
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

<!-- এডিট করার পপ-আপ (Modal) -->
<div class="modal fade" id="editTipModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-navy text-white" style="background:var(--primary-navy)">
                <h5 class="modal-title">পরামর্শ সংশোধন করুন</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="edit_id" id="edit_id">
                    <div class="mb-3">
                        <label class="small fw-bold">ডাক্তার</label>
                        <select name="doctor_id" id="edit_doctor_id" class="form-select rounded-3" required>
                            <?php 
                            $docs2 = mysqli_query($conn, "SELECT id, name FROM doctors WHERE status='active'");
                            while($d2 = mysqli_fetch_assoc($docs2)) echo "<option value='{$d2['id']}'>{$d2['name']}</option>";
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold">পরামর্শের বর্ণনা</label>
                        <textarea name="tip_text" id="edit_tip_text" class="form-control rounded-3" rows="6" required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="submit" name="update_tip" class="btn btn-primary w-100 rounded-pill py-2 shadow-sm fw-bold">পরিবর্তন সেভ করুন</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditModal(id, docId, text) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_doctor_id').value = docId;
    document.getElementById('edit_tip_text').value = text;
    new bootstrap.Modal(document.getElementById('editTipModal')).show();
}
</script>

<style>
.text-navy { color: var(--primary-navy); }
.tip-preview { font-size: 11px; max-width: 200px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
</style>

<?php include_once '../../includes/footer.php'; ?>