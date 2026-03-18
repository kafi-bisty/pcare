<?php
session_start();
include_once '../../config/database.php';
include_once '../../config/constants.php';
include_once '../../config/functions.php'; // লগ ফাংশনের জন্য এটি জরুরি

// ১. এডমিন চেক
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// ২. মেসেজ স্ট্যাটাস 'read' করে দেওয়া (একসাথে সব আনরিড মেসেজ রিড হবে)
mysqli_query($conn, "UPDATE contact_messages SET status = 'read' WHERE status = 'unread'");

// ৩. মেসেজ ডিলিট লজিক (অ্যাক্টিভিটি লগ সহ)
if (isset($_GET['delete_id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    
    // ডিলিট করার আগে প্রেরকের নাম জেনে রাখা
    $info_query = mysqli_query($conn, "SELECT name FROM contact_messages WHERE id = '$id'");
    $msg_info = mysqli_fetch_assoc($info_query);
    
    if ($msg_info) {
        $sender_name = $msg_info['name'];

        // মেসেজটি ডিলিট করা
        if (mysqli_query($conn, "DELETE FROM contact_messages WHERE id = '$id'")) {
            // মালিকের জন্য লগ রেকর্ড করা (এটিই আপনার মূল চাহিদা)
            log_activity($conn, "DELETE", "কন্টাক্ট মেসেজ ডিলিট করা হয়েছে (প্রেরক: $sender_name)");
            $_SESSION['success'] = "মেসেজটি সফলভাবে মুছে ফেলা হয়েছে!";
        }
    }
    header("Location: messages.php");
    exit;
}

// ৪. সব মেসেজ ডাটাবেজ থেকে আনা
$query = mysqli_query($conn, "SELECT * FROM contact_messages ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>মেসেজ ইনবক্স | এডমিন প্যানেল</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --navy: #0A2647; --cyan: #2AA7E5; }
        body { background-color: #f4f7f6; }
        .sidebar { height: 100vh; width: 250px; position: fixed; background: var(--navy); color: white; padding-top: 20px; }
        .sidebar a { color: rgba(255,255,255,0.8); text-decoration: none; padding: 15px 25px; display: block; transition: 0.3s; }
        .sidebar a:hover { background: rgba(255,255,255,0.1); color: white; }
        .main-content { margin-left: 250px; padding: 40px; min-height: 100vh; }
        .message-card { border: none; border-radius: 15px; border-left: 5px solid var(--navy); transition: 0.3s; }
        .message-card:hover { transform: scale(1.01); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
        .text-navy { color: var(--navy); }
    </style>
</head>
<body>

    <!-- সাইডবার -->
    <div class="sidebar shadow">
        <div class="text-center mb-4"><h4 class="fw-bold text-white">Admin Panel</h4></div>
        <a href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>ড্যাশবোর্ড</a>
        <a href="manage-all-staff.php"><i class="fas fa-users me-2"></i>স্টাফ ম্যানেজার</a>
        <a href="messages.php" class="bg-primary text-white"><i class="fas fa-envelope me-2"></i> ইনবক্স</a>
        <a href="activity-logs.php"><i class="fas fa-history me-2"></i>অ্যাক্টিভিটি লগ</a>
        <a href="../auth/logout.php" class="text-danger mt-5"><i class="fas fa-sign-out-alt me-2"></i>লগআউট</a>
           <a href="dashboard.php" class="btn btn-outline-primary rounded-pill px-4">ড্যাশবোর্ড</a>
    </div>

    <!-- মেইন কন্টেন্ট -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-navy">কন্টাক্ট মেসেজ ইনবক্স</h2>
            <span class="badge bg-white text-navy border px-3 py-2 rounded-pill shadow-sm">মোট মেসেজ: <?php echo mysqli_num_rows($query); ?></span>

        </div>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <?php if (mysqli_num_rows($query) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($query)): ?>
                    <div class="col-md-12 mb-3">
                        <div class="card message-card shadow-sm p-4 bg-white">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h5 class="fw-bold mb-0 text-navy"><?php echo $row['name']; ?></h5>
                                    <p class="text-muted small mb-0"><i class="fas fa-envelope me-1"></i><?php echo $row['email']; ?></p>
                                </div>
                                <div class="text-end">
                                    <span class="text-muted small d-block"><?php echo date('d M, Y', strtotime($row['created_at'])); ?></span>
                                    <span class="text-muted small"><?php echo date('h:i A', strtotime($row['created_at'])); ?></span>
                                </div>
                            </div>
                            <hr class="opacity-10">
                            <h6 class="fw-bold">বিষয়: <span class="text-primary"><?php echo $row['subject']; ?></span></h6>
                            <p class="text-dark bg-light p-3 rounded-3 mt-2"><?php echo nl2br($row['message']); ?></p>
                            <div class="text-end">
                                <a href="?delete_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="return confirm('আপনি কি নিশ্চিতভাবে এই মেসেজটি ডিলিট করতে চান? এটি মালিকের লগ-এ রেকর্ড হবে।')">
                                    <i class="fas fa-trash-alt me-1"></i> মেসেজটি মুছুন
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <div class="p-5 bg-white rounded-4 shadow-sm">
                        <i class="fas fa-envelope-open fa-4x text-muted opacity-25 mb-3"></i>
                        <h5 class="text-muted">আপনার ইনবক্স এই মুহূর্তে ফাঁকা আছে।</h5>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>