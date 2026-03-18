<?php
include_once '../../includes/header.php';

if (!isset($_SESSION['reception_id'])) { header("Location: ../auth/staff-login.php"); exit; }

$filter_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$doc_filter = isset($_GET['doctor_id']) ? $_GET['doctor_id'] : '';

// ডাক্তারদের তালিকা আনা (ফিল্টারের জন্য)
$doctors_list = mysqli_query($conn, "SELECT id, name FROM doctors");

// কুয়েরি তৈরি
$sql = "SELECT a.*, d.name as doc_name FROM appointments a JOIN doctors d ON a.doctor_id = d.id WHERE a.appointment_date = '$filter_date'";
if($doc_filter != '') $sql .= " AND a.doctor_id = '$doc_filter'";
$sql .= " ORDER BY a.id ASC";

$query = mysqli_query($conn, $sql);
?>
<div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-navy"><i class="fas fa-calendar-check me-2 text-warning"></i>পেন্ডিং অ্যাপয়েন্টমেন্ট</h3>
        <a href="dashboard.php" class="btn btn-outline-primary rounded-pill btn-sm px-4 shadow-sm">
            <i class="fas fa-arrow-left me-1"></i> ড্যাশবোর্ড
        </a>
    </div>
    
<div class="container py-5">
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
        <h4 class="fw-bold text-navy mb-4">সিরিয়াল ও অ্যাপয়েন্টমেন্ট রিপোর্ট</h4>
        <form action="" method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="small fw-bold">তারিখ নির্বাচন করুন</label>
                <input type="date" name="date" class="form-control rounded-pill" value="<?php echo $filter_date; ?>">
            </div>
            <div class="col-md-4">
                <label class="small fw-bold">ডাক্তার নির্বাচন করুন</label>
                <select name="doctor_id" class="form-select rounded-pill">
                    <option value="">সকল ডাক্তার</option>
                    <?php while($d = mysqli_fetch_assoc($doctors_list)): ?>
                        <option value="<?php echo $d['id']; ?>" <?php if($doc_filter == $d['id']) echo 'selected'; ?>><?php echo $d['name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary rounded-pill w-100 fw-bold">রিপোর্ট দেখুন</button>
                <button type="button" onclick="window.print()" class="btn btn-dark rounded-pill px-4"><i class="fas fa-print"></i></button>
            </div>
        </form>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive p-3">
            <div class="d-none d-print-block text-center mb-4">
                <h3>পেশেন্ট কেয়ার হাসপাতাল - অ্যাপয়েন্টমেন্ট রিপোর্ট</h3>
                <p>তারিখ: <?php echo date('d M, Y', strtotime($filter_date)); ?></p>
            </div>
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>সিরিয়াল #</th>
                        <th>রোগীর নাম</th>
                        <th>ফোন</th>
                        <th>ডাক্তার</th>
                        <th>অবস্থা (Status)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($query) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($query)): ?>
                            <tr>
                                <td class="fw-bold">#<?php echo $row['id']; ?></td>
                                <td><?php echo $row['patient_name']; ?></td>
                                <td><?php echo $row['patient_phone']; ?></td>
                                <td>ডা. <?php echo $row['doc_name']; ?></td>
                                <td>
                                    <?php 
                                    $s = $row['status'];
                                    if($s == 'completed') echo '<span class="badge bg-success rounded-pill">দেখা হয়েছে</span>';
                                    elseif($s == 'approved') echo '<span class="badge bg-primary rounded-pill">সিরিয়াল দেওয়া</span>';
                                    elseif($s == 'cancelled') echo '<span class="badge bg-danger rounded-pill">বাতিল</span>';
                                    else echo '<span class="badge bg-warning text-dark rounded-pill">পেন্ডিং</span>';
                                    ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-5">এই তারিখে কোনো অ্যাপয়েন্টমেন্ট নেই।</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
@media print {
    .navbar, .btn, form, footer, label { display: none !important; }
    .card { box-shadow: none !important; border: 1px solid #ddd !important; }
    .badge { border: 1px solid #000; color: #000 !important; background: transparent !important; }
}
</style>

<?php include_once '../../includes/footer.php'; ?>