<?php
session_start();
echo $_SESSION['test'] ?? "Session NOT working";
