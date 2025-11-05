<?php
session_start();
include('db.php');

$rfid_tag = $_POST['rfid_tag'] ?? '';
$password = $_POST['password'] ?? '';

if ($rfid_tag) {
    $query = "SELECT * FROM users WHERE rfid_tag = '$rfid_tag'";
} elseif ($password) {
    $query = "SELECT * FROM users WHERE password = '$password'";
} else {
    echo "Please enter RFID or Password to continue.";
    exit;
}

$result = $conn->query($query);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $_SESSION['rfid_tag'] = $user['rfid_tag'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['name'] = $user['name'];
    
    if ($user['role'] === 'admin') header("Location: admin_dashboard.php.php");
    elseif ($user['role'] === 'librarian') header("Location: lib_dash.php");
    else header("Location: stud_dash.php");
} else {
    echo "<script>alert('Invalid RFID or Password'); window.location.href='login.php';</script>";
}
?>
