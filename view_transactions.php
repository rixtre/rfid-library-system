<?php
session_start();
include 'db.php';

// Only admin or librarian can access this page
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'librarian')) {
    header("Location: login.php");
    exit();
}

$query = "SELECT * FROM transactions ORDER BY borrow_date DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Transactions</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            width: 90%;
            max-width: 1000px;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 18px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #8B0000;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table, th, td {
            border: 1px solid #A40000;
        }
        th {
            background-color: #A40000;
            color: white;
            padding: 12px;
            text-align: left;
        }
        td {
            padding: 10px;
            text-align: left;
        }
        tr Late td {
            background-color: #f8d7da;
        }
        .btn-back {
            display: inline-block;
            text-decoration: none;
            background-color: #8B0000;
            color: white;
            padding: 10px 15px;
            font-weight: bold;
            border-radius: 5px;
        }
        .btn-back:hover {
            background-color: #A40000;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Transaction Records</h2>
    <table>
        <tr>
            <th>Student RFID</th>
            <th>Book Title</th>
            <th>Borrow Date</th>
            <th>Due Date</th>
            <th>Return Date</th>
            <th>Status</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
        <tr style="<?php echo ($row['status'] == 'Late') ? 'background-color:#f8d7da;' : ''; ?>">
            <td><?php echo $row['student_rfid']; ?></td>
            <td><?php echo $row['book_title']; ?></td>
            <td><?php echo $row['borrow_date']; ?></td>
            <td><?php echo $row['return_due']; ?></td>
            <td><?php echo $row['return_date'] ? $row['return_date'] : 'Not yet returned'; ?></td>
            <td><?php echo $row['status']; ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <a href="<?php echo ($_SESSION['role'] == 'admin') ? 'admin_dashboard.php.php' : 'lib_dash.php'; ?>" class="btn-back">⬅ Back to Dashboard</a>
</div>

</body>
</html>
