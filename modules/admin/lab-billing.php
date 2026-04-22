<?php
session_start();
include_once '../../config/database.php';
include_once '../../includes/header.php';
$test_query = mysqli_query($conn, "SELECT * FROM lab_tests ORDER BY category, test_name");
?>

<style>
    :root { --navy: #0A2647; --cyan: #2AA7E5; --light-bg: #f4f7f6; }
    body { background: var(--light-bg); font-family: 'Segoe UI', sans-serif; }
    .billing-card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); overflow: hidden; }
    .memo-pad { background: #fff; border-radius: 15px; padding: 30px; position: sticky; top: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); }
    .test-row:hover { background: #eef9ff !important; cursor: pointer; border-left: 4px solid var(--cyan); }
    @media print { .no-print { display: none !important; } }
</style>

<div class="container-fluid py-4 no-print">
    <div class="row g-4">
        <!-- টেস্ট লিস্ট -->
        <div class="col-lg-7">
            <div class="card billing-card">
                <div class="card-header bg-navy text-white p-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">ল্যাব টেস্ট সিলেকশন</h5>
                    <input type="text" id="searchTest" class="form-control form-control-sm w-50 rounded-pill" placeholder="খুঁজুন...">
                </div>
                <div class="card-body p-0">
                    <div style="max-height: 70vh; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr><th>টেস্টের নাম</th><th>বিভাগ</th><th>মূল্য</th><th>যোগ</th></tr>
                            </thead>
                            <tbody id="testTable">
                                <?php while($row = mysqli_fetch_assoc($test_query)): ?>
                                <tr class="test-row" onclick="addToCart('<?= $row['test_name']; ?>', <?= $row['price']; ?>)">
                                    <td class="fw-bold text-navy"><?= $row['test_name']; ?></td>
                                    <td><small class="badge bg-light text-dark border"><?= $row['category']; ?></small></td>
                                    <td class="fw-bold text-success">৳<?= number_format($row['price'], 2); ?></td>
                                    <td><i class="fas fa-plus-circle text-cyan"></i></td>
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
                    <div class="col-md-6"><input type="text" id="p_name" class="form-control form-control-sm bg-light border-0" placeholder="রোগীর নাম" required></div>
                    <div class="col-md-3"><input type="number" id="p_age" class="form-control form-control-sm bg-light border-0" placeholder="বয়স"></div>
                    <div class="col-md-3">
                        <select id="p_sex" class="form-select form-select-sm bg-light border-0">
                            <option value="Male">Male</option><option value="Female">Female</option>
                        </select>
                    </div>
                </div>

                <table class="table table-sm small">
                    <tbody id="cartItems"></tbody>
                </table>

                <div class="bg-light p-3 rounded-4 mt-3">
                    <div class="d-flex justify-content-between"><span>Subtotal:</span><span class="fw-bold">৳ <span id="subtotal">0.00</span></span></div>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <span class="text-danger small">Discount (%):</span>
                        <input type="number" id="discount" class="form-control form-control-sm w-25 border-danger" value="0" onkeyup="calculateTotal()">
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between h5 fw-bold text-navy"><span>Net Total:</span><span>৳ <span id="net_total">0.00</span></span></div>
                </div>

                <button class="btn btn-primary w-100 mt-4 py-2 rounded-pill fw-bold shadow" id="btnSavePrint" onclick="saveAndPrint()">
                    <i class="fas fa-save me-2"></i>SAVE & PRINT RECEIPT
                </button>
            </div>
        </div>
    </div>
</div>

<!-- প্রিন্ট এরিয়া -->
<div id="printArea" style="display:none;">
    <div style="padding: 40px; font-family: 'Segoe UI', sans-serif;">
        <div style="text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px;">
            <h2 style="margin:0;">পেশেন্ট কেয়ার হাসপাতাল</h2>
            <p style="margin:0;">কলেজ রোড, বরগুনা। মোবাইল: ০১৩৩১৪ ৩৪৩৪৭</p>
            <div style="margin-top:10px; background:#000; color:#fff; display:inline-block; padding:2px 20px; border-radius:50px;">LAB MONEY RECEIPT</div>
        </div>
        <div style="display:flex; justify-content:space-between; margin-bottom: 20px;">
            <div>রোগী: <strong id="pr_name"></strong><br>বয়স/লিঙ্গ: <strong id="pr_age_sex"></strong></div>
            <div style="text-align:right;">তারিখ: <strong><?= date('d/m/Y'); ?></strong><br>বিল নং: <strong id="pr_bill_no"></strong></div>
        </div>
        <table style="width:100%; border-collapse: collapse;">
            <thead><tr style="border-bottom: 1px solid #000;"><th style="text-align:left; padding:8px;">পরীক্ষার নাম</th><th style="text-align:right; padding:8px;">মূল্য</th></tr></thead>
            <tbody id="pr_items"></tbody>
        </table>
        <div style="float:right; width:200px; margin-top:20px;">
            <div style="display:flex; justify-content:space-between;"><span>Total:</span><span id="pr_total"></span></div>
            <div style="display:flex; justify-content:space-between;"><span>Discount:</span><span id="pr_disc"></span></div>
            <hr><div style="display:flex; justify-content:space-between; font-weight:bold; font-size:18px;"><span>Net:</span><span id="pr_net"></span></div>
        </div>
    </div>
</div>

<script>
let cart = [];

function addToCart(name, price) {
    cart.push({name, price});
    renderCart();
}

function renderCart() {
    const cartBody = document.getElementById('cartItems');
    cartBody.innerHTML = '';
    let total = 0;
    cart.forEach((item, index) => {
        total += item.price;
        cartBody.innerHTML += `<tr><td class="py-2 text-navy fw-bold">${item.name}</td><td class="text-end">৳${item.price.toFixed(2)} <i class="fas fa-times-circle text-danger ms-2" onclick="removeFromCart(${index})" style="cursor:pointer"></i></td></tr>`;
    });
    document.getElementById('subtotal').innerText = total.toFixed(2);
    calculateTotal();
}

function removeFromCart(index) { cart.splice(index, 1); renderCart(); }

function calculateTotal() {
    const subtotal = parseFloat(document.getElementById('subtotal').innerText);
    const disc = parseFloat(document.getElementById('discount').value) || 0;
    const net = subtotal - (subtotal * disc / 100);
    document.getElementById('net_total').innerText = net.toFixed(2);
}

function saveAndPrint() {
    const pName = document.getElementById('p_name').value;
    if (!pName || cart.length === 0) { alert("রোগীর নাম এবং টেস্ট সিলেক্ট করুন!"); return; }

    const data = {
        p_name: pName,
        p_age: document.getElementById('p_age').value,
        p_sex: document.getElementById('p_sex').value,
        total: document.getElementById('subtotal').innerText,
        discount: document.getElementById('discount').value,
        net: document.getElementById('net_total').innerText,
        tests: cart
    };

    const btn = document.getElementById('btnSavePrint');
    btn.disabled = true;
    btn.innerHTML = "Saving...";

    fetch('save-lab-bill.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            triggerPrint(res.bill_no, data);
            setTimeout(() => location.reload(), 2000);
        } else { alert("Error saving bill!"); btn.disabled = false; }
    });
}

function triggerPrint(billNo, data) {
    document.getElementById('pr_name').innerText = data.p_name;
    document.getElementById('pr_age_sex').innerText = data.p_age + "Y / " + data.p_sex;
    document.getElementById('pr_bill_no').innerText = billNo;
    
    let itemsHtml = '';
    data.tests.forEach(t => { itemsHtml += `<tr><td style="padding:5px; border-bottom:1px dashed #eee;">${t.name}</td><td style="text-align:right; padding:5px; border-bottom:1px dashed #eee;">৳${parseFloat(t.price).toFixed(2)}</td></tr>`; });
    document.getElementById('pr_items').innerHTML = itemsHtml;
    
    document.getElementById('pr_total').innerText = "৳" + data.total;
    document.getElementById('pr_disc').innerText = data.discount + "%";
    document.getElementById('pr_net').innerText = "৳" + data.net;

    const content = document.getElementById('printArea').innerHTML;
    const win = window.open('', '', 'height=700,width=900');
    win.document.write('<html><head><title>Print</title></head><body>' + content + '</body></html>');
    win.document.close();
    win.print();
}

// সার্চ লজিক
document.getElementById('searchTest').addEventListener('keyup', function() {
    let filter = this.value.toUpperCase();
    let tr = document.querySelectorAll('#testTable tr');
    tr.forEach(row => {
        let text = row.cells[0].innerText.toUpperCase();
        row.style.display = text.indexOf(filter) > -1 ? '' : 'none';
    });
});
</script>

<?php include_once '../../includes/footer.php'; ?>