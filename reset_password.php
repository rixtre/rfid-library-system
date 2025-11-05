<?php
session_start();
include 'db.php';

// only admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$admin_username = $_SESSION['username'] ?? ($_SESSION['name'] ?? 'admin');

// incoming POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['librarian_username'])) {
    header("Location: admin_dashboard.php.php?reset=0&msg=" . urlencode("Invalid request"));
    exit;
}

$librarian = $_POST['librarian_username'];

// verify librarian exists and role is librarian
$stmt = $conn->prepare("SELECT username, name FROM users WHERE username = ? AND role = 'librarian' LIMIT 1");
$stmt->bind_param("s", $librarian);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    header("Location: admin_dashboard.php.php?reset=0&msg=" . urlencode("Selected user not found or not a librarian"));
    exit;
}
$row = $res->fetch_assoc();

// perform reset (password stored as MD5 of lib123)
$new_plain = 'lib123';
$new_hash = md5($new_plain);

$u_stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ? AND role='librarian'");
$u_stmt->bind_param("ss", $new_hash, $librarian);
$ok = $u_stmt->execute();
$u_stmt->close();

if ($ok) {
    // insert log
    $log_stmt = $conn->prepare("INSERT INTO password_resets (admin_username, librarian_username, action) VALUES (?, ?, 'password reset')");
    $log_stmt->bind_param("ss", $admin_username, $librarian);
    $log_stmt->execute();
    $log_stmt->close();

    // redirect back with success and librarian display
    // Show "Name (EmployeeID)" format if name available
    $display = trim($row['name']) ? $row['name'] . ' (' . $row['username'] . ')' : $row['username'];
    header("Location: admin_dashboard.php.php?reset=1&lib=" . urlencode($display));
    exit;
} else {
    header("Location: admin_dashboard.php.php?reset=0&msg=" . urlencode("Database update failed"));
    exit;
}
?>
