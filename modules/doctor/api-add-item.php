<?php
include_once '../../config/database.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = $_POST['type'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $table = ($type == 'medicine') ? 'medicines_list' : 'lab_tests_list';
    $col = ($type == 'medicine') ? 'medicine_name' : 'test_name';
    
    if(mysqli_query($conn, "INSERT INTO $table ($col) VALUES ('$name')")) {
        echo "success";
    }
}
?>