<?php
session_start();
include('db.php');

if ($_SESSION['role'] !== 'librarian') {
    echo "Unauthorized access!";
    exit;
}

$name = $_SESSION['name'] ?? 'Librarian';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Librarian Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
        }
        header {
            background: #8B0000;
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 1.5rem;
        }
        .container {
            padding: 20px;
            text-align: center;
        }
        .btn {
            display: inline-block;
            padding: 15px;
            margin: 10px;
            width: 200px;
            background: #A40000;
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
            transition: 0.3s;
        }
        .btn:hover {
            background: #E46E00;
        }
        .logout {
            margin-top: 20px;
            background: #555;
        }
        .change-pass {
            background: #0066cc;
        }
        .change-pass:hover {
            background: #0099ff;
        }
    </style>
</head>
<body>
    <header>
        Welcome, <?php echo $name; ?> (Librarian)
    </header>

    <div class="container">
        <a href="add_student.php" class="btn">Add Student</a>
        <a href="borrow.php" class="btn">Borrow Book</a>
        <a href="return.php" class="btn">Return Book</a>
        <a href="change_password.php" class="btn change-pass">Change Password</a>
        <a href="logout.php" class="btn logout">Logout</a>
    </div>
</body>
</html>
