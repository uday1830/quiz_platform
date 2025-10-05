<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database();
    $conn = $db->connect();

    $attempt_id = intval($_POST['attempt_id']);
    $question_id = intval($_POST['question_id']);
    $option_id = isset($_POST['option_id']) && $_POST['option_id'] !== '' ? intval($_POST['option_id']) : null;
    $marked_for_review = intval($_POST['marked_for_review']);
    $remaining_time = isset($_POST['remaining_time']) ? intval($_POST['remaining_time']) : null;

    $user_id = $_SESSION['user_id'];

    $verify_query = "SELECT id FROM attempts WHERE id = ? AND user_id = ? AND status = 'in_progress'";
    $stmt = $conn->prepare($verify_query);
    $stmt->bind_param("ii", $attempt_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        if ($option_id !== null) {
            $update_query = "UPDATE attempt_answers
                             SET selected_option_id = ?, marked_for_review = ?
                             WHERE attempt_id = ? AND question_id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("iiii", $option_id, $marked_for_review, $attempt_id, $question_id);
        } else {
            $update_query = "UPDATE attempt_answers
                             SET marked_for_review = ?
                             WHERE attempt_id = ? AND question_id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("iii", $marked_for_review, $attempt_id, $question_id);
        }

        if ($remaining_time !== null) {
    $stmt_time = $conn->prepare("UPDATE attempts SET remaining_time = ? WHERE id = ?");
    $stmt_time->bind_param("ii", $remaining_time, $attempt_id);
    $stmt_time->execute();
    $stmt_time->close();
}

        $stmt->execute();

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid attempt']);
    }

    $stmt->close();
    $conn->close();
}
?>
