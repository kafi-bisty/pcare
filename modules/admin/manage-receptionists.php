<?php
session_start();
include_once '../../config/database.php';
include_once '../../config/constants.php';

// এডমিন চেক (লগইন না থাকলে লগইন পেজে পাঠিয়ে দেবে)
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// স্টাফ ডিলিট করার লজিক
if (isset($_GET['delete_id'])) {
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    
    // ডাটাবেজ থেকে মুছে ফেলা
    $delete_query = "DELETE FROM receptionists WHERE id = '$delete_id'";
    
    if (mysqli_query($conn, $delete_query)) {
        $_SESSION['success'] = "স্টাফের তথ্য সফলভাবে মুছে ফেলা হয়েছে!";
    } else {
        $_SESSION['error'] = "দুঃখিত, তথ্যটি মোছা সম্ভব হয়নি।";
    }
    header("Location: manage-receptionists.php");
    exit;
}

// সকল স্টাফের তালিকা আনা
$query = "SELECT * FROM receptionists ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>স্টাফ ম্যানেজমেন্ট | এডমিন প্যানেল</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { height: 100vh; width: 250px; position: fixed; background: #0A2647; color: white; padding-top: 20px; }
        .sidebar a { color: white; text-decoration: none; padding: 15px 25px; display: block; }
        .sidebar a:hover { background: #1a4a7a; }
        .main-content { margin-left: 250px; padding: 40px; }
        .card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .bg-navy { background-color: #0A2647; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h4 class="text-center fw-bold mb-4">Admin Panel</h4>
        <a href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>ড্যাশবোর্ড</a>
        <a href="manage-doctors.php"><i class="fas fa-user-md me-2"></i>ডাক্তার ম্যানেজমেন্ট</a>
        <a href="add-receptionist.php"><i class="fas fa-user-plus me-2"></i>রিসেপশন স্টাফ যোগ</a>
        <a href="manage-receptionists.php" class="bg-primary"><i class="fas fa-users-cog me-2"></i>স্টাফ তালিকা</a>
        <a href="../auth/logout.php" class="text-danger mt-5"><i class="fas fa-sign-out-alt me-2"></i>লগআউট</a>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">রিসেপশন স্টাফ তালিকা</h2>
            <a href="add-receptionist.php" class="btn btn-primary rounded-pill px-4 shadow-sm">
                <i class="fas fa-user-plus me-2"></i>নতুন স্টাফ যোগ করুন
            </a>
        </div>

        <!-- নোটিফিকেশন মেসেজ -->
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>আইডি</th>
                            <th>স্টাফের নাম</th>
                            <th>ইউজারনেম</th>
                            <th>মোবাইল নম্বর</th>
                            <th>যোগদানের তারিখ</th>
                            <th class="text-center">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td>#<?php echo $row['id']; ?></td>
                                    <td class="fw-bold text-navy"><?php echo $row['name']; ?></td>
                                    <td><code><?php echo $row['username']; ?></code></td>
                                    <td><?php echo $row['phone']; ?></td>
                                    <td class="small text-muted"><?php echo date('d M, Y', strtotime($row['created_at'])); ?></td>
                                    <td class="text-center">
                                        <!-- ডিলিট বাটন -->
                                        <a href="manage-receptionists.php?delete_id=<?php echo $row['id']; ?>" 
                                           class="btn btn-outline-danger btn-sm rounded-circle px-2" 
                                           onclick="return confirm('আপনি কি নিশ্চিতভাবে এই স্টাফের একাউন্টটি মুছে ফেলতে চান?')" 
                                           title="ডিলিট করুন">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-users-slash fa-3x mb-3 opacity-25"></i>
                                    <p>এখনো কোনো স্টাফ যোগ করা হয়নি।</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>