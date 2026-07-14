<?php
/**
 * ১. সেশন এবং লজিক কন্ট্রোল
 */
session_start();
include_once '../../config/database.php';
include_once '../../config/constants.php';
include_once '../../config/functions.php';

// লগইন চেক (অ্যাডমিন বা ম্যানেজার ছাড়া কেউ দেখতে পারবে না)
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'manager'])) {
    header("Location: ../auth/staff-login.php");
    exit;
}

$user_role = $_SESSION['user_role'];

// ২. মেসেজ ডিলিট করার লজিক
if (isset($_GET['delete_id'])) {
    $del_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    if (mysqli_query($conn, "DELETE FROM contact_messages WHERE id = '$del_id'")) {
        $_SESSION['success'] = "মেসেজটি সফলভাবে মুছে ফেলা হয়েছে!";
    }
    header("Location: messages.php");
    exit;
}

// ৩. মেসেজ 'পঠিত' (Mark as Read) করার লজিক
if (isset($_GET['read_id'])) {
    $read_id = mysqli_real_escape_string($conn, $_GET['read_id']);
    mysqli_query($conn, "UPDATE contact_messages SET status = 'read' WHERE id = '$read_id'");
    header("Location: messages.php");
    exit;
}

// ৪. ডাটা সংগ্রহ
$msg_query = mysqli_query($conn, "SELECT * FROM contact_messages ORDER BY created_at DESC");
$total_msg = mysqli_num_rows($msg_query);

include_once '../../includes/header.php';
?>

<style>
    :root { --navy: #0A2647; --cyan: #2AA7E5; }
    body { background-color: #f1f5f9; font-family: 'Segoe UI', sans-serif; margin: 0; }
    
    /* সাইডবার সেটিংস */
    .sidebar { height: 100vh; width: 260px; position: fixed; top: 155px; left: 0; background: var(--navy); color: white; z-index: 1000; overflow-y: auto; transition: 0.3s; }
    .sidebar-menu a { color: rgba(255,255,255,0.7); text-decoration: none; padding: 12px 20px; display: block; border-left: 4px solid transparent; transition: 0.3s; font-size: 14px; }
    .sidebar-menu a:hover, .sidebar-menu a.active { background: rgba(255,255,255,0.1); color: white; border-left-color: var(--cyan); }
    
    /* মেইন কন্টেন্ট এরিয়া */
    .main-wrapper { margin-left: 260px; min-height: 80vh; display: flex; flex-direction: column; }
    .main-content { padding: 30px; flex: 1; }

    .msg-card { border: none; border-radius: 15px; transition: 0.3s; background: white; border-left: 5px solid #ccc; }
    .unread-msg { border-left-color: var(--cyan); background: #f0faff; }
    .msg-card:hover { transform: scale(1.01); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }

    @media (max-width: 992px) { .sidebar { margin-left: -260px; } .main-wrapper { margin-left: 0; } }
</style>

<!-- সাইডবার শুরু -->
<nav class="sidebar no-print shadow">
    <div class="py-3 text-center border-bottom border-secondary border-opacity-25 mb-2">
        <small class="text-info x-small text-uppercase fw-bold"><?php echo $user_role; ?> Portal</small>
    </div>
    <div class="sidebar-menu">
        <a href="dashboard.php"><i class="fas fa-th-large me-2"></i>ড্যাশবোর্ড</a>
        <a href="manage-accounts.php"><i class="fas fa-wallet me-2"></i>আয়-ব্যয় হিসাব</a>
        <a href="admission-manager.php"><i class="fas fa-bed me-2"></i>পেশেন্ট এডমিশন</a>
        <a href="messages.php" class="active"><i class="fas fa-envelope me-2"></i>ইনবক্স</a>
        <?php if($user_role == 'admin'): ?>
            <a href="manage-all-staff.php"><i class="fas fa-users-cog me-2"></i>স্টাফ ম্যানেজার</a>
        <?php endif; ?>
        <a href="../auth/logout.php" class="text-danger mt-4"><i class="fas fa-power-off me-2"></i>লগআউট</a>
    </div>
</nav>

<!-- মেইন র‍্যাপার শুরু -->
<div class="main-wrapper">
    <div class="main-content">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-navy mb-0"><i class="fas fa-envelope-open-text me-2 text-primary"></i>পেশেন্ট ইনবক্স</h3>
            <span class="badge bg-white text-navy border px-3 py-2 rounded-pill shadow-sm">মোট মেসেজ: <?php echo $total_msg; ?></span>
        </div>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4">
                <i class="fas fa-check-circle me-1"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-12">
                <?php if($total_msg > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($msg_query)): ?>
                        <div class="card msg-card mb-3 shadow-sm <?php echo ($row['status'] == 'unread') ? 'unread-msg' : ''; ?>">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="fw-bold text-navy mb-1"><?php echo $row['name']; ?> 
                                            <?php if($row['status'] == 'unread'): ?>
                                                <span class="badge bg-danger ms-2" style="font-size: 9px;">NEW</span>
                                            <?php endif; ?>
                                        </h6>
                                        <p class="small text-muted mb-2"><i class="fas fa-envelope me-1"></i> <?php echo $row['email']; ?> | <i class="fas fa-clock me-1"></i> <?php echo date('d M, h:i A', strtotime($row['created_at'])); ?></p>
                                        <div class="text-dark bg-white p-3 rounded-3 border">
                                            <strong>বিষয়: <?php echo $row['subject']; ?></strong><br>
                                            <p class="mb-0 mt-2"><?php echo $row['message']; ?></p>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <?php if($row['status'] == 'unread'): ?>
                                            <a href="?read_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-light border" title="Mark as Read"><i class="fas fa-check text-success"></i></a>
                                        <?php endif; ?>
                                        <a href="?delete_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-light border text-danger" onclick="return confirm('মুছে ফেলবেন?')" title="Delete"><i class="fas fa-trash-alt"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
                        <i class="fas fa-envelope-open fa-4x text-light mb-3"></i>
                        <h5 class="text-muted">আপনার ইনবক্স এই মুহূর্তে ফাঁকা আছে।</h5>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div> <!-- main-content শেষ -->

    <!-- ফুটার ইনক্লুড -->
    <?php include_once '../../includes/footer.php'; ?>

</div> <!-- main-wrapper শেষ -->

</body>
</html>