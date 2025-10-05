<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database();
    $conn = $db->connect();

    $attempt_id = intval($_POST['attempt_id']);
    $time_taken = intval($_POST['time_taken']);
    $user_id = $_SESSION['user_id'];

    $verify_query = "SELECT quiz_id, total_questions FROM attempts WHERE id = ? AND user_id = ? AND status = 'in_progress'";
    $stmt = $conn->prepare($verify_query);
    $stmt->bind_param("ii", $attempt_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $attempt = $result->fetch_assoc();
        $quiz_id = $attempt['quiz_id'];
        $total_questions = $attempt['total_questions'];

        $correct_query = "SELECT COUNT(*) as correct
                          FROM attempt_answers aa
                          JOIN options o ON aa.selected_option_id = o.id
                          WHERE aa.attempt_id = ? AND o.is_correct = 1";
        $stmt = $conn->prepare($correct_query);
        $stmt->bind_param("i", $attempt_id);
        $stmt->execute();
        $correct_result = $stmt->get_result();
        $correct_answers = $correct_result->fetch_assoc()['correct'];

        $score = $total_questions > 0 ? ($correct_answers / $total_questions) * 100 : 0;

        $update_query = "UPDATE attempts
                         SET status = 'completed',
                             score = ?,
                             correct_answers = ?,
                             time_taken = ?,
                             submitted_at = NOW()
                         WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("diii", $score, $correct_answers, $time_taken, $attempt_id);
        $stmt->execute();

        echo json_encode([
            'success' => true,
            'quiz_id' => $quiz_id,
            'score' => $score,
            'correct_answers' => $correct_answers,
            'total_questions' => $total_questions
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid attempt']);
    }

    $stmt->close();
    $conn->close();
}
?>
