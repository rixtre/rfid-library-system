<?php
session_start();
include('db.php');

if ($_SESSION['role'] !== 'librarian') {
    echo "Unauthorized access!";
    exit;
}

// Get RFID from session instead of username
$rfid = $_SESSION['rfid_tag'] ?? null;

if (!$rfid) {
    echo "<script>alert('Session error. Please log in again.'); window.location.href='login.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'];

    if (strlen($new_password) < 4) {
        echo "<script>alert('Password must be at least 4 characters long.');</script>";
    } else {
        $conn->query("UPDATE users SET password='$new_password' WHERE rfid_tag='$rfid' AND role='librarian'");
        echo "<script>alert('✅ Password updated successfully!'); window.location.href='lib_dash.php';</script>";
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; text-align: center; }
        .box { margin: 100px auto; width: 350px; padding: 25px; background: white; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.2); }
        input { width: 90%; padding: 10px; margin: 10px 0; border: 2px solid #ccc; border-radius: 5px; }
        button { padding: 12px 20px; background: #A40000; color: white; border: none; border-radius: 6px; cursor: pointer; width: 100%; font-size: 1rem; }
        button:hover { background: #E46E00; }
        .back { display: block; margin-top: 10px; text-decoration: none; color: #444; }
    </style>
</head>
<body>
    <div class="box">
        <h2>Change Password</h2>
        <form method="post">
            <input type="password" name="new_password" placeholder="Enter new password" required>
            <button type="submit">Update Password</button>
        </form>
        <a href="lib_dash.php" class="back">⬅ Back</a>
    </div>
</body>
</html>
