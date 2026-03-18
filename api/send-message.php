<?php
session_start();
include_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    $query = "INSERT INTO contact_messages (name, email, subject, message, status) 
              VALUES ('$name', '$email', '$subject', '$message', 'unread')";

    if (mysqli_query($conn, $query)) {
        // পপ-আপ দেখানোর জন্য সেশন সেট করা
        $_SESSION['show_contact_popup'] = "success";
    } else {
        $_SESSION['show_contact_popup'] = "error";
    }
    
    header("Location: ../index.php#contact");
    exit;
}
?>