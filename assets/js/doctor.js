// doctor.js - ডাক্তার পৃষ্ঠার জন্য আলাদা জাভাস্ক্রিপ্ট

$(document).ready(function() {
    
    // ফিল্টার ফর্ম সাবমিট করার সময় লোডিং দেখান
    $('#filterForm').submit(function() {
        $('.search-btn').html('<i class="fas fa-spinner fa-spin me-2"></i>অপেক্ষা করুন...');
        return true;
    });
    
    // কুইক ফিল্টার ব্যাজ ক্লিক
    $('.quick-filter-badge').click(function() {
        if(!$(this).hasClass('active')) {
            $(this).addClass('active').siblings().removeClass('active');
        }
    });
    
    // ডাক্তার কার্ড হোভার ইফেক্ট
    $('.doctor-card').hover(
        function() {
            $(this).find('.btn-primary').css({
                'transform': 'scale(1.05)',
                'box-shadow': '0 5px 15px rgba(42,167,229,0.4)'
            });
        },
        function() {
            $(this).find('.btn-primary').css({
                'transform': 'scale(1)',
                'box-shadow': 'none'
            });
        }
    );
    
    // স্ক্রলে অ্যানিমেশন
    function checkScroll() {
        $('.doctor-card').each(function() {
            var cardTop = $(this).offset().top;
            var windowBottom = $(window).scrollTop() + $(window).height();
            
            if (windowBottom > cardTop + 100) {
                $(this).addClass('fadeInUp');
            }
        });
    }
    
    $(window).scroll(function() {
        checkScroll();
    });
    
    // লোডে একবার চেক করুন
    checkScroll();
    
});