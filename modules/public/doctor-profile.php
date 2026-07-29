<?php
include_once '../../includes/header.php';

// ডাক্তারের ID চেক করা
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>window.location.href='doctors.php';</script>";
    exit;
}

$doctor_id = mysqli_real_escape_string($conn, $_GET['id']);

// ডাক্তারের বিস্তারিত তথ্য ডাটাবেজ থেকে আনা
$query = "SELECT * FROM doctors WHERE id = '$doctor_id' AND status = 'active'";
$result = mysqli_query($conn, $query);
$doctor = mysqli_fetch_assoc($result);

// ডাক্তার না পাওয়া গেলে
if (!$doctor) {
    echo "<div class='container mt-5 py-5 text-center'>
            <div class='alert alert-danger shadow-sm rounded-4'>দুঃখিত, এই ডাক্তারের প্রোফাইলটি পাওয়া যায়নি।</div>
            <a href='doctors.php' class='btn btn-primary rounded-pill px-4'>ডাক্তার তালিকায় ফিরে যান</a>
          </div>";
    include_once '../../includes/footer.php';
    exit;
}

// ১. কিউআর কোডের জন্য তথ্য সাজানো (Dynamic Information)
$h_name = $settings_data['hospital_name'] ?? "পেশেন্ট কেয়ার হাসপাতাল";
$h_phone = $settings_data['hospital_phone'] ?? "০১৩৩১৪ ৩৪৩৪৭";

$qr_text = "🏥 $h_name\n";
$qr_text .= "👨‍⚕️ ডাক্তার: " . $doctor['name'] . "\n";
$qr_text .= "🎓 ডিগ্রি: " . $doctor['qualification'] . "\n";
$qr_text .= "🏷️ বিশেষজ্ঞ: " . $doctor['specialization'] . "\n";
$qr_text .= "📞 ফোন: $h_phone";

$profile_qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qr_text);
?>

<div class="container py-5">
    <!-- ব্রেডক্রাম্ব নেভিগেশন -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb bg-light p-3 rounded-pill px-4 shadow-sm border">
            <li class="breadcrumb-item"><a href="../../index.php" class="text-decoration-none text-navy">হোম</a></li>
            <li class="breadcrumb-item"><a href="doctors.php" class="text-decoration-none text-navy">ডাক্তার তালিকা</a></li>
            <li class="breadcrumb-item active fw-bold text-primary" aria-current="page"><?php echo $doctor['name']; ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- বাম পাশে প্রোফাইল কার্ড -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden p-4">
                <div class="text-center">
                    <div class="mb-4 position-relative">
                        <img src="../../assets/images/doctors/<?php echo $doctor['image']; ?>" 
                             class="rounded-circle shadow-sm border border-5 border-light" 
                             width="180" height="180" 
                             style="object-fit: cover; border-color: var(--light-bg) !important;">
                    </div>
                    
                    <h4 class="fw-bold mb-1 text-navy"><?php echo $doctor['name']; ?></h4>
                    <p class="badge rounded-pill px-3 py-2 mb-3 shadow-sm" style="background-color: var(--light-bg); color: var(--secondary-cyan); font-size: 0.9rem;">
                        <i class="fas fa-stethoscope me-1"></i> <?php echo $doctor['specialization']; ?>
                    </p>

                    <div class="d-grid gap-2 mb-4">
                        <a href="book-appointment.php?doctor_id=<?php echo $doctor['id']; ?>" class="btn btn-primary btn-lg rounded-pill shadow" style="background-color: var(--secondary-cyan); border: none;">
                            <i class="fas fa-calendar-check me-2"></i> অ্যাপয়েন্টমেন্ট নিন
                        </a>
                    </div>

                    <div class="text-start border-top pt-3 mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <div class="icon-sm bg-light text-primary rounded-circle me-3 text-center" style="width: 35px; height: 35px; line-height: 35px;">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div>
                                <p class="small text-muted mb-0">ভিজিট ফি</p>
                                <span class="fw-bold text-success">৳ <?php echo $doctor['fee']; ?> (প্রতি ভিজিট)</span>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center">
                            <div class="icon-sm bg-light text-primary rounded-circle me-3 text-center" style="width: 35px; height: 35px; line-height: 35px;">
                                <i class="fas fa-door-open"></i>
                            </div>
                            <div>
                                <p class="small text-muted mb-0">চেম্বার/রুম নং</p>
                                <span class="fw-bold text-navy"><?php echo $doctor['chamber_no'] ? $doctor['chamber_no'] : 'তথ্য নেই'; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ★ নতুন ডিজিটাল প্রোফাইল QR কোড কার্ড ★ -->
                <div class="card border-0 bg-light rounded-4 overflow-hidden text-center p-3 mt-2 shadow-sm" style="border-top: 4px solid var(--secondary-cyan) !important;">
                    <h6 class="fw-bold text-navy small mb-3">ডিজিটাল প্রোফাইল কোড</h6>
                    
                    <div class="qr-box d-inline-block p-2 rounded-4 mb-2 bg-white" style="border: 2px dashed var(--secondary-cyan);">
                        <img src="<?php echo $profile_qr_url; ?>" alt="Profile QR" class="img-fluid" style="width: 110px; height: 110px;">
                    </div>
                    
                    <p class="text-muted mb-1" style="font-size: 10px; line-height: 1.3;">
                        এই কোডটি মোবাইলে স্ক্যান করে ডাক্তারের তথ্য ও হাসপাতালের নম্বর সেভ করে রাখুন।
                    </p>

                    <div class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 mt-1" style="font-size: 9px; font-weight: 700;">
                        <i class="fas fa-check-circle me-1"></i> VERIFIED PROFILE
                    </div>
                </div>
            </div>
        </div>

        <!-- ডান পাশে বিস্তারিত তথ্য -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5">
                <div class="mb-5">
                    <h5 class="fw-bold border-bottom pb-2 mb-3 text-navy">
                        <i class="fas fa-graduation-cap me-2 text-primary"></i> শিক্ষাগত যোগ্যতা ও পদবী
                    </h5>
                    <p class="lead text-dark fw-semibold"><?php echo $doctor['qualification']; ?></p>
                </div>

                <?php if(!empty($doctor['expertise'])): ?>
                <div class="mb-5">
                    <h5 class="fw-bold border-bottom pb-2 mb-3 text-navy">
                        <i class="fas fa-notes-medical me-2 text-primary"></i> বিশেষ দক্ষতা (Expertise)
                    </h5>
                    <div class="p-3 bg-light rounded-3 border-start border-primary border-4 shadow-sm text-dark">
                        <?php echo nl2br($doctor['expertise']); ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if(!empty($doctor['bio'])): ?>
                <div class="mb-5">
                    <h5 class="fw-bold border-bottom pb-2 mb-3 text-navy">
                        <i class="fas fa-user-md me-2 text-primary"></i> ডাক্তার সম্পর্কে
                    </h5>
                    <p class="text-secondary" style="text-align: justify; line-height: 1.8;">
                        <?php echo nl2br($doctor['bio']); ?>
                    </p>
                </div>
                <?php endif; ?>

                <!-- ডাইনামিক সময়সূচি -->
                <div class="mb-2">
                    <h5 class="fw-bold border-bottom pb-2 mb-3 text-navy">
                        <i class="fas fa-clock me-2 text-primary"></i> চেম্বারের সময়সূচি
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover shadow-sm rounded-3 overflow-hidden">
                            <thead class="table-primary text-white" style="background-color: var(--primary-navy) !important;">
                                <tr>
                                    <th>দিন / তারিখ</th>
                                    <th>ধরণ</th>
                                    <th>সময়</th>
                                    <th>অবস্থা</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $s_query = mysqli_query($conn, "SELECT * FROM doctor_schedules WHERE doctor_id = '$doctor_id' ORDER BY schedule_date DESC, FIELD(day_of_week, 'Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday')");
                                
                                if(mysqli_num_rows($s_query) > 0):
                                    while($s = mysqli_fetch_assoc($s_query)): 
                                        $days_bn = ['Saturday'=>'শনিবার', 'Sunday'=>'রবিবার', 'Monday'=>'সোমবার', 'Tuesday'=>'মঙ্গলবার', 'Wednesday'=>'বুধবার', 'Thursday'=>'বৃহস্পতিবার', 'Friday'=>'শুক্রবার'];
                                        $day_name_bn = $days_bn[$s['day_of_week']] ?? $s['day_of_week'];
                                ?>
                                    <tr>
                                        <td class="fw-bold text-navy">
                                            <?php 
                                                if(!empty($s['schedule_date'])) {
                                                    echo date('d M, Y', strtotime($s['schedule_date'])) . " ($day_name_bn)";
                                                } else {
                                                    echo $day_name_bn;
                                                }
                                            ?>
                                        </td>
                                        <td>
                                            <?php echo !empty($s['schedule_date']) ? '<span class="badge bg-danger rounded-pill px-2" style="font-size:10px;">একক তারিখ</span>' : '<span class="badge bg-primary rounded-pill px-2" style="font-size:10px;">প্রতি সপ্তাহ</span>'; ?>
                                        </td>
                                        <td>
                                            <i class="far fa-clock me-1 text-info"></i>
                                            <?php echo date('h:i A', strtotime($s['start_time'])) . " - " . date('h:i A', strtotime($s['end_time'])); ?>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo ($s['is_available'] == 1) ? 'bg-success' : 'bg-danger'; ?> rounded-pill px-3">
                                                <?php echo ($s['is_available'] == 1) ? 'সক্রিয়' : 'বন্ধ'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; else: ?>
                                    <tr><td colspan="4" class="text-center py-4 text-muted">বর্তমানে কোনো সময়সূচি সেট করা নেই।</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="alert alert-info border-0 shadow-sm rounded-4 mt-4 d-flex align-items-center">
                    <i class="fas fa-info-circle fa-2x me-3 opacity-75"></i>
                    <div>
                        <h6 class="fw-bold mb-1">প্রয়োজনে যোগাযোগ করুন</h6>
                        <p class="small mb-0">হাসপাতালের হেল্পলাইন: +8809617558899 অথবা ইমেইল: patientcare@gmail.com</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.qr-box { transition: 0.3s; }
.qr-box:hover { transform: scale(1.05); }
.text-navy { color: var(--primary-navy); }
.table thead th { background-color: var(--primary-navy) !important; color: white !important; border: none; }
</style>

<?php include_once '../../includes/footer.php'; ?>