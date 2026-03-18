<?php
include_once '../../includes/header.php';

// রিসেপশন লগইন চেক
if (!isset($_SESSION['reception_id']) || $_SESSION['user_role'] != 'reception') {
    echo "<script>window.location.href='../auth/staff-login.php';</script>";
    exit;
}

// সকল ডাক্তারের তথ্য ডাটাবেজ থেকে আনা
$query = mysqli_query($conn, "SELECT * FROM doctors ORDER BY id DESC");
?>


<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-navy">ডাক্তার প্রোফাইল ম্যানেজমেন্ট</h3>
    <!-- ব্যাক বাটন -->
    <a href="dashboard.php" class="btn btn-outline-primary rounded-pill btn-sm px-3 shadow-sm">
        <i class="fas fa-arrow-left me-1"></i> ড্যাশবোর্ড
    </a>
</div>
    <!-- ডাক্তার তালিকা টেবিল -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive p-3">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">ডাক্তারের নাম</th>
                        <th>বিভাগ</th>
                        <th>ফি (৳)</th>
                        <th>রুম নং</th>
                        <th class="text-center">অ্যাকশন / ম্যানেজমেন্ট</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($query) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($query)): ?>
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center">
                                    <img src="../../assets/images/doctors/<?php echo $row['image']; ?>" 
                                         class="rounded-circle me-3 border border-2 border-light shadow-sm" 
                                         width="45" height="45" style="object-fit:cover;">
                                    <div>
                                        <span class="fw-bold d-block text-navy"><?php echo $row['name']; ?></span>
                                        <small class="text-muted"><?php echo $row['qualification']; ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if($row['specialization']): ?>
                                    <span class="badge bg-light text-primary rounded-pill border"><?php echo $row['specialization']; ?></span>
                                <?php else: ?>
                                    <span class="text-danger small italic">সেট করা নেই</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="fw-bold">৳ <?php echo $row['fee']; ?></span></td>
                            <td><span class="badge bg-secondary bg-opacity-10 text-secondary px-3"><?php echo $row['chamber_no'] ? $row['chamber_no'] : 'N/A'; ?></span></td>
                            <td class="text-center">
                                <!-- ১. তথ্য আপডেট বাটন -->
                                <a href="edit-doctor-profile.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 me-1 shadow-sm">
                                    <i class="fas fa-edit me-1"></i> তথ্য আপডেট
                                </a>
                                
                                <!-- ২. সময়সূচি ম্যানেজ বাটন (আপনার নতুন বাটন) -->
                                <a href="manage-schedules.php?doctor_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info rounded-pill px-3 text-white shadow-sm">
                                    <i class="fas fa-clock me-1"></i> সময়সূচি ম্যানেজ
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">কোনো ডাক্তার পাওয়া যায়নি।</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- নোট সেকশন -->
    <div class="mt-4">
        <div class="alert alert-warning border-0 shadow-sm rounded-4 small">
            <i class="fas fa-info-circle me-2"></i> 
            <b>টিপস:</b> ডাক্তার যদি আজ ছুটিতে থাকেন, তবে "সময়সূচি ম্যানেজ" লিঙ্কে গিয়ে আজকের দিনটি সাময়িকভাবে বন্ধ করে দিন। এতে রোগীরা সিরিয়াল বুক করতে পারবে না।
        </div>
    </div>
</div>

<style>
.text-navy { color: var(--primary-navy); }
.table thead th { border: none; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
.btn-info { background-color: var(--secondary-cyan) !important; border: none; }
.btn-info:hover { filter: brightness(0.9); }
</style>

<?php include_once '../../includes/footer.php'; ?>