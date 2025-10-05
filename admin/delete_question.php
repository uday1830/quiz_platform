<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

requireAdmin();

$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;

if (isset($_GET['id']) && $quiz_id > 0) {
    $db = new Database();
    $conn = $db->connect();

    $question_id = intval($_GET['id']);

    $stmt = $conn->prepare("DELETE FROM questions WHERE id = ?");
    $stmt->bind_param("i", $question_id);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

header("Location: " . dirname($_SERVER['PHP_SELF']) . "/questions.php?quiz_id=$quiz_id");
exit();
