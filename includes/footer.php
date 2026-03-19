<!-- ==========================================================================
       আধুনিক ও ডাইনামিক ফুটার সেকশন
       ========================================================================== -->
    <footer class="footer mt-5 pt-5 pb-0 shadow-lg" style="background-color: var(--primary-navy); border-top: 4px solid var(--secondary-cyan);">
        <div class="container">
            <div class="row g-4 mb-5">
                
                <!-- ১. প্রতিষ্ঠানের পরিচয় ও মালিকের প্রোফাইল ট্রিগার -->
                <div class="col-lg-4 col-md-6 footer-brand-section">
                    <div class="footer-logo-container mb-3 cursor-pointer" onclick="showOwnerInfo()" title="মালিকের প্রোফাইল দেখতে ক্লিক করুন">
                        <img src="<?php echo BASE_URL; ?>assets/images/sakib.png" alt="Owner Photo" class="footer-main-logo shadow-lg">
                        <div class="logo-pulse-ring"></div>
                    </div>
                    <h4 class="fw-bold text-white mb-2 hospital-title-footer">পেশেন্ট কেয়ার</h4>
                    <p class="text-info small fw-bold text-uppercase mb-3" style="letter-spacing: 1px; font-size: 11px;">হাসপাতাল এন্ড ডায়াগনস্টিক সেন্টার</p>
                    <p class="small text-white-50 lh-lg">
                        আধুনিক প্রযুক্তি ও বিশেষজ্ঞ চিকিৎসকদের সমন্বয়ে আমরা দিচ্ছি বিশ্বমানের স্বাস্থ্যসেবা। সঠিক রোগ নির্ণয় এবং রোগীর দ্রুত সুস্থতাই আমাদের একমাত্র লক্ষ্য। 
                    </p>
                    <div class="footer-social-icons d-flex gap-2 mt-4">
                        <a href="#" class="social-link fb"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link tw"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link ln"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="social-link yt"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <!-- ২. দ্রুত লিংক -->
                <div class="col-lg-2 col-md-6 ps-lg-5">
                    <h5 class="fw-bold text-white mb-4">দ্রুত লিংক</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?php echo BASE_URL; ?>index.php" class="text-white-50 text-decoration-none small footer-link-item"><i class="fas fa-chevron-right me-2 x-small"></i>হোম</a></li>
                        <li class="mb-2"><a href="<?php echo BASE_URL; ?>modules/public/doctors.php" class="text-white-50 text-decoration-none small footer-link-item"><i class="fas fa-chevron-right me-2 x-small"></i>ডাক্তারবৃন্দ</a></li>
                        <li class="mb-2"><a href="<?php echo BASE_URL; ?>#services" class="text-white-50 text-decoration-none small footer-link-item"><i class="fas fa-chevron-right me-2 x-small"></i>সেবাসমূহ</a></li>
                        <li class="mb-2"><a href="<?php echo BASE_URL; ?>#contact" class="text-white-50 text-decoration-none small footer-link-item"><i class="fas fa-chevron-right me-2 x-small"></i>যোগাযোগ</a></li>
                        <li class="mt-3"><a href="<?php echo BASE_URL; ?>modules/public/find-prescription.php" class="text-info text-decoration-none small fw-bold"><i class="fas fa-search me-1"></i> প্রেসক্রিপশন খুঁজুন</a></li>
                    </ul>
                </div>
                
                <!-- ৩. সেবাসমূহ -->
                <div class="col-lg-3 col-md-6 ps-lg-4">
                    <h5 class="fw-bold text-white mb-4">সেবাসমূহ</h5>
                    <ul class="list-unstyled text-white-50 small">
                        <li class="mb-3 d-flex align-items-center"><i class="fas fa-check-circle me-2 text-info"></i>২৪ ঘণ্টা জরুরি বিভাগ</li>
                        <li class="mb-3 d-flex align-items-center"><i class="fas fa-check-circle me-2 text-info"></i>আধুনিক ডায়াগনস্টিক</li>
                        <li class="mb-3 d-flex align-items-center"><i class="fas fa-check-circle me-2 text-info"></i>ডিজিটাল প্রেসক্রিপশন</li>
                        <li class="mb-3 d-flex align-items-center"><i class="fas fa-check-circle me-2 text-info"></i>ফ্রি অনলাইন সিরিয়াল</li>
                    </ul>
                </div>
                
                <!-- ৪. যোগাযোগ -->
                <div class="col-lg-3 col-md-6 text-white-50">
                    <h5 class="fw-bold text-white mb-4">যোগাযোগ</h5>
                    <p class="small mb-2"><i class="fas fa-map-marker-alt text-info me-2"></i> কলেজ রোড, বরগুনা।</p>
                    <p class="small mb-2"><i class="fas fa-phone-alt text-info me-2"></i> +09617558899</p>
                    <p class="small mb-2"><i class="fas fa-mobile-alt text-info me-2"></i> 01331434347</p>
                    <p class="small mb-2"><i class="fas fa-envelope text-info me-2"></i> patientcare@gmail.com</p>
                </div>
            </div>
            
            <!-- কপিরাইট ও ডেভেলপার ক্রেডিট -->
            <div class="copyright border-top border-secondary border-opacity-25 py-4">
                <div class="row align-items-center">
                    <div class="col-md-6 text-center text-md-start">
                        <p class="small mb-0 text-white-50">&copy; <?php echo date('Y'); ?> পেশেন্ট কেয়ার হাসপাতাল এন্ড ডায়াগনস্টিক সেন্টার।</p>
                    </div>
                    <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">
                        <button onclick="showDeveloperInfo()" class="btn btn-dev-profile shadow-sm">
                            <i class="fas fa-code me-2"></i> SYSTEM BY: BICHARAN ROY
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- স্ক্রিপ্টসমূহ -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    // ১. ওনার প্রোফাইল পপ-আপ (Md. Nazmus Sakib)
    function showOwnerInfo() {
        Swal.fire({
            title: '<h6 class="fw-bold text-navy mb-0" style="font-size:18px;">Chairman Profile</h6>',
            html: `
                <div class="text-center p-1">
                    <div class="position-relative d-inline-block mb-2">
                        <img src="<?php echo BASE_URL; ?>assets/images/sakib.png" class="rounded-circle shadow border border-3 border-info" width="110" height="110" style="object-fit:cover; background:#fff;">
                        <span class="position-absolute bottom-0 end-0 badge rounded-circle bg-primary p-1 border border-2 border-white"><i class="fas fa-crown text-white" style="font-size:8px;"></i></span>
                    </div>
                    <h5 class="fw-bold mb-0 text-navy">Md. Nazmus Sakib</h5>
                    <p class="text-primary fw-bold text-uppercase mb-2" style="font-size:11px;">BDS (Dentist) & Hospital Owner</p>
                    <div class="rounded-3 p-2 mb-3 text-white" style="background: linear-gradient(45deg, #0A2647, #2AA7E5); font-size: 11px;">
                        <p class="mb-0 italic">"বরগুনাবাসীর দোরগোড়ায় আধুনিক এবং সাশ্রয়ী চিকিৎসা সেবা পৌঁছে দেওয়াই আমাদের লক্ষ্য।"</p>
                    </div>
                    <div class="text-start mb-3" style="font-size: 12px;">
                        <div class="mb-1 border-bottom pb-1"><i class="fas fa-award text-warning me-2"></i> <strong>পদবী:</strong> চেয়ারম্যান</div>
                        <div class="mb-1 border-bottom pb-1"><i class="fas fa-stethoscope text-info me-2"></i> <strong>স্পেশালিটি:</strong> ডেন্টাল সার্জন</div>
                        <div class="mb-0"><i class="fas fa-phone-alt text-success me-2"></i> <strong>যোগাযোগ:</strong> 01711114534</div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6"><a href="tel:01711114534" class="btn btn-primary btn-sm w-100 rounded-pill shadow-sm">Call Now</a></div>
                        <div class="col-6"><a href="https://wa.me/8801711114534" target="_blank" class="btn btn-success btn-sm w-100 rounded-pill shadow-sm">WhatsApp</a></div>
                    </div>
                </div>
            `,
            showConfirmButton: false,
            showCloseButton: true,
            backdrop: `rgba(10, 38, 71, 0.85)`,
            customClass: { popup: 'compact-profile-popup rounded-4' }
        });
    }

    // ২. ডেভেলপার প্রোফাইল পপ-আপ (Bicharan Roy)
    function showDeveloperInfo() {
        Swal.fire({
            title: '<h6 class="fw-bold text-navy mb-0" style="font-size:18px;">Developer Portfolio</h6>',
            html: `
                <div class="text-center p-1">
                    <img src="<?php echo BASE_URL; ?>assets/images/bicharan.jpg" class="rounded-circle shadow border border-3 border-white mb-2" width="100" height="100" style="object-fit:cover; background:#fff;">
                    <h5 class="fw-bold mb-0" style="color:#0A2647;">Bicharan Chandra Roy</h5>
                    <p class="text-muted small mb-3">Senior Full Stack Web Developer</p>
                    <div class="d-flex justify-content-center gap-1 mb-3 flex-wrap">
                        <span class="badge rounded-pill bg-primary px-2 py-1" style="font-size:9px;">PHP & Laravel</span>
                        <span class="badge rounded-pill bg-success px-2 py-1" style="font-size:9px;">MySQL Expert</span>
                        <span class="badge rounded-pill bg-dark px-2 py-1" style="font-size:9px;">Python</span>
                    </div>
                    <div class="bg-light rounded-3 p-2 text-start border small">
                        <p class="mb-1"><strong>Email:</strong> fiveg2024@gmail.com</p>
                        <p class="mb-0"><strong>Contact:</strong> 01745056266</p>
                    </div>
                    <a href="tel:01745056266" class="btn btn-navy btn-sm w-100 rounded-pill mt-3 text-white fw-bold" style="background:#0A2647;">Hire Developer</a>
                </div>
            `,
            showConfirmButton: false,
            showCloseButton: true,
            backdrop: `rgba(0,0,0,0.85)`,
            customClass: { popup: 'compact-profile-popup rounded-4' }
        });
    }

    $(document).ready(function() {
        // ঘড়ি আপডেট
        function updateClock() {
            const now = new Date();
            let h = now.getHours(); let m = now.getMinutes(); let s = now.getSeconds();
            const ampm = h >= 12 ? 'PM' : 'AM';
            h = h % 12 || 12;
            m = m < 10 ? '0'+m : m; s = s < 10 ? '0'+s : s;
            if(document.getElementById('navClock')) document.getElementById('navClock').innerText = h + ":" + m + ":" + s + " " + ampm;
        }
        setInterval(updateClock, 1000); updateClock();
    });
    </script>

    <style>
    /* পপ-আপ সেটিংস */
    .compact-profile-popup { width: 340px !important; padding: 10px !important; z-index: 99999 !important; }
    .swal2-html-container { margin: 10px 0 0 !important; overflow: hidden !important; }
    
    /* ফুটার লোগো ও এনিমেশন */
    .footer-logo-container { position: relative; width: 65px; height: 65px; display: flex; align-items: center; justify-content: center; }
    .footer-main-logo { width: 60px !important; height: 60px !important; border-radius: 50% !important; border: 3px solid var(--secondary-cyan); background: #fff; position: relative; z-index: 5; transition: 0.5s; cursor: pointer; }
    .footer-main-logo:hover { transform: rotate(360deg) scale(1.1); }
    .logo-pulse-ring { position: absolute; top: 0; left: 0; width: 65px; height: 65px; border-radius: 50%; background: var(--secondary-cyan); animation: footer-pulse 2s infinite; z-index: 1; }
    @keyframes footer-pulse { 0% { transform: scale(0.9); opacity: 0.5; } 100% { transform: scale(1.5); opacity: 0; } }

    /* সোশ্যাল ও ক্রেডিট বাটন */
    .social-link { width: 35px; height: 35px; background: rgba(255,255,255,0.1); color: #fff; display: flex; align-items: center; justify-content: center; border-radius: 10px; text-decoration: none; transition: 0.3s; }
    .social-link:hover { transform: translateY(-5px); background: var(--secondary-cyan); color: #fff !important; }
    .btn-dev-profile { background: linear-gradient(45deg, #0A2647, #2AA7E5); color: #fff !important; border: none; border-radius: 50px; font-size: 10px; font-weight: 700; padding: 8px 18px; transition: 0.3s; }
    .btn-dev-profile:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(42, 167, 229, 0.3); }
    
    @media (max-width: 768px) { .footer-brand-section { text-align: center; display: flex; flex-direction: column; align-items: center; } }
    </style>
    