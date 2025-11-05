<?php
session_start();
include('db.php');

if ($_SESSION['role'] !== 'student') {
    echo "Unauthorized access!";
    exit;
}

$rfid_tag = $_SESSION['rfid_tag'];
$name = $_SESSION['name'] ?? 'Student';

// fetch transaction and penalty data
$transactions = $conn->query("SELECT * FROM transactions WHERE rfid_tag='$rfid_tag' ORDER BY borrow_date DESC");
$penalties = $conn->query("SELECT * FROM penalties WHERE rfid_tag='$rfid_tag' AND settled='no'");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f7f7f7; }
        h2, h3 { color: #8B0000; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; }
        th { background: #8B0000; color: #fff; }
        tr:nth-child(even) { background: #f2f2f2; }
        a { background: #8B0000; color: #fff; padding: 8px 15px; border-radius: 5px; text-decoration: none; }
        a:hover { background: #B22222; }
    </style>
</head>
<body>

    <h2>Welcome, <?php echo htmlspecialchars($name); ?>!</h2>

    <h3>Your Borrow History</h3>
    <?php if ($transactions->num_rows > 0): ?>
        <table>
            <tr><th>Book Title</th><th>Status</th><th>Borrowed On</th><th>Returned On</th></tr>
            <?php while ($row = $transactions->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['book_title']) ?></td>
                <td><?= htmlspecialchars($row['status']) ?></td>
                <td><?= htmlspecialchars($row['borrow_date']) ?></td>
                <td><?= $row['return_date'] ?: 'Not yet returned' ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No borrow history found.</p>
    <?php endif; ?>

    <h3>Pending Penalties</h3>
    <?php if ($penalties->num_rows > 0): ?>
        <table>
            <tr><th>Penalty Amount (PHP)</th></tr>
            <?php while ($p = $penalties->fetch_assoc()): ?>
            <tr>
                <td><?= number_format($p['penalty_amount'], 2) ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>You have no pending penalties 🎉</p>
    <?php endif; ?>

    <a href="logout.php">Logout</a>
</body>
</html>
