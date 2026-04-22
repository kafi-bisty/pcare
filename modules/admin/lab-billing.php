<?php
session_start();
include_once '../../config/database.php';
include_once '../../includes/header.php';

// লগইন চেক
if (!isset($_SESSION['user_role'])) { header("Location: ../auth/login.php"); exit; }

$test_query = mysqli_query($conn, "SELECT * FROM lab_tests ORDER BY category, test_name");
?>

<style>
    :root { --navy: #0A2647; --cyan: #2AA7E5; }
    body { background-color: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
    .billing-card { border: none; border-radius: 15px; box-shadow: 0 5px 25px rgba(0,0,0,0.1); }
    .memo-pad { background: white; border: 1px solid #ddd; padding: 25px; border-radius: 15px; position: sticky; top: 20px; }
    .test-list { max-height: 60vh; overflow-y: auto; }
    .bg-navy { background-color: var(--navy) !important; }
    .btn-cyan { background-color: var(--cyan); color: white; border: none; }
    .btn-cyan:hover { background-color: #2391c7; color: white; }
    
    @media print {
        .no-print, .navbar, footer { display: none !important; }
        .memo-pad { border: none; box-shadow: none; width: 100%; position: static; }
        body { background: white; }
    }
</style>

<div class="container-fluid py-4 no-print">
    <div class="row g-4">
        <!-- ১. টেস্ট সিলেকশন এরিয়া -->
        <div class="col-lg-7">
            <div class="card billing-card h-100">
                <div class="card-header bg-navy text-white d-flex justify-content-between align-items-center p-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-microscope me-2 text-cyan"></i>ল্যাব টেস্ট লিস্ট</h5>
                    <input type="text" id="testSearch" class="form-control form-control-sm w-50 rounded-pill" placeholder="টেস্টের নাম দিয়ে খুঁজুন...">
                </div>
                <div class="card-body p-0">
                    <div class="test-list">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light sticky-top">
                                <tr><th>টেস্টের নাম</th><th>ক্যাটাগরি</th><th>মূল্য</th><th>অ্যাকশন</th></tr>
                            </thead>
                            <tbody id="testTable">
                                <?php while($row = mysqli_fetch_assoc($test_query)): ?>
                                <tr onclick="addToBill('<?= $row['test_name']; ?>', <?= $row['price']; ?>)" style="cursor:pointer">
                                    <td class="fw-bold text-navy"><?= $row['test_name']; ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= $row['category']; ?></span></td>
                                    <td class="fw-bold">৳ <?= number_format($row['price'], 0); ?></td>
                                    <td><button class="btn btn-sm btn-cyan"><i class="fas fa-plus"></i></button></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ২. মেমো জেনারেটর এরিয়া -->
        <div class="col-lg-5">
            <div class="memo-pad shadow-lg">
                <div class="text-center border-bottom pb-3 mb-3">
                    <h4 class="fw-bold text-navy mb-0">পেশেন্ট কেয়ার হাসপাতাল</h4>
                    <p class="small text-muted mb-0">এন্ড ডায়াগনস্টিক সেন্টার</p>
                    <small>কলেজ রোড, বরগুনা। ফোন: ০১৩৩১৪ ৩৪৩৪৭</small>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-8"><input type="text" id="p_name" class="form-control form-control-sm" placeholder="রোগীর নাম"></div>
                    <div class="col-4"><input type="number" id="p_age" class="form-control form-control-sm" placeholder="বয়স"></div>
                </div>

                <table class="table table-sm">
                    <thead><tr class="small text-muted border-bottom"><th>বিবরণ</th><th class="text-end">মূল্য</th></tr></thead>
                    <tbody id="billItems"></tbody>
                </table>

                <div class="mt-4 border-top pt-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>মোট বিল (Total):</span>
                        <span class="fw-bold">৳ <span id="total_fig">0</span></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-danger">ছাড় (Less Discount %):</span>
                        <input type="number" id="discount_input" class="form-control form-control-sm w-25 text-end" value="0" onkeyup="calculateBill()">
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center text-navy fw-bold h5">
                        <span>নিট বিল (Net Figure):</span>
                        <span>৳ <span id="net_fig">0</span></span>
                    </div>
                </div>

                <div class="mt-4 d-grid gap-2">
                    <button class="btn btn-navy py-2 fw-bold" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>প্রিন্ট মেমো ও সেভ
                    </button>
                    <button class="btn btn-outline-danger btn-sm border-0" onclick="location.reload()">মুছে ফেলুন</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let bill = [];

function addToBill(name, price) {
    bill.push({name, price});
    renderBill();
}

function renderBill() {
    const list = document.getElementById('billItems');
    list.innerHTML = '';
    let total = 0;
    
    bill.forEach((item, index) => {
        total += item.price;
        list.innerHTML += `
            <tr class="small">
                <td>${item.name} <i class="fas fa-times-circle text-danger ms-2" onclick="removeFromBill(${index})" style="cursor:pointer"></i></td>
                <td class="text-end">৳ ${item.price}</td>
            </tr>`;
    });
    
    document.getElementById('total_fig').innerText = total;
    calculateBill();
}

function removeFromBill(index) {
    bill.splice(index, 1);
    renderBill();
}

function calculateBill() {
    const total = parseFloat(document.getElementById('total_fig').innerText);
    const discPercent = parseFloat(document.getElementById('discount_input').value) || 0;
    const discountAmount = (total * discPercent) / 100;
    const net = total - discountAmount;
    
    document.getElementById('net_fig').innerText = Math.round(net);
}

// লাইভ সার্চ
document.getElementById('testSearch').addEventListener('keyup', function() {
    let filter = this.value.toUpperCase();
    let rows = document.getElementById('testTable').getElementsByTagName('tr');
    for (let row of rows) {
        row.style.display = row.innerText.toUpperCase().includes(filter) ? "" : "none";
    }
});
</script>

<?php include_once '../../includes/footer.php'; ?>