<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

requireLogin();

$db = new Database();
$conn = $db->connect();

$user_id = $_SESSION['user_id'];
$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;

if ($quiz_id === 0) {
    header('Location: ' . dirname($_SERVER['PHP_SELF']) . '/dashboard.php');
    exit();
}

$quiz_query = "SELECT * FROM quizzes WHERE id = ? AND is_active = 1";
$stmt = $conn->prepare($quiz_query);
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$quiz_result = $stmt->get_result();

if ($quiz_result->num_rows === 0) {
    header('Location: ' . dirname($_SERVER['PHP_SELF']) . '/dashboard.php');
    exit();
}

$quiz = $quiz_result->fetch_assoc();

$check_attempt = "SELECT id FROM attempts WHERE user_id = ? AND quiz_id = ? AND status = 'in_progress'";
$stmt = $conn->prepare($check_attempt);
$stmt->bind_param("ii", $user_id, $quiz_id);
$stmt->execute();
$attempt_result = $stmt->get_result();

if ($attempt_result->num_rows > 0) {
    $attempt = $attempt_result->fetch_assoc();
    $attempt_id = $attempt['id'];
} else {
    $questions_query = "SELECT COUNT(*) as count FROM questions WHERE quiz_id = ?";
    $stmt = $conn->prepare($questions_query);
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $count_result = $stmt->get_result();
    $question_count = $count_result->fetch_assoc()['count'];

    $insert_attempt = "INSERT INTO attempts (user_id, quiz_id, total_questions) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insert_attempt);
    $stmt->bind_param("iii", $user_id, $quiz_id, $question_count);
    $stmt->execute();
    $attempt_id = $stmt->insert_id;

    $questions_query = "SELECT id FROM questions WHERE quiz_id = ? ORDER BY question_order, id";
    $stmt = $conn->prepare($questions_query);
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $questions_result = $stmt->get_result();

    while ($question = $questions_result->fetch_assoc()) {
        $insert_answer = "INSERT INTO attempt_answers (attempt_id, question_id) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_answer);
        $stmt->bind_param("ii", $attempt_id, $question['id']);
        $stmt->execute();
    }
}

header("Location: " . dirname($_SERVER['PHP_SELF']) . "/take_quiz.php?attempt_id=$attempt_id");
exit();
?>
