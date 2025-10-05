<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

requireLogin();

if (!isset($_GET['attempt_id']) || !is_numeric($_GET['attempt_id'])) {
    header('Location: dashboard.php');
    exit();
}
$_SESSION['quiz_access_granted'] = 1;
header('Location: take_quiz.php?attempt_id=' . intval($_GET['attempt_id']));
exit();
