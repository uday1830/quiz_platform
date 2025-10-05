<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

requireLogin();

$conn = (new Database())->connect();

$attempt_id = intval($_POST['attempt_id'] ?? 0);
$remaining_time = intval($_POST['remaining_time'] ?? -1);

if ($attempt_id > 0 && $remaining_time >= 0) {
    $stmt = $conn->prepare("UPDATE attempts SET remaining_time = ? WHERE id = ?");
    $stmt->bind_param("ii", $remaining_time, $attempt_id);
    $stmt->execute();
    $stmt->close();
}

$conn->close();
?>
