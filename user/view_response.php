<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

requireLogin();

$attempt_id = isset($_GET['attempt_id']) ? intval($_GET['attempt_id']) : 0;
$user_id = $_SESSION['user_id'];

if ($attempt_id <= 0) {
    // Redirect if invalid attempt id
    header('Location:' . dirname($_SERVER['PHP_SELF']) . '/dashboard.php');
    exit();
}

$db = new Database();
$conn = $db->connect();

// Fetch attempt info with quiz title
$attempt_q = "SELECT a.*, q.title AS quiz_title FROM attempts a JOIN quizzes q ON a.quiz_id = q.id WHERE a.id = ? AND a.user_id = ?";
$stmt = $conn->prepare($attempt_q);
$stmt->bind_param('ii', $attempt_id, $user_id);
$stmt->execute();
$attempt_res = $stmt->get_result();

if ($attempt_res->num_rows === 0) {
    
    header('Location:' . dirname($_SERVER['PHP_SELF']) . '/dashboard.php');
    exit();
}
$attempt = $attempt_res->fetch_assoc();
$stmt->close();


$questions_q = "
    SELECT q.id AS question_id, q.question_text,
           o.id AS option_id, o.option_text, o.is_correct,
           aa.selected_option_id
    FROM questions q
    LEFT JOIN options o ON q.id = o.question_id
    LEFT JOIN attempt_answers aa ON aa.question_id = q.id AND aa.attempt_id = ?
    WHERE q.quiz_id = ?
    ORDER BY q.question_order, q.id, o.option_order, o.id";

$stmt = $conn->prepare($questions_q);
$stmt->bind_param('ii', $attempt_id, $attempt['quiz_id']);
$stmt->execute();
$result = $stmt->get_result();


$questions = [];
while ($row = $result->fetch_assoc()) {
    $qid = $row['question_id'];
    if (!isset($questions[$qid])) {
        $questions[$qid] = [
            'text' => $row['question_text'],
            'options' => [],
            'selected_option_id' => $row['selected_option_id']
        ];
    }
    $questions[$qid]['options'][] = [
        'id' => $row['option_id'],
        'text' => $row['option_text'],
        'is_correct' => $row['is_correct']
    ];
}
$stmt->close();
$conn->close();

$page_title = "Response Sheet - " . htmlspecialchars($attempt['quiz_title']);
include '../includes/header.php';
?>

<div class="container my-4">
    <h2 class="mb-3">Response Sheet: <?php echo htmlspecialchars($attempt['quiz_title']); ?></h2>
    <p>
        <strong>User:</strong> <?php echo htmlspecialchars($_SESSION['name']); ?><br>
        <strong>Score:</strong> <?php echo number_format($attempt['score'], 2); ?>%<br>
        <strong>Submitted on:</strong> <?php echo date('M d, Y H:i', strtotime($attempt['submitted_at'])); ?>
    </p>
    <hr>

    <?php foreach ($questions as $qid => $q): ?>
        <div class="mb-4">
            <h5><?php echo htmlspecialchars($q['text']); ?></h5>
            <ul class="list-group">
                <?php foreach ($q['options'] as $opt): 
                    $is_selected = $opt['id'] == $q['selected_option_id'];
                    $is_correct = $opt['is_correct'];
                    $class = '';
                    if ($is_selected && $is_correct) {
                        $class = 'list-group-item-success'; 
                    } elseif ($is_selected && !$is_correct) {
                        $class = 'list-group-item-danger'; 
                    } elseif ($is_correct) {
                        $class = 'list-group-item-info'; 
                    }
                ?>
                    <li class="list-group-item <?php echo $class; ?>">
                        <?php if ($is_selected): ?>
                            <strong>&#10148; </strong>
                        <?php else: ?>
                            &nbsp;&nbsp;&nbsp;
                        <?php endif; ?>
                        <?php echo htmlspecialchars($opt['text']); ?>
                        <?php if ($is_correct): ?>
                            <span class="badge bg-primary ms-2">Correct</span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endforeach; ?>
    <a href="./dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
</div>

<?php include '../includes/footer.php'; ?>
