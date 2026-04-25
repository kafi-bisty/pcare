<?php
session_start();
include_once '../../config/database.php';

// ১. এটি হলো ব্যাকএন্ড লজিক যা ডাটাবেজে ইনকাম যোগ করবে (AJAX দিয়ে কাজ করবে)
if (isset($_POST['ajax_action']) && $_POST['ajax_action'] == 'save_lab_income') {
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);
    $p_name = mysqli_real_escape_string($conn, $_POST['patient_name']);
    $receipt_no = "LB-" . time();
    $date = date('Y-m-d');
    $desc = "ল্যাব বিল (রোগী: $p_name)";

    $sql = "INSERT INTO hospital_accounts (type, category, amount, receipt_no, description, date) 
            VALUES ('income', 'ল্যাব (Lab)', '$amount', '$receipt_no', '$desc', '$date')";
    
    if (mysqli_query($conn, $sql)) {
        echo json_encode(['status' => 'success', 'receipt' => $receipt_no]);
    } else {
        echo json_encode(['status' => 'error']);
    }
    exit;
}

include_once '../../includes/header.php';

// ২. সিকিউরিটি চেক
if (!isset($_SESSION['user_role'])) { header("Location: ../auth/login.php"); exit; }

// ৩. টেস্টের তালিকা আনা
$test_query = mysqli_query($conn, "SELECT * FROM lab_tests ORDER BY category, test_name");
?>

<style>
    :root { --navy: #0A2647; --cyan: #2AA7E5; --light: #f8f9fa; }
    body { background-color: var(--light); font-family: 'Segoe UI', sans-serif; }
    .billing-card { border: none; border-radius: 15px; box-shadow: 0 5px 25px rgba(0,0,0,0.05); }
    .memo-pad { background: white; border: 1px solid #ddd; padding: 25px; border-radius: 15px; position: sticky; top: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    .test-list-scroll { max-height: 65vh; overflow-y: auto; }
    .test-row { cursor: pointer; transition: 0.2s; }
    .test-row:hover { background: #eef9ff !important; }
    @media print { .no-print { display: none !important; } }
</style>

<div class="container-fluid py-4 no-print">
    <div class="row g-4">
        <!-- টেস্ট সিলেকশন -->
        <div class="col-lg-7">
            <div class="card billing-card h-100">
                <div class="card-header bg-navy text-white d-flex justify-content-between align-items-center p-3" style="background-color: #0A2647;">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-microscope me-2 text-cyan"></i>ল্যাব টেস্ট নির্বাচন</h5>
                    <input type="text" id="testSearch" class="form-control form-control-sm w-50 rounded-pill shadow-none" placeholder="খুঁজুন...">
                </div>
                <div class="card-body p-0">
                    <div class="test-list-scroll">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr><th>টেস্টের নাম</th><th>মূল্য</th><th>যোগ</th></tr>
                            </thead>
                            <tbody id="testTable">
                                <?php while($row = mysqli_fetch_assoc($test_query)): ?>
                                <tr class="test-row" onclick="addToBill('<?= $row['test_name']; ?>', <?= $row['price']; ?>)">
                                    <td class="fw-bold text-navy"><?= $row['test_name']; ?> <br><small class="text-muted"><?= $row['category']; ?></small></td>
                                    <td class="fw-bold text-success">৳<?= number_format($row['price'], 0); ?></td>
                                    <td><button class="btn btn-sm btn-outline-cyan rounded-circle"><i class="fas fa-plus"></i></button></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- মেমো প্যাড -->
        <div class="col-lg-5">
            <div class="memo-pad">
                <div class="text-center border-bottom pb-2 mb-3">
                    <h4 class="fw-bold text-navy mb-0">পেশেন্ট কেয়ার হাসপাতাল</h4>
                    <p class="small text-muted mb-0">মানি রিসিট (ডায়াগনস্টিক)</p>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-7"><input type="text" id="p_name" class="form-control form-control-sm bg-light border-0" placeholder="রোগীর নাম"></div>
                    <div class="col-5"><input type="number" id="p_phone" class="form-control form-control-sm bg-light border-0" placeholder="ফোন নম্বর"></div>
                    <div class="col-4"><input type="number" id="p_age" class="form-control form-control-sm bg-light border-0" placeholder="বয়স"></div>
                    <div class="col-8"><input type="text" id="p_address" class="form-control form-control-sm bg-light border-0" placeholder="ঠিকানা"></div>
                </div>

                <table class="table table-sm">
                    <tbody id="billItems"></tbody>
                </table>

                <div class="mt-4 border-top pt-3 bg-light p-3 rounded-4">
                    <div class="d-flex justify-content-between mb-1"><span>Total:</span><span id="total_fig">0</span></div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small text-info">Discount %:</span>
                        <input type="number" id="disc_percent" class="form-control form-control-sm w-25 border-0 text-end" value="0" onkeyup="calculateBill()">
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small text-danger">Discount ৳:</span>
                        <input type="number" id="disc_fixed" class="form-control form-control-sm w-25 border-0 text-end" value="0" onkeyup="calculateBill()">
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between text-navy fw-bold h5 mb-0">
                        <span>Net Payable:</span>
                        <span>৳ <span id="net_fig">0</span></span>
                    </div>
                </div>

                <button class="btn btn-primary w-100 mt-4 py-3 rounded-pill fw-bold shadow" id="savePrintBtn" onclick="saveAndPrint()">
                    <i class="fas fa-save me-2"></i>SAVE & PRINT MEMO
                </button>
            </div>
        </div>
    </div>
</div>

<!-- প্রিন্ট এরিয়া -->
<div id="printArea" style="display:none;">
    <div style="padding: 40px; font-family: sans-serif;">
        <div style="text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px;">
            <h2 style="margin:0;">পেশেন্ট কেয়ার হাসপাতাল</h2>
            <p style="margin:0;">এন্ড ডায়াগনস্টিক সেন্টার, বরগুনা। ফোন: ০১৩৩১৪ ৩৪৩৪৭</p>
            <div style="margin-top:10px; background:#000; color:#fff; display:inline-block; padding:3px 25px; border-radius:50px;">মানি রিসিট (ল্যাব)</div>
        </div>
        <div style="display:flex; justify-content:space-between; margin-bottom: 20px;">
            <div>রোগী: <strong id="pr_name"></strong><br>বয়স: <strong id="pr_age"></strong> | ফোন: <strong id="pr_phone"></strong></div>
            <div style="text-align:right;">তারিখ: <strong><?= date('d/m/Y'); ?></strong><br>রিসিট: <strong id="pr_receipt"></strong></div>
        </div>
        <table style="width:100%; border-collapse: collapse;">
            <thead><tr style="border-bottom: 1px solid #000; background: #f0f0f0;"><th style="text-align:left; padding:10px;">Investigation Name</th><th style="text-align:right; padding:10px;">Price</th></tr></thead>
            <tbody id="pr_items"></tbody>
        </table>
        <div style="float:right; width:200px; margin-top:20px;">
            <div style="display:flex; justify-content:space-between;"><span>Net Total:</span><strong id="pr_net"></strong></div>
        </div>
        <div style="margin-top:100px; display:flex; justify-content:space-between;">
            <div style="border-top:1px solid #000; width:150px; text-align:center;">Signature</div>
            <div style="border-top:1px solid #000; width:150px; text-align:center;">Cashier</div>
        </div>
    </div>
</div>

<script>
let cart = [];

function addToBill(name, price) {
    cart.push({name, price});
    renderBill();
}

function renderBill() {
    const list = document.getElementById('billItems');
    list.innerHTML = '';
    let total = 0;
    cart.forEach((item, index) => {
        total += item.price;
        list.innerHTML += `<tr><td class="py-2 fw-bold text-navy">${item.name}</td><td class="text-end">৳ ${item.price} <i class="fas fa-times-circle text-danger ms-2" onclick="removeFromBill(${index})" style="cursor:pointer"></i></td></tr>`;
    });
    document.getElementById('total_fig').innerText = total;
    calculateBill();
}

function removeFromBill(index) { cart.splice(index, 1); renderBill(); }

function calculateBill() {
    const total = parseFloat(document.getElementById('total_fig').innerText) || 0;
    const net = total - (total * (document.getElementById('disc_percent').value / 100)) - document.getElementById('disc_fixed').value;
    document.getElementById('net_fig').innerText = Math.round(net);
}

// ★ মেইন ফাংশন: এটি ডাটা সেভ করবে এবং প্রিন্ট করবে
function saveAndPrint() {
    const pName = document.getElementById('p_name').value;
    const netAmount = document.getElementById('net_fig').innerText;

    if(!pName || cart.length === 0) { alert("রোগীর নাম ও টেস্ট সিলেক্ট করুন!"); return; }

    const btn = document.getElementById('savePrintBtn');
    btn.disabled = true; btn.innerHTML = "Saving...";

    // ১. ডাটাবেজে ইনকাম হিসেবে সেভ করা (AJAX)
    let formData = new FormData();
    formData.append('ajax_action', 'save_lab_income');
    formData.append('amount', netAmount);
    formData.append('patient_name', pName);

    fetch('lab-billing.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(res => {
        if(res.status === 'success') {
            // ২. প্রিন্ট প্রিভিউ সেটআপ
            document.getElementById('pr_name').innerText = pName;
            document.getElementById('pr_age').innerText = document.getElementById('p_age').value || 'N/A';
            document.getElementById('pr_phone').innerText = document.getElementById('p_phone').value || 'N/A';
            document.getElementById('pr_receipt').innerText = res.receipt;
            document.getElementById('pr_net').innerText = "৳ " + netAmount;
            
            let prItems = '';
            cart.forEach(item => { prItems += `<tr><td style="padding:10px; border-bottom:1px solid #eee;">${item.name}</td><td style="text-align:right; padding:10px; border-bottom:1px solid #eee;">৳ ${item.price}</td></tr>`; });
            document.getElementById('pr_items').innerHTML = prItems;

            const win = window.open('', '', 'height=800,width=900');
            win.document.write('<html><body>' + document.getElementById('printArea').innerHTML + '</body></html>');
            win.document.close();
            setTimeout(() => { win.print(); location.reload(); }, 500);
        } else {
            alert("হিসাব সেভ করতে সমস্যা হয়েছে!");
            btn.disabled = false; btn.innerHTML = "SAVE & PRINT MEMO";
        }
    });
}

// সার্চ ফিল্টার
document.getElementById('testSearch').addEventListener('keyup', function() {
    let filter = this.value.toUpperCase();
    let rows = document.querySelectorAll('#testTable tr');
    rows.forEach(row => { row.style.display = row.innerText.toUpperCase().includes(filter) ? "" : "none"; });
});
</script>

<?php include_once '../../includes/footer.php'; ?>