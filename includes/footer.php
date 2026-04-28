<!-- ==========================================================================
       Hospital Management System - Modern Professional Footer
       Developed By: Bicharan Roy
       ========================================================================== -->

<footer class="hospital-footer mt-5 pt-5 pb-0 shadow-lg no-print">
    <div class="container">
        <div class="row g-4 mb-5">
            
            <!-- ১. প্রতিষ্ঠানের পরিচয় ও মালিকের প্রোফাইল -->
            <div class="col-lg-4 col-md-6 footer-brand-section">
                <div class="owner-image-wrapper mb-3 cursor-pointer" onclick="showOwnerInfo()" title="চেয়ারম্যান প্রোফাইল দেখতে ক্লিক করুন">
                    <img src="<?php echo BASE_URL; ?>assets/images/sakib.png" alt="Chairman" class="footer-owner-img shadow-lg">
                    <div class="owner-pulse-ring"></div>
                </div>
                <h4 class="fw-bold text-white mb-1">পেশেন্ট কেয়ার</h4>
                <p class="text-cyan small fw-bold text-uppercase mb-3" style="letter-spacing: 1px; font-size: 11px;">হাসপাতাল এন্ড ডায়াগনস্টিক সেন্টার</p>
                <p class="small text-white-50 lh-lg">
                    দক্ষ ও অভিজ্ঞ বিশেষজ্ঞ চিকিৎসকদের সমন্বয়ে আমরা দিচ্ছি আধুনিক চিকিৎসা সেবা। সঠিক রোগ নির্ণয় এবং রোগীর দ্রুত সুস্থতাই আমাদের একমাত্র লক্ষ্য।
                </p>
                <div class="footer-social-icons d-flex gap-2 mt-4">
                    <a href="#" class="social-btn facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-btn twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-btn linkedin"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" class="social-btn youtube"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            
            <!-- ২. দ্রুত লিংক -->
            <div class="col-lg-2 col-md-6 ps-lg-5">
                <h5 class="footer-section-title">দ্রুত লিংক</h5>
                <ul class="list-unstyled footer-link-list">
                    <li><a href="<?php echo BASE_URL; ?>index.php"><i class="fas fa-chevron-right me-2"></i>হোম</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right me-2"></i>ডাক্তারবৃন্দ</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right me-2"></i>সেবাসমূহ</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right me-2"></i>যোগাযোগ</a></li>
                    <li class="mt-3"><a href="<?php echo BASE_URL; ?>modules/public/find-prescription.php" class="text-cyan text-decoration-none small fw-bold"><i class="fas fa-search me-1"></i> প্রেসক্রিপশন খুঁজুন</a></li>
                </ul>
            </div>
            
            <!-- ৩. সেবাসমূহ -->
            <div class="col-lg-3 col-md-6 ps-lg-4">
                <h5 class="footer-section-title">সেবাসমূহ</h5>
                <ul class="list-unstyled text-white-50 small footer-service-list">
                    <li class="mb-3 d-flex align-items-center"><i class="fas fa-check-circle me-2 text-cyan"></i>২৪ ঘণ্টা জরুরি বিভাগ</li>
                    <li class="mb-3 d-flex align-items-center"><i class="fas fa-check-circle me-2 text-cyan"></i>আধুনিক ডায়াগনস্টিক</li>
                    <li class="mb-3 d-flex align-items-center"><i class="fas fa-check-circle me-2 text-cyan"></i>ডিজিটাল প্রেসক্রিপশন</li>
                    <li class="mb-3 d-flex align-items-center"><i class="fas fa-check-circle me-2 text-cyan"></i>ফ্রি অনলাইন সিরিয়াল</li>
                </ul>
            </div>
            
            <!-- ৪. যোগাযোগ -->
            <div class="col-lg-3 col-md-6 text-white-50">
                <h5 class="footer-section-title">যোগাযোগ</h5>
                <div class="footer-contact-info">
                    <p class="small mb-3 d-flex align-items-start"><i class="fas fa-map-marker-alt text-cyan me-3 mt-1"></i> কলেজ রোড, বরগুনা।</p>
                    <p class="small mb-3 d-flex align-items-center"><i class="fas fa-phone-alt text-cyan me-3"></i> +০৯৬১৭৫৫৮৮৯৯</p>
                    <p class="small mb-3 d-flex align-items-center"><i class="fas fa-mobile-alt text-cyan me-3"></i> ০১৩৩১৪৩৪৩৪৭</p>
                    <p class="small mb-0 d-flex align-items-center"><i class="fas fa-envelope text-cyan me-3"></i> patientcare@gmail.com</p>
                </div>
            </div>
        </div>
        
        <!-- কপিরাইট এবং ডেভেলপার তথ্য -->
        <div class="footer-bottom border-top border-secondary border-opacity-25 py-4">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="small mb-0 text-white-50">&copy; <?php echo date('Y'); ?> <strong>পেশেন্ট কেয়ার হাসপাতাল</strong>। সর্বস্বত্ব সংরক্ষিত।</p>
                </div>
                <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">
                    <button onclick="showDeveloperInfo()" class="btn-dev-badge shadow-sm">
                        <span class="small opacity-75 text-white">SYSTEM BY:</span>
                        <span class="name fw-bold text-cyan ms-1">BICHARAN ROY</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- অতিরিক্ত CSS স্টাইল -->
<style>
    .hospital-footer {
        background-color: #06182c; /* ডার্ক কর্পোরেট নেভি ব্লু */
        color: #fff;
        border-top: 4px solid #2AA7E5;
    }
    .text-cyan { color: #2AA7E5 !important; }
    .footer-section-title {
        color: #fff;
        font-weight: 700;
        margin-bottom: 25px;
        position: relative;
        font-size: 18px;
    }
    .footer-section-title::after {
        content: '';
        width: 35px;
        height: 3px;
        background: #2AA7E5;
        position: absolute;
        bottom: -8px;
        left: 0;
    }

    /* মালিকের ছবির এনিমেশন */
    .owner-image-wrapper { position: relative; width: 75px; height: 75px; display: flex; align-items: center; justify-content: center; }
    .footer-owner-img { width: 70px !important; height: 70px !important; border-radius: 50% !important; border: 3px solid #2AA7E5; background: #fff; position: relative; z-index: 5; transition: 0.5s; object-fit: cover; }
    .owner-pulse-ring { position: absolute; width: 75px; height: 75px; border-radius: 50%; background: #2AA7E5; animation: owner-pulse 2s infinite; z-index: 1; }
    @keyframes owner-pulse { 0% { transform: scale(0.95); opacity: 0.5; } 100% { transform: scale(1.5); opacity: 0; } }
    .footer-owner-img:hover { transform: scale(1.1) rotate(5deg); }

    /* লিংক ও সোশ্যাল বাটন */
    .footer-link-list li { margin-bottom: 12px; }
    .footer-link-list a { color: rgba(255,255,255,0.6); text-decoration: none; font-size: 14px; transition: 0.3s; }
    .footer-link-list a:hover { color: #2AA7E5; padding-left: 8px; }
    
    .social-btn {
        width: 35px; height: 35px; background: rgba(255,255,255,0.08);
        display: flex; align-items: center; justify-content: center;
        border-radius: 10px; color: #fff; text-decoration: none; transition: 0.3s;
    }
    .social-btn:hover { background: #2AA7E5; transform: translateY(-5px); color: #fff; }

    /* ডেভেলপার ব্যাজ */
    .btn-dev-badge {
        background: rgba(42, 167, 229, 0.1);
        border: 1px solid rgba(42, 167, 229, 0.2);
        padding: 8px 20px;
        border-radius: 50px;
        color: #fff;
        transition: 0.3s;
        cursor: pointer;
    }
    .btn-dev-badge:hover { background: #2AA7E5; transform: translateY(-3px); box-shadow: 0 10px 20px rgba(42, 167, 229, 0.3); }

    @media (max-width: 768px) { .footer-brand-section { text-align: center; display: flex; flex-direction: column; align-items: center; } }
</style>

<!-- SweetAlert2 এবং স্ক্রিপ্টসমূহ -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // ১. চেয়ারম্যান প্রোফাইল পপ-আপ
    function showOwnerInfo() {
        Swal.fire({
            title: '<span class="fw-bold" style="color:#0A2647">চেয়ারম্যান প্রোফাইল</span>',
            html: `
                <div class="text-center">
                    <img src="<?php echo BASE_URL; ?>assets/images/sakib.png" class="rounded-circle shadow-sm border border-3 border-info mb-3" width="120" height="120" style="object-fit:cover;">
                    <h5 class="fw-bold mb-1" style="color:#0A2647">ডাঃ মোঃ নাজমুল সাকিব</h5>
                    <p class="text-primary small fw-bold mb-3 text-uppercase">বিডিএস (ডেন্টিস্ট) এবং হাসপাতাল মালিক</p>
                    <div class="bg-light p-3 rounded-4 mb-3" style="font-size: 13px; font-style: italic;">
                        "বরগুনাবাসীর দোরগোড়ায় আধুনিক চিকিৎসা পৌঁছে দেওয়াই আমাদের মূল লক্ষ্য।"
                    </div>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="tel:01711114534" class="btn btn-primary btn-sm rounded-pill px-4">কল করুন</a>
                        <a href="https://wa.me/8801711114534" target="_blank" class="btn btn-success btn-sm rounded-pill px-4">হোয়াটসঅ্যাপ</a>
                    </div>
                </div>
            `,
            showConfirmButton: false, showCloseButton: true,
            customClass: { popup: 'rounded-5 shadow-lg' }
        });
    }

    // ২. ডেভেলপার প্রোফাইল পপ-আপ
    function showDeveloperInfo() {
        Swal.fire({
            title: '<span class="fw-bold" style="color:#0A2647">ডেভেলপার প্রোফাইল</span>',
            html: `
                <div class="text-center">
                    <img src="<?php echo BASE_URL; ?>assets/images/bicharan.jpg" class="rounded-circle shadow-sm border border-3 border-primary mb-3" width="100" height="100" style="object-fit:cover;">
                    <h5 class="fw-bold mb-1" style="color:#0A2647">বিচরণ চন্দ্র রায়</h5>
                    <p class="text-muted small mb-3">সিনিয়র ফুল-স্ট্যাক ওয়েব ডেভেলপার</p>
                    <div class="text-start bg-light p-3 rounded-4 border small">
                        <p class="mb-2"><strong>স্কিল:</strong> PHP, MySQL, Python, JS</p>
                        <p class="mb-2"><strong>ইমেইল:</strong> fiveg2024@gmail.com</p>
                        <p class="mb-0"><strong>যোগাযোগ:</strong> ০১৭৪৫০৫৬২৬৬</p>
                    </div>
                    <a href="tel:01745056266" class="btn btn-dark btn-sm w-100 rounded-pill mt-3 fw-bold">Hire Developer</a>
                </div>
            `,
            showConfirmButton: false, showCloseButton: true,
            customClass: { popup: 'rounded-5 shadow-lg' }
        });
    }
</script>