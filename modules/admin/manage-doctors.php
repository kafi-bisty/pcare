<?php
session_start();
include_once '../../config/database.php';
include_once '../../config/constants.php';

// এডমিন চেক (লগইন না থাকলে লগইন পেজে পাঠিয়ে দেবে)
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// ডাক্তার ডিলিট করার লজিক
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    mysqli_query($conn, "DELETE FROM doctors WHERE id = '$delete_id'");
    $_SESSION['success'] = "ডাক্তার সফলভাবে মুছে ফেলা হয়েছে!";
    header("Location: manage-doctors.php");
    exit;
}

// ডাক্তারদের তালিকা আনা
$query = "SELECT * FROM doctors ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>ডাক্তার ম্যানেজমেন্ট | এডমিন প্যানেল</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { height: 100vh; width: 250px; position: fixed; background: #0A2647; color: white; padding-top: 20px; }
        .sidebar a { color: white; text-decoration: none; padding: 15px 25px; display: block; }
        .sidebar a:hover { background: #1a4a7a; }
        .main-content { margin-left: 250px; padding: 40px; }
        .card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

    <div class="sidebar">
        <h4 class="text-center fw-bold mb-4">Admin Panel</h4>
        <a href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>ড্যাশবোর্ড</a>
        <a href="manage-doctors.php" class="bg-primary"><i class="fas fa-user-md me-2"></i>ডাক্তার ম্যানেজমেন্ট</a>
        <a href="add-receptionist.php"><i class="fas fa-user-plus me-2"></i>রিসেপশন স্টাফ যোগ</a>
        <a href="manage-receptionists.php"><i class="fas fa-users-cog me-2"></i>স্টাফ তালিকা</a>
        <a href="../auth/logout.php" class="text-danger mt-5"><i class="fas fa-sign-out-alt me-2"></i>লগআউট</a>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">ডাক্তার তালিকা</h2>
            <a href="add-doctor.php" class="btn btn-primary rounded-pill px-4">
                <i class="fas fa-plus me-2"></i>নতুন ডাক্তার যোগ করুন
            </a>
        </div>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>আইডি</th>
                            <th>ডাক্তারের নাম</th>
                            <th>বিভাগ</th>
                            <th>যোগ্যতা</th>
                            <th>ভিজিট ফি</th>
                            <th>স্ট্যাটাস</th>
                            <th class="text-center">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td>#<?php echo $row['id']; ?></td>
                                    <td class="fw-bold"><?php echo $row['name']; ?></td>
                                    <td><span class="badge bg-info text-dark"><?php echo $row['specialization']; ?></span></td>
                                    <td class="small"><?php echo $row['qualification']; ?></td>
                                    <td>৳ <?php echo $row['fee']; ?></td>
                                    <td>
                                        <span class="badge <?php echo ($row['status'] == 'active') ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo ($row['status'] == 'active') ? 'সক্রিয়' : 'নিষ্ক্রিয়'; ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="edit-doctor.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary rounded-circle me-1" title="এডিট">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="manage-doctors.php?delete_id=<?php echo $row['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger rounded-circle" 
                                           onclick="return confirm('আপনি কি নিশ্চিতভাবে এই ডাক্তারের তথ্য মুছে ফেলতে চান?')" title="ডিলিট">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center py-4 text-muted">কোনো ডাক্তারের তথ্য পাওয়া যায়নি।</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>