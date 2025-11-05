<?php
session_start();
include 'db.php';

if (!in_array($_SESSION['role'], ['admin', 'librarian'])) {
    echo "Unauthorized access.";
    exit;
}

$query = "SELECT * FROM transactions ORDER BY borrow_date DESC";
$results = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Records</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <h2>Transaction Logs</h2>
    <table border="1">
        <tr>
            <th>Student RFID</th>
            <th>Book Title</th>
            <th>Borrowed On</th>
            <th>Due Date</th>
            <th>Returned On</th>
            <th>Status</th>
            <th>Penalty</th>
        </tr>
        <?php
        if (mysqli_num_rows($results) > 0) {
            while ($row = mysqli_fetch_assoc($results)) {
                echo "<tr>
                        <td>{$row['student_rfid']}</td>
                        <td>{$row['book_title']}</td>
                        <td>{$row['borrow_date']}</td>
                        <td>{$row['return_due']}</td>
                        <td>{$row['return_date']}</td>
                        <td>{$row['status']}</td>
                        <td>₱{$row['penalty']}.00</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='7'>No transactions found.</td></tr>";
        }
        ?>
    </table>
    <br>
    <a href="<?php echo ($_SESSION['role'] == 'admin') ? 'admin_dashboard.php.php' : 'lib_dash.php'; ?>">Back</a>
</body>
</html>
