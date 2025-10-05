<?php
require_once __DIR__ . '/config/session.php';

session_destroy();
header('Location: ' . dirname($_SERVER['PHP_SELF']) . '/login.php');
exit();
