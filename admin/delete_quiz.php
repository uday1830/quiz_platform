<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

requireAdmin();

if (isset($_GET['id'])) {
    $db = new Database();
    $conn = $db->connect();

    $quiz_id = intval($_GET['id']);

    $stmt = $conn->prepare("DELETE FROM quizzes WHERE id = ?");
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

header('Location: ' . dirname($_SERVER['PHP_SELF']) . '/quizzes.php');
exit();
