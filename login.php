<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RFID Login</title>
    <style>
        body {
            background-color: #8B0000;
            color: #222;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            max-width: 350px;
            width: 100%;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
            text-align: center;
        }
        h2 {
            color: #8B0000;
        }
        input[type="text"], input[type="submit"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            box-sizing: border-box;
        }
        input[type="submit"] {
            background-color: #E46E00;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <form action="validate_login.php" method="post">
            <input type="text" name="rfid_tag" placeholder="Scan or enter RFID (optional)">
            <input type="text" name="password" placeholder="Enter password (optional)">
            <input type="submit" value="Login">
        </form>
    </div>
</body>
</html>
