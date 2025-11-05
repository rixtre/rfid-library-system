<?php
session_start();
$_SESSION['test'] = "session is working";
echo "Session saved. <a href='session_test2.php'>Check</a>";
