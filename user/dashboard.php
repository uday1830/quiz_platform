<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

requireLogin();

$db = new Database();
$conn = $db->connect();

$user_id = $_SESSION['user_id'];

$quizzes_query = "SELECT q.*, u.name as created_by_name,
                  (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) as question_count
                  FROM quizzes q
                  JOIN users u ON q.created_by = u.id
                  WHERE q.is_active = 1
                  ORDER BY q.created_at DESC";
$quizzes_result = $conn->query($quizzes_query);

$attempts_query = "SELECT a.*, q.title as quiz_title
                   FROM attempts a
                   JOIN quizzes q ON a.quiz_id = q.id
                   WHERE a.user_id = ? AND a.status = 'completed'
                   ORDER BY a.submitted_at DESC
                   LIMIT 10";
$stmt = $conn->prepare($attempts_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$attempts_result = $stmt->get_result();

$page_title = 'Dashboard';
include __DIR__ . '/../includes/header.php';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/dashboard.php">Quiz Platform</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <span class="navbar-text text-white me-3">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></span>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo dirname(dirname($_SERVER['PHP_SELF'])); ?>/logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h2 class="mb-4">Available Quizzes</h2>

    <?php if ($quizzes_result->num_rows > 0): ?>
        <div class="row">
           <?php while ($quiz = $quizzes_result->fetch_assoc()): ?>
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card quiz-card h-100">
            <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($quiz['title']); ?></h5>
                <p class="card-text"><?php echo htmlspecialchars($quiz['description']); ?></p>
                <div class="mb-3">
                    <small class="text-muted">
                        <strong>Questions:</strong> <?php echo $quiz['question_count']; ?><br>
                        <strong>Time Limit:</strong> <?php echo $quiz['time_limit']; ?> minutes<br>
                        <strong>Created by:</strong> <?php echo htmlspecialchars($quiz['created_by_name']); ?>
                    </small>
                </div>

                <?php if ($quiz['question_count'] > 0): ?>
                    <?php
                    // Check if an in-progress attempt exists for this quiz and user
                    $stmt_attempt = $conn->prepare("SELECT id FROM attempts WHERE user_id = ? AND quiz_id = ? AND status = 'in_progress'");
                    $stmt_attempt->bind_param("ii", $user_id, $quiz['id']);
                    $stmt_attempt->execute();
                    $attempt_result = $stmt_attempt->get_result();

                    if ($attempt_result->num_rows > 0):
                        $attempt = $attempt_result->fetch_assoc();
                        ?>
                        <a href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/take_quiz.php?attempt_id=<?php echo $attempt['id']; ?>" class="btn btn-warning">Continue Quiz</a>
                    <?php else: ?>
                        <a href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/start_quiz.php?quiz_id=<?php echo $quiz['id']; ?>" class="btn btn-primary">Start Quiz</a>
                    <?php
                    endif;
                    $stmt_attempt->close();
                else: ?>
                    <button class="btn btn-secondary" disabled>No Questions Available</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endwhile; ?>

        </div>
    <?php else: ?>
        <div class="alert alert-info">No quizzes available at the moment.</div>
    <?php endif; ?>

    <h2 class="mb-4 mt-5">Your Recent Attempts</h2>

    <?php if ($attempts_result->num_rows > 0): ?>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive attempt-history">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Quiz</th>
                                <th>Score</th>
                                <th>Correct Answers</th>
                                <th>Total Questions</th>
                                <th>Time Taken</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($attempt = $attempts_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($attempt['quiz_title']); ?></td>
                                    <td><strong><?php echo number_format($attempt['score'], 2); ?>%</strong></td>
                                    <td><?php echo $attempt['correct_answers']; ?></td>
                                    <td><?php echo $attempt['total_questions']; ?></td>
                                    <td><?php echo gmdate("i:s", $attempt['time_taken']); ?></td>
                                    <td><?php echo date('M d, Y H:i', strtotime($attempt['submitted_at'])); ?></td>
                                    <td>
                                        <a href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/leaderboard.php?quiz_id=<?php echo $attempt['quiz_id']; ?>" class="btn btn-sm btn-info">Leaderboard</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info">You haven't attempted any quizzes yet.</div>
    <?php endif; ?>
</div>

<?php
$stmt->close();
$conn->close();
include __DIR__ . '/../includes/footer.php';
?>
