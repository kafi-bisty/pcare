<?php
session_start();
include_once '../../config/database.php';
include_once '../../config/constants.php';
include_once '../../config/functions.php';

if (!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit; }

// মিডিয়া যোগ করার লজিক
if (isset($_POST['add_media'])) {
    $type = $_POST['media_type'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    
    if ($type == 'image') {
        $img_name = time() . '_' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "../../assets/images/gallery/" . $img_name);
        $url = $img_name;
    } else {
        $url = mysqli_real_escape_string($conn, $_POST['video_url']); // ইউটিউব লিঙ্ক
    }

    mysqli_query($conn, "INSERT INTO gallery (media_type, media_url, title) VALUES ('$type', '$url', '$title')");
    $_SESSION['success'] = "গ্যালারিতে নতুন আইটেম যোগ হয়েছে!";
}

// ডিলিট লজিক
if (isset($_GET['del_id'])) {
    $id = $_GET['del_id'];
    mysqli_query($conn, "DELETE FROM gallery WHERE id = '$id'");
    header("Location: manage-gallery.php"); exit;
}

$items = mysqli_query($conn, "SELECT * FROM gallery ORDER BY id DESC");
include_once '../../includes/header.php';
?>

<div class="container py-5">
    <h3 class="fw-bold text-navy mb-4">মিডিয়া গ্যালারি ম্যানেজমেন্ট</h3>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4 rounded-4 sticky-top" style="top: 100px;">
                <h6 class="fw-bold mb-3 border-bottom pb-2">নতুন মিডিয়া যোগ করুন</h6>
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="small fw-bold">টাইপ</label>
                        <select name="media_type" id="typeSelect" class="form-select shadow-none" required>
                            <option value="image">ছবি (Image)</option>
                            <option value="video">ভিডিও (YouTube Link)</option>
                        </select>
                    </div>
                    <div class="mb-3" id="imgInput">
                        <label class="small fw-bold">ছবি সিলেক্ট করুন</label>
                        <input type="file" name="image" class="form-control">
                    </div>
                    <div class="mb-3 d-none" id="videoInput">
                        <label class="small fw-bold">ইউটিউব লিঙ্ক</label>
                        <input type="text" name="video_url" class="form-control" placeholder="https://youtube.com/watch?v=...">
                    </div>
                    <div class="mb-4">
                        <label class="small fw-bold">ক্যাপশন / শিরোনাম</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <button type="submit" name="add_media" class="btn btn-primary w-100 rounded-pill">আপলোড করুন</button>
                </form>
            </div>
        </div>

        <div class="col-md-8">
            <div class="row g-3">
                <?php while($row = mysqli_fetch_assoc($items)): ?>
                <div class="col-6 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                        <?php if($row['media_type'] == 'image'): ?>
                            <img src="../../assets/images/gallery/<?php echo $row['media_url']; ?>" class="card-img-top" style="height: 150px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-dark d-flex align-items-center justify-content-center" style="height: 150px;">
                                <i class="fab fa-youtube fa-3x text-danger"></i>
                            </div>
                        <?php endif; ?>
                        <div class="p-2 text-center">
                            <small class="d-block fw-bold mb-2"><?php echo $row['title']; ?></small>
                            <a href="?del_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger border-0"><i class="fas fa-trash"></i></a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('typeSelect').addEventListener('change', function() {
    if(this.value == 'image') {
        document.getElementById('imgInput').classList.remove('d-none');
        document.getElementById('videoInput').classList.add('d-none');
    } else {
        document.getElementById('imgInput').classList.add('d-none');
        document.getElementById('videoInput').classList.remove('d-none');
    }
});
</script>
<?php include_once '../../includes/footer.php'; ?>