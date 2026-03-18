<?php
// ১. প্রয়োজনীয় ফাইল এবং সেশন চেক
include_once '../../config/database.php';
include_once '../../config/constants.php';
include_once '../../config/functions.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'doctor') {
    header("Location: ../auth/staff-login.php"); 
    exit;
}

$doctor_id = $_SESSION['user_id'];
if (!isset($_GET['appointment_id'])) { header("Location: today-patients.php"); exit; }
$app_id = mysqli_real_escape_string($conn, $_GET['appointment_id']);

// ২. তথ্য সংগ্রহ
$app_query = mysqli_query($conn, "SELECT a.*, d.name as doc_name, d.qualification as doc_qual, d.chamber_no 
                                FROM appointments a 
                                JOIN doctors d ON a.doctor_id = d.id 
                                WHERE a.id = '$app_id'");
$patient = mysqli_fetch_assoc($app_query);

$med_list = mysqli_query($conn, "SELECT * FROM medicines_list ORDER BY medicine_name ASC");
$test_list = mysqli_query($conn, "SELECT * FROM lab_tests_list ORDER BY test_name ASC");

// ৩. সেভ/আপডেট লজিক
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['final_save'])) {
    $symptoms = mysqli_real_escape_string($conn, $_POST['symptoms']);
    $diagnosis = mysqli_real_escape_string($conn, $_POST['diagnosis']);
    $advice = mysqli_real_escape_string($conn, $_POST['advice']);
    $pulse = mysqli_real_escape_string($conn, $_POST['pulse']);
    $bp = mysqli_real_escape_string($conn, $_POST['bp']);
    $temp = mysqli_real_escape_string($conn, $_POST['temp']);
    $weight = mysqli_real_escape_string($conn, $_POST['weight']);
    
    $medicines = mysqli_real_escape_string($conn, $_POST['medicines_output']); 
    $tests = mysqli_real_escape_string($conn, $_POST['tests_output']);
    
    $p_id = (!empty($patient['patient_id']) && $patient['patient_id'] != 'NULL') ? $patient['patient_id'] : "NULL";

    $check_exists = mysqli_query($conn, "SELECT id FROM prescriptions WHERE appointment_id = '$app_id'");
    $is_update = (mysqli_num_rows($check_exists) > 0);

    if ($is_update) {
        $sql = "UPDATE prescriptions SET symptoms='$symptoms', diagnosis='$diagnosis', medicines='$medicines', advice='$advice', pulse='$pulse', bp='$bp', temperature='$temp', weight='$weight' WHERE appointment_id = '$app_id'";
    } else {
        $sql = "INSERT INTO prescriptions (appointment_id, doctor_id, patient_id, symptoms, diagnosis, medicines, advice, pulse, bp, temperature, weight) 
                VALUES ('$app_id', '$doctor_id', $p_id, '$symptoms', '$diagnosis', '$medicines', '$advice', '$pulse', '$bp', '$temp', '$weight')";
    }
    
    if (mysqli_query($conn, $sql)) {
        $last_id = ($is_update) ? mysqli_fetch_assoc($check_exists)['id'] : mysqli_insert_id($conn);
        mysqli_query($conn, "UPDATE appointments SET status = 'completed' WHERE id = '$app_id'");

        if (!$is_update) {
            $today_day_name = date('l'); 
            mysqli_query($conn, "UPDATE doctor_schedules SET current_serial = current_serial + 1 WHERE doctor_id = '$doctor_id' AND day_of_week = '$today_day_name' AND is_available = 1");
        }

        $_SESSION['prescription_saved'] = "yes";
        $_SESSION['p_id_for_link'] = $last_id;
        $_SESSION['p_phone'] = $patient['patient_phone'];
        $_SESSION['p_name'] = $patient['patient_name'];
        header("Location: today-patients.php"); exit;
    }
}

include_once '../../includes/header.php';
?>

<style>
    :root { --navy: #0A2647; --cyan: #2AA7E5; }
    body { background-color: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
    .work-grid { display: grid; grid-template-columns: 260px 1fr 340px; gap: 15px; padding: 10px; height: 92vh; }
    .sidebar-modern { background: #fff; border-radius: 15px; padding: 15px; border: 1px solid #ddd; overflow-y: auto; }
    .prescription-pad { background: #fff; padding: 40px; box-shadow: 0 0 30px rgba(0,0,0,0.1); position: relative; height: 100%; overflow-y: auto; border-radius: 5px; }
    .rx-icon { font-size: 2.2rem; font-weight: 900; color: var(--navy); border-bottom: 2px solid var(--cyan); width: 60px; margin-bottom: 20px; }
    .input-flat { border: none; border-bottom: 1px dashed #ccc; width: 100%; padding: 5px; outline: none; background: transparent; font-size: 13px; }
    .active-row { background-color: #f0f7ff !important; }
    .tag-btn { cursor: pointer; padding: 4px 10px; background: #eee; border-radius: 50px; font-size: 11px; font-weight: 600; display: inline-block; margin: 2px; border: 1px solid #ddd; }
    .tag-btn:hover { background: var(--cyan); color: #fff; border-color: var(--cyan); }
    .scroll-box { height: 180px; overflow-y: auto; border: 1px solid #eee; border-radius: 8px; margin-bottom: 10px; }
    .clickable { cursor: pointer; display: block; padding: 6px 10px; border-bottom: 1px solid #f9f9f9; font-size: 13px; }
    .clickable:hover { background: #f0f7ff; color: var(--cyan); }
</style>

<form action="" method="POST" id="mainPresForm" onsubmit="prepareData()">
    <div class="work-grid">
        <!-- ১. বাম পাশ: ভাইটালস ও কমপ্লেইন -->
        <aside class="sidebar-modern shadow-sm">
            <h6 class="fw-bold text-navy border-bottom pb-2 mb-3 uppercase">O/E (Vitals)</h6>
            <input type="text" name="pulse" class="input-flat mb-2" placeholder="Pulse (72/min)">
            <input type="text" name="bp" class="input-flat mb-2" placeholder="BP (120/80)">
            <input type="text" name="temp" class="input-flat mb-2" placeholder="Temp (98.4)">
            <input type="text" name="weight" class="input-flat mb-2" placeholder="Weight (kg)">

            <h6 class="fw-bold text-navy border-bottom pb-2 mt-4 mb-2 uppercase">Diagnosis</h6>
            <textarea name="diagnosis" class="form-control border-0 bg-light small" rows="2" placeholder="রোগ নির্ণয়..."></textarea>
            
            <h6 class="fw-bold text-navy border-bottom pb-2 mt-4 mb-2 uppercase">Complaints</h6>
            <textarea name="symptoms" class="form-control border-0 bg-light small" rows="4" placeholder="রোগীর সমস্যা..."></textarea>
        </aside>

        <!-- ২. মাঝখান: মেইন ডিজিটাল প্যাড -->
        <main class="prescription-pad shadow">
            <div class="row mb-4 border-bottom pb-3 align-items-center">
                <div class="col-7">
                    <h4 class="fw-bold text-navy mb-0">ডাাঃ <?php echo $_SESSION['user_name']; ?></h4>
                    <small class="text-muted small"><?php echo $patient['doc_qual']; ?></small>
                </div>
                <div class="col-5 text-end">
                    <h5 class="fw-bold text-navy mb-0">Patient Care Hospital</h5>
                    <small class="text-muted">Barguna, Bangladesh</small>
                </div>
            </div>

            <!-- রোগীর তথ্য বার -->
            <div class="row mb-3 small fw-bold bg-light p-2 rounded mx-0 shadow-sm border">
                <div class="col-4">Name: <?php echo $patient['patient_name']; ?></div>
                <div class="col-2 text-center">Age: <?php echo $patient['age']; ?></div>
                <div class="col-2 text-center">Sex: <?php echo $patient['gender']; ?></div>
                <div class="col-4 text-end">Date: <?php echo date('d-m-Y'); ?></div>
            </div>

            <div class="rx-icon">R<sub>x</sub></div>
            
            <!-- ওষুধের মূল টেবিল -->
            <table class="table table-borderless align-middle">
                <thead>
                    <tr class="small text-muted border-bottom">
                        <th width="40%">Medicine Name</th>
                        <th width="15%">Dose</th>
                        <th width="20%">Rule</th>
                        <th width="15%">Duration</th>
                        <th width="10%"></th>
                    </tr>
                </thead>
                <tbody id="medicineTable">
                    <!-- জাভাস্ক্রিপ্ট দিয়ে রো যোগ হবে -->
                </tbody>
            </table>

            <!-- ল্যাব টেস্ট তালিকা -->
            <div class="mt-4">
                <h6 class="fw-bold text-navy border-bottom pb-1 mb-2" style="width: 130px;">Investigations:</h6>
                <ul id="testDisplayList" class="list-unstyled fw-bold small text-muted ps-2"></ul>
            </div>

            <div class="mt-5 pt-3 border-top">
                <label class="fw-bold small text-navy">Advice / বিশেষ পরামর্শ:</label>
                <textarea name="advice" class="form-control border-0 shadow-none bg-transparent" rows="3" placeholder="পরামর্শ লিখুন..."></textarea>
            </div>

            <!-- হিডেন ফিল্ডস -->
            <input type="hidden" name="medicines_output" id="medicines_output">
            <input type="hidden" name="tests_output" id="tests_output">
        </main>

        <!-- ৩. ডান পাশ: স্মার্ট টুলস (ওষুধ, টেস্ট ও ডোজ সিলেক্টর) -->
        <aside class="sidebar-modern shadow-sm">
            <!-- ট্যাব -->
            <ul class="nav nav-pills nav-justified mb-3 bg-light rounded-pill p-1 shadow-sm">
                <li class="nav-item"><button type="button" class="nav-link active small py-1" data-bs-toggle="tab" data-bs-target="#medTab">Drugs</button></li>
                <li class="nav-item"><button type="button" class="nav-link small py-1" data-bs-toggle="tab" data-bs-target="#testTab">Tests</button></li>
            </ul>

            <div class="tab-content">
                <!-- ড্রাগ লিস্ট -->
                <div class="tab-pane fade show active" id="medTab">
                    <input type="text" id="mSearch" class="form-control form-control-sm rounded-pill mb-2" placeholder="ওষুধ খুঁজুন...">
                    <div class="scroll-box">
                        <?php while($m = mysqli_fetch_assoc($med_list)): ?>
                            <span class="clickable m-item" onclick="addMedicine('<?php echo $m['medicine_name']; ?>')">
                                <i class="fas fa-plus-circle text-success me-1"></i><?php echo $m['medicine_name']; ?>
                            </span>
                        <?php endwhile; ?>
                    </div>
                </div>
                <!-- টেস্ট লিস্ট -->
                <div class="tab-pane fade" id="testTab">
                    <input type="text" id="tSearch" class="form-control form-control-sm rounded-pill mb-2" placeholder="টেস্ট খুঁজুন...">
                    <div class="scroll-box">
                        <?php while($t = mysqli_fetch_assoc($test_list)): ?>
                            <span class="clickable t-item" onclick="addTest('<?php echo $t['test_name']; ?>')">
                                <i class="fas fa-flask text-danger me-1"></i><?php echo $t['test_name']; ?>
                            </span>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <!-- স্মার্ট ডোজ ও খাবারের নিয়ম সিলেক্টর -->
            <div class="p-3 bg-light rounded-4 border mt-2">
                <h6 class="fw-bold small mb-2 border-bottom pb-1 text-center text-navy">Quick Setup Tools</h6>
                
                <label class="d-block x-small fw-bold mb-1 opacity-75">DOSE (ডোজ):</label>
                <div class="mb-2 d-flex flex-wrap">
                    <span class="tag-btn" onclick="insertVal('১+০+১')">১+০+১</span>
                    <span class="tag-btn" onclick="insertVal('১+১+১')">১+১+১</span>
                    <span class="tag-btn" onclick="insertVal('০+০+১')">০+০+১</span>
                    <span class="tag-btn" onclick="insertVal('১+০+০')">১+০+০</span>
                </div>

                <label class="d-block x-small fw-bold mb-1 opacity-75 mt-2">RULE (নিয়ম):</label>
                <div class="mb-2 d-flex flex-wrap">
                    <span class="tag-btn text-danger" onclick="insertVal('খাবার আগে')">খাবার আগে</span>
                    <span class="tag-btn text-success" onclick="insertVal('খাবার পরে')">খাবার পরে</span>
                    <span class="tag-btn" onclick="insertVal('ভরা পেটে')">ভরা পেটে</span>
                </div>

                <label class="d-block x-small fw-bold mb-1 opacity-75 mt-2">DURATION (সময়):</label>
                <div class="mb-3 d-flex flex-wrap">
                    <span class="tag-btn" onclick="insertVal('৫ দিন')">৫ দিন</span>
                    <span class="tag-btn" onclick="insertVal('৭ দিন')">৭ দিন</span>
                    <span class="tag-btn" onclick="insertVal('১৫ দিন')">১৫ দিন</span>
                    <span class="tag-btn" onclick="insertVal('১ মাস')">১ মাস</span>
                </div>

                <button type="submit" name="final_save" class="btn btn-primary w-100 rounded-pill py-3 fw-bold shadow-sm mt-2">
                    FINISH & SAVE <i class="fas fa-save ms-1"></i>
                </button>
            </div>
        </aside>
    </div>
</form>

<script>
let activeInput = null;

// ওষুধ যোগ করার ফাংশন (৪টি ইনপুট কলাম সহ)
function addMedicine(name) {
    const table = document.getElementById('medicineTable');
    const id = Date.now();
    const row = `<tr id="row_${id}" class="med-row-item border-bottom">
        <td width="40%"><strong class="text-navy small">${name}</strong></td>
        <td><input type="text" class="input-flat text-center m-dose" onfocus="activeInput=this" placeholder="ডোজ"></td>
        <td><input type="text" class="input-flat text-center m-rule" onfocus="activeInput=this" placeholder="আগে/পরে"></td>
        <td><input type="text" class="input-flat text-center m-dur" onfocus="activeInput=this" placeholder="কতদিন"></td>
        <td width="5%"><i class="fas fa-times text-danger cursor-pointer" onclick="this.closest('tr').remove()"></i></td>
    </tr>`;
    table.insertAdjacentHTML('beforeend', row);
}

// টেস্ট যোগ করা
function addTest(name) {
    const list = document.getElementById('testDisplayList');
    const li = `<li class="test-item-row d-flex justify-content-between mb-1">
        <span>- <span class="test-name">${name}</span></span>
        <i class="fas fa-times text-danger cursor-pointer px-2" onclick="this.closest('li').remove()"></i>
    </li>`;
    list.insertAdjacentHTML('beforeend', li);
}

// সিলেক্ট করা ইনপুটে মান বসানো
function insertVal(val) {
    if(activeInput) {
        activeInput.value = val;
        activeInput.focus();
    }
}

// সেভ করার আগে সব ডাটা এক জায়গায় করা
function prepareData() {
    let medsArr = [];
    document.querySelectorAll('.med-row-item').forEach(row => {
        let name = row.querySelector('strong').innerText;
        let dose = row.querySelector('.m-dose').value;
        let rule = row.querySelector('.m-rule').value;
        let dur = row.querySelector('.m-dur').value;
        medsArr.push(`${name} -- (${dose}) -- ${rule} -- ${dur}`);
    });
    document.getElementById('medicines_output').value = medsArr.join("\n");

    let testsArr = [];
    document.querySelectorAll('.test-item-row').forEach(li => {
        testsArr.push(li.querySelector('.test-name').innerText);
    });
    document.getElementById('tests_output').value = testsArr.join(", ");
}

// সার্চ ফিল্টার
function setupSearch(inputId, itemClass) {
    document.getElementById(inputId).addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        document.querySelectorAll('.' + itemClass).forEach(item => {
            item.style.display = item.innerText.toLowerCase().includes(filter) ? "block" : "none";
        });
    });
}
setupSearch('mSearch', 'm-item'); setupSearch('tSearch', 't-item');
</script>

<?php include_once '../../includes/footer.php'; ?>