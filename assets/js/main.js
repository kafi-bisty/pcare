// main.js - ডায়নামিক ফিচার

$(document).ready(function() {
    
    // নেভিগেশন বার স্ক্রল ইফেক্ট
    $(window).scroll(function() {
        if ($(this).scrollTop() > 100) {
            $('.navbar').css({
                'background': 'linear-gradient(135deg, #0A2647 0%, #1A3A5F 100%)',
                'padding': '0.5rem 0',
                'box-shadow': '0 2px 20px rgba(0,0,0,0.2)'
            });
        } else {
            $('.navbar').css({
                'background': 'linear-gradient(135deg, #0A2647 0%, #1A3A5F 100%)',
                'padding': '1rem 0',
                'box-shadow': '0 5px 15px rgba(0,0,0,0.1)'
            });
        }
    });
    
    // ডাক্তার নির্বাচনে সময় স্লট লোড
    $('#doctor_id, #appointment_date').change(function() {
        var doctorId = $('#doctor_id').val();
        var appointmentDate = $('#appointment_date').val();
        
        if (doctorId && appointmentDate) {
            $.ajax({
                url: 'api/get-slots.php',
                method: 'POST',
                data: {
                    doctor_id: doctorId,
                    date: appointmentDate
                },
                success: function(response) {
                    $('#time_slots_container').html(response);
                }
            });
        }
    });
    
    // টাইম স্লট নির্বাচন
    $(document).on('click', '.time-slot', function() {
        if (!$(this).hasClass('disabled')) {
            $('.time-slot').removeClass('selected');
            $(this).addClass('selected');
            $('#selected_time').val($(this).data('time'));
        }
    });
    
    // অ্যাপয়েন্টমেন্ট বুকিং
    $('#appointmentForm').submit(function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: 'api/book-appointment.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    toastr.success('অ্যাপয়েন্টমেন্ট বুকিং সফল হয়েছে!');
                    setTimeout(function() {
                        window.location.href = response.redirect_url;
                    }, 2000);
                } else {
                    toastr.error(response.message);
                }
            }
        });
    });
    
    // রোগী খোঁজা (রিসেপশনের জন্য)
    $('#search_patient').keyup(function() {
        var searchTerm = $(this).val();
        
        if (searchTerm.length > 2) {
            $.ajax({
                url: 'api/search-patient.php',
                method: 'POST',
                data: {search: searchTerm},
                success: function(response) {
                    $('#search_results').html(response).fadeIn();
                }
            });
        } else {
            $('#search_results').fadeOut();
        }
    });
    
    // অ্যাপয়েন্টমেন্ট এপ্রুভ
    $('.approve-btn').click(function() {
        var appointmentId = $(this).data('id');
        
        if (confirm('অ্যাপয়েন্টমেন্টটি এপ্রুভ করবেন?')) {
            $.ajax({
                url: 'api/approve-appointment.php',
                method: 'POST',
                data: {appointment_id: appointmentId},
                success: function(response) {
                    location.reload();
                }
            });
        }
    });
    
    // প্রেসক্রিপশনে ডায়নামিক ওষুধ যোগ
    var medicineCount = 0;
    
    $('#addMedicine').click(function() {
        medicineCount++;
        
        var medicineHtml = `
            <div class="medicine-row" id="medicine_${medicineCount}">
                <div class="row g-2 mb-2">
                    <div class="col-md-4">
                        <input type="text" name="medicines[${medicineCount}][name]" class="form-control" placeholder="ওষুধের নাম" required>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="medicines[${medicineCount}][dosage]" class="form-control" placeholder="ডোজ (১+০+১)">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="medicines[${medicineCount}][duration]" class="form-control" placeholder="সময়কাল">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="medicines[${medicineCount}][instruction]" class="form-control" placeholder="খাওয়ার নিয়ম">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger remove-medicine" data-id="${medicineCount}">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        $('#medicineList').append(medicineHtml);
    });
    
    // ওষুধ রিমুভ
    $(document).on('click', '.remove-medicine', function() {
        var id = $(this).data('id');
        $('#medicine_' + id).remove();
    });
    
    // কাউন্টার অ্যানিমেশন
    function animateCounter(element, start, end, duration) {
        var range = end - start;
        var current = start;
        var increment = range / (duration / 10);
        var timer = setInterval(function() {
            current += increment;
            if (current >= end) {
                clearInterval(timer);
                current = end;
            }
            $(element).text(Math.round(current));
        }, 10);
    }
    
    // স্ক্রলে কাউন্টার শুরু
    var countersStarted = false;
    
    $(window).scroll(function() {
        if (!countersStarted) {
            var countersSection = $('.hero-section .row .col-4 h3');
            if (countersSection.length > 0) {
                var sectionTop = countersSection.offset().top;
                var windowBottom = $(window).scrollTop() + $(window).height();
                
                if (windowBottom > sectionTop + 100) {
                    countersStarted = true;
                    
                    animateCounter('.hero-section .row .col-4:first-child h3', 0, 20, 2000);
                    animateCounter('.hero-section .row .col-4:nth-child(2) h3', 0, 5000, 2000);
                }
            }
        }
    });
    
    // টুলটিপ সক্রিয়
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // অ্যানিমেশন ক্লাস
    $('.doctor-card, .dashboard-card').each(function(index) {
        $(this).css('animation-delay', (index * 0.2) + 's');
    });
    
});