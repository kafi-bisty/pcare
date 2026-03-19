<?php
// রুট ডিরেক্টরিতে থাকায় সরাসরি হেডার ইনক্লুড করা যাচ্ছে
include_once 'includes/header.php';
$today_date = date('Y-m-d');
$today_day = date('l');

// স্মার্ট কুয়েরি: নির্দিষ্ট তারিখ থাকলে সেটি আগে নিবে
$raw_docs = mysqli_query($conn, "
    SELECT d.*, s.id as sched_id, s.current_serial, s.max_patients, s.start_time, s.end_time, s.schedule_date 
    FROM doctors d 
    JOIN doctor_schedules s ON d.id = s.doctor_id 
    WHERE (s.schedule_date = '$today_date' OR (s.day_of_week = '$today_day' AND s.schedule_date IS NULL))
    AND s.is_available = 1 AND d.status = 'active'
    ORDER BY s.start_time ASC
");

// ডাটাগুলোকে ডাক্তার অনুযায়ী গ্রুপ করা
$doctors_array = [];
while($row = mysqli_fetch_assoc($raw_docs)) {
    $doctors_array[$row['id']]['info'] = $row; 
    $doctors_array[$row['id']]['slots'][] = $row; 
}

$all_modals = ""; // সব মডাল এখানে জমা হবে
?>

<!-- ১. হিরো সেকশন -->
<section class="hero-section py-5" style="background: linear-gradient(135deg, var(--primary-navy) 0%, #1a4a7a 100%); color: white;">
    <div class="container">
        <div class="row align-items-center py-5">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-3">আপনার সুস্বাস্থ্যই আমাদের <span style="color: var(--secondary-cyan);">একমাত্র লক্ষ্য</span></h1>
                <p class="lead mb-4">পেশেন্ট কেয়ার হাসপাতালে আমরা দিচ্ছি ২৪ ঘণ্টা জরুরি চিকিৎসা সেবা।</p>
                <div class="d-flex gap-3">
                    <a href="modules/public/doctors.php" class="btn btn-lg rounded-pill px-4" style="background-color: var(--secondary-cyan); color: white; border: none;">ডাক্তার খুঁজুন</a>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block">
                <img src="assets/images/hero-image.png" alt="Hospital" class="img-fluid">
            </div>
        </div>
    </div>
</section>

<!-- ২. আজকের ডাক্তার সেকশন -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <h6 class="text-uppercase fw-bold text-info px-3 py-1 rounded-pill mb-2 shadow-sm d-inline-block" style="background: #f0faff; font-size: 12px;">Live Status</h6>
            <h2 class="fw-bold text-navy">আজকের ডাক্তার ও লাইভ সিরিয়াল</h2>
            <div class="mx-auto" style="width: 70px; height: 3px; background: #00bcd4; border-radius: 10px;"></div>
        </div>
        
        <div class="row g-4">
            <?php 
            if(!empty($doctors_array)):
                foreach($doctors_array as $doctor_id => $data):
                    $doc = $data['info'];
                    $modal_id = "docModal_" . $doc['id'];
                    $img = !empty($doc['image']) ? $doc['image'] : 'default-doctor.jpg'; 
                    $img_url = BASE_URL . "assets/images/doctors/" . $img;
            ?>
            <!-- ডাক্তার কার্ড -->
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden h-100 border-top border-primary border-4 transition-hover">
                    <div class="card-body p-4 text-center">
                        <img src="<?php echo $img_url; ?>" class="rounded-circle shadow-sm border border-3 border-white mb-3" width="100" height="100" style="object-fit:cover;">
                        <h5 class="fw-bold text-navy mb-1"><?php echo $doc['name']; ?></h5>
                        <p class="small text-primary fw-bold mb-1"><?php echo $doc['specialization']; ?></p>
                        <p class="text-muted mb-3" style="font-size: 10px;"><i class="fas fa-graduation-cap me-1"></i> <?php echo $doc['qualification']; ?></p>
                        <?php foreach($data['slots'] as $slot): ?>
                            <div class="rounded-4 p-2 mb-2 border shadow-sm" style="background-color: #f8f9ff;">
                                <div class="row align-items-center g-0">
                                    <div class="col-5 border-end"><h4 class="fw-bold text-danger mb-0">#<?php echo $slot['current_serial']; ?></h4></div>
                                    <div class="col-7"><strong class="text-navy" style="font-size: 10px;"><?php echo date('h:i A', strtotime($slot['start_time'])); ?> - <?php echo date('h:i A', strtotime($slot['end_time'])); ?></strong></div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <div class="d-grid gap-2 mt-3">
                            <button type="button" class="btn btn-outline-primary rounded-pill btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#<?php echo $modal_id; ?>">প্রোফাইল ও বিস্তারিত</button>
                            <a href="modules/public/book-appointment.php?doctor_id=<?php echo $doc['id']; ?>" class="btn btn-primary rounded-pill py-2 shadow-sm fw-bold">সিরিয়াল নিন</a>
                        </div>
                    </div>
                </div>
            </div>

            <?php 
            // মডালগুলো ভেরিয়েবলে জমা করা (যাতে পেজের নিচে প্রিন্ট করা যায়)
            $slots_html = "";
            foreach($data['slots'] as $slot) {
                $display_date = $slot['schedule_date'] ? date('d M, Y', strtotime($slot['schedule_date'])) : 'সাপ্তাহিক শিডিউল';
                $slots_html .= '
                <div class="p-3 mb-2 rounded-4 border bg-light shadow-sm">
                    <div class="d-flex justify-content-between small fw-bold"><span>'.$display_date.'</span></div>
                    <div class="d-flex justify-content-between align-items-center mt-1">
                        <span class="small"><i class="far fa-clock text-info"></i> '.date('h:i A', strtotime($slot['start_time'])).' - '.date('h:i A', strtotime($slot['end_time'])).'</span>
                        <span class="fw-bold text-danger">সিরিয়াল: #'.$slot['current_serial'].'</span>
                    </div>
                </div>';
            }

            $all_modals .= '
            <div class="modal fade" id="'.$modal_id.'" tabindex="-1" aria-hidden="true" data-bs-backdrop="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 rounded-5 overflow-hidden shadow-2xl">
                        <div class="modal-header border-0 p-4 text-white" style="background: linear-gradient(135deg, #0A2647 0%, #2AA7E5 100%);">
                            <div class="d-flex align-items-center">
                                <img src="'.$img_url.'" class="rounded-circle border border-3 border-white me-3" width="70" height="70" style="object-fit:cover; background:#fff;">
                                <div><h5 class="modal-title fw-bold mb-0">'.$doc['name'].'</h5><small class="opacity-75">'.$doc['qualification'].'</small></div>
                            </div>
                            <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4 bg-white">
                            <h6 class="fw-bold text-navy border-bottom pb-1 mb-3">বিশেষজ্ঞতা:</h6>
                            <p class="small text-muted">'.($doc['expertise'] ?: 'সঠিক রোগ নির্ণয় ও চিকিৎসায় বিশেষ অভিজ্ঞ।').'</p>
                            <h6 class="fw-bold text-navy border-bottom pb-1 mb-3">আজকের সময়সূচি:</h6>
                            '.$slots_html.'
                            <div class="p-3 rounded-4 border-start border-4 border-info bg-light mt-3">
                                <h6 class="fw-bold small mb-1">চেম্বার লোকেশন:</h6>
                                <p class="mb-0 small fw-bold">রুম নং: '.$doc['chamber_no'].'</p>
                            </div>
                        </div>
                        <div class="modal-footer border-0 p-3 bg-white">
                            <a href="modules/public/book-appointment.php?doctor_id='.$doc['id'].'" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow-lg">সিরিয়াল বুকিং করুন</a>
                        </div>
                    </div>
                </div>
            </div>';
            ?>
            <?php endforeach; else: ?>
                <div class="col-12 text-center py-5 text-muted">আজ কোনো ডাক্তারের চেম্বার নেই।</div>
            <?php endif; ?>
        </div>
    </div>
</section>


<!-- ৪. অতিরিক্ত সিএসএস ফিক্স -->
<style>
   
    /* মডালকে সবকিছুর উপরে আনার জন্য */
    .modal { 
        z-index: 99999 !important; 
    }
    /* মডালের পেছনের কালো আবছা অংশকে ঠিক করা */
    .modal-backdrop { 
        z-index: 99998 !important; 
    }
    
    /* যদি মডালের উপরের অংশ হেডারের নিচে ঢাকা পড়ে যায় */
    .modal-dialog {
        margin-top: 80px !important; /* আপনার হেডার যতটা চওড়া সেই অনুযায়ী এটি বাড়াতে পারেন */
    }

    .transition-hover { transition: transform 0.3s; }
    .transition-hover:hover { transform: translateY(-10px); }
    .text-navy { color: #0A2647; }
</style>

<!-- সব মডাল পেজের একদম শেষে প্রিন্ট করা হলো -->
<?php echo $all_modals; ?>




<!-- অতিরিক্ত স্টাইল -->
<style>
    .transition-hover { transition: all 0.3s ease; }
    .transition-hover:hover { transform: translateY(-10px); }
    .animate-pulse { animation: pulse 2s infinite; }
    @keyframes pulse {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(40, 167, 69, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(40, 167, 69, 0); }
    }
    .modal-content { border: none; }
    .bg-navy { background-color: #1a237e; }
</style>




<!-- ২. স্ট্যাটাস কাউন্টার (ছোট তথ্য) -->
<section class="py-4 shadow-sm bg-white">
    <div class="container">
        <div class="row text-center gy-4">
            <div class="col-6 col-md-3">
                <h3 class="fw-bold mb-0" style="color: var(--primary-navy);">2০+</h3>
                <p class="text-muted mb-0">বিশেষজ্ঞ ডাক্তার</p>
            </div>
            <div class="col-6 col-md-3">
                <h3 class="fw-bold mb-0" style="color: var(--primary-navy);">১০+</h3>
                <p class="text-muted mb-0">বিভাগসমূহ</p>
            </div>
            <div class="col-6 col-md-3">
                <h3 class="fw-bold mb-0" style="color: var(--primary-navy);">২৪/৭</h3>
                <p class="text-muted mb-0">জরুরি সেবা</p>
            </div>
            <div class="col-6 col-md-3">
                <h3 class="fw-bold mb-0" style="color: var(--primary-navy);">৫০০০+</h3>
                <p class="text-muted mb-0">সুস্থ রোগী</p>
            </div>
        </div>
    </div>
</section>

<!-- ১. আধুনিক টু-পার্ট প্রমোশনাল ব্যানার -->
<section class="hero-banner py-5" style="background: linear-gradient(135deg, var(--primary-navy) 0%, #1a4a7a 100%); position: relative; overflow: hidden; min-height: 500px;">
    <!-- ব্যাকগ্রাউন্ড এনিমেশন এলিমেন্ট -->
    <div class="bg-circle-1"></div>
    <div class="bg-circle-2"></div>

    <div class="container py-lg-5 position-relative" style="z-index: 10;">
        <div class="row align-items-center g-5">
            
            <!-- বাম পাশ: ফেসবুক ও ব্র্যান্ডিং প্রমোশন -->
            <div class="col-lg-6 text-center text-lg-start border-lg-end border-white border-opacity-10 pe-lg-5">
                <div class="mb-4">
                    <!-- বড় গোলাকার লোগো -->
                    <img src="assets/images/logo.png" alt="Logo" class="main-logo-large shadow-glow mb-3">
                </div>
                <h1 class="display-4 fw-bold text-white mb-2 hospital-brand-name">পেশেন্ট কেয়ার</h1>
                <h5 class="text-info fw-bold text-uppercase mb-4" style="letter-spacing: 1px; font-size: 0.9rem;">হাসপাতাল এন্ড ডায়াগনস্টিক সেন্টার</h5>
                <p class="text-white-50 mb-5 fs-6">বরগুনায় আমরাই দিচ্ছি বিশ্বমানের আধুনিক ডিজিটাল চিকিৎসা সেবা। আপনার এবং আপনার পরিবারের সুস্বাস্থ্য নিশ্চিত করাই আমাদের লক্ষ্য।</p>
                
                <div class="d-flex justify-content-center justify-content-lg-start gap-3 flex-wrap">
                    <a href="modules/public/doctors.php" class="btn btn-cyan btn-lg rounded-pill px-4 fw-bold shadow-lg">
                        <i class="fas fa-calendar-check me-2"></i>সিরিয়াল নিন
                    </a>
                    <a href="https://www.facebook.com/share/1GUFDEoj89/" target="_blank" class="btn btn-facebook btn-lg rounded-pill px-4 fw-bold shadow">
                        <i class="fab fa-facebook-f me-2"></i>Facebook Page
                    </a>
                </div>
            </div>
 <!-- ডান পাশ: ডাক্তারদের হেলথ টিপস (Modern Tips Card) -->
<!-- ডান পাশ: ডাক্তারদের হেলথ টিপস (Modern & Dynamic) -->
<div class="col-lg-6 mt-5 mt-lg-0">
    <div class="tips-card p-4 p-md-5 rounded-5 border border-white border-opacity-10 shadow-lg" style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(15px);">
        <!-- হেডার অংশ -->
        <div class="d-flex align-items-center mb-4">
            <div class="icon-box-tips me-3" style="width: 60px; height: 60px; background: rgba(42, 167, 229, 0.15); border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(42,167,229,0.3);">
                <i class="fas fa-user-md fa-2x text-info"></i>
            </div>
            <div>
                <h4 class="fw-bold text-white mb-0">ডাক্তারের পরামর্শ <span class="badge bg-danger ms-2" style="font-size: 10px; animation: pulse 1.5s infinite;">LIVE</span></h4>
                <p class="text-white-50 small mb-0">সুস্থ থাকতে আজই মেনে চলুন</p>
            </div>
        </div>

        <div class="tips-content">
            <ul class="list-unstyled">
                <?php 
                // ডাটাবেজ থেকে টিপস এবং ডাক্তারের তথ্য আনা
                $dynamic_tips = mysqli_query($conn, "
                    SELECT t.*, d.name as doc_name, d.image as doc_img, d.specialization 
                    FROM doctor_tips t 
                    JOIN doctors d ON t.doctor_id = d.id 
                    ORDER BY t.id DESC LIMIT 4
                ");

                if($dynamic_tips && mysqli_num_rows($dynamic_tips) > 0):
                    while($tip = mysqli_fetch_assoc($dynamic_tips)):
                        $doc_pic = !empty($tip['doc_img']) ? $tip['doc_img'] : 'default-doctor.jpg';
                        // প্রথম লাইনটি প্রিভিউ হিসেবে দেখানোর জন্য
                        $first_line = explode("\n", $tip['tip_text'])[0];
                ?>
                <li class="mb-4 cursor-pointer tip-item-row" 
                    onclick='showModernTip(<?php echo json_encode($tip["tip_text"]); ?>, "<?php echo $tip["doc_name"]; ?>", "<?php echo $doc_pic; ?>", "<?php echo $tip["specialization"]; ?>")'>
                    <div class="d-flex align-items-start">
                        <!-- ডাক্তারের গোল ছবি -->
                        <img src="assets/images/doctors/<?php echo $doc_pic; ?>" class="rounded-circle border border-2 border-info me-3 shadow-sm" width="45" height="45" style="object-fit: cover; background: #fff;">
                        <div>
                            <!-- সংক্ষেপিত টিপস টেক্সট -->
                            <p class="text-white small mb-1 lh-sm opacity-90 fw-semibold">
                                <i class="fas fa-check-circle text-info me-2 small"></i><?php echo mb_strimwidth($first_line, 0, 80, "..."); ?>
                            </p>
                            <small class="text-info fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">- ডাঃ <?php echo $tip['doc_name']; ?></small>
                        </div>
                    </div>
                </li>
                <?php endwhile; else: ?>
                    <li class="text-center text-white-50 opacity-50 py-4">বর্তমানে কোনো নতুন পরামর্শ নেই।</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<!-- আধুনিক পপ-আপ স্ক্রিপ্ট (SweetAlert2) -->
<script>
function showModernTip(rawText, docName, docImg, spec) {
    // টেক্সটকে পয়েন্টে রূপান্তর করা
    let points = rawText.split('\n');
    let pointsHtml = '<div class="text-start mt-3">';
    points.forEach(point => {
        if(point.trim() !== "") {
            pointsHtml += `<p class="small text-dark mb-2"><i class="fas fa-arrow-right text-info me-2"></i> ${point.trim()}</p>`;
        }
    });
    pointsHtml += '</div>';

    Swal.fire({
        title: '<h5 class="fw-bold text-navy mb-0">বিশেষ স্বাস্থ্য পরামর্শ</h5>',
        html: `
            <div class="p-2">
                <div class="bg-light p-3 rounded-4 shadow-inner border-start border-4 border-info">
                    ${pointsHtml}
                </div>
                <hr class="my-4 opacity-10">
                <div class="d-flex align-items-center justify-content-center">
                    <img src="assets/images/doctors/${docImg}" class="rounded-circle me-3 border border-4 border-white shadow" width="70" height="70" style="object-fit:cover; background:#fff;">
                    <div class="text-start">
                        <h6 class="fw-bold mb-0 text-navy">ডাঃ ${docName}</h6>
                        <small class="badge bg-info text-dark rounded-pill">${spec}</small>
                        <p class="x-small text-muted mb-0 mt-1">পেশেন্ট কেয়ার হাসপাতাল</p>
                    </div>
                </div>
            </div>
        `,
        confirmButtonText: 'ধন্যবাদ, তথ্যটি জানলাম',
        confirmButtonColor: '#0A2647',
        customClass: {
            popup: 'rounded-5 border-0 shadow-lg',
            confirmButton: 'rounded-pill px-4 fw-bold'
        },
        showClass: { popup: 'animate__animated animate__fadeInUp' },
        hideClass: { popup: 'animate__animated animate__fadeOutDown' },
        backdrop: `rgba(10, 38, 71, 0.7)`
    });
}
</script>

<style>
/* সাইডবার ডিজাইন */
.tip-item-row { 
    transition: all 0.3s ease; 
    padding: 12px; 
    border-radius: 20px; 
    border: 1px solid transparent;
}
.tip-item-row:hover { 
    background: rgba(255, 255, 255, 0.1); 
    border-color: rgba(42, 167, 229, 0.3);
    transform: translateX(8px);
}
.text-navy { color: #0A2647; }
.x-small { font-size: 11px; }

/* এনিমেশন */
@keyframes pulse {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.1); opacity: 0.7; }
    100% { transform: scale(1); opacity: 1; }
}
</style>


<!-- ৩. সেবাসমূহ সেকশন (Services) -->
<section id="services" class="py-5" style="background-color: var(--soft-gray);">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h6 class="text-uppercase fw-bold" style="color: var(--secondary-cyan);">আমাদের সেবাসমূহ</h6>
            <h2 class="fw-bold" style="color: var(--primary-navy);">আধুনিক ও মানসম্মত চিকিৎসাসেবা</h2>
            <div class="mx-auto" style="width: 80px; height: 3px; background-color: var(--secondary-cyan);"></div>
        </div>

        <div class="row g-4">
            <!-- সার্ভিস ১: ইমার্জেন্সি -->
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm p-4 service-card">
                    <div class="icon-box mb-3 text-center rounded-circle d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; background-color: var(--light-blue); color: var(--secondary-cyan);">
                        <i class="fas fa-ambulance fa-2x"></i>
                    </div>
                    <h5 class="fw-bold" style="color: var(--primary-navy);">জরুরি বিভাগ</h5>
                    <p class="text-muted">২৪ ঘণ্টা ইমার্জেন্সি সার্ভিস এবং দ্রুত অ্যাম্বুলেন্স সুবিধা। আমাদের দক্ষ টিম সবসময় প্রস্তুত।</p>
                </div>
            </div>

            <!-- সার্ভিস ২: ডায়াগনস্টিক -->
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm p-4 service-card">
                    <div class="icon-box mb-3 text-center rounded-circle d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; background-color: var(--light-blue); color: var(--secondary-cyan);">
                        <i class="fas fa-microscope fa-2x"></i>
                    </div>
                    <h5 class="fw-bold" style="color: var(--primary-navy);">ডায়াগনস্টিক সেন্টার</h5>
                    <p class="text-muted">আধুনিক ল্যাবরেটরি এবং উন্নত ইমেজিং প্রযুক্তির মাধ্যমে নির্ভুল রিপোর্ট নিশ্চিত করা হয়।</p>
                </div>
            </div>

            <!-- সার্ভিস ৩: ওপিডি -->
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm p-4 service-card">
                    <div class="icon-box mb-3 text-center rounded-circle d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; background-color: var(--light-blue); color: var(--secondary-cyan);">
                        <i class="fas fa-user-md fa-2x"></i>
                    </div>
                    <h5 class="fw-bold" style="color: var(--primary-navy);">আউটডোর সেবা</h5>
                    <p class="text-muted">প্রতিদিন অভিজ্ঞ বিশেষজ্ঞ ডাক্তারদের মাধ্যমে আউটডোর কনসালটেশন প্রদান করা হয়।</p>
                </div>
            </div>

            <!-- সার্ভিস ৪: ফার্মেসি -->
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm p-4 service-card">
                    <div class="icon-box mb-3 text-center rounded-circle d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; background-color: var(--light-blue); color: var(--secondary-cyan);">
                        <i class="fas fa-pills fa-2x"></i>
                    </div>
                    <h5 class="fw-bold" style="color: var(--primary-navy);">২৪/৭ ফার্মেসি</h5>
                    <p class="text-muted">হাসপাতালের ভেতর ২৪ ঘণ্টা নির্ভরযোগ্য ওষুধের দোকান এবং লাইফ সেভিং ড্রাগস।</p>
                </div>
            </div>

            <!-- সার্ভিস ৫: আইসিইউ -->
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm p-4 service-card">
                    <div class="icon-box mb-3 text-center rounded-circle d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; background-color: var(--light-blue); color: var(--secondary-cyan);">
                        <i class="fas fa-procedures fa-2x"></i>
                    </div>
                    <h5 class="fw-bold" style="color: var(--primary-navy);">আইসিইউ ও সিসিইউ</h5>
                    <p class="text-muted">অত্যাধুনিক লাইফ সাপোর্ট এবং উন্নত মনিটরিং সমৃদ্ধ আইসিইউ ও সিসিইউ সুবিধা।</p>
                </div>
            </div>

            <!-- সার্ভিস ৬: ব্লাড ব্যাংক -->
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm p-4 service-card">
                    <div class="icon-box mb-3 text-center rounded-circle d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; background-color: var(--light-blue); color: var(--secondary-cyan);">
                        <i class="fas fa-tint fa-2x"></i>
                    </div>
                    <h5 class="fw-bold" style="color: var(--primary-navy);">ব্লাড ব্যাংক</h5>
                    <p class="text-muted">নিরাপদ রক্ত সংগ্রহ এবং প্রয়োজনীয় মুহূর্তে রক্তের গ্রুপের যোগান দেওয়া হয়।</p>
                </div>
            </div>
        </div>
    </div>
</section>




<!-- ৪. যোগাযোগ সেকশন (Contact Us) -->
<section id="contact" class="py-5 bg-white">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h6 class="text-uppercase fw-bold" style="color: var(--secondary-cyan);">যোগাযোগ করুন</h6>
            <h2 class="fw-bold" style="color: var(--primary-navy);">আমাদের সাথে সংযুক্ত হোন</h2>
            <div class="mx-auto" style="width: 80px; height: 3px; background-color: var(--secondary-cyan);"></div>
        </div>

        <div class="row g-5">
            <!-- বাম পাশে যোগাযোগের তথ্য -->
            <div class="col-lg-5">
                <div class="d-flex mb-4">
                    <div class="flex-shrink-0 btn-lg-square rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background-color: var(--light-blue); color: var(--secondary-cyan);">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="ms-3">
                        <h5 style="color: var(--primary-navy);">ঠিকানা</h5>
                        <p class="text-muted mb-0"> #কলেজ রোড বরগুনা। </p>
                    </div>
                </div>
                <div class="d-flex mb-4">
                    <div class="flex-shrink-0 btn-lg-square rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background-color: var(--light-blue); color: var(--secondary-cyan);">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <div class="ms-3">
                        <h5 style="color: var(--primary-navy);">ফোন করুন</h5>
                        <p class="text-muted mb-0">+09617558899
</p>
                        <p class="text-muted mb-0">+8801331434347</p>
                    </div>
                </div>
                <div class="d-flex mb-4">
                    <div class="flex-shrink-0 btn-lg-square rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background-color: var(--light-blue); color: var(--secondary-cyan);">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="ms-3">
                        <h5 style="color: var(--primary-navy);">ইমেইল</h5>
                        <p class="text-muted mb-0">patientcarehospital.com</p>
                    </div>
                </div>
                
                <!-- ছোট ম্যাপ (প্লাসহোল্ডার হিসেবে) -->
                <!-- গুগল ম্যাপ সেকশন -->
<div class="rounded overflow-hidden shadow-sm mt-4 border" style="border-color: var(--secondary-cyan) !important;">
    
    <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d3695.1554880877334!2d90.1158579!3d22.1581526!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x30aa99d5e7a05bed%3A0x15ffd574e4ed25c6!2sPatient%20Care%20Hospital%20%26%20Diagnostic%20Centre%2CBarguna!5e0!3m2!1sen!2sbd!4v1772712355975!5m2!1sen!2sbd" width="600" height="450" style="border:0;"
         allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </iframe>
</div>
            </div>

            <!-- ডান পাশে কন্টাক্ট ফর্ম -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm p-4 rounded-4">
                    <form action="api/send-message.php" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="name" name="name" placeholder="আপনার নাম" required>
                                    <label for="name">আপনার নাম</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="email" class="form-control" id="email" name="email" placeholder="আপনার ইমেইল" required>
                                    <label for="email">আপনার ইমেইল</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="subject" name="subject" placeholder="বিষয়" required>
                                    <label for="subject">বিষয়</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <textarea class="form-control" placeholder="আপনার বার্তাটি এখানে লিখুন" id="message" name="message" style="height: 150px" required></textarea>
                                    <label for="message">বার্তা</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary w-100 py-3 rounded-pill fw-bold" type="submit" style="background-color: var(--secondary-cyan); border: none;">বার্তা পাঠান</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- video and galarry -->
<!-- গ্যালারি সেকশন শুরু -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <h6 class="text-uppercase fw-bold text-info">Our Facilities</h6>
            <h2 class="fw-bold text-navy">হাসপাতাল গ্যালারি</h2>
            <div class="mx-auto" style="width: 70px; height: 3px; background: var(--secondary-cyan);"></div>
        </div>

        <div class="row g-4">
            <?php 
            $gallery_query = mysqli_query($conn, "SELECT * FROM gallery ORDER BY id DESC LIMIT 6");
            while($g = mysqli_fetch_assoc($gallery_query)):
            ?>
            <div class="col-lg-4 col-md-6">
                <div class="gallery-item rounded-4 overflow-hidden shadow-sm position-relative">
                    <?php if($g['media_type'] == 'image'): ?>
                        <img src="assets/images/gallery/<?php echo $g['media_url']; ?>" class="img-fluid w-100" style="height: 250px; object-fit: cover;">
                        <div class="gallery-overlay">
                            <a href="assets/images/gallery/<?php echo $g['media_url']; ?>" class="view-btn" data-fancybox="gallery">
                                <i class="fas fa-search-plus"></i>
                            </a>
                        </div>
                    <?php else: 
                        // ইউটিউব লিঙ্ক থেকে আইডি বের করা
                        parse_str(parse_url($g['media_url'], PHP_URL_QUERY), $yt_id);
                        $video_id = $yt_id['v'] ?? 'default';
                    ?>
                        <div class="video-thumbnail" style="height: 250px; background: url('https://img.youtube.com/vi/<?php echo $video_id; ?>/hqdefault.jpg') center/cover;">
                            <a href="<?php echo $g['media_url']; ?>" class="play-btn" data-fancybox="gallery">
                                <i class="fas fa-play-circle"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                    <div class="p-3 bg-white text-center border-top">
                        <h6 class="fw-bold text-navy mb-0 small"><?php echo $g['title']; ?></h6>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- Fancybox CSS & JS (ছবি বড় করে দেখার জন্য) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css">
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
<script>Fancybox.bind("[data-fancybox='gallery']", {});</script>

<style>
    .gallery-item { transition: 0.3s; height: 100%; border: 1px solid #eee; }
    .gallery-item:hover { transform: translateY(-10px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); }
    .gallery-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(10, 38, 71, 0.7); display: flex; align-items: center; justify-content: center; opacity: 0; transition: 0.3s; }
    .gallery-item:hover .gallery-overlay { opacity: 1; }
    .view-btn, .play-btn { color: #fff; font-size: 3rem; text-decoration: none; transition: 0.3s; }
    .view-btn:hover, .play-btn:hover { color: var(--secondary-cyan); transform: scale(1.1); }
    .video-thumbnail { display: flex; align-items: center; justify-content: center; position: relative; }


/* লার্জ লোগো গ্লো ইফেক্ট */
    .main-logo-large {
        width: 120px; height: 120px;
        border-radius: 50%; border: 4px solid var(--secondary-cyan);
        background: #fff; padding: 5px; animation: pulse-glow 2s infinite;
    }
    @keyframes pulse-glow {
        0% { box-shadow: 0 0 0 0 rgba(42, 167, 229, 0.4); }
        70% { box-shadow: 0 0 0 20px rgba(42, 167, 229, 0); }
        100% { box-shadow: 0 0 0 0 rgba(42, 167, 229, 0); }
    }

    /* ফেসবুক বাটন */
    .btn-facebook { background-color: #1877F2; color: white !important; border: none; }
    .btn-facebook:hover { background-color: #166fe5; transform: translateY(-3px); }

    /* টিপস কার্ড ডিজাইন (Glass-morphism) */
    .tips-card {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
        position: relative;
    }
    .icon-box-tips {
        width: 60px; height: 60px;
        background: rgba(42, 167, 229, 0.1);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
    }

    /* ব্যাকগ্রাউন্ড ডেকোরেশন */
    .bg-circle-1 { position: absolute; top: -100px; left: -100px; width: 400px; height: 400px; background: rgba(42, 167, 229, 0.05); border-radius: 50%; }
    .bg-circle-2 { position: absolute; bottom: -150px; right: -150px; width: 500px; height: 500px; background: rgba(42, 167, 229, 0.03); border-radius: 50%; }

    @media (max-width: 991px) {
        .hospital-brand-name { font-size: 2.5rem; }
        .hero-banner { text-align: center; }
    }












.service-card {
    transition: all 0.3s ease;
    cursor: pointer;
}
.service-card:hover {
    transform: translateY(-5px);
    background-color: var(--primary-navy);
}
.service-card:hover h5, .service-card:hover p {
    color: white !important;
}
.service-card:hover .icon-box {
    background-color: var(--secondary-cyan) !important;
    color: white !important;
}
</style>

<?php include_once 'includes/footer.php'; ?>