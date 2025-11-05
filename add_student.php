<?php
session_start();
include('db.php');

if ($_SESSION['role'] !== 'librarian') {
    echo "Unauthorized access!";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $student_id = $_POST['student_id'];
    $rfid_tag = $_POST['rfid_tag'];
    $gender = $_POST['gender'];
    
    $check_sql = "SELECT * FROM users WHERE rfid_tag='$rfid_tag' OR username='$student_id'";
    $result = $conn->query($check_sql);

    if ($result->num_rows > 0) {
        echo "<script>alert('⚠️ Duplicate entry! Student already exists.'); window.location.href='add_student.php';</script>";
    } else {
        $insert_sql = "INSERT INTO users (username, password, role, rfid_tag, name, gender) 
                       VALUES ('$student_id', '$student_id', 'student', '$rfid_tag', '$name', '$gender')";
        if ($conn->query($insert_sql)) {
            echo "<script>alert('✅ Student added successfully!'); window.location.href='add_student.php';</script>";
        } else {
            echo "Error: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Student</title>
    <style>
        body { background: #f5f5f5; font-family: Arial; margin: 0; }
        .container { width: 400px; margin: auto; padding: 20px; background: white; margin-top: 50px; border-radius: 8px; }
        input, select { width: 100%; padding: 12px; margin: 10px 0; }
        button { padding: 10px 20px; background: #A40000; color: white; border: none; }
        button:hover { background: #E46E00; }
    </style>
</head>
<body>
<div class="container">
    <h2>Add Student</h2>
    <form method="post">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="text" name="student_id" placeholder="Student ID" required>
        <input type="text" name="rfid_tag" placeholder="RFID Tag" required>
        <select name="gender" required><option value="">Gender</option><option>male</option><option>female</option></select>
        <button type="submit">Add Student</button>
    </form>
</div>
</body>
</html>
