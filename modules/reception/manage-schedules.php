<?php
// ১. কনফিগ এবং ডাটাবেজ সবার আগে
include_once '../../config/database.php';
include_once '../../config/constants.php';
include_once '../../config/functions.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// ২. রিসেপশন লগইন চেক
if (!isset($_SESSION['reception_id']) || $_SESSION['user_role'] != 'reception') {
    header("Location: ../auth/staff-login.php"); 
    exit; 
}

// ৩. ডাক্তারের আইডি চেক
if (!isset($_GET['doctor_id'])) { header("Location: manage-doctors.php"); exit; }
$doctor_id = mysqli_real_escape_string($conn, $_GET['doctor_id']);

// --- ৪. প্রসেসিং লজিক ---

// ৪.১ নতুন শিডিউল যোগ করা (Weekly or Specific Date)
if (isset($_POST['add_schedule'])) {
    $type = $_POST['schedule_mode']; // weekly or date
    $start = $_POST['start_time'];
    $end = $_POST['end_time'];
    $max = $_POST['max_patients'];
    
    if ($type == 'weekly') {
        $day = $_POST['day'];
        $date_val = "NULL";
    } else {
        $date_input = $_POST['specific_date'];
        $date_val = "'$date_input'";
        $day = date('l', strtotime($date_input)); // তারিখ থেকে বারের নাম বের করা
    }

    $query = "INSERT INTO doctor_schedules (doctor_id, day_of_week, schedule_date, start_time, end_time, max_patients, is_available, current_serial) 
              VALUES ('$doctor_id', '$day', $date_val, '$start', '$end', '$max', 1, 0)";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "নতুন শিডিউল সফলভাবে যোগ হয়েছে!";
    }
    header("Location: manage-schedules.php?doctor_id=$doctor_id"); exit;
}

// ৪.২ লাইভ সিরিয়াল আপডেট
if (isset($_POST['next_serial'])) {
    $s_id = $_POST['schedule_id'];
    $next_val = $_POST['current_val'] + 1;
    if($next_val <= $_POST['max_limit']) {
        mysqli_query($conn, "UPDATE doctor_schedules SET current_serial = '$next_val' WHERE id = '$s_id'");
    }
    header("Location: manage-schedules.php?doctor_id=$doctor_id"); exit;
}

// ৪.৩ সিরিয়াল রিসেট, টগল এবং ডিলিট
if (isset($_POST['reset_serial'])) {
    mysqli_query($conn, "UPDATE doctor_schedules SET current_serial = 0 WHERE id = '{$_POST['schedule_id']}'");
    header("Location: manage-schedules.php?doctor_id=$doctor_id"); exit;
}
if (isset($_GET['toggle_id'])) {
    mysqli_query($conn, "UPDATE doctor_schedules SET is_available = 1 - is_available WHERE id = '{$_GET['toggle_id']}'");
    header("Location: manage-schedules.php?doctor_id=$doctor_id"); exit;
}
if (isset($_GET['delete_id'])) {
    mysqli_query($conn, "DELETE FROM doctor_schedules WHERE id = '{$_GET['delete_id']}'");
    header("Location: manage-schedules.php?doctor_id=$doctor_id"); exit;
}

// ৫. ডাটা আনা
$doctor_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name, image FROM doctors WHERE id = '$doctor_id'"));
$schedules = mysqli_query($conn, "SELECT * FROM doctor_schedules WHERE doctor_id = '$doctor_id' ORDER BY schedule_date DESC, day_of_week ASC");

include_once '../../includes/header.php';
?>

<div class="container py-5">
    <!-- হেডার কার্ড -->
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4" style="background: var(--primary-navy); color: white;">
        <div class="d-flex align-items-center">
            <img src="../../assets/images/doctors/<?php echo $doctor_data['image']; ?>" class="rounded-circle border border-3 border-info me-3 shadow" width="75" height="75" style="object-fit: cover;">
            <div>
                <h4 class="fw-bold mb-0 text-white"><?php echo $doctor_data['name']; ?></h4>
                <p class="mb-0 opacity-75 small">ডাইনামিক সময়সূচি ও লাইভ সিরিয়াল কন্ট্রোল</p>
            </div>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm ms-auto rounded-pill px-4 shadow-sm"><i class="fas fa-home me-1"></i> ড্যাশবোর্ড</a>
        </div>
    </div>

    <div class="row g-4">
        <!-- বাম পাশ: নতুন সময় যোগ করার ফরম -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top: 100px;">
                <h6 class="fw-bold mb-3 border-bottom pb-2 text-navy">শিডিউল যোগ করুন</h6>
                <form action="" method="POST">
                    <!-- শিডিউল টাইপ সুইচ -->
                    <div class="mb-3 bg-light p-2 rounded-pill d-flex justify-content-around">
                        <div class="form-check form-check-inline m-0">
                            <input class="form-check-input" type="radio" name="schedule_mode" id="mode1" value="weekly" checked onclick="toggleMode('weekly')">
                            <label class="form-check-label small fw-bold" for="mode1">সাপ্তাহিক</label>
                        </div>
                        <div class="form-check form-check-inline m-0">
                            <input class="form-check-input" type="radio" name="schedule_mode" id="mode2" value="date" onclick="toggleMode('date')">
                            <label class="form-check-label small fw-bold" for="mode2">একক তারিখ</label>
                        </div>
                    </div>

                    <!-- সাপ্তাহিক ইনপুট -->
                    <div id="weeklyBox" class="mb-3">
                        <label class="small fw-bold">বারের নাম</label>
                        <select name="day" class="form-select shadow-none border-primary border-opacity-25">
                            <option value="Saturday">শনিবার</option>
                            <option value="Sunday">রবিবার</option>
                            <option value="Monday">সোমবার</option>
                            <option value="Tuesday">মঙ্গলবার</option>
                            <option value="Wednesday">বুধবার</option>
                            <option value="Thursday">বৃহস্পতিবার</option>
                            <option value="Friday">শুক্রবার</option>
                        </select>
                    </div>

                    <!-- তারিখ ইনপুট -->
                    <div id="dateBox" class="mb-3 d-none">
                        <label class="small fw-bold">তারিখ নির্বাচন করুন</label>
                        <input type="date" name="specific_date" class="form-control shadow-none border-danger border-opacity-25" min="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6"><label class="small fw-bold">শুরু</label><input type="time" name="start_time" class="form-control shadow-none" required></div>
                        <div class="col-6"><label class="small fw-bold">শেষ</label><input type="time" name="end_time" class="form-control shadow-none" required></div>
                    </div>
                    <div class="mb-4">
                        <label class="small fw-bold">সর্বোচ্চ সিরিয়াল</label>
                        <input type="number" name="max_patients" class="form-control shadow-none" value="20" required>
                    </div>
                    <button type="submit" name="add_schedule" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow">শিডিউল সেভ করুন</button>
                </form>
            </div>
        </div>

        <!-- ডান পাশ: শিডিউল তালিকা ও লাইভ কন্ট্রোল -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="table-responsive p-3">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr class="small text-muted text-uppercase">
                                <th>দিন / তারিখ</th>
                                <th class="text-center">লাইভ সিরিয়াল</th>
                                <th>অবস্থা</th>
                                <th class="text-center">অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($schedules) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($schedules)): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-navy"><?php echo $row['day_of_week']; ?></div>
                                        <small class="<?php echo $row['schedule_date'] ? 'text-danger fw-bold' : 'text-muted'; ?>">
                                            <?php echo $row['schedule_date'] ? date('d M, Y', strtotime($row['schedule_date'])) : 'প্রতি সপ্তাহ'; ?>
                                        </small><br>
                                        <small class="x-small text-muted"><?php echo date('h:i A', strtotime($row['start_time'])) . " - " . date('h:i A', strtotime($row['end_time'])); ?></small>
                                    </td>
                                    <td class="text-center" style="min-width: 150px;">
                                        <div class="p-2 bg-light rounded-3 border mb-1">
                                            <span class="x-small d-block text-muted uppercase">চলমান</span>
                                            <h4 class="fw-bold text-danger mb-0">#<?php echo $row['current_serial']; ?></h4>
                                        </div>
                                        <form action="" method="POST" class="d-flex gap-1">
                                            <input type="hidden" name="schedule_id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="current_val" value="<?php echo $row['current_serial']; ?>">
                                            <input type="hidden" name="max_limit" value="<?php echo $row['max_patients']; ?>">
                                            <button type="submit" name="next_serial" class="btn btn-sm btn-danger rounded-pill flex-grow-1 x-small fw-bold">NEXT</button>
                                            <button type="submit" name="reset_serial" class="btn btn-sm btn-outline-secondary rounded-pill" title="রিসেট"><i class="fas fa-sync-alt"></i></button>
                                        </form>
                                    </td>
                                    <td>
                                        <?php if($row['is_available']): ?>
                                            <span class="badge bg-success rounded-pill px-3">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger rounded-pill px-3">Off</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="?doctor_id=<?php echo $doctor_id; ?>&toggle_id=<?php echo $row['id']; ?>" class="btn btn-sm <?php echo $row['is_available'] ? 'btn-outline-warning' : 'btn-outline-success'; ?> rounded-pill mb-1 w-100 x-small fw-bold">
                                            <?php echo $row['is_available'] ? 'বন্ধ করুন' : 'চালু করুন'; ?>
                                        </a>
                                        <a href="?doctor_id=<?php echo $doctor_id; ?>&delete_id=<?php echo $row['id']; ?>" class="text-danger x-small text-decoration-none fw-bold" onclick="return confirm('মুছে ফেলতে চান?')"><i class="fas fa-trash-alt me-1"></i>ডিলিট</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center py-5 text-muted">কোনো সময় সেট করা নেই।</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleMode(mode) {
    if(mode === 'weekly') {
        document.getElementById('weeklyBox').classList.remove('d-none');
        document.getElementById('dateBox').classList.add('d-none');
    } else {
        document.getElementById('weeklyBox').classList.add('d-none');
        document.getElementById('dateBox').classList.remove('d-none');
    }
}
</script>

<?php include_once '../../includes/footer.php'; ?>