<?php
session_start();
include('db.php');

if ($_SESSION['role'] !== 'librarian') {
    echo "Unauthorized access!";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rfid_tag = $_POST['rfid_tag'];
    $book_title = $_POST['book_title'];
    $borrow_date = date("Y-m-d H:i:s");
    $return_due = date('Y-m-d H:i:s', strtotime("+3 days"));

    $sql = "INSERT INTO transactions (student_rfid, book_title, borrow_date, return_due, status) 
            VALUES ('$rfid_tag', '$book_title', '$borrow_date', '$return_due', 'Borrowed')";
    
    if ($conn->query($sql)) {
        echo "<script>alert('✅ Book borrowed successfully!'); window.location.href='borrow.php';</script>";
    } else {
        echo "❌ Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Borrow Book</title>
    <style>
        body {
            background: #8B0000;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .card {
            background: white;
            width: 420px;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
            text-align: center;
        }
        input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ccc;
            border-radius: 6px;
            margin-top: 8px;
            font-size: 16px;
        }
        .btn {
            padding: 12px 20px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            border-radius: 6px;
            margin: 10px 5px;
            font-weight: bold;
            transition: 0.2s;
        }
        .btn-main {
            background: #E46E00;
            color: white;
        }
        .btn-main:hover {
            background: #FF8A00;
        }
        .btn-back {
            background: #555;
            color: white;
        }
        .btn-back:hover {
            background: #333;
        }
        h3 {
            color: #8B0000;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="card">
    <h3>Borrow Book</h3>

    <form method="post">
        <input type="text" name="rfid_tag" placeholder="Scan RFID Tag" required><br><br>
        <input type="text" name="book_title" placeholder="Enter Book Title" required><br><br>

        <button type="submit" class="btn btn-main">Borrow</button>
        <a href="lib_dash.php" class="btn btn-back">Back</a>
    </form>
</div>
</body>
</html>
