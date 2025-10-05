<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

requireAdmin();

$question_id = isset($_GET['question_id']) ? intval($_GET['question_id']) : 0;

if (isset($_GET['id']) && $question_id > 0) {
    $db = new Database();
    $conn = $db->connect();

    $option_id = intval($_GET['id']);

    $stmt = $conn->prepare("DELETE FROM options WHERE id = ?");
    $stmt->bind_param("i", $option_id);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

header("Location: " . dirname($_SERVER['PHP_SELF']) . "/options.php?question_id=$question_id");
exit();
