<?php
// ১. হেডার ইনক্লুড
include_once '../../includes/header.php';

// ২. সার্চ এবং ফিল্টার লজিক
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$specialization = isset($_GET['specialization']) ? mysqli_real_escape_string($conn, $_GET['specialization']) : '';

// ৩. কুয়েরি তৈরি (Status চেক সরিয়ে দিয়ে দেখতে পারেন সব আসে কি না, তবে active রাখা ভালো)
$query = "SELECT * FROM doctors WHERE status = 'active'";

if (!empty($search)) {
    $query .= " AND (name LIKE '%$search%' OR qualification LIKE '%$search%' OR specialization LIKE '%$search%')";
}

if (!empty($specialization)) {
    $query .= " AND specialization = '$specialization'";
}

$query .= " ORDER BY name ASC";
$result = mysqli_query($conn, $query);
?>

<div class="container-fluid bg-light py-5">
    <div class="container">
        <!-- পেজ হেডার -->
        <div class="row mb-5 align-items-center">
            <div class="col-md-8 text-center text-md-start">
                <h2 class="fw-bold text-navy">আমাদের বিশেষজ্ঞ ডাক্তারগণ</h2>
                <p class="text-muted">আপনার সেবায় নিয়োজিত আমাদের অভিজ্ঞ চিকিৎসকবৃন্দ।</p>
            </div>
            <div class="col-md-4 text-center text-md-end">
                <span class="badge bg-white text-primary shadow-sm p-3 rounded-pill border border-primary">
                    <i class="fas fa-user-md me-2"></i><?php echo mysqli_num_rows($result); ?> জন ডাক্তার পাওয়া গেছে
                </span>
            </div>
        </div>

        <!-- ফিল্টার সেকশন -->
        <div class="card border-0 shadow-sm mb-5 p-4 rounded-4">
            <form action="" method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small fw-bold">সার্চ করুন</label>
                    <input type="text" name="search" class="form-control" placeholder="নাম বা বিভাগ লিখে খুঁজুন..." value="<?php echo $search; ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">বিভাগ</label>
                    <select name="specialization" class="form-select">
                        <option value="">সকল বিভাগ</option>
                        <?php
                        $spec_res = mysqli_query($conn, "SELECT DISTINCT specialization FROM doctors WHERE specialization != ''");
                        while($s = mysqli_fetch_assoc($spec_res)) {
                            $sel = ($specialization == $s['specialization']) ? 'selected' : '';
                            echo "<option value='".$s['specialization']."' $sel>".$s['specialization']."</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3 d-grid">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill">ফিল্টার করুন</button>
                </div>
            </form>
        </div>

        <!-- ডাক্তার লিস্ট গ্রিড -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($doctor = mysqli_fetch_assoc($result)): ?>
                    <div class="col">
                        <div class="card h-100 border-0 shadow-sm doctor-card rounded-4 overflow-hidden">
                            <div class="text-center pt-4" style="background: var(--light-bg);">
                                <?php 
                                    // ইমেজের সঠিক পাথ
                                    $doc_img = !empty($doctor['image']) ? $doctor['image'] : 'default-doctor.jpg';
                                    $img_url = BASE_URL . "assets/images/doctors/" . $doc_img;
                                ?>
                                <img src="<?php echo $img_url; ?>" class="rounded-circle shadow-sm border border-4 border-white" width="120" height="120" style="object-fit: cover;">
                            </div>

                            <div class="card-body text-center mt-2">
                                <span class="badge bg-white text-primary border rounded-pill mb-2 px-3 py-2">
                                    <i class="fas fa-stethoscope me-1"></i> <?php echo $doctor['specialization']; ?>
                                </span>
                                <h5 class="fw-bold text-navy mb-1"><?php echo $doctor['name']; ?></h5>
                                <p class="small text-muted mb-3"><?php echo $doctor['qualification']; ?></p>
                                
                                <div class="d-flex justify-content-between bg-light p-2 rounded-3">
                                    <div class="small"><b>চেম্বার:</b> <?php echo $doctor['chamber_no'] ? $doctor['chamber_no'] : 'N/A'; ?></div>
                                    <div class="small"><b>ফি:</b> ৳<?php echo $doctor['fee']; ?></div>
                                </div>
                            </div>

                            <div class="card-footer bg-white border-0 pb-4 px-4 d-grid gap-2">
                                <a href="doctor-profile.php?id=<?php echo $doctor['id']; ?>" class="btn btn-outline-secondary rounded-pill btn-sm fw-bold">প্রোফাইল দেখুন</a>
                                <a href="book-appointment.php?doctor_id=<?php echo $doctor['id']; ?>" class="btn btn-primary rounded-pill btn-sm shadow-sm fw-bold">সিরিয়াল নিন</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <h5 class="text-muted">দুঃখিত, কোনো ডাক্তার পাওয়া যায়নি!</h5>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>